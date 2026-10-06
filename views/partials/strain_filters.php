<?php
// ฟอร์มกรอง strain ใช้ร่วมกันในหน้า strains และ map — ต้องมี $districts $sources $species $genera
$dist = array_combine(array_column($districts, 'id'), array_map(fn($d) => 'อ.' . $d['name_th'], $districts));
$src = array_combine(array_column($sources, 'id'), array_column($sources, 'name_th'));
$sps = array_combine(array_column($species, 'id'), array_map(fn($s) => $s['genus'] . ' ' . $s['epithet'], $species));
$gen = array_combine(array_column($genera, 'genus'), array_column($genera, 'genus'));
?>
<form class="card filter-card mb-3" action="<?= e($action) ?>"><div class="card-body row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label">ค้นหา</label>
    <input class="form-control form-control-sm" name="q" value="<?= e(input('q')) ?>" placeholder="รหัส, ชื่อชนิด, substrate, สถานที่"></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'genus', 'label' => 'Genus', 'options' => $gen, 'value' => input('genus')]); ?></div>
  <div class="col-6 col-md-3"><?php partial('select', ['name' => 'species', 'label' => 'Species', 'options' => $sps, 'value' => input('species')]); ?></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'district', 'label' => 'อำเภอ', 'options' => $dist, 'value' => input('district')]); ?></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'source', 'label' => 'แหล่งที่แยก', 'options' => $src, 'value' => input('source')]); ?></div>
  <div class="col-6 col-md-2"><label class="form-label">ปีที่เก็บ (ค.ศ.) ตั้งแต่</label><input class="form-control form-control-sm" type="number" name="year_from" value="<?= e(input('year_from')) ?>"></div>
  <div class="col-6 col-md-2"><label class="form-label">ถึง</label><input class="form-control form-control-sm" type="number" name="year_to" value="<?= e(input('year_to')) ?>"></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'status', 'label' => 'สถานะการระบุชนิด', 'options' => ['confirmed' => 'ยืนยันแล้ว', 'tentative' => 'เบื้องต้น', 'unidentified' => 'ยังไม่ระบุ'], 'value' => input('status')]); ?></div>
  <div class="col-6 col-md-2"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="has_seq" value="1" id="hs" <?= input('has_seq') === '1' ? 'checked' : '' ?>>
    <label class="form-check-label small" for="hs">มีลำดับ DNA</label></div>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="has_d1d2" value="1" id="hd" <?= input('has_d1d2') === '1' ? 'checked' : '' ?>>
    <label class="form-check-label small" for="hd">มีเลข D1/D2 accession</label></div></div>
  <?php if (!empty($with_sort)): ?>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'sort', 'label' => 'เรียงตาม', 'options' => ['species' => 'ชื่อชนิด', 'date' => 'วันที่เก็บล่าสุด', 'district' => 'อำเภอ'], 'value' => input('sort'), 'empty' => 'รหัส']); ?></div>
  <?php endif; ?>
  <div class="col-md-2 d-flex gap-2"><button class="btn btn-sm btn-pyo flex-fill"><i class="bi bi-funnel"></i> กรอง</button>
    <a class="btn btn-sm btn-outline-secondary" href="<?= e($action) ?>">ล้าง</a></div>
</div></form>
