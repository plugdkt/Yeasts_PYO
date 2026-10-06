<?php
function config(?string $key = null)
{
    static $cfg = null;
    $cfg ??= require __DIR__ . '/config.php';
    return $key === null ? $cfg : ($cfg[$key] ?? null);
}

function e($v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $path = '', array $query = []): string
{
    $u = rtrim(config('base_url'), '/') . '/' . ltrim($path, '/');
    $query = array_filter($query, fn($v) => $v !== '' && $v !== null);
    return $query ? $u . '?' . http_build_query($query) : $u;
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function flash(?string $msg = null, string $type = 'success')
{
    if ($msg !== null) {
        $_SESSION['flash'][] = [$type, $msg];
        return null;
    }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), $_POST['_csrf'] ?? '')) {
        http_response_code(403);
        exit('CSRF token ไม่ถูกต้อง กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง');
    }
}

// ใช้ชื่อพารามิเตอร์ขึ้นต้นด้วย __ เพื่อไม่ให้ extract() ทับ (เช่นตัวแปร $name ของ view)
function view(string $__view, array $vars = [], string $__layout = 'layout'): void
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require __DIR__ . "/../views/$__view.php";
    $content = ob_get_clean();
    require __DIR__ . "/../views/$__layout.php";
}

function partial(string $__partial, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    require __DIR__ . "/../views/partials/$__partial.php";
}

function abort(int $code = 404, string $msg = 'ไม่พบหน้าที่ต้องการ'): never
{
    http_response_code($code);
    view('error', ['title' => "$code", 'code' => $code, 'msg' => $msg]);
    exit;
}

function input(string $k, $default = '')
{
    $v = $_GET[$k] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

/** แปลงค่าว่างจากฟอร์มเป็น null */
function nn($v)
{
    if (is_string($v)) $v = trim($v);
    return ($v === '' || $v === null) ? null : $v;
}

function species_name(array $row, string $g = 'genus', string $s = 'epithet'): string
{
    if (empty($row[$g])) return '<span class="text-muted">ยังไม่ระบุชนิด</span>';
    return '<i>' . e($row[$g] . ' ' . $row[$s]) . '</i>';
}

/** พิกัดสำหรับแสดงผล: ปัดเศษถ้าไม่ได้ล็อกอิน */
function coord($v): ?float
{
    if ($v === null || $v === '') return null;
    return is_logged_in() ? (float) $v : round((float) $v, config('public_coord_precision'));
}

function paginate(int $total, int $page, int $per): array
{
    $pages = max(1, (int) ceil($total / $per));
    $page = min(max(1, $page), $pages);
    return ['total' => $total, 'page' => $page, 'pages' => $pages, 'per' => $per, 'offset' => ($page - 1) * $per];
}

function result_badge(?string $r): string
{
    $r = $r ?? '?';
    $map = ['+' => 'success', '-' => 'secondary', 'w' => 'warning', 'd' => 'info', 's' => 'info', 'v' => 'primary', '?' => 'light'];
    $cls = $map[$r] ?? (str_contains($r, ',') ? 'primary' : 'light');
    return '<span class="badge rb text-bg-' . $cls . '">' . e($r) . '</span>';
}

function thai_date(?string $d): string
{
    if (!$d) return '–';
    $m = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    $t = strtotime($d);
    return (int) date('j', $t) . ' ' . $m[(int) date('n', $t)] . ' ' . (date('Y', $t) + 543);
}

function audit(string $action, string $entity, ?int $id, string $detail = ''): void
{
    db_insert('audit_log', [
        'user_id' => current_user()['id'] ?? null, 'action' => $action,
        'entity' => $entity, 'entity_id' => $id, 'detail' => mb_substr($detail, 0, 500),
    ]);
}

function test_groups(): array
{
    return [
        'fermentation' => 'Fermentation (การหมัก)',
        'carbon'       => 'Assimilation – carbon (การใช้แหล่งคาร์บอน)',
        'nitrogen'     => 'Assimilation – nitrogen (การใช้แหล่งไนโตรเจน)',
        'additional'   => 'Additional tests (การทดสอบอื่น ๆ)',
        'temperature'  => 'Growth temperatures (อุณหภูมิการเจริญ)',
        'antimycotic'  => 'Antimycotics (ความไวต่อยาต้านเชื้อรา)',
    ];
}

/** locus นี้ครอบคลุมบริเวณ D1/D2 ของ LSU rRNA หรือไม่ */
function is_d1d2(?string $locus): bool
{
    return (bool) preg_match('#D1/?D2|LSU|26S|28S#i', (string) $locus);
}

/** แยกลำดับเป็น [D1/D2, อื่น ๆ] */
function split_d1d2(array $sequences): array
{
    $d = array_values(array_filter($sequences, fn($q) => is_d1d2($q['locus'])));
    $o = array_values(array_filter($sequences, fn($q) => !is_d1d2($q['locus'])));
    return [$d, $o];
}

function clean_sequence(string $s): string
{
    // ตัดบรรทัด header FASTA และอักขระที่ไม่ใช่เบส
    $s = preg_replace('/^>.*$/m', '', $s);
    return strtoupper(preg_replace('/[^A-Za-z]/', '', $s));
}
