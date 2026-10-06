<?php
$use_map = (bool) $points;
$status = ['confirmed' => 'ยืนยันแล้ว', 'tentative' => 'เบื้องต้น (tentative)', 'unidentified' => 'ยังไม่ระบุชนิด'];
$avail = ['available' => 'ให้บริการได้', 'restricted' => 'จำกัดการเข้าถึง', 'not_available' => 'ไม่ให้บริการ'];
$row = function ($label, $value, $raw = false) {
    if ($value === null || $value === '') return;
    echo '<dt class="col-sm-4 col-lg-3">' . e($label) . '</dt><dd class="col-sm-8 col-lg-9">' . ($raw ? $value : e($value)) . '</dd>';
};
?>
<nav class="small mb-2"><a href="<?= url('strains') ?>">สายพันธุ์</a> / <?= e($st['strain_code']) ?></nav>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h1 class="h2 mb-1"><?= e($st['strain_code']) ?> <?= $st['is_type_strain'] ? '<span class="badge text-bg-info fs-6 align-middle">type strain</span>' : '' ?>
      <?= $st['is_demo'] ? '<span class="badge text-bg-warning fs-6 align-middle">demo</span>' : '' ?></h1>
    <div class="fs-5"><?= $st['species_id'] ? '<a href="' . url('species/' . $st['species_id']) . '">' . species_name($st) . '</a>' : species_name($st) ?>
      <span class="text-muted fs-6">· <?= $status[$st['identification_status']] ?? '' ?></span></div>
  </div>
  <?php if (is_logged_in()): ?><a class="btn btn-sm btn-pyo" href="<?= url('admin/strain/' . $st['id']) ?>"><i class="bi bi-pencil"></i> แก้ไข</a><?php endif; ?>
</div>

<div class="row g-4">
  <div class="<?= $points ? 'col-lg-7' : 'col-12' ?>">
    <div class="card"><div class="card-header">ข้อมูลการเก็บตัวอย่าง</div><div class="card-body"><dl class="row small mb-0">
      <?php
      $row('รหัสในคลังอื่น', $st['other_codes']);
      $row('ประเภทแหล่งที่แยก', $st['source_th'] ? $st['source_th'] . ' (' . $st['source_en'] . ')' : null);
      $row('Substrate', $st['substrate']);
      $row('สถานที่', implode(', ', array_filter([$st['locality'], $st['subdistrict'] ? 'ต.' . $st['subdistrict'] : null,
          $st['district_th'] ? 'อ.' . $st['district_th'] : null, 'จ.พะเยา'])));
      if ($st['latitude'] !== null) $row('พิกัด', coord($st['latitude']) . ', ' . coord($st['longitude']) .
          (is_logged_in() ? '' : ' <span class="text-muted">(แสดงแบบปัดเศษ ~1 กม.)</span>'), true);
      $row('ความสูง', $st['elevation_m'] !== null ? number_format((int) $st['elevation_m']) . ' ม. รทก.' : null);
      $row('วันที่เก็บ', $st['collection_date'] ? thai_date($st['collection_date']) : null);
      $row('ผู้เก็บตัวอย่าง', $st['collector']);
      $row('ผู้แยกเชื้อ', $st['isolator']);
      $row('วิธีแยกเชื้อ', $st['isolation_method']);
      $row('ผู้ระบุชนิด', $st['identified_by']);
      $row('วิธีระบุชนิด', $st['identification_method']);
      $row('การเก็บรักษา', $st['storage']);
      $row('สถานะการให้บริการ', $avail[$st['availability']] ?? null);
      $row('หมายเหตุ', $st['remarks'] ? nl2br(e($st['remarks'])) : null, true);
      ?>
    </dl></div></div>
  </div>
  <?php if ($points): ?><div class="col-lg-5"><div id="stmap" class="map-small" style="height:100%;min-height:300px"></div></div><?php endif; ?>
</div>

<?php [$d1d2, $other_seq] = split_d1d2($sequences); ?>
<h2 class="section-title h5" id="molecular">Molecular information <small class="text-muted fw-normal">— D1/D2 LSU rRNA accession number</small></h2>
<?php foreach ($d1d2 as $q): ?>
  <div class="card mb-3 border-start border-4" style="border-color:var(--pyo-green-2)!important"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <div class="small text-muted">GenBank accession number (D1/D2)</div>
        <?php if ($q['accession']): ?>
          <a class="fs-4 fw-semibold font-monospace" target="_blank" rel="noopener" href="<?= e(genbank_url($q['accession'])) ?>"><?= e($q['accession']) ?> <i class="bi bi-box-arrow-up-right fs-6"></i></a>
        <?php else: ?><span class="fs-5 text-muted">ยังไม่มีเลข accession</span><?php endif; ?>
        <?php if ($q['length_bp']): ?><span class="text-muted small ms-2"><?= number_format((int) $q['length_bp']) ?> bp</span><?php endif; ?>
      </div>
      <?php if ($q['sequence']): ?><a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="<?= e(blast_url($q['sequence'])) ?>"><i class="bi bi-search"></i> BLAST ที่ NCBI</a><?php endif; ?>
    </div>
    <?php if ($q['blast_top_hit']): ?><div class="small mt-2"><b>BLAST top hit:</b> <?= e($q['blast_top_hit']) ?>
      <?= $q['blast_identity'] ? ' · identity ' . e($q['blast_identity']) . '%' : '' ?><?= $q['blast_coverage'] ? ' · coverage ' . e($q['blast_coverage']) . '%' : '' ?></div><?php endif; ?>
    <?php if ($q['ncbi_title']): ?><div class="small text-muted mt-1">GenBank: <?= e($q['ncbi_title']) ?></div><?php endif; ?>
    <?php if ($q['sequence']): ?><details class="mt-2"><summary class="small">แสดงลำดับเบส</summary>
      <div class="seq-box mt-1">&gt;<?= e($st['strain_code'] . ' ' . $q['locus']) ?><br><?= e($q['sequence']) ?></div></details><?php endif; ?>
  </div></div>
<?php endforeach; ?>
<?php if (!$d1d2): ?><p class="text-muted">ยังไม่มีเลข accession ของลำดับ D1/D2</p><?php endif; ?>
<?php if ($st['d1d2_ref_accession']): ?>
  <p class="small">ลำดับอ้างอิงของ type strain <i><?= e($st['genus'] . ' ' . $st['epithet']) ?></i><?= $st['d1d2_ref_strain'] ? ' (' . e($st['d1d2_ref_strain']) . ')' : '' ?>:
    <a class="font-monospace" target="_blank" rel="noopener" href="<?= e(genbank_url($st['d1d2_ref_accession'])) ?>"><?= e($st['d1d2_ref_accession']) ?></a></p>
<?php endif; ?>
<?php if ($other_seq): ?>
  <div class="small"><b>ลำดับบริเวณอื่น:</b>
  <?php foreach ($other_seq as $q): ?><span class="me-3"><?= e($q['locus']) ?>
    <?= $q['accession'] ? '<a class="font-monospace" target="_blank" rel="noopener" href="' . e(genbank_url($q['accession'])) . '">' . e($q['accession']) . '</a>' : '(ไม่มีเลข accession)' ?></span><?php endforeach; ?></div>
<?php endif; ?>

<h2 class="section-title h5" id="physiology">Physiology
  <?php if (is_logged_in()): ?><a class="btn btn-sm btn-outline-secondary ms-2" href="<?= url('admin/phenotype/strain/' . $st['id']) ?>"><i class="bi bi-pencil"></i> กรอกผลทดสอบ</a><?php endif; ?></h2>
<?php partial('pheno_table', ['pheno' => $pheno, 'compare' => $pheno_sp ?: null]); ?>

<h2 class="section-title h5" id="images">รูปภาพ <?= $images ? '<span class="badge text-bg-success">' . count($images) . '</span>' : '' ?>
  <?php if (is_logged_in()): ?><a class="btn btn-sm btn-outline-secondary ms-2" href="<?= url('admin/strain/' . $st['id']) ?>#images"><i class="bi bi-upload"></i> เพิ่มรูป</a><?php endif; ?></h2>
<?php partial('gallery', ['images' => $images]); ?>
<?php if ($points) $scripts = '<script>PYOMap("stmap", ' . json_encode($points, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ', {legend:false, cluster:false, scroll:false});</script>'; ?>
