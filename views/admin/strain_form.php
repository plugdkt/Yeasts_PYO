<?php
$use_map = true;
$f = function ($name, $label, $col = 'col-md-4', $type = 'text', $attrs = '') use ($st) {
    echo "<div class=\"$col\"><label class=\"form-label small\" for=\"f_$name\">" . e($label) . "</label>";
    if ($type === 'textarea') echo "<textarea class=\"form-control form-control-sm\" id=\"f_$name\" name=\"$name\" rows=\"2\" $attrs>" . e($st[$name]) . '</textarea>';
    else echo "<input class=\"form-control form-control-sm\" type=\"$type\" id=\"f_$name\" name=\"$name\" value=\"" . e($st[$name]) . "\" $attrs>";
    echo '</div>';
};
$sel = function ($name, $label, $options, $col = 'col-md-4', $empty = '– เลือก –') use ($st) {
    echo "<div class=\"$col\">";
    partial('select', ['name' => $name, 'label' => $label, 'options' => $options, 'value' => $st[$name], 'empty' => $empty]);
    echo '</div>';
};
$sps = array_combine(array_column($species, 'id'), array_map(fn($s) => $s['genus'] . ' ' . $s['epithet'], $species));
$dist = array_combine(array_column($districts, 'id'), array_map(fn($d) => $d['name_th'] . ' (' . $d['name_en'] . ')', $districts));
$src = array_combine(array_column($sources, 'id'), array_map(fn($s) => $s['name_th'] . ' (' . $s['name_en'] . ')', $sources));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0"><?= $st['id'] ? 'แก้ไขสายพันธุ์ ' . e($st['strain_code']) : 'เพิ่มสายพันธุ์ใหม่' ?></h1>
  <?php if ($st['id']): ?><div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('strain/' . $st['strain_code']) ?>"><i class="bi bi-eye"></i> ดูหน้าสาธารณะ</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('admin/phenotype/strain/' . $st['id']) ?>"><i class="bi bi-table"></i> ผลทดสอบ</a>
  </div><?php endif; ?>
</div>

<form method="post" action="<?= url('admin/strain/' . ($st['id'] ?: 'new')) ?>">
  <?= csrf_field() ?>
  <div class="card mb-3"><div class="card-header">รหัสและการระบุชนิด</div><div class="card-body row g-2">
    <?php $f('strain_code', 'รหัสสายพันธุ์ *', 'col-md-3', 'text', 'required pattern="[\w\-.]{2,40}"'); $f('other_codes', 'รหัสในคลังอื่น (TBRC, CBS …)', 'col-md-5'); ?>
    <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_type_strain" value="1" id="ts" <?= !empty($st['is_type_strain']) ? 'checked' : '' ?>>
      <label class="form-check-label small" for="ts">เป็น type strain</label></div></div>
    <?php $sel('species_id', 'ชนิด (Species)', $sps, 'col-md-5', '– ยังไม่ระบุชนิด –'); ?>
    <div class="col-md-1 d-flex align-items-end"><a class="btn btn-sm btn-outline-secondary w-100" href="<?= url('admin/species/new') ?>" target="_blank" title="เพิ่มชนิดใหม่"><i class="bi bi-plus"></i></a></div>
    <?php $sel('identification_status', 'สถานะการระบุชนิด', ['confirmed' => 'ยืนยันแล้ว (confirmed)', 'tentative' => 'เบื้องต้น (tentative)', 'unidentified' => 'ยังไม่ระบุ (unidentified)'], 'col-md-3', '');
          $f('identified_by', 'ผู้ระบุชนิด', 'col-md-3'); $f('identification_method', 'วิธีระบุชนิด', 'col-md-12', 'text', 'placeholder="เช่น ITS + D1/D2 LSU sequencing, BLAST ≥99% identity"'); ?>
  </div></div>

  <div class="card mb-3"><div class="card-header">แหล่งที่มาและสถานที่เก็บ</div><div class="card-body row g-2">
    <?php $sel('source_id', 'ประเภทแหล่งที่แยก', $src, 'col-md-4'); $f('substrate', 'Substrate (รายละเอียด)', 'col-md-8', 'text', 'placeholder="เช่น ดอกทองกวาว (Butea monosperma)"'); ?>
    <?php $sel('district_id', 'อำเภอ', $dist, 'col-md-4'); $f('subdistrict', 'ตำบล', 'col-md-3'); $f('locality', 'ชื่อสถานที่', 'col-md-5'); ?>
    <div class="col-md-5"><div class="row g-2">
      <?php $f('latitude', 'ละติจูด', 'col-6', 'text', 'inputmode="decimal" placeholder="19.1663"'); $f('longitude', 'ลองจิจูด', 'col-6', 'text', 'inputmode="decimal" placeholder="99.9019"'); ?>
      <?php $f('elevation_m', 'ความสูง (ม. รทก.)', 'col-6', 'number'); $f('collection_date', 'วันที่เก็บ', 'col-6', 'date'); ?>
      <?php $f('collector', 'ผู้เก็บตัวอย่าง', 'col-6'); $f('isolator', 'ผู้แยกเชื้อ', 'col-6'); ?>
    </div></div>
    <div class="col-md-7"><label class="form-label small">คลิกบนแผนที่เพื่อกำหนดพิกัด (ลากหมุดเพื่อปรับ)</label><div id="picker" class="map-small"></div></div>
    <?php $f('isolation_method', 'วิธีแยกเชื้อ', 'col-md-12', 'text', 'placeholder="เช่น enrichment ใน YM broth + chloramphenicol, 25°C"'); ?>
  </div></div>

  <div class="card mb-3"><div class="card-header">การเก็บรักษาและอื่น ๆ</div><div class="card-body row g-2">
    <?php $f('storage', 'วิธีเก็บรักษา', 'col-md-6', 'text', 'placeholder="-80°C 20% glycerol"');
          $sel('availability', 'สถานะการให้บริการ', ['available' => 'ให้บริการได้', 'restricted' => 'จำกัดการเข้าถึง', 'not_available' => 'ไม่ให้บริการ'], 'col-md-6', '');
          $f('remarks', 'หมายเหตุ', 'col-md-12', 'textarea'); ?>
  </div></div>
  <button class="btn btn-pyo mb-4"><i class="bi bi-save"></i> บันทึก</button>
</form>

<?php if ($st['id']): ?>
<div class="card mb-3" id="sequences"><div class="card-header">ลำดับ DNA</div><div class="card-body">
  <?php foreach ($sequences as $q): ?>
    <div class="border rounded p-2 mb-2">
      <div class="d-flex flex-wrap justify-content-between gap-2">
        <div><span class="badge text-bg-secondary"><?= e($q['locus']) ?></span> <?= $q['accession'] ? '<a target="_blank" rel="noopener" href="' . e(genbank_url($q['accession'])) . '">' . e($q['accession']) . '</a>' : '' ?>
          <span class="small text-muted"><?= $q['length_bp'] ? (int) $q['length_bp'] . ' bp' : 'ยังไม่มีลำดับ' ?><?= $q['ncbi_synced_at'] ? ' · ซิงก์ ' . e($q['ncbi_synced_at']) : '' ?></span></div>
        <div class="d-flex gap-1">
          <?php if ($q['sequence']): ?><a class="btn btn-sm btn-outline-primary py-0" target="_blank" rel="noopener" href="<?= e(blast_url($q['sequence'])) ?>">BLAST</a><?php endif; ?>
          <?php if ($q['accession']): ?><form method="post" action="<?= url('admin/sequence/' . $q['id'] . '/sync') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary py-0" title="ดึงลำดับจาก GenBank"><i class="bi bi-arrow-repeat"></i> ซิงก์</button></form><?php endif; ?>
          <form method="post" action="<?= url('admin/sequence/' . $q['id'] . '/delete') ?>" onsubmit="return confirm('ลบลำดับนี้?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-trash"></i></button></form>
        </div>
      </div>
      <?php if ($q['blast_top_hit']): ?><div class="small">BLAST: <?= e($q['blast_top_hit']) ?> <?= $q['blast_identity'] ? '(' . e($q['blast_identity']) . '%)' : '' ?></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
  <form method="post" action="<?= url('admin/strain/' . $st['id'] . '/sequence') ?>" class="row g-2 mt-1">
    <?= csrf_field() ?>
    <div class="col-md-3"><label class="form-label small">Locus</label>
      <select class="form-select form-select-sm" name="locus"><?php foreach (['D1/D2 LSU', 'ITS', 'ITS + D1/D2', 'SSU', 'TEF1', 'RPB1', 'RPB2', 'ACT1', 'อื่น ๆ'] as $l): ?><option><?= $l ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small">GenBank accession</label><input class="form-control form-control-sm" name="accession" placeholder="เช่น OR123456"></div>
    <div class="col-md-6 small text-muted d-flex align-items-end">ใส่เฉพาะ accession แล้วเว้นช่องลำดับว่าง ระบบจะดึงลำดับจาก NCBI ให้อัตโนมัติ</div>
    <div class="col-12"><label class="form-label small">ลำดับเบส (FASTA หรือข้อความล้วน)</label><textarea class="form-control form-control-sm font-monospace" name="sequence" rows="3"></textarea></div>
    <div class="col-md-6"><label class="form-label small">BLAST top hit</label><input class="form-control form-control-sm" name="blast_top_hit" placeholder="เช่น Pichia kudriavzevii CBS 573 (NR_131315)"></div>
    <div class="col-6 col-md-2"><label class="form-label small">% identity</label><input class="form-control form-control-sm" type="number" step="0.01" max="100" name="blast_identity"></div>
    <div class="col-6 col-md-2"><label class="form-label small">% coverage</label><input class="form-control form-control-sm" type="number" step="0.01" max="100" name="blast_coverage"></div>
    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-sm btn-outline-success w-100"><i class="bi bi-plus"></i> เพิ่มลำดับ</button></div>
  </form>
</div></div>

<?php partial('images_admin', ['type' => 'strain', 'id' => $st['id'], 'images' => $images]); ?>
<?php if (is_admin()): ?>
<form method="post" action="<?= url('admin/strain/' . $st['id'] . '/delete') ?>" onsubmit="return confirm('ลบสายพันธุ์นี้ถาวร รวมถึงลำดับ DNA ผลทดสอบ และรูปภาพ?')">
  <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> ลบสายพันธุ์นี้</button></form>
<?php endif; ?>
<?php endif; ?>
<?php $scripts = '<script>PYOPicker("picker", "f_latitude", "f_longitude");</script>'; ?>
