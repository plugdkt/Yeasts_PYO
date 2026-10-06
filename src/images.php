<?php
// ======================= รูปภาพยีสต์ =======================

const IMAGE_META_FIELDS = ['image_type', 'caption', 'medium', 'incubation_temp', 'incubation_days', 'technique',
    'magnification', 'scale_bar', 'photographer', 'taken_date', 'credit', 'license', 'sort_order'];

const THUMB_WIDTH = 480;

function image_types(): array
{
    return [
        'colony' => 'โคโลนี (Colony)',
        'cell'   => 'เซลล์ (Cells)',
        'hyphae' => 'เส้นใย (Pseudohyphae / hyphae)',
        'spore'  => 'สปอร์ (Asci / ascospores / basidiospores)',
        'other'  => 'อื่น ๆ',
    ];
}

function image_techniques(): array
{
    return ['Plate photograph', 'Stereo microscope', 'Bright field', 'Phase contrast', 'DIC', 'Fluorescence', 'SEM', 'TEM'];
}

function image_media(): array
{
    return ['YM agar', 'YPD agar', 'PDA', 'Malt extract agar (MEA)', 'Corn meal agar (Dalmau plate)', 'Potato dextrose agar',
        'McClary acetate agar', 'Gorodkowa agar', 'V8 agar', 'YM broth'];
}

/** อ่านข้อมูลประกอบรูปจากฟอร์ม */
function image_meta_from_post(array $p): array
{
    $m = [];
    foreach (IMAGE_META_FIELDS as $f) $m[$f] = nn($p[$f] ?? null);
    if (!isset(image_types()[$m['image_type'] ?? ''])) $m['image_type'] = 'other';
    if ($m['taken_date'] !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $m['taken_date'])) $m['taken_date'] = null;
    $m['sort_order'] = (int) ($m['sort_order'] ?? 0);
    foreach ($m as $k => $v) if (is_string($v)) $m[$k] = mb_substr($v, 0, $k === 'caption' || $k === 'credit' ? 255 : 150);
    return $m;
}

/** ตรวจไฟล์ที่อัปโหลด คืนค่า [ext, error] */
function image_check_upload(array $f): array
{
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'อัปโหลดไม่สำเร็จ (รหัส ' . $f['error'] . ')'];
    if ($f['size'] > config('upload_max')) return [null, 'ไฟล์ใหญ่เกิน 10 MB'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || !@getimagesize($f['tmp_name'])) return [null, 'รองรับเฉพาะไฟล์ JPG, PNG, WEBP'];
    return [$ext, null];
}

/** โหลดรูปเป็น GD image พร้อมหมุนตาม EXIF (รูปจากมือถือ/กล้อง) */
function image_gd_load(string $path, string $ext)
{
    $img = match ($ext) {
        'jpg'  => @imagecreatefromjpeg($path),
        'png'  => @imagecreatefrompng($path),
        'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
        default => false,
    };
    if ($img && $ext === 'jpg' && function_exists('exif_read_data')) {
        $o = (@exif_read_data($path)['Orientation'] ?? 1);
        $img = match ((int) $o) { 3 => imagerotate($img, 180, 0), 6 => imagerotate($img, -90, 0), 8 => imagerotate($img, 90, 0), default => $img };
    }
    return $img;
}

/**
 * สร้างภาพย่อกว้าง THUMB_WIDTH px (JPEG) คืนค่า path สัมพัทธ์ หรือ null ถ้าเซิร์ฟเวอร์ไม่มี GD
 */
function image_make_thumb(string $src_abs, string $ext, string $thumb_name): ?string
{
    if (!function_exists('imagecreatetruecolor')) return null;
    $img = image_gd_load($src_abs, $ext);
    if (!$img) return null;
    $w = imagesx($img);
    $h = imagesy($img);
    $tw = min(THUMB_WIDTH, $w);
    $th = (int) round($h * $tw / $w);
    $t = imagecreatetruecolor($tw, $th);
    imagefill($t, 0, 0, imagecolorallocate($t, 255, 255, 255)); // พื้นขาวแทนความโปร่งใสของ PNG
    imagecopyresampled($t, $img, 0, 0, 0, 0, $tw, $th, $w, $h);
    $dir = config('upload_dir') . '/thumbs';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    imagejpeg($t, "$dir/$thumb_name", 82);
    imagedestroy($img);
    imagedestroy($t);
    return 'uploads/thumbs/' . $thumb_name;
}

/** บันทึกไฟล์ 1 รูป + ข้อมูลประกอบ คืนค่า id */
function image_store(array $f, string $ext, string $type, int $id, array $meta): int
{
    $base = $type . '_' . $id . '_' . bin2hex(random_bytes(6));
    $dir = config('upload_dir');
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $abs = "$dir/$base.$ext";
    if (!move_uploaded_file($f['tmp_name'], $abs)) throw new RuntimeException('ย้ายไฟล์ไม่สำเร็จ');
    [$w, $h] = getimagesize($abs);
    $thumb = image_make_thumb($abs, $ext, "$base.jpg");
    $row = [$type . '_id' => $id, 'file_path' => "uploads/$base.$ext", 'thumb_path' => $thumb,
        'width' => $w, 'height' => $h, 'created_by' => current_user()['id'] ?? null] + $meta;
    try {
        return db_insert('images', $row);
    } catch (Throwable $ex) {
        image_delete_files($row); // ไม่ให้เหลือไฟล์กำพร้าเมื่อบันทึกไม่สำเร็จ
        throw $ex;
    }
}

function image_delete_files(array $img): void
{
    foreach (['file_path', 'thumb_path'] as $k) {
        if ($img[$k] && str_starts_with($img[$k], 'uploads/')) @unlink(__DIR__ . '/../public/' . $img[$k]);
    }
}

/** URL ของภาพย่อ (ถ้าไม่มีใช้ไฟล์ต้นฉบับ) */
function image_thumb_url(array $img): string
{
    return url($img['thumb_path'] ?: $img['file_path']);
}

/** ข้อความสรุปเงื่อนไขการถ่าย เช่น "YM agar · 25 °C · 3 วัน · Phase contrast · ×1000" */
function image_conditions(array $im): string
{
    return implode(' · ', array_filter([
        $im['medium'],
        $im['incubation_temp'] !== null ? $im['incubation_temp'] . ' °C' : null,
        $im['incubation_days'] !== null ? $im['incubation_days'] . ' วัน' : null,
        $im['technique'],
        $im['magnification'] ? (preg_match('/^\d/', $im['magnification']) ? '×' : '') . $im['magnification'] : null,
    ], fn($v) => $v !== null && $v !== ''));
}

/** จัดกลุ่มรูปตามประเภทตามลำดับใน image_types() */
function images_grouped(array $images): array
{
    $g = [];
    foreach (array_keys(image_types()) as $t) $g[$t] = [];
    foreach ($images as $im) $g[$im['image_type'] ?? 'other'][] = $im;
    return array_filter($g);
}
