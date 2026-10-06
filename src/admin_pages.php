<?php
// ======================= ส่วนทีมวิจัย =======================

const SPECIES_FIELDS = ['kingdom', 'subkingdom', 'phylum', 'subphylum', 'class_name', 'order_name', 'family', 'genus', 'epithet',
    'authority', 'year_described', 'mycobank_no', 'ncbi_taxid', 'current_name', 'basionym', 'phylogenetic_placement', 'comments',
    'ecology', 'applications', 'growth_description', 'cell_shape', 'cell_size', 'filaments', 'asexual_reproduction',
    'sexual_reproduction', 'ascospores', 'physiology_summary', 'coq_system', 'ncbi_lineage',
    'd1d2_ref_accession', 'd1d2_ref_strain'];

const STRAIN_FIELDS = ['strain_code', 'other_codes', 'species_id', 'identification_status', 'source_id', 'substrate',
    'district_id', 'subdistrict', 'locality', 'latitude', 'longitude', 'elevation_m', 'collection_date', 'collector', 'isolator',
    'isolation_method', 'identified_by', 'identification_method', 'storage', 'availability', 'remarks'];

function admin_dashboard(): void
{
    require_login();
    $counts = q_one("SELECT (SELECT COUNT(*) FROM species) species, (SELECT COUNT(*) FROM strains) strains,
        (SELECT COUNT(*) FROM sequences) sequences, (SELECT COUNT(*) FROM strains WHERE species_id IS NULL) unidentified,
        (SELECT COUNT(*) FROM strains WHERE latitude IS NULL) no_coords,
        (SELECT COUNT(*) FROM strains st WHERE NOT EXISTS (SELECT 1 FROM sequences s WHERE s.strain_id = st.id)) no_seq,
        (SELECT COUNT(*) FROM strains WHERE is_demo = 1) demo");
    $log = q_all('SELECT a.*, u.full_name FROM audit_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 15');
    $species = q_all('SELECT s.id, s.genus, s.epithet, s.family, s.ncbi_taxid, (SELECT COUNT(*) FROM strains x WHERE x.species_id = s.id) n
        FROM species s ORDER BY genus, epithet');
    view('admin/dashboard', compact('counts', 'log', 'species') + ['title' => 'จัดการข้อมูล']);
}

// ---------------- Species ----------------
function admin_species_form(string $id): void
{
    require_login();
    $sp = $id === 'new' ? array_fill_keys(SPECIES_FIELDS, null) + ['id' => null, 'kingdom' => 'Fungi', 'subkingdom' => 'Dikarya']
        : q_one('SELECT * FROM species WHERE id = ?', [$id]);
    if (!$sp) abort();
    $synonyms = $sp['id'] ? q_all('SELECT * FROM synonyms WHERE species_id = ?', [$id]) : [];
    $bib = $sp['id'] ? q_all('SELECT b.* FROM bibliography b JOIN species_bibliography x ON x.bib_id = b.id WHERE x.species_id = ? ORDER BY b.year', [$id]) : [];
    $images = $sp['id'] ? q_all('SELECT * FROM images WHERE species_id = ? ORDER BY sort_order, id', [$id]) : [];
    view('admin/species_form', compact('sp', 'synonyms', 'bib', 'images') + ['title' => $sp['id'] ? 'แก้ไข ' . $sp['genus'] . ' ' . $sp['epithet'] : 'เพิ่มชนิดใหม่']);
}

function admin_species_save(string $id): void
{
    require_login();
    $data = [];
    foreach (SPECIES_FIELDS as $f) $data[$f] = nn($_POST[$f] ?? null);
    $data['genus'] = ucfirst(strtolower((string) $data['genus']));
    $data['epithet'] = strtolower((string) $data['epithet']);
    if (!$data['genus'] || !$data['epithet']) {
        flash('ต้องระบุ Genus และ epithet', 'danger');
        redirect("admin/species/$id");
    }
    $dup = q_val('SELECT id FROM species WHERE genus = ? AND epithet = ? AND id <> ?', [$data['genus'], $data['epithet'], (int) $id]);
    if ($dup) {
        flash("มีชนิด {$data['genus']} {$data['epithet']} อยู่แล้ว", 'danger');
        redirect("admin/species/$dup");
    }
    if ($data['ncbi_taxid'] && !empty($_POST['ncbi_synced'])) $data['ncbi_synced_at'] = date('Y-m-d H:i:s');
    $data['updated_by'] = current_user()['id'];
    if ($id === 'new') {
        $data['created_by'] = current_user()['id'];
        $id = db_insert('species', $data);
        audit('create', 'species', $id, "{$data['genus']} {$data['epithet']}");
    } else {
        $id = (int) $id;
        db_update('species', $id, $data);
        audit('update', 'species', $id, "{$data['genus']} {$data['epithet']}");
    }
    save_synonyms($id, $_POST['synonyms'] ?? '');
    save_bibliography($id, $_POST['bibliography'] ?? '');
    flash('บันทึกข้อมูลชนิดเรียบร้อย');
    redirect("admin/species/$id");
}

/** บรรทัดละ 1 ชื่อ รูปแบบ "ชื่อ | MB#เลข" */
function save_synonyms(int $id, string $text): void
{
    q('DELETE FROM synonyms WHERE species_id = ?', [$id]);
    foreach (preg_split('/\R/', $text) as $line) {
        if (!trim($line)) continue;
        [$name, $mb] = array_pad(array_map('trim', explode('|', $line, 2)), 2, null);
        db_insert('synonyms', ['species_id' => $id, 'name' => $name, 'mycobank_no' => nn(preg_replace('/^MB#?/i', '', (string) $mb))]);
    }
}

/** บรรทัดละ 1 เอกสารอ้างอิง ถ้ามี DOI ในบรรทัดจะดึงแยกเก็บ */
function save_bibliography(int $id, string $text): void
{
    q('DELETE FROM species_bibliography WHERE species_id = ?', [$id]);
    foreach (preg_split('/\R/', $text) as $line) {
        $line = trim($line);
        if (!$line) continue;
        $bid = q_val('SELECT id FROM bibliography WHERE citation = ?', [$line]);
        if (!$bid) {
            preg_match('#\b(10\.\d{4,9}/\S+[^\s.,;])#', $line, $doi);
            preg_match('/\((\d{4})\)|\b(19|20)\d{2}\b/', $line, $yr);
            $year = $yr ? (int) preg_replace('/\D/', '', $yr[1] ?? $yr[0]) : null;
            $bid = db_insert('bibliography', ['citation' => $line, 'doi' => $doi[1] ?? null, 'year' => $year]);
        }
        q('INSERT IGNORE INTO species_bibliography (species_id, bib_id) VALUES (?, ?)', [$id, $bid]);
    }
}

function admin_species_delete(string $id): void
{
    require_admin();
    $sp = q_one('SELECT genus, epithet FROM species WHERE id = ?', [$id]);
    if (!$sp) abort();
    q('DELETE FROM species WHERE id = ?', [$id]);
    audit('delete', 'species', (int) $id, "{$sp['genus']} {$sp['epithet']}");
    flash('ลบชนิดแล้ว (strain ที่เคยผูกไว้จะกลายเป็น "ยังไม่ระบุชนิด")', 'warning');
    redirect('admin');
}

function api_ncbi_taxonomy(): void
{
    require_login();
    header('Content-Type: application/json; charset=utf-8');
    try {
        $taxid = ctype_digit(input('taxid')) ? (int) input('taxid') : ncbi_taxid(input('name'));
        if (!$taxid) {
            echo json_encode(['ok' => false, 'error' => 'ไม่พบชื่อนี้ใน NCBI Taxonomy']);
            return;
        }
        $tax = ncbi_taxonomy($taxid);
        echo json_encode(['ok' => (bool) $tax, 'name' => $tax['name'] ?? null, 'rank' => $tax['rank'] ?? null,
            'fields' => $tax ? ncbi_taxonomy_to_species($tax) : null], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $ex) {
        echo json_encode(['ok' => false, 'error' => 'เชื่อมต่อ NCBI ไม่ได้: ' . $ex->getMessage()]);
    }
}

/** ค้นเลข accession ลำดับ D1/D2 ของ type strain จาก GenBank */
function api_ncbi_d1d2(): void
{
    require_login();
    header('Content-Type: application/json; charset=utf-8');
    try {
        $items = ncbi_d1d2_type_candidates(input('name'));
        echo json_encode(['ok' => (bool) $items, 'items' => $items,
            'error' => $items ? null : 'ไม่พบลำดับ D1/D2 ของ type material ใน GenBank'], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $ex) {
        echo json_encode(['ok' => false, 'error' => 'เชื่อมต่อ NCBI ไม่ได้: ' . $ex->getMessage()]);
    }
}

// ---------------- Strain ----------------
function admin_strain_form(string $id): void
{
    require_login();
    if ($id === 'new') {
        $st = array_fill_keys(STRAIN_FIELDS, null) + ['id' => null, 'identification_status' => 'tentative', 'availability' => 'available'];
        $st['strain_code'] = next_strain_code();
        if (ctype_digit(input('species'))) $st['species_id'] = (int) input('species');
    } else {
        $st = q_one('SELECT * FROM strains WHERE id = ?', [$id]);
        if (!$st) abort();
    }
    // คืนค่าที่กรอกไว้เมื่อบันทึกไม่ผ่าน
    if ($old = $_SESSION['old'] ?? null) {
        unset($_SESSION['old']);
        foreach (STRAIN_FIELDS as $f) if (array_key_exists($f, $old)) $st[$f] = $old[$f];
        $st['is_type_strain'] = empty($old['is_type_strain']) ? 0 : 1;
    }
    $sequences = $st['id'] ? q_all('SELECT * FROM sequences WHERE strain_id = ? ORDER BY locus', [$id]) : [];
    $images = $st['id'] ? q_all('SELECT * FROM images WHERE strain_id = ? ORDER BY sort_order, id', [$id]) : [];
    view('admin/strain_form', compact('st', 'sequences', 'images') + lookup_lists()
        + ['title' => $st['id'] ? 'แก้ไข ' . $st['strain_code'] : 'เพิ่มสายพันธุ์ใหม่']);
}

function next_strain_code(): string
{
    $max = q_val("SELECT MAX(CAST(SUBSTRING(strain_code, 6) AS UNSIGNED)) FROM strains WHERE strain_code REGEXP '^PYO-Y[0-9]+$'");
    return sprintf('PYO-Y%04d', ((int) $max) + 1);
}

function admin_strain_save(string $id): void
{
    require_login();
    $data = [];
    foreach (STRAIN_FIELDS as $f) $data[$f] = nn($_POST[$f] ?? null);
    $data['is_type_strain'] = empty($_POST['is_type_strain']) ? 0 : 1;
    $errors = validate_strain($data, $id === 'new' ? 0 : (int) $id);
    if ($errors) {
        foreach ($errors as $er) flash($er, 'danger');
        $_SESSION['old'] = $_POST;
        redirect("admin/strain/$id");
    }
    $data['updated_by'] = current_user()['id'];
    if ($id === 'new') {
        $data['created_by'] = current_user()['id'];
        $id = db_insert('strains', $data);
        audit('create', 'strain', $id, $data['strain_code']);
    } else {
        $id = (int) $id;
        db_update('strains', $id, $data);
        audit('update', 'strain', $id, $data['strain_code']);
    }
    flash('บันทึกสายพันธุ์เรียบร้อย');
    redirect("admin/strain/$id");
}

function validate_strain(array $d, int $id): array
{
    $e = [];
    if (!$d['strain_code'] || !preg_match('/^[\w\-.]{2,40}$/', $d['strain_code'])) $e[] = 'รหัสสายพันธุ์ต้องเป็น A-Z, 0-9, - _ . ความยาว 2–40 ตัว';
    elseif (q_val('SELECT id FROM strains WHERE strain_code = ? AND id <> ?', [$d['strain_code'], $id])) $e[] = "รหัส {$d['strain_code']} ถูกใช้แล้ว";
    if (($d['latitude'] === null) !== ($d['longitude'] === null)) $e[] = 'ต้องกรอกทั้งละติจูดและลองจิจูด';
    if ($d['latitude'] !== null && (!is_numeric($d['latitude']) || abs((float) $d['latitude']) > 90)) $e[] = 'ละติจูดไม่ถูกต้อง';
    if ($d['longitude'] !== null && (!is_numeric($d['longitude']) || abs((float) $d['longitude']) > 180)) $e[] = 'ลองจิจูดไม่ถูกต้อง';
    if ($d['collection_date'] !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d['collection_date'])) $e[] = 'วันที่เก็บต้องเป็นรูปแบบ YYYY-MM-DD (ค.ศ.)';
    if ($d['elevation_m'] !== null && !is_numeric($d['elevation_m'])) $e[] = 'ความสูงต้องเป็นตัวเลข';
    return $e;
}

function admin_strain_delete(string $id): void
{
    require_admin();
    $code = q_val('SELECT strain_code FROM strains WHERE id = ?', [$id]);
    if (!$code) abort();
    q('DELETE FROM strains WHERE id = ?', [$id]);
    audit('delete', 'strain', (int) $id, $code);
    flash("ลบสายพันธุ์ $code แล้ว", 'warning');
    redirect('admin');
}

// ---------------- Sequence ----------------
function admin_sequence_add(string $strain_id): void
{
    require_login();
    if (!q_val('SELECT id FROM strains WHERE id = ?', [$strain_id])) abort();
    $acc = nn($_POST['accession'] ?? null);
    $seq = clean_sequence($_POST['sequence'] ?? '');
    $row = [
        'strain_id' => (int) $strain_id, 'locus' => nn($_POST['locus'] ?? null) ?? 'ITS', 'accession' => $acc,
        'sequence' => $seq ?: null, 'length_bp' => $seq ? strlen($seq) : null,
        'blast_top_hit' => nn($_POST['blast_top_hit'] ?? null),
        'blast_identity' => nn($_POST['blast_identity'] ?? null), 'blast_coverage' => nn($_POST['blast_coverage'] ?? null),
    ];
    if ($acc && !$seq) {
        try {
            if ($g = ncbi_sequence($acc)) {
                $row = array_merge($row, ['sequence' => $g['sequence'], 'length_bp' => $g['length'], 'ncbi_title' => mb_substr($g['title'], 0, 500),
                    'ncbi_synced_at' => date('Y-m-d H:i:s')]);
            } else {
                flash("ไม่พบ accession $acc ใน GenBank — บันทึกเฉพาะเลข accession", 'warning');
            }
        } catch (Throwable $ex) {
            flash('เชื่อมต่อ NCBI ไม่ได้ — บันทึกเฉพาะเลข accession ลองกด "ซิงก์" อีกครั้งภายหลัง', 'warning');
        }
    }
    if (!$row['accession'] && !$row['sequence']) {
        flash('ต้องกรอก accession หรือวางลำดับเบสอย่างใดอย่างหนึ่ง', 'danger');
        redirect("admin/strain/$strain_id#sequences");
    }
    $sid = db_insert('sequences', $row);
    audit('create', 'sequence', $sid, "{$row['locus']} {$row['accession']}");
    flash('เพิ่มลำดับ DNA แล้ว');
    redirect("admin/strain/$strain_id#sequences");
}

function admin_sequence_sync(string $id): void
{
    require_login();
    $s = q_one('SELECT * FROM sequences WHERE id = ?', [$id]);
    if (!$s || !$s['accession']) abort();
    try {
        $g = ncbi_sequence($s['accession']);
        if ($g) {
            db_update('sequences', (int) $id, ['sequence' => $g['sequence'], 'length_bp' => $g['length'],
                'ncbi_title' => mb_substr($g['title'], 0, 500), 'ncbi_synced_at' => date('Y-m-d H:i:s')]);
            flash("ซิงก์ {$s['accession']} จาก GenBank แล้ว ({$g['length']} bp)");
        } else {
            flash("ไม่พบ {$s['accession']} ใน GenBank", 'warning');
        }
    } catch (Throwable $ex) {
        flash('เชื่อมต่อ NCBI ไม่ได้: ' . $ex->getMessage(), 'danger');
    }
    redirect("admin/strain/{$s['strain_id']}#sequences");
}

function admin_sequence_delete(string $id): void
{
    require_login();
    $s = q_one('SELECT * FROM sequences WHERE id = ?', [$id]);
    if (!$s) abort();
    q('DELETE FROM sequences WHERE id = ?', [$id]);
    audit('delete', 'sequence', (int) $id, "{$s['locus']} {$s['accession']}");
    flash('ลบลำดับแล้ว', 'warning');
    redirect("admin/strain/{$s['strain_id']}#sequences");
}

// ---------------- Phenotype ----------------
function admin_phenotype_form(string $type, string $id): void
{
    require_login();
    $entity = $type === 'species'
        ? q_one("SELECT id, CONCAT(genus,' ',epithet) label FROM species WHERE id = ?", [$id])
        : q_one('SELECT id, strain_code label FROM strains WHERE id = ?', [$id]);
    if (!$entity) abort();
    $pheno = phenotype_matrix($type . '_id', (int) $id);
    view('admin/phenotype_form', compact('type', 'entity', 'pheno') + ['title' => 'ผลทดสอบทางสรีรวิทยา: ' . $entity['label']]);
}

function admin_phenotype_save(string $type, string $id): void
{
    require_login();
    $col = $type . '_id';
    $method = nn($_POST['method'] ?? null);
    db()->beginTransaction();
    q("DELETE FROM phenotype_results WHERE $col = ?", [$id]);
    foreach ($_POST['r'] ?? [] as $test_id => $result) {
        $result = trim((string) $result);
        if ($result === '' || !ctype_digit((string) $test_id)) continue;
        db_insert('phenotype_results', ['test_id' => (int) $test_id, $col => (int) $id, 'result' => mb_substr($result, 0, 30), 'method' => $method]);
    }
    db()->commit();
    audit('update', "phenotype_$type", (int) $id);
    flash('บันทึกผลทดสอบแล้ว');
    redirect($type === 'species' ? "species/$id#physiology" : 'strain/' . q_val('SELECT strain_code FROM strains WHERE id = ?', [$id]) . '#physiology');
}

// ---------------- Images ----------------
function image_back(array $img): string
{
    return $img['species_id'] ? "admin/species/{$img['species_id']}#images" : "admin/strain/{$img['strain_id']}#images";
}

/** อัปโหลดได้หลายไฟล์พร้อมกัน ใช้ข้อมูลประกอบชุดเดียวกัน */
function admin_image_upload(string $type, string $id): void
{
    require_login();
    $back = ($type === 'species' ? "admin/species/$id" : "admin/strain/$id") . '#images';
    $exists = q_val($type === 'species' ? 'SELECT 1 FROM species WHERE id = ?' : 'SELECT 1 FROM strains WHERE id = ?', [$id]);
    if (!$exists) abort();
    $files = [];
    $up = $_FILES['images'] ?? null;
    if ($up && is_array($up['name'])) {
        foreach ($up['name'] as $i => $n) {
            if ($up['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
            $files[] = ['name' => $n, 'tmp_name' => $up['tmp_name'][$i], 'error' => $up['error'][$i], 'size' => $up['size'][$i]];
        }
    }
    if (!$files) {
        // เมื่อขนาดรวมเกิน post_max_size PHP จะทิ้งข้อมูลทั้งหมด จึงมาถึงตรงนี้ด้วย
        flash('ไม่พบไฟล์รูป — กรุณาเลือกไฟล์ (ขนาดรวมต่อครั้งไม่เกิน 100 MB)', 'danger');
        redirect($back);
    }
    $meta = image_meta_from_post($_POST);
    $ok = 0;
    foreach ($files as $f) {
        [$ext, $err] = image_check_upload($f);
        if ($err) {
            flash($f['name'] . ': ' . $err, 'danger');
            continue;
        }
        $iid = image_store($f, $ext, $type, (int) $id, $meta);
        audit('create', 'image', $iid, $f['name']);
        $ok++;
    }
    if ($ok) flash("อัปโหลดรูปแล้ว $ok ไฟล์" . (function_exists('imagecreatetruecolor') ? '' : ' (เซิร์ฟเวอร์ไม่มี GD จึงไม่ได้สร้างภาพย่อ)'));
    redirect($back);
}

function admin_image_update(string $id): void
{
    require_login();
    $img = q_one('SELECT * FROM images WHERE id = ?', [$id]);
    if (!$img) abort();
    db_update('images', (int) $id, image_meta_from_post($_POST));
    audit('update', 'image', (int) $id, $img['file_path']);
    flash('บันทึกข้อมูลรูปแล้ว');
    redirect(image_back($img));
}

function admin_image_delete(string $id): void
{
    require_login();
    $img = q_one('SELECT * FROM images WHERE id = ?', [$id]);
    if (!$img) abort();
    image_delete_files($img);
    q('DELETE FROM images WHERE id = ?', [$id]);
    audit('delete', 'image', (int) $id, $img['file_path']);
    flash('ลบรูปแล้ว', 'warning');
    redirect(image_back($img));
}

// ---------------- Import CSV ----------------
const IMPORT_COLUMNS = ['strain_code', 'other_codes', 'genus', 'epithet', 'identification_status', 'source', 'substrate',
    'district', 'subdistrict', 'locality', 'latitude', 'longitude', 'elevation_m', 'collection_date', 'collector', 'isolator',
    'isolation_method', 'identified_by', 'identification_method', 'storage', 'remarks',
    'd1d2_accession', 'd1d2_sequence', 'its_accession', 'its_sequence'];

function admin_import_template(): void
{
    require_login();
    csv_out('pyo_import_template.csv', IMPORT_COLUMNS, [[
        'PYO-Y0001', 'TBRC 12345', 'Saccharomyces', 'cerevisiae', 'confirmed', 'ผลไม้', 'ผลลำไยสุก', 'เมืองพะเยา', 'แม่กา',
        'มหาวิทยาลัยพะเยา', '19.0290', '99.8960', '450', '2025-07-15', 'ชื่อผู้เก็บ', 'ชื่อผู้แยก', 'enrichment YM broth',
        'ชื่อผู้ระบุ', 'ITS + D1/D2 sequencing', '-80°C 20% glycerol', '', '', '', '', '',
    ]]);
}

function admin_import_form(): void
{
    require_login();
    view('admin/import', ['title' => 'นำเข้าข้อมูลจาก CSV', 'preview' => $_SESSION['import'] ?? null]);
}

function admin_import_do(): void
{
    require_login();
    if (($_POST['step'] ?? '') === 'cancel') {
        unset($_SESSION['import']);
        redirect('admin/import');
    }
    if (($_POST['step'] ?? '') === 'commit') {
        import_commit();
        return;
    }
    $f = $_FILES['csv'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
        flash('กรุณาเลือกไฟล์ CSV', 'danger');
        redirect('admin/import');
    }
    $_SESSION['import'] = import_parse($f['tmp_name'], !empty($_POST['create_species']));
    redirect('admin/import');
}

function import_parse(string $file, bool $create_species): array
{
    $fh = fopen($file, 'r');
    $first = fgets($fh);
    $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
    $header = array_map(fn($h) => strtolower(trim($h)), str_getcsv($first));
    $missing = array_diff(['strain_code'], $header);
    $districts = [];
    foreach (q_all('SELECT * FROM districts') as $d) {
        $districts[mb_strtolower($d['name_th'])] = $districts[strtolower($d['name_en'])] = $d['id'];
        $districts[mb_strtolower('อ.' . $d['name_th'])] = $districts[mb_strtolower('อำเภอ' . $d['name_th'])] = $d['id'];
    }
    $sources = [];
    foreach (q_all('SELECT * FROM sources') as $s) {
        $sources[mb_strtolower($s['name_th'])] = $sources[strtolower($s['name_en'])] = $s['id'];
    }
    $rows = [];
    $seen = [];
    $line = 1;
    while (!$missing && ($cells = fgetcsv($fh)) !== false) {
        $line++;
        if (!array_filter($cells, fn($c) => trim((string) $c) !== '')) continue;
        $r = array_combine($header, array_pad(array_slice($cells, 0, count($header)), count($header), ''));
        $r = array_map(fn($v) => trim((string) $v), $r);
        $err = [];
        $warn = [];
        $code = $r['strain_code'] ?? '';
        if (!preg_match('/^[\w\-.]{2,40}$/', $code)) $err[] = 'รหัสไม่ถูกต้อง';
        elseif (isset($seen[$code])) $err[] = "รหัสซ้ำในไฟล์ (บรรทัด {$seen[$code]})";
        elseif (q_val('SELECT 1 FROM strains WHERE strain_code = ?', [$code])) $err[] = 'รหัสมีในระบบแล้ว';
        $seen[$code] = $line;

        $species_id = null;
        if (!empty($r['genus']) && !empty($r['epithet'])) {
            $species_id = q_val('SELECT id FROM species WHERE genus = ? AND epithet = ?', [ucfirst(strtolower($r['genus'])), strtolower($r['epithet'])]);
            if (!$species_id) {
                if ($create_species) $warn[] = "จะสร้างชนิดใหม่ {$r['genus']} {$r['epithet']}";
                else $err[] = "ไม่พบชนิด {$r['genus']} {$r['epithet']} (ติ๊ก \"สร้างชนิดใหม่อัตโนมัติ\" หรือเพิ่มชนิดก่อน)";
            }
        }
        $district_id = null;
        if (!empty($r['district'])) {
            $district_id = $districts[mb_strtolower(str_replace(' ', '', $r['district']))] ?? $districts[mb_strtolower($r['district'])] ?? null;
            if (!$district_id) $err[] = "ไม่รู้จักอำเภอ \"{$r['district']}\"";
        }
        $source_id = null;
        if (!empty($r['source'])) {
            $source_id = $sources[mb_strtolower($r['source'])] ?? null;
            if (!$source_id) $warn[] = "ไม่รู้จักประเภทแหล่ง \"{$r['source']}\" (จะเว้นว่าง)";
        }
        $lat = $r['latitude'] ?? '';
        $lng = $r['longitude'] ?? '';
        if ($lat !== '' || $lng !== '') {
            if (!is_numeric($lat) || !is_numeric($lng)) $err[] = 'พิกัดไม่ใช่ตัวเลข';
            elseif ($lat < 18.6 || $lat > 19.9 || $lng < 99.5 || $lng > 100.7) $warn[] = 'พิกัดอยู่นอกจังหวัดพะเยา';
        }
        $date = $r['collection_date'] ?? '';
        if ($date !== '') {
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $date, $m)) {
                $y = (int) $m[3] > 2400 ? (int) $m[3] - 543 : (int) $m[3]; // รองรับ พ.ศ.
                $date = sprintf('%04d-%02d-%02d', $y, $m[2], $m[1]);
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) $err[] = 'วันที่ไม่ถูกต้อง (ใช้ YYYY-MM-DD หรือ DD/MM/YYYY)';
        }
        $status = $r['identification_status'] ?? '';
        if ($status !== '' && !in_array($status, ['confirmed', 'tentative', 'unidentified'], true)) $err[] = 'identification_status ต้องเป็น confirmed/tentative/unidentified';
        $rows[] = ['line' => $line, 'data' => $r, 'species_id' => $species_id, 'district_id' => $district_id,
            'source_id' => $source_id, 'date' => $date, 'errors' => $err, 'warnings' => $warn];
    }
    fclose($fh);
    return ['rows' => $rows, 'missing' => array_values($missing), 'create_species' => $create_species];
}

function import_commit(): void
{
    $imp = $_SESSION['import'] ?? null;
    if (!$imp) redirect('admin/import');
    $uid = current_user()['id'];
    $n = 0;
    db()->beginTransaction();
    foreach ($imp['rows'] as $row) {
        if ($row['errors']) continue;
        $r = $row['data'];
        $species_id = $row['species_id'];
        if (!$species_id && !empty($r['genus']) && !empty($r['epithet']) && $imp['create_species']) {
            $g = ucfirst(strtolower($r['genus']));
            $e = strtolower($r['epithet']);
            $species_id = q_val('SELECT id FROM species WHERE genus = ? AND epithet = ?', [$g, $e])
                ?: db_insert('species', ['genus' => $g, 'epithet' => $e, 'created_by' => $uid, 'updated_by' => $uid]);
        }
        $id = db_insert('strains', [
            'strain_code' => $r['strain_code'], 'other_codes' => nn($r['other_codes'] ?? null), 'species_id' => $species_id,
            'identification_status' => nn($r['identification_status'] ?? null) ?? ($species_id ? 'tentative' : 'unidentified'),
            'source_id' => $row['source_id'], 'substrate' => nn($r['substrate'] ?? null), 'district_id' => $row['district_id'],
            'subdistrict' => nn($r['subdistrict'] ?? null), 'locality' => nn($r['locality'] ?? null),
            'latitude' => nn($r['latitude'] ?? null), 'longitude' => nn($r['longitude'] ?? null), 'elevation_m' => nn($r['elevation_m'] ?? null),
            'collection_date' => nn($row['date']), 'collector' => nn($r['collector'] ?? null), 'isolator' => nn($r['isolator'] ?? null),
            'isolation_method' => nn($r['isolation_method'] ?? null), 'identified_by' => nn($r['identified_by'] ?? null),
            'identification_method' => nn($r['identification_method'] ?? null), 'storage' => nn($r['storage'] ?? null),
            'remarks' => nn($r['remarks'] ?? null), 'created_by' => $uid, 'updated_by' => $uid,
        ]);
        // รองรับคอลัมน์ชื่อเดิม lsu_* ด้วย
        foreach (['d1d2' => 'D1/D2 LSU', 'its' => 'ITS'] as $k => $locus) {
            $acc = nn($r["{$k}_accession"] ?? ($k === 'd1d2' ? $r['lsu_accession'] ?? null : null));
            $seq = clean_sequence($r["{$k}_sequence"] ?? ($k === 'd1d2' ? $r['lsu_sequence'] ?? '' : ''));
            if ($acc || $seq) db_insert('sequences', ['strain_id' => $id, 'locus' => $locus, 'accession' => $acc,
                'sequence' => $seq ?: null, 'length_bp' => $seq ? strlen($seq) : null]);
        }
        $n++;
    }
    db()->commit();
    audit('import', 'strain', null, "นำเข้า $n รายการ");
    unset($_SESSION['import']);
    flash("นำเข้าเรียบร้อย $n รายการ — ลำดับที่มีแต่ accession สามารถกด \"ซิงก์\" ในหน้าแก้ไขสายพันธุ์เพื่อดึงจาก NCBI");
    redirect('strains');
}

// ---------------- Users ----------------
function admin_users(): void
{
    require_admin();
    $users = q_all('SELECT id, username, full_name, email, role, created_at FROM users ORDER BY id');
    view('admin/users', compact('users') + ['title' => 'ผู้ใช้งาน']);
}

function admin_users_save(): void
{
    require_admin();
    $act = $_POST['act'] ?? '';
    $uid = (int) ($_POST['id'] ?? 0);
    if ($act === 'create') {
        $u = trim($_POST['username'] ?? '');
        $pw = $_POST['password'] ?? '';
        if (!preg_match('/^\w{3,50}$/', $u) || strlen($pw) < 8) {
            flash('ชื่อผู้ใช้ a-z/0-9 3–50 ตัว และรหัสผ่านอย่างน้อย 8 ตัว', 'danger');
        } elseif (q_val('SELECT 1 FROM users WHERE username = ?', [$u])) {
            flash('ชื่อผู้ใช้นี้มีแล้ว', 'danger');
        } else {
            $id = db_insert('users', ['username' => $u, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
                'full_name' => trim($_POST['full_name'] ?? '') ?: $u, 'email' => nn($_POST['email'] ?? null),
                'role' => ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'editor']);
            audit('create', 'user', $id, $u);
            flash("เพิ่มผู้ใช้ $u แล้ว");
        }
    } elseif ($act === 'reset' && $uid) {
        $pw = $_POST['password'] ?? '';
        if (strlen($pw) < 8) flash('รหัสผ่านอย่างน้อย 8 ตัว', 'danger');
        else {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $uid]);
            audit('reset_pw', 'user', $uid);
            flash('ตั้งรหัสผ่านใหม่แล้ว');
        }
    } elseif ($act === 'role' && $uid && $uid !== current_user()['id']) {
        q('UPDATE users SET role = ? WHERE id = ?', [($_POST['role'] ?? '') === 'admin' ? 'admin' : 'editor', $uid]);
        audit('role', 'user', $uid, $_POST['role'] ?? '');
        flash('เปลี่ยนสิทธิ์แล้ว');
    } elseif ($act === 'delete' && $uid && $uid !== current_user()['id']) {
        q('DELETE FROM users WHERE id = ?', [$uid]);
        audit('delete', 'user', $uid);
        flash('ลบผู้ใช้แล้ว', 'warning');
    }
    redirect('admin/users');
}

function admin_password_form(): void
{
    require_login();
    view('admin/password', ['title' => 'เปลี่ยนรหัสผ่าน']);
}

function admin_password_save(): void
{
    require_login();
    $u = q_one('SELECT password_hash FROM users WHERE id = ?', [current_user()['id']]);
    $new = $_POST['new'] ?? '';
    if (!password_verify($_POST['current'] ?? '', $u['password_hash'])) flash('รหัสผ่านปัจจุบันไม่ถูกต้อง', 'danger');
    elseif (strlen($new) < 8 || $new !== ($_POST['new2'] ?? '')) flash('รหัสผ่านใหม่ต้องอย่างน้อย 8 ตัวและตรงกันทั้งสองช่อง', 'danger');
    else {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), current_user()['id']]);
        flash('เปลี่ยนรหัสผ่านแล้ว');
    }
    redirect('admin/password');
}
