<?php
// คัดลอกเป็น config.local.php แล้วแก้ค่า (ไฟล์ config.local.php ไม่ถูก commit)
return [
    // 'base_url' => '/yeast',          // ถ้าติดตั้งในโฟลเดอร์ย่อย
    // 'db' => ['host' => '127.0.0.1', 'port' => '3306', 'name' => 'yeast_pyo', 'user' => 'yeast', 'pass' => '...'],
    'ncbi' => [
        'api_key' => '',   // ขอได้ที่ https://account.ncbi.nlm.nih.gov/settings/ → API Key Management
        'email'   => '',
    ],
];
