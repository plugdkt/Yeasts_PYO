<?php
// ค่าตั้งระบบ: อ่านจาก environment ก่อน แล้วให้ config.local.php (ไม่ commit) ทับได้
$config = [
    'app_name'     => 'Phayao Yeast Database',
    'app_name_th'  => 'ฐานข้อมูลยีสต์จังหวัดพะเยา',
    'base_url'     => getenv('BASE_URL') ?: '',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'yeast_pyo',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
    ],
    'ncbi' => [
        'tool'    => 'pyo_ydb',
        'email'   => getenv('NCBI_EMAIL') ?: '',
        'api_key' => getenv('NCBI_API_KEY') ?: '',
        'timeout' => 20,
    ],
    // จำนวนทศนิยมของพิกัดที่แสดงต่อสาธารณะ (2 ≈ 1.1 กม.) ผู้ใช้ที่ล็อกอินเห็นค่าเต็ม
    'public_coord_precision' => 2,
    'per_page'     => 20,
    'upload_dir'   => __DIR__ . '/../public/uploads',
    'upload_max'   => 10 * 1024 * 1024,
    'data_license' => 'CC BY 4.0',
];

$local = __DIR__ . '/../config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}
return $config;
