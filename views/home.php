<?php $full_width = true; ?>
<section class="hero">
  <div class="container position-relative" style="z-index:1">
    <p class="text-uppercase small mb-2 opacity-75 fw-semibold">Phayao Yeast Database</p>
    <h1 class="display-6 mb-3"><?= e(config('app_name_th')) ?></h1>
    <p class="lead mb-4 opacity-90" style="max-width:640px">คลังข้อมูลยีสต์ที่แยกได้จากดอกไม้ ผลไม้ ดิน แมลง และอาหารหมักพื้นบ้านใน 9 อำเภอของจังหวัดพะเยา
      พร้อมข้อมูลอนุกรมวิธาน ลำดับ DNA และลักษณะทางสรีรวิทยา เชื่อมโยงกับ NCBI</p>
    <form class="search-box" action="<?= url('species') ?>">
      <div class="input-group input-group-lg">
        <input class="form-control" name="q" placeholder="ค้นหาชื่อชนิด เช่น Saccharomyces, Pichia kudriavzevii">
        <button class="btn btn-warning px-4"><i class="bi bi-search"></i></button>
      </div>
    </form>
  </div>
</section>

<div class="container">
  <div class="row g-3 stat-row">
    <?php foreach ([
        ['strains', 'สายพันธุ์ที่แยกได้', 'strains'], ['species_found', 'ชนิดที่พบในพะเยา', 'species?found=1'],
        ['genera', 'สกุล', 'species?found=1&sort=family'], ['sequences', 'ลำดับ DNA', 'strains?has_seq=1'],
        ['districts', 'อำเภอที่มีข้อมูล (จาก 9)', 'map']] as [$k, $label, $link]): ?>
      <div class="col-6 col-md"><a href="<?= url($link) ?>" class="text-decoration-none"><div class="stat-card">
        <div class="num"><?= number_format((int) $stats[$k]) ?></div><div class="lbl"><?= $label ?></div></div></a></div>
    <?php endforeach; ?>
  </div>

  <?php if ($has_demo): ?>
  <div class="demo-banner rounded p-3 mt-4 small"><i class="bi bi-info-circle"></i>
    ขณะนี้ระบบมี <b>ข้อมูลตัวอย่าง (รหัสขึ้นต้นด้วย DEMO-)</b> สำหรับทดสอบเท่านั้น ไม่ใช่ข้อมูลการเก็บตัวอย่างจริง — จะถูกลบเมื่อนำเข้าข้อมูลจริง</div>
  <?php endif; ?>

  <div class="row g-4 mt-1">
    <div class="col-lg-7">
      <h2 class="section-title h5">สายพันธุ์ที่เพิ่มล่าสุด</h2>
      <div class="list-group shadow-sm">
        <?php foreach ($recent as $r): ?>
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start" href="<?= url('strain/' . $r['strain_code']) ?>">
          <div><div class="fw-semibold"><?= e($r['strain_code']) ?> · <?= species_name($r) ?></div>
            <small class="text-muted"><?= e($r['source_th'] ?? '-') ?><?= $r['substrate'] ? ' – ' . e($r['substrate']) : '' ?></small></div>
          <span class="badge text-bg-light">อ.<?= e($r['district_th'] ?? '-') ?></span>
        </a>
        <?php endforeach; ?>
        <?php if (!$recent): ?><div class="list-group-item text-muted">ยังไม่มีข้อมูล</div><?php endif; ?>
      </div>
      <div class="mt-2 text-end"><a href="<?= url('strains') ?>">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a></div>
    </div>
    <div class="col-lg-5">
      <h2 class="section-title h5">จำนวนสายพันธุ์รายอำเภอ</h2>
      <div class="card"><div class="card-body">
        <?php $max = max([1, ...array_column($by_district, 'n')]); foreach ($by_district as $d): ?>
        <a href="<?= url('strains', ['district' => $d['id']]) ?>" class="d-block text-decoration-none text-reset mb-2">
          <div class="d-flex justify-content-between small"><span>อ.<?= e($d['name_th']) ?></span><span class="fw-semibold"><?= (int) $d['n'] ?></span></div>
          <div class="progress" style="height:6px"><div class="progress-bar" style="width:<?= round($d['n'] / $max * 100) ?>%;background:var(--pyo-green-2)"></div></div>
        </a>
        <?php endforeach; ?>
      </div></div>
      <div class="d-grid gap-2 mt-3">
        <a class="btn btn-pyo" href="<?= url('map') ?>"><i class="bi bi-geo-alt"></i> เปิดแผนที่จุดเก็บตัวอย่าง</a>
        <a class="btn btn-outline-secondary" href="<?= url('stats') ?>"><i class="bi bi-bar-chart"></i> ดูสถิติทั้งหมด</a>
      </div>
    </div>
  </div>
</div>
