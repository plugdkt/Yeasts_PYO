<?php
// $pheno [group => rows], $compare (optional, matrix ระดับ species เพื่อเทียบ)
$compare = $compare ?? null;
$any = false;
foreach ($pheno as $rows) foreach ($rows as $r) if ($r['result'] !== null) $any = true;
if (!$any && !$compare): ?>
  <p class="text-muted">ยังไม่มีผลการทดสอบ</p>
<?php return; endif;
$cmp = [];
if ($compare) foreach ($compare as $rows) foreach ($rows as $r) $cmp[$r['id']] = $r['result'];
foreach (test_groups() as $g => $label):
  $rows = array_values(array_filter($pheno[$g] ?? [], fn($r) => $r['result'] !== null || ($compare && ($cmp[$r['id']] ?? null) !== null)));
  if (!$rows) continue;
  $method = current(array_filter(array_column($rows, 'method')));
  $half = (int) ceil(count($rows) / 2);
?>
  <h3 class="h6 mt-3 fw-semibold"><?= e($label) ?></h3>
  <div class="row g-2">
  <?php foreach ([array_slice($rows, 0, $half), array_slice($rows, $half)] as $col): ?>
    <div class="col-md-6"><table class="table table-sm table-pheno table-striped mb-0">
      <thead><tr><th>Test</th><th class="text-center">ผล</th><?php if ($compare): ?><th class="text-center small text-muted">อ้างอิงชนิด</th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($col as $r): $ref = $cmp[$r['id']] ?? null; ?>
        <tr><td><?= e($r['name']) ?><?= $r['code'] ? ' <small class="text-muted">(' . e($r['code']) . ')</small>' : '' ?></td>
          <td class="text-center"><?= result_badge($r['result']) ?></td>
          <?php if ($compare): ?><td class="text-center"><?= $ref !== null ? result_badge($ref) : '' ?>
            <?php if ($ref !== null && $r['result'] !== null && !in_array($ref, ['?', 'v'], true) && $ref !== $r['result']): ?><i class="bi bi-exclamation-triangle text-warning" title="ผลต่างจากค่าอ้างอิง"></i><?php endif; ?></td><?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
  <?php endforeach; ?>
  </div>
  <?php if ($method): ?><div class="small text-muted mt-1">วิธีทดสอบ: <?= e($method) ?></div><?php endif; ?>
<?php endforeach; ?>
<div class="small text-muted mt-2">สัญลักษณ์: <?= result_badge('+') ?> บวก <?= result_badge('-') ?> ลบ <?= result_badge('w') ?> อ่อน
  <?= result_badge('d') ?> ช้า (delayed) <?= result_badge('v') ?> แปรผัน <?= result_badge('?') ?> ไม่ทราบ</div>
