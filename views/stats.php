<?php
$use_chart = true;
$status_th = ['confirmed' => 'ยืนยันแล้ว', 'tentative' => 'เบื้องต้น', 'unidentified' => 'ยังไม่ระบุ'];
$data['status'] = array_map(fn($r) => ['label' => $status_th[$r['label']] ?? $r['label'], 'n' => $r['n']], $data['status']);
$cell = [];
foreach ($matrix as $m) { $cell[$m['sp_name']]['id'] = $m['id']; $cell[$m['sp_name']][$m['did']] = $m['n']; }
$cmax = max([1, ...array_column($matrix, 'n')]);
$charts = [
    'district' => ['จำนวนสายพันธุ์รายอำเภอ', 'bar', 'col-lg-6'],
    'source'   => ['แหล่งที่แยก', 'doughnut', 'col-lg-6'],
    'genus'    => ['สกุลที่พบมากที่สุด (15 อันดับแรก)', 'barh', 'col-lg-6'],
    'year'     => ['จำนวนสายพันธุ์ตามปีที่เก็บ (ค.ศ.)', 'line', 'col-lg-6'],
    'phylum'   => ['Phylum', 'doughnut', 'col-lg-6'],
    'status'   => ['สถานะการระบุชนิด', 'doughnut', 'col-lg-6'],
];
?>
<h1 class="h3 mb-3">สถิติ</h1>
<div class="row g-3">
  <?php foreach ($charts as $k => [$label, $type, $col]): ?>
  <div class="<?= $col ?>"><div class="card h-100"><div class="card-header"><?= e($label) ?></div>
    <div class="card-body"><?php if ($data[$k]): ?><canvas id="c_<?= $k ?>" height="230"></canvas><?php else: ?><p class="text-muted mb-0">ยังไม่มีข้อมูล</p><?php endif; ?></div></div></div>
  <?php endforeach; ?>
</div>

<h2 class="section-title h5">การกระจายของชนิดในแต่ละอำเภอ</h2>
<?php if ($cell): ?>
<div class="card"><div class="table-responsive"><table class="table table-sm table-bordered heat mb-0">
  <thead class="table-light"><tr><th class="text-start">ชนิด</th><?php foreach ($districts as $d): ?><th class="small"><?= e($d['name_th']) ?></th><?php endforeach; ?><th>รวม</th></tr></thead>
  <tbody><?php foreach ($cell as $name => $c): $tot = 0; ?>
    <tr><td class="text-start"><a href="<?= url('species/' . $c['id']) ?>"><i><?= e($name) ?></i></a></td>
      <?php foreach ($districts as $d): $n = $c[$d['id']] ?? 0; $tot += $n; ?>
        <td style="<?= $n ? 'background:rgba(47,125,99,' . round(0.15 + 0.75 * $n / $cmax, 2) . ');color:' . ($n / $cmax > .5 ? '#fff' : 'inherit') : '' ?>"><?= $n ?: '' ?></td>
      <?php endforeach; ?><td class="fw-semibold"><?= $tot ?></td></tr>
  <?php endforeach; ?></tbody>
</table></div></div>
<?php else: ?><p class="text-muted">ยังไม่มีข้อมูล</p><?php endif; ?>

<?php ob_start(); ?>
<script>
const D = <?= json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const T = <?= json_encode(array_map(fn($c) => $c[1], $charts)) ?>;
const PAL = ['#2f7d63', '#d9a441', '#3b6fb6', '#b5523b', '#7a4fa3', '#3a9aa5', '#8a8a2e', '#c2577f', '#6b7a75', '#e07b39', '#1f5f4a', '#9aa5a1', '#5e8fd1', '#d07a62', '#a07cc5'];
Chart.defaults.font.family = "'IBM Plex Sans Thai', sans-serif";
for (const [k, rows] of Object.entries(D)) {
  const el = document.getElementById('c_' + k);
  if (!el) continue;
  const t = T[k], horiz = t === 'barh', round = t === 'doughnut';
  new Chart(el, {
    type: horiz ? 'bar' : t,
    data: { labels: rows.map(r => r.label), datasets: [{ label: 'จำนวนสายพันธุ์', data: rows.map(r => +r.n),
      backgroundColor: round ? PAL : '#2f7d63', borderColor: round ? '#fff' : '#2f7d63', tension: .3, fill: false }] },
    options: { indexAxis: horiz ? 'y' : 'x', maintainAspectRatio: false,
      plugins: { legend: { display: round, position: 'right' } },
      scales: round ? {} : { [horiz ? 'x' : 'y']: { beginAtZero: true, ticks: { precision: 0 } },
        ...(horiz ? { y: { ticks: { font: { style: 'italic' } } } } : {}) } }
  });
}
</script>
<?php $scripts = ob_get_clean(); ?>
