<?php
// ช่องข้อมูลประกอบรูป ใช้ทั้งฟอร์มอัปโหลดและฟอร์มแก้ไข — $im (ค่าเดิม หรือ []), $uid (คำนำหน้า id ของ input)
$v = fn($k) => e($im[$k] ?? '');
$uid = $uid ?? 'n';
?>
<div class="col-md-4"><label class="form-label small">ประเภทรูป</label>
  <select class="form-select form-select-sm" name="image_type">
    <?php foreach (image_types() as $k => $t): ?><option value="<?= $k ?>" <?= ($im['image_type'] ?? '') === $k ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
  </select></div>
<div class="col-md-4"><label class="form-label small">อาหารเลี้ยงเชื้อ</label>
  <input class="form-control form-control-sm" name="medium" value="<?= $v('medium') ?>" list="dl_media" placeholder="เช่น YM agar"></div>
<div class="col-6 col-md-2"><label class="form-label small">อุณหภูมิ (°C)</label>
  <input class="form-control form-control-sm" name="incubation_temp" value="<?= $v('incubation_temp') ?>" placeholder="25"></div>
<div class="col-6 col-md-2"><label class="form-label small">ระยะเวลา (วัน)</label>
  <input class="form-control form-control-sm" name="incubation_days" value="<?= $v('incubation_days') ?>" placeholder="3"></div>
<div class="col-md-4"><label class="form-label small">เทคนิคการถ่าย</label>
  <input class="form-control form-control-sm" name="technique" value="<?= $v('technique') ?>" list="dl_tech" placeholder="เช่น Phase contrast"></div>
<div class="col-6 col-md-2"><label class="form-label small">กำลังขยาย</label>
  <input class="form-control form-control-sm" name="magnification" value="<?= $v('magnification') ?>" placeholder="1000"></div>
<div class="col-6 col-md-2"><label class="form-label small">Scale bar</label>
  <input class="form-control form-control-sm" name="scale_bar" value="<?= $v('scale_bar') ?>" placeholder="10 µm"></div>
<div class="col-md-4"><label class="form-label small">คำบรรยาย</label>
  <input class="form-control form-control-sm" name="caption" value="<?= $v('caption') ?>" maxlength="255"></div>
<div class="col-md-4"><label class="form-label small">ผู้ถ่าย</label>
  <input class="form-control form-control-sm" name="photographer" value="<?= $v('photographer') ?>"></div>
<div class="col-6 col-md-2"><label class="form-label small">วันที่ถ่าย</label>
  <input class="form-control form-control-sm" type="date" name="taken_date" value="<?= $v('taken_date') ?>"></div>
<div class="col-6 col-md-2"><label class="form-label small">ลำดับการแสดง</label>
  <input class="form-control form-control-sm" type="number" name="sort_order" value="<?= (int) ($im['sort_order'] ?? 0) ?>"></div>
<div class="col-md-4"><label class="form-label small">สัญญาอนุญาต</label>
  <input class="form-control form-control-sm" name="license" value="<?= $v('license') ?>" list="dl_lic" placeholder="ว่าง = <?= e(config('data_license')) ?>"></div>
<div class="col-md-8"><label class="form-label small">เครดิต / ที่มา (ถ้าเป็นรูปจากแหล่งอื่น)</label>
  <input class="form-control form-control-sm" name="credit" value="<?= $v('credit') ?>" maxlength="255"></div>
