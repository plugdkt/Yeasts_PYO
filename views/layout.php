<?php
$cfg = config();
$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$nav = fn(string $p) => ($p === '/' ? $path === '/' : str_starts_with($path, $p)) ? ' active' : '';
?><!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $title ? e($title) . ' · ' : '' ?><?= e($cfg['app_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php if (!empty($use_map)): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<?php endif; ?>
<link href="<?= url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark site-nav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('') ?>">
      <span class="brand-mark"><i class="bi bi-droplet-half"></i></span>
      <span class="lh-1"><span class="d-block fw-semibold"><?= e($cfg['app_name_th']) ?></span>
      <small class="brand-sub"><?= e($cfg['app_name']) ?></small></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link<?= $nav('/species') ?>" href="<?= url('species') ?>">ชนิด</a></li>
        <li class="nav-item"><a class="nav-link<?= $nav('/strain') ?>" href="<?= url('strains') ?>">สายพันธุ์</a></li>
        <li class="nav-item"><a class="nav-link<?= $nav('/map') ?>" href="<?= url('map') ?>">แผนที่</a></li>
        <li class="nav-item"><a class="nav-link<?= $nav('/stats') ?>" href="<?= url('stats') ?>">สถิติ</a></li>
        <li class="nav-item"><a class="nav-link<?= $nav('/about') ?>" href="<?= url('about') ?>">เกี่ยวกับ</a></li>
        <?php if (is_logged_in()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $nav('/admin') ?>" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= e(current_user()['full_name']) ?></a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= url('admin') ?>"><i class="bi bi-speedometer2"></i> จัดการข้อมูล</a></li>
            <li><a class="dropdown-item" href="<?= url('admin/species/new') ?>"><i class="bi bi-plus-circle"></i> เพิ่มชนิด</a></li>
            <li><a class="dropdown-item" href="<?= url('admin/strain/new') ?>"><i class="bi bi-plus-circle"></i> เพิ่มสายพันธุ์</a></li>
            <li><a class="dropdown-item" href="<?= url('admin/import') ?>"><i class="bi bi-upload"></i> นำเข้า CSV</a></li>
            <?php if (is_admin()): ?><li><a class="dropdown-item" href="<?= url('admin/users') ?>"><i class="bi bi-people"></i> ผู้ใช้งาน</a></li><?php endif; ?>
            <li><a class="dropdown-item" href="<?= url('admin/password') ?>"><i class="bi bi-key"></i> เปลี่ยนรหัสผ่าน</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><form method="post" action="<?= url('logout') ?>"><?= csrf_field() ?><button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</button></form></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?= url('login') ?>"><i class="bi bi-box-arrow-in-right"></i> ทีมวิจัย</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="<?= !empty($full_width) ? '' : 'container py-4' ?>">
  <?php foreach (flash() as [$type, $msg]): ?>
    <div class="alert alert-<?= e($type) ?> alert-dismissible fade show <?= !empty($full_width) ? 'container mt-3' : '' ?>"><?= e($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endforeach; ?>
  <?= $content ?>
</main>

<footer class="site-footer mt-5">
  <div class="container py-4 small">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="fw-semibold"><?= e($cfg['app_name_th']) ?></div>
        <div class="opacity-75">ข้อมูลเผยแพร่ภายใต้สัญญาอนุญาต <?= e($cfg['data_license']) ?> · โครงสร้างข้อมูลอ้างอิง The Yeasts Database</div>
      </div>
      <div class="col-md-6 text-md-end opacity-75">
        เชื่อมโยงข้อมูลกับ <a href="https://www.ncbi.nlm.nih.gov/" target="_blank" rel="noopener">NCBI</a> ·
        <a href="https://www.mycobank.org/" target="_blank" rel="noopener">MycoBank</a> ·
        <a href="https://theyeasts.org/" target="_blank" rel="noopener">The Yeasts</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($use_map)): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="<?= url('assets/js/map.js') ?>"></script>
<?php endif; ?>
<?php if (!empty($use_chart)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<?php endif; ?>
<?= $scripts ?? '' ?>
</body>
</html>
