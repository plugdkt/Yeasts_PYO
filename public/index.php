<?php
declare(strict_types=1);

require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/ncbi.php';
require __DIR__ . '/../src/images.php';
require __DIR__ . '/../src/public_pages.php';
require __DIR__ . '/../src/admin_pages.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
// หมดเวลา session หลังไม่ใช้งาน 2 ชั่วโมง
if (isset($_SESSION['uid']) && time() - ($_SESSION['last_seen'] ?? 0) > 7200) {
    logout();
    session_start();
}
$_SESSION['last_seen'] = time();

$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$base = rtrim(config('base_url'), '/');
if ($base && str_starts_with($path, $base)) $path = substr($path, strlen($base)) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    csrf_check();
    // ตัดไบต์ที่ไม่ใช่ UTF-8 ทิ้ง ป้องกันฐานข้อมูลปฏิเสธข้อมูลจาก client ที่เข้ารหัสผิด
    array_walk_recursive($_POST, function (&$v) { if (is_string($v)) $v = mb_scrub($v, 'UTF-8'); });
}

$routes = [
    // สาธารณะ
    ['GET',  '#^/$#',                         'page_home'],
    ['GET',  '#^/species$#',                  'page_species_list'],
    ['GET',  '#^/species/(\d+)$#',            'page_species_detail'],
    ['GET',  '#^/strains$#',                  'page_strain_list'],
    ['GET',  '#^/strain/([\w\-.]+)$#',        'page_strain_detail'],
    ['GET',  '#^/map$#',                      'page_map'],
    ['GET',  '#^/stats$#',                    'page_stats'],
    ['GET',  '#^/about$#',                    'page_about'],
    ['GET',  '#^/export/strains\.csv$#',      'export_strains_csv'],
    ['GET',  '#^/export/species\.csv$#',      'export_species_csv'],
    ['GET',  '#^/export/sequences\.fasta$#',  'export_fasta'],
    ['GET',  '#^/login$#',                    'page_login'],
    ['POST', '#^/login$#',                    'do_login'],
    ['POST', '#^/logout$#',                   'do_logout'],
    ['GET',  '#^/setup$#',                    'page_setup'],
    ['POST', '#^/setup$#',                    'do_setup'],
    // ทีมวิจัย
    ['GET',  '#^/admin$#',                              'admin_dashboard'],
    ['GET',  '#^/admin/species/(new|\d+)$#',            'admin_species_form'],
    ['POST', '#^/admin/species/(new|\d+)$#',            'admin_species_save'],
    ['POST', '#^/admin/species/(\d+)/delete$#',         'admin_species_delete'],
    ['GET',  '#^/admin/strain/(new|\d+)$#',             'admin_strain_form'],
    ['POST', '#^/admin/strain/(new|\d+)$#',             'admin_strain_save'],
    ['POST', '#^/admin/strain/(\d+)/delete$#',          'admin_strain_delete'],
    ['POST', '#^/admin/strain/(\d+)/sequence$#',        'admin_sequence_add'],
    ['POST', '#^/admin/sequence/(\d+)/delete$#',        'admin_sequence_delete'],
    ['POST', '#^/admin/sequence/(\d+)/sync$#',          'admin_sequence_sync'],
    ['GET',  '#^/admin/phenotype/(species|strain)/(\d+)$#',  'admin_phenotype_form'],
    ['POST', '#^/admin/phenotype/(species|strain)/(\d+)$#',  'admin_phenotype_save'],
    ['POST', '#^/admin/image/(species|strain)/(\d+)$#', 'admin_image_upload'],
    ['POST', '#^/admin/image/(\d+)/update$#',           'admin_image_update'],
    ['POST', '#^/admin/image/(\d+)/delete$#',           'admin_image_delete'],
    ['GET',  '#^/admin/import$#',                       'admin_import_form'],
    ['POST', '#^/admin/import$#',                       'admin_import_do'],
    ['GET',  '#^/admin/import/template\.csv$#',         'admin_import_template'],
    ['GET',  '#^/admin/users$#',                        'admin_users'],
    ['POST', '#^/admin/users$#',                        'admin_users_save'],
    ['GET',  '#^/admin/password$#',                     'admin_password_form'],
    ['POST', '#^/admin/password$#',                     'admin_password_save'],
    ['GET',  '#^/admin/api/ncbi-taxonomy$#',            'api_ncbi_taxonomy'],
    ['GET',  '#^/admin/api/ncbi-d1d2$#',                'api_ncbi_d1d2'],
];

try {
    foreach ($routes as [$m, $re, $fn]) {
        if ($m === $method && preg_match($re, $path, $mm)) {
            array_shift($mm);
            $fn(...$mm);
            exit;
        }
    }
    abort(404);
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    abort(500, 'เกิดข้อผิดพลาดของฐานข้อมูล' . (getenv('APP_DEBUG') ? ': ' . $ex->getMessage() : ''));
}
