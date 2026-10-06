<h1 class="h3 mb-3">นำเข้าข้อมูลสายพันธุ์จาก CSV</h1>

<?php if (!$preview): ?>
<div class="row g-3">
  <div class="col-lg-6"><div class="card"><div class="card-body">
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">ไฟล์ CSV (UTF-8)</label><input class="form-control" type="file" name="csv" accept=".csv,text/csv" required></div>
      <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="create_species" value="1" id="cs" checked>
        <label class="form-check-label" for="cs">สร้างชนิดใหม่อัตโนมัติถ้ายังไม่มีในระบบ (เติม Classification ภายหลังด้วยปุ่ม "ดึงจาก NCBI")</label></div>
      <button class="btn btn-pyo"><i class="bi bi-eye"></i> ตรวจสอบก่อนนำเข้า</button>
    </form>
  </div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body small">
    <p class="fw-semibold mb-2">ขั้นตอน</p>
    <ol>
      <li>ดาวน์โหลด <a href="<?= url('admin/import/template.csv') ?>">ไฟล์เทมเพลต</a> แล้วกรอกข้อมูล 1 แถวต่อ 1 สายพันธุ์</li>
      <li>ใน Excel ให้ "บันทึกเป็น" <b>CSV UTF-8 (Comma delimited)</b> เพื่อให้ภาษาไทยถูกต้อง</li>
      <li>อัปโหลดเพื่อตรวจสอบ ระบบจะแสดงแถวที่มีปัญหาก่อนบันทึกจริง</li>
    </ol>
    <p class="mb-1"><b>คอลัมน์:</b> <code><?= e(implode(', ', IMPORT_COLUMNS)) ?></code></p>
    <ul class="mb-0">
      <li><code>district</code>: ชื่ออำเภอไทยหรืออังกฤษ เช่น เมืองพะเยา / Mueang Phayao</li>
      <li><code>source</code>: ประเภทแหล่ง เช่น ดอกไม้, ผลไม้, ดิน, แมลง</li>
      <li><code>collection_date</code>: YYYY-MM-DD หรือ DD/MM/YYYY (รับทั้ง ค.ศ. และ พ.ศ.)</li>
      <li><code>identification_status</code>: confirmed / tentative / unidentified</li>
    </ul>
  </div></div></div>
</div>
<?php else:
  $ok = count(array_filter($preview['rows'], fn($r) => !$r['errors']));
  $bad = count($preview['rows']) - $ok; ?>
  <?php if ($preview['missing']): ?>
    <div class="alert alert-danger">ไฟล์ไม่มีคอลัมน์ที่จำเป็น: <?= e(implode(', ', $preview['missing'])) ?></div>
  <?php endif; ?>
  <div class="alert alert-<?= $bad ? 'warning' : 'success' ?>">พร้อมนำเข้า <b><?= $ok ?></b> แถว<?= $bad ? " · มีปัญหา <b>$bad</b> แถว (จะข้ามแถวเหล่านี้)" : '' ?></div>
  <div class="card mb-3"><div class="table-responsive" style="max-height:60vh"><table class="table table-sm align-middle mb-0">
    <thead class="table-light sticky-top"><tr><th>บรรทัด</th><th>รหัส</th><th>ชนิด</th><th>อำเภอ</th><th>แหล่ง</th><th>วันที่</th><th>ผลตรวจ</th></tr></thead>
    <tbody><?php foreach ($preview['rows'] as $r): $d = $r['data']; ?>
      <tr class="<?= $r['errors'] ? 'table-danger' : ($r['warnings'] ? 'table-warning' : '') ?>">
        <td><?= (int) $r['line'] ?></td><td><?= e($d['strain_code'] ?? '') ?></td>
        <td><i><?= e(trim(($d['genus'] ?? '') . ' ' . ($d['epithet'] ?? ''))) ?></i></td>
        <td><?= e($d['district'] ?? '') ?></td><td><?= e($d['source'] ?? '') ?></td><td><?= e($r['date']) ?></td>
        <td class="small"><?php foreach ($r['errors'] as $x): ?><div class="text-danger"><i class="bi bi-x-circle"></i> <?= e($x) ?></div><?php endforeach; ?>
          <?php foreach ($r['warnings'] as $x): ?><div class="text-warning-emphasis"><i class="bi bi-exclamation-triangle"></i> <?= e($x) ?></div><?php endforeach; ?>
          <?= !$r['errors'] && !$r['warnings'] ? '<span class="text-success"><i class="bi bi-check"></i> OK</span>' : '' ?></td></tr>
    <?php endforeach; ?></tbody></table></div></div>
  <form method="post" class="d-flex gap-2">
    <?= csrf_field() ?>
    <button name="step" value="commit" class="btn btn-pyo" <?= $ok ? '' : 'disabled' ?>><i class="bi bi-check2"></i> นำเข้า <?= $ok ?> แถว</button>
    <button name="step" value="cancel" class="btn btn-outline-secondary">ยกเลิก / เลือกไฟล์ใหม่</button>
  </form>
<?php endif; ?>
