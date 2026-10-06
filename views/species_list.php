<?php
$opt = fn($rows, $k, $v = null) => array_combine(array_column($rows, $k), array_column($rows, $v ?? $k));
$dist = array_combine(array_column($districts, 'id'), array_map(fn($d) => 'อ.' . $d['name_th'], $districts));
$src = array_combine(array_column($sources, 'id'), array_column($sources, 'name_th'));
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div><h1 class="h3 mb-0">ชนิดยีสต์ (Species)</h1><div class="text-muted small">พบ <?= number_format($pg['total']) ?> ชนิด</div></div>
  <div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('export/species.csv') ?>"><i class="bi bi-download"></i> CSV</a>
    <?php if (is_logged_in()): ?><a class="btn btn-sm btn-pyo" href="<?= url('admin/species/new') ?>"><i class="bi bi-plus"></i> เพิ่มชนิด</a><?php endif; ?>
  </div>
</div>

<form class="card filter-card mb-3"><div class="card-body row g-2 align-items-end">
  <div class="col-md-4"><label class="form-label">ค้นหา</label>
    <input class="form-control form-control-sm" name="q" value="<?= e(input('q')) ?>" placeholder="ชื่อชนิด, synonym, นิเวศวิทยา, การใช้ประโยชน์"></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'phylum', 'label' => 'Phylum', 'options' => $opt($phyla, 'phylum'), 'value' => input('phylum')]); ?></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'family', 'label' => 'Family', 'options' => $opt($families, 'family'), 'value' => input('family')]); ?></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'genus', 'label' => 'Genus', 'options' => $opt($genera, 'genus'), 'value' => input('genus')]); ?></div>
  <div class="col-6 col-md-2"><?php partial('select', ['name' => 'district', 'label' => 'พบที่อำเภอ', 'options' => $dist, 'value' => input('district')]); ?></div>
  <div class="col-6 col-md-3"><?php partial('select', ['name' => 'source', 'label' => 'แหล่งที่แยก', 'options' => $src, 'value' => input('source')]); ?></div>
  <div class="col-6 col-md-3"><?php partial('select', ['name' => 'sort', 'label' => 'เรียงตาม', 'options' => ['family' => 'Family', 'strains' => 'จำนวนสายพันธุ์'], 'value' => input('sort'), 'empty' => 'ชื่อวิทยาศาสตร์']); ?></div>
  <div class="col-md-3"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="found" value="1" id="found" <?= input('found') === '1' ? 'checked' : '' ?>>
    <label class="form-check-label small" for="found">เฉพาะชนิดที่พบในพะเยา</label></div></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-sm btn-pyo flex-fill"><i class="bi bi-funnel"></i> กรอง</button>
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('species') ?>">ล้าง</a></div>
</div></form>

<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><th>ชื่อวิทยาศาสตร์</th><th class="d-none d-md-table-cell">Family</th><th class="d-none d-lg-table-cell">Phylum</th><th class="text-center">สายพันธุ์</th><th class="d-none d-md-table-cell">อำเภอที่พบ</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><a class="fw-semibold" href="<?= url('species/' . $r['id']) ?>"><?= species_name($r) ?></a>
        <small class="text-muted"><?= e($r['authority']) ?></small><?= $r['is_demo'] ? ' <span class="badge text-bg-warning">demo</span>' : '' ?></td>
      <td class="d-none d-md-table-cell"><?= e($r['family']) ?></td>
      <td class="d-none d-lg-table-cell small"><?= e($r['phylum']) ?></td>
      <td class="text-center"><?= $r['n_strains'] ? '<a href="' . url('strains', ['species' => $r['id']]) . '" class="badge text-bg-success">' . $r['n_strains'] . '</a>' : '<span class="text-muted">0</span>' ?></td>
      <td class="d-none d-md-table-cell small"><?= e($r['districts'] ?? '–') ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไข</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
<div class="mt-3"><?php partial('pager', ['pg' => $pg]); ?></div>
