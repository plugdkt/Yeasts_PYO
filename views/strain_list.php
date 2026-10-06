<?php
$qs = $_GET;
unset($qs['page'], $qs['sort']);
$status = ['confirmed' => ['success', 'ยืนยัน'], 'tentative' => ['warning', 'เบื้องต้น'], 'unidentified' => ['secondary', 'ยังไม่ระบุ']];
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div><h1 class="h3 mb-0">สายพันธุ์ (Strains / Isolates)</h1><div class="text-muted small">พบ <?= number_format($pg['total']) ?> สายพันธุ์</div></div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('map', $qs) ?>"><i class="bi bi-geo-alt"></i> ดูบนแผนที่</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('export/strains.csv', $qs) ?>"><i class="bi bi-download"></i> CSV</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('export/sequences.fasta', $qs) ?>"><i class="bi bi-download"></i> FASTA</a>
    <?php if (is_logged_in()): ?><a class="btn btn-sm btn-pyo" href="<?= url('admin/strain/new') ?>"><i class="bi bi-plus"></i> เพิ่มสายพันธุ์</a><?php endif; ?>
  </div>
</div>

<?php partial('strain_filters', get_defined_vars() + ['action' => url('strains'), 'with_sort' => true]); ?>

<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><th>รหัส</th><th>ชนิด</th><th class="d-none d-md-table-cell">แหล่งที่แยก</th><th>อำเภอ</th>
    <th class="d-none d-lg-table-cell">วันที่เก็บ</th><th class="d-none d-md-table-cell">D1/D2 accession</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): [$cls, $lbl] = $status[$r['identification_status']] ?? ['light', '']; ?>
    <tr>
      <td><a class="fw-semibold" href="<?= url('strain/' . $r['strain_code']) ?>"><?= e($r['strain_code']) ?></a>
        <?= $r['is_type_strain'] ? '<span title="type strain">♦</span>' : '' ?></td>
      <td><?= $r['species_id'] ? '<a href="' . url('species/' . $r['species_id']) . '">' . species_name($r) . '</a>' : species_name($r) ?>
        <span class="badge text-bg-<?= $cls ?> ms-1" style="font-size:.65rem"><?= $lbl ?></span></td>
      <td class="d-none d-md-table-cell small"><?= e($r['source_th'] ?? '–') ?><?= $r['substrate'] ? '<div class="text-muted">' . e($r['substrate']) . '</div>' : '' ?></td>
      <td class="small"><?= e($r['district_th'] ?? '–') ?></td>
      <td class="d-none d-lg-table-cell small"><?= thai_date($r['collection_date']) ?></td>
      <td class="d-none d-md-table-cell"><?= $r['d1d2_accession'] ? '<a class="font-monospace small" target="_blank" rel="noopener" href="' . e(genbank_url($r['d1d2_accession'])) . '">' . e($r['d1d2_accession']) . '</a>' : '<span class="text-muted">–</span>' ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไข</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
<div class="mt-3"><?php partial('pager', ['pg' => $pg]); ?></div>
