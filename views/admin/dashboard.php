<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <h1 class="h3 mb-0">จัดการข้อมูล</h1>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-sm btn-pyo" href="<?= url('admin/species/new') ?>"><i class="bi bi-plus"></i> เพิ่มชนิด</a>
    <a class="btn btn-sm btn-pyo" href="<?= url('admin/strain/new') ?>"><i class="bi bi-plus"></i> เพิ่มสายพันธุ์</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('admin/import') ?>"><i class="bi bi-upload"></i> นำเข้า CSV</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <?php foreach ([['species', 'ชนิด', 'species'], ['strains', 'สายพันธุ์', 'strains'], ['sequences', 'ลำดับ DNA', 'strains?has_seq=1'],
      ['unidentified', 'สายพันธุ์ที่ยังไม่ระบุชนิด', 'strains?status=unidentified'], ['no_coords', 'ยังไม่มีพิกัด', 'strains'],
      ['no_seq', 'ยังไม่มีลำดับ DNA', 'strains']] as [$k, $label, $link]): ?>
    <div class="col-6 col-md-4 col-lg-2"><a href="<?= url($link) ?>" class="text-decoration-none"><div class="stat-card">
      <div class="num"><?= number_format((int) $counts[$k]) ?></div><div class="lbl"><?= $label ?></div></div></a></div>
  <?php endforeach; ?>
</div>

<?php if ($counts['demo']): ?>
<div class="demo-banner rounded p-3 mb-3 small"><i class="bi bi-info-circle"></i> มีข้อมูลตัวอย่าง <?= (int) $counts['demo'] ?> สายพันธุ์ (DEMO-xxxx)
  ลบได้ด้วยคำสั่ง SQL ใน <code>database/remove_demo.sql</code> ก่อนเปิดใช้งานจริง</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-7"><div class="card"><div class="card-header">ชนิดทั้งหมด</div>
    <div class="table-responsive" style="max-height:520px"><table class="table table-sm table-hover align-middle mb-0">
      <thead class="table-light sticky-top"><tr><th>ชื่อ</th><th>Family</th><th class="text-center">NCBI</th><th class="text-center">สายพันธุ์</th><th></th></tr></thead>
      <tbody><?php foreach ($species as $s): ?>
        <tr><td><a href="<?= url('species/' . $s['id']) ?>"><?= species_name($s) ?></a></td><td class="small"><?= e($s['family']) ?></td>
          <td class="text-center"><?= $s['ncbi_taxid'] ? '<i class="bi bi-check-circle text-success" title="TaxID ' . (int) $s['ncbi_taxid'] . '"></i>' : '<i class="bi bi-dash-circle text-muted" title="ยังไม่เชื่อม NCBI"></i>' ?></td>
          <td class="text-center"><?= (int) $s['n'] ?></td>
          <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-secondary py-0" href="<?= url('admin/species/' . $s['id']) ?>"><i class="bi bi-pencil"></i></a>
            <a class="btn btn-sm btn-outline-secondary py-0" href="<?= url('admin/phenotype/species/' . $s['id']) ?>" title="ผลทดสอบ"><i class="bi bi-table"></i></a></td></tr>
      <?php endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-5"><div class="card"><div class="card-header">การแก้ไขล่าสุด</div>
    <ul class="list-group list-group-flush small">
      <?php foreach ($log as $l): ?>
        <li class="list-group-item"><span class="badge text-bg-light"><?= e($l['action']) ?></span> <?= e($l['entity']) ?>
          <?= $l['entity_id'] ? '#' . (int) $l['entity_id'] : '' ?> <?= e($l['detail']) ?>
          <div class="text-muted"><?= e($l['full_name'] ?? 'system') ?> · <?= e($l['created_at']) ?></div></li>
      <?php endforeach; ?>
      <?php if (!$log): ?><li class="list-group-item text-muted">ยังไม่มีรายการ</li><?php endif; ?>
    </ul></div></div>
</div>
