<?php
// ======================= หน้าสาธารณะ =======================

const STRAIN_SELECT = "SELECT st.*, sp.genus, sp.epithet, sp.family, sp.d1d2_ref_accession, sp.d1d2_ref_strain,
        d.name_th AS district_th, d.name_en AS district_en, so.name_th AS source_th, so.name_en AS source_en,
        (SELECT COUNT(*) FROM sequences sq WHERE sq.strain_id = st.id) AS seq_count,
        (SELECT sq.accession FROM sequences sq WHERE sq.strain_id = st.id AND sq.accession IS NOT NULL
            AND sq.locus REGEXP 'D1/?D2|LSU|26S|28S' ORDER BY sq.id LIMIT 1) AS d1d2_accession
    FROM strains st
    LEFT JOIN species sp  ON sp.id = st.species_id
    LEFT JOIN districts d ON d.id = st.district_id
    LEFT JOIN sources so  ON so.id = st.source_id";

/** เงื่อนไขกรอง strain ที่ใช้ร่วมกันในหน้า list / map / export */
function strain_filters(): array
{
    $w = [];
    $p = [];
    if ($v = input('q')) {
        $w[] = "(st.strain_code LIKE ? OR st.other_codes LIKE ? OR st.substrate LIKE ? OR st.locality LIKE ?
                 OR CONCAT(sp.genus,' ',sp.epithet) LIKE ?)";
        array_push($p, ...array_fill(0, 5, "%$v%"));
    }
    foreach (['species' => 'st.species_id', 'district' => 'st.district_id', 'source' => 'st.source_id'] as $k => $col) {
        if (ctype_digit((string) input($k))) { $w[] = "$col = ?"; $p[] = (int) input($k); }
    }
    if ($v = input('genus'))  { $w[] = 'sp.genus = ?';  $p[] = $v; }
    if ($v = input('family')) { $w[] = 'sp.family = ?'; $p[] = $v; }
    if (ctype_digit((string) input('year_from'))) { $w[] = 'YEAR(st.collection_date) >= ?'; $p[] = (int) input('year_from'); }
    if (ctype_digit((string) input('year_to')))   { $w[] = 'YEAR(st.collection_date) <= ?'; $p[] = (int) input('year_to'); }
    if (input('status')) { $w[] = 'st.identification_status = ?'; $p[] = input('status'); }
    if (input('has_seq') === '1') $w[] = 'EXISTS (SELECT 1 FROM sequences sq WHERE sq.strain_id = st.id)';
    if (input('has_d1d2') === '1') $w[] = "EXISTS (SELECT 1 FROM sequences sq WHERE sq.strain_id = st.id AND sq.accession IS NOT NULL AND sq.locus REGEXP 'D1/?D2|LSU|26S|28S')";
    return [$w ? 'WHERE ' . implode(' AND ', $w) : '', $p];
}

function lookup_lists(): array
{
    return [
        'districts' => q_all('SELECT * FROM districts ORDER BY id'),
        'sources'   => q_all('SELECT * FROM sources ORDER BY name_th'),
        'families'  => q_all('SELECT DISTINCT family FROM species WHERE family IS NOT NULL ORDER BY family'),
        'genera'    => q_all('SELECT DISTINCT genus FROM species ORDER BY genus'),
        'species'   => q_all('SELECT id, genus, epithet FROM species ORDER BY genus, epithet'),
    ];
}

function page_home(): void
{
    $stats = q_one("SELECT
        (SELECT COUNT(*) FROM species WHERE id IN (SELECT species_id FROM strains)) AS species_found,
        (SELECT COUNT(*) FROM species) AS species_total,
        (SELECT COUNT(*) FROM strains) AS strains,
        (SELECT COUNT(*) FROM sequences) AS sequences,
        (SELECT COUNT(DISTINCT district_id) FROM strains) AS districts,
        (SELECT COUNT(DISTINCT genus) FROM species WHERE id IN (SELECT species_id FROM strains)) AS genera");
    $recent = q_all(STRAIN_SELECT . ' ORDER BY st.created_at DESC, st.id DESC LIMIT 6');
    $by_district = q_all('SELECT d.id, d.name_th, COUNT(st.id) n FROM districts d
        LEFT JOIN strains st ON st.district_id = d.id GROUP BY d.id ORDER BY n DESC, d.id');
    $has_demo = (bool) q_val('SELECT EXISTS(SELECT 1 FROM strains WHERE is_demo = 1)');
    view('home', compact('stats', 'recent', 'by_district', 'has_demo') + ['title' => '']);
}

function page_species_list(): void
{
    $w = [];
    $p = [];
    if ($v = input('q')) {
        $w[] = "(CONCAT(s.genus,' ',s.epithet) LIKE ? OR s.family LIKE ? OR s.ecology LIKE ? OR s.applications LIKE ?
                 OR EXISTS (SELECT 1 FROM synonyms y WHERE y.species_id = s.id AND y.name LIKE ?))";
        array_push($p, ...array_fill(0, 5, "%$v%"));
    }
    if ($v = input('family')) { $w[] = 's.family = ?'; $p[] = $v; }
    if ($v = input('genus'))  { $w[] = 's.genus = ?';  $p[] = $v; }
    if ($v = input('phylum')) { $w[] = 's.phylum = ?'; $p[] = $v; }
    $sub = [];
    if (ctype_digit((string) input('district'))) { $sub[] = 'x.district_id = ?'; $p[] = (int) input('district'); }
    if (ctype_digit((string) input('source')))   { $sub[] = 'x.source_id = ?';   $p[] = (int) input('source'); }
    if ($sub) $w[] = 'EXISTS (SELECT 1 FROM strains x WHERE x.species_id = s.id AND ' . implode(' AND ', $sub) . ')';
    if (input('found') === '1') $w[] = 'EXISTS (SELECT 1 FROM strains x WHERE x.species_id = s.id)';
    $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';

    $sorts = ['name' => 's.genus, s.epithet', 'family' => 's.family, s.genus, s.epithet', 'strains' => 'n_strains DESC, s.genus'];
    $sort = $sorts[input('sort', 'name')] ?? $sorts['name'];

    $pg = paginate((int) q_val("SELECT COUNT(*) FROM species s $where", $p), (int) input('page', 1), config('per_page'));
    $rows = q_all("SELECT s.*, (SELECT COUNT(*) FROM strains x WHERE x.species_id = s.id) n_strains,
            (SELECT GROUP_CONCAT(DISTINCT d.name_th ORDER BY d.id SEPARATOR ', ') FROM strains x
               JOIN districts d ON d.id = x.district_id WHERE x.species_id = s.id) districts
        FROM species s $where ORDER BY $sort LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p);
    $phyla = q_all('SELECT DISTINCT phylum FROM species WHERE phylum IS NOT NULL ORDER BY phylum');
    view('species_list', compact('rows', 'pg', 'phyla') + lookup_lists() + ['title' => 'ชนิดยีสต์ (Species)']);
}

function page_species_detail(string $id): void
{
    $sp = q_one('SELECT s.*, cu.full_name AS created_name, uu.full_name AS updated_name FROM species s
        LEFT JOIN users cu ON cu.id = s.created_by LEFT JOIN users uu ON uu.id = s.updated_by WHERE s.id = ?', [$id]);
    if (!$sp) abort();
    $synonyms = q_all('SELECT * FROM synonyms WHERE species_id = ? ORDER BY id', [$id]);
    $strains = q_all(STRAIN_SELECT . ' WHERE st.species_id = ? ORDER BY st.strain_code', [$id]);
    $sequences = q_all('SELECT sq.*, st.strain_code FROM sequences sq JOIN strains st ON st.id = sq.strain_id
        WHERE st.species_id = ? ORDER BY sq.locus, st.strain_code', [$id]);
    $pheno = phenotype_matrix('species_id', (int) $id);
    $images = q_all('SELECT * FROM images WHERE species_id = ? OR strain_id IN (SELECT id FROM strains WHERE species_id = ?) ORDER BY id', [$id, $id]);
    $bib = q_all('SELECT b.* FROM bibliography b JOIN species_bibliography sb ON sb.bib_id = b.id WHERE sb.species_id = ? ORDER BY b.year, b.citation', [$id]);
    $points = map_points($strains);
    view('species_detail', compact('sp', 'synonyms', 'strains', 'sequences', 'pheno', 'images', 'bib', 'points')
        + ['title' => $sp['genus'] . ' ' . $sp['epithet']]);
}

/** คืนค่า [group => [ [test, result], ... ]] */
function phenotype_matrix(string $col, int $id): array
{
    $rows = q_all("SELECT t.*, r.result, r.method FROM phenotype_tests t
        LEFT JOIN phenotype_results r ON r.test_id = t.id AND r.$col = ?
        ORDER BY t.test_group, t.sort_order, t.name", [$id]);
    $out = [];
    foreach ($rows as $r) $out[$r['test_group']][] = $r;
    return $out;
}

function map_points(array $strains): array
{
    $pts = [];
    foreach ($strains as $s) {
        if ($s['latitude'] === null || $s['longitude'] === null) continue;
        $pts[] = [
            'lat' => coord($s['latitude']), 'lng' => coord($s['longitude']),
            'code' => $s['strain_code'], 'url' => url('strain/' . $s['strain_code']),
            'name' => $s['genus'] ? $s['genus'] . ' ' . $s['epithet'] : 'ยังไม่ระบุชนิด',
            'genus' => $s['genus'] ?? 'Unidentified',
            'source' => $s['source_th'], 'substrate' => $s['substrate'], 'district' => $s['district_th'],
        ];
    }
    return $pts;
}

function page_strain_list(): void
{
    [$where, $p] = strain_filters();
    $sorts = ['code' => 'st.strain_code', 'species' => 'sp.genus, sp.epithet, st.strain_code',
              'date' => 'st.collection_date DESC', 'district' => 'd.id, st.strain_code'];
    $sort = $sorts[input('sort', 'code')] ?? $sorts['code'];
    $total = (int) q_val("SELECT COUNT(*) FROM strains st LEFT JOIN species sp ON sp.id = st.species_id
        LEFT JOIN districts d ON d.id = st.district_id $where", $p);
    $pg = paginate($total, (int) input('page', 1), config('per_page'));
    $rows = q_all(STRAIN_SELECT . " $where ORDER BY $sort LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p);
    view('strain_list', compact('rows', 'pg') + lookup_lists() + ['title' => 'สายพันธุ์ (Strains)']);
}

function page_strain_detail(string $code): void
{
    $st = q_one(STRAIN_SELECT . ' WHERE st.strain_code = ?', [$code]);
    if (!$st) abort();
    $sequences = q_all('SELECT * FROM sequences WHERE strain_id = ? ORDER BY locus', [$st['id']]);
    $pheno = phenotype_matrix('strain_id', (int) $st['id']);
    $pheno_sp = $st['species_id'] ? phenotype_matrix('species_id', (int) $st['species_id']) : [];
    $images = q_all('SELECT * FROM images WHERE strain_id = ? ORDER BY id', [$st['id']]);
    $points = map_points([$st]);
    view('strain_detail', compact('st', 'sequences', 'pheno', 'pheno_sp', 'images', 'points') + ['title' => $st['strain_code']]);
}

function page_map(): void
{
    [$where, $p] = strain_filters();
    $points = map_points(q_all(STRAIN_SELECT . " $where", $p));
    $districts = q_all('SELECT * FROM districts ORDER BY id');
    view('map', compact('points') + lookup_lists() + ['title' => 'แผนที่จุดเก็บตัวอย่าง']);
}

function page_stats(): void
{
    $data = [
        'district' => q_all('SELECT d.name_th label, COUNT(st.id) n FROM districts d LEFT JOIN strains st ON st.district_id = d.id GROUP BY d.id ORDER BY n DESC'),
        'source'   => q_all('SELECT COALESCE(so.name_th, "ไม่ระบุ") label, COUNT(*) n FROM strains st LEFT JOIN sources so ON so.id = st.source_id GROUP BY so.id ORDER BY n DESC'),
        'genus'    => q_all('SELECT COALESCE(sp.genus, "ยังไม่ระบุ") label, COUNT(*) n FROM strains st LEFT JOIN species sp ON sp.id = st.species_id GROUP BY sp.genus ORDER BY n DESC LIMIT 15'),
        'year'     => q_all('SELECT YEAR(collection_date) label, COUNT(*) n FROM strains WHERE collection_date IS NOT NULL GROUP BY label ORDER BY label'),
        'status'   => q_all("SELECT identification_status label, COUNT(*) n FROM strains GROUP BY identification_status"),
        'phylum'   => q_all('SELECT COALESCE(sp.phylum, "ยังไม่ระบุ") label, COUNT(*) n FROM strains st LEFT JOIN species sp ON sp.id = st.species_id GROUP BY sp.phylum ORDER BY n DESC'),
    ];
    $matrix = q_all('SELECT CONCAT(sp.genus," ",sp.epithet) sp_name, sp.id, d.id did, COUNT(*) n FROM strains st
        JOIN species sp ON sp.id = st.species_id JOIN districts d ON d.id = st.district_id
        GROUP BY sp.id, d.id ORDER BY sp.genus, sp.epithet');
    $districts = q_all('SELECT * FROM districts ORDER BY id');
    view('stats', compact('data', 'matrix', 'districts') + ['title' => 'สถิติ']);
}

function page_about(): void
{
    view('about', ['title' => 'เกี่ยวกับฐานข้อมูล']);
}

// ---------------- Export ----------------
function csv_out(string $filename, array $header, iterable $rows): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM ให้ Excel อ่านภาษาไทยได้
    fputcsv($out, $header);
    foreach ($rows as $r) fputcsv($out, $r);
    exit;
}

function export_strains_csv(): void
{
    [$where, $p] = strain_filters();
    $rows = q_all(STRAIN_SELECT . " $where ORDER BY st.strain_code", $p);
    // ชื่อคอลัมน์แบบ Darwin Core เท่าที่ map ได้
    csv_out('pyo_strains_' . date('Ymd') . '.csv',
        ['catalogNumber', 'otherCatalogNumbers', 'scientificName', 'family', 'identificationVerificationStatus', 'typeStatus',
         'habitat', 'substrate', 'stateProvince', 'county', 'municipality', 'locality', 'decimalLatitude', 'decimalLongitude',
         'minimumElevationInMeters', 'eventDate', 'recordedBy', 'identifiedBy', 'identificationRemarks', 'd1d2Accession', 'sequences'],
        array_map(fn($r) => [
            $r['strain_code'], $r['other_codes'], trim(($r['genus'] ?? '') . ' ' . ($r['epithet'] ?? '')), $r['family'],
            $r['identification_status'], $r['is_type_strain'] ? 'type' : '', $r['source_en'], $r['substrate'],
            'Phayao', $r['district_en'], $r['subdistrict'], $r['locality'], coord($r['latitude']), coord($r['longitude']),
            $r['elevation_m'], $r['collection_date'], $r['collector'], $r['identified_by'], $r['identification_method'],
            $r['d1d2_accession'], $r['seq_count'],
        ], $rows));
}

function export_species_csv(): void
{
    $rows = q_all('SELECT s.*, (SELECT COUNT(*) FROM strains x WHERE x.species_id = s.id) n FROM species s ORDER BY genus, epithet');
    csv_out('pyo_species_' . date('Ymd') . '.csv',
        ['id', 'scientificName', 'scientificNameAuthorship', 'kingdom', 'phylum', 'class', 'order', 'family', 'genus',
         'specificEpithet', 'mycobank', 'ncbiTaxID', 'd1d2TypeStrainAccession', 'd1d2TypeStrain', 'strainsInPhayao'],
        array_map(fn($r) => [$r['id'], $r['genus'] . ' ' . $r['epithet'], $r['authority'], $r['kingdom'], $r['phylum'],
            $r['class_name'], $r['order_name'], $r['family'], $r['genus'], $r['epithet'], $r['mycobank_no'], $r['ncbi_taxid'],
            $r['d1d2_ref_accession'], $r['d1d2_ref_strain'], $r['n']], $rows));
}

function export_fasta(): void
{
    [$where, $p] = strain_filters();
    $w = $where ? "$where AND sq.sequence IS NOT NULL AND sq.sequence <> ''" : "WHERE sq.sequence IS NOT NULL AND sq.sequence <> ''";
    if ($v = input('locus')) {
        if (is_d1d2($v)) $w .= " AND sq.locus REGEXP 'D1/?D2|LSU|26S|28S'";
        else { $w .= ' AND sq.locus = ?'; $p[] = $v; }
    }
    $rows = q_all("SELECT sq.*, st.strain_code, sp.genus, sp.epithet FROM sequences sq
        JOIN strains st ON st.id = sq.strain_id LEFT JOIN species sp ON sp.id = st.species_id
        LEFT JOIN districts d ON d.id = st.district_id $w ORDER BY st.strain_code, sq.locus", $p);
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="pyo_sequences_' . date('Ymd') . '.fasta"');
    foreach ($rows as $r) {
        $name = $r['genus'] ? "{$r['genus']}_{$r['epithet']}" : 'unidentified';
        echo ">{$r['strain_code']}|{$name}|{$r['locus']}" . ($r['accession'] ? "|{$r['accession']}" : '') . "\n";
        echo wordwrap($r['sequence'], 70, "\n", true) . "\n";
    }
    exit;
}

// ---------------- Login / Setup ----------------
function page_login(): void
{
    if (!q_val('SELECT COUNT(*) FROM users')) redirect('setup');
    view('login', ['title' => 'เข้าสู่ระบบ']);
}

function do_login(): void
{
    if (attempt_login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        $next = $_POST['next'] ?? '';
        // อนุญาตเฉพาะ path ภายในเว็บ
        redirect(preg_match('#^/[^/]#', $next) ? $next : 'admin');
    }
    flash('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง', 'danger');
    redirect('login');
}

function do_logout(): void
{
    logout();
    redirect('');
}

/** สร้างผู้ดูแลระบบคนแรก (ใช้ได้เฉพาะเมื่อยังไม่มีผู้ใช้) */
function page_setup(): void
{
    if (q_val('SELECT COUNT(*) FROM users')) redirect('login');
    view('setup', ['title' => 'ตั้งค่าครั้งแรก']);
}

function do_setup(): void
{
    if (q_val('SELECT COUNT(*) FROM users')) redirect('login');
    $u = trim($_POST['username'] ?? '');
    $pw = $_POST['password'] ?? '';
    if (!preg_match('/^\w{3,50}$/', $u) || strlen($pw) < 8 || $pw !== ($_POST['password2'] ?? '')) {
        flash('ชื่อผู้ใช้ต้องเป็น a-z/0-9 3–50 ตัว และรหัสผ่านอย่างน้อย 8 ตัวและตรงกันทั้งสองช่อง', 'danger');
        redirect('setup');
    }
    db_insert('users', ['username' => $u, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
        'full_name' => trim($_POST['full_name'] ?? '') ?: $u, 'email' => nn($_POST['email'] ?? ''), 'role' => 'admin']);
    attempt_login($u, $pw);
    flash('สร้างผู้ดูแลระบบเรียบร้อย');
    redirect('admin');
}
