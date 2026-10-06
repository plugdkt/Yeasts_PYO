<?php
$choices = ['', '+', '-', 'w', 'd', 's', 'v', '-,+', '-,w', '?'];
$method = '';
foreach ($pheno as $rows) foreach ($rows as $r) if ($r['method']) { $method = $r['method']; break 2; }
$back = $type === 'species' ? url('admin/species/' . $entity['id']) : url('admin/strain/' . $entity['id']);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div><h1 class="h4 mb-0">ผลทดสอบทางสรีรวิทยา</h1>
    <div class="text-muted"><?= $type === 'species' ? 'ระดับชนิด (ค่าอ้างอิง): <i>' . e($entity['label']) . '</i>' : 'ระดับสายพันธุ์ (ผลจากแล็บ): ' . e($entity['label']) ?></div></div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= $back ?>"><i class="bi bi-arrow-left"></i> กลับ</a>
</div>
<form method="post">
  <?= csrf_field() ?>
  <div class="card mb-3"><div class="card-body row g-2 align-items-end">
    <div class="col-md-6"><label class="form-label small">วิธีทดสอบ</label>
      <input class="form-control form-control-sm" name="method" value="<?= e($method) ?>" list="methods" placeholder="Classical assay tubes method">
      <datalist id="methods"><option>Classical assay tubes method</option><option>API ID 32 C</option><option>Biolog YT MicroPlate</option><option>Replica plating</option></datalist></div>
    <div class="col-md-6 small text-muted">ค่า: <b>+</b> บวก · <b>-</b> ลบ · <b>w</b> อ่อน · <b>d</b> ช้า (delayed) · <b>s</b> ช้า (slow) · <b>v</b> แปรผัน · <b>?</b> ไม่ทราบ · เว้นว่าง = ไม่ได้ทดสอบ
      · ช่อง MIC พิมพ์ตัวเลขได้</div>
  </div></div>
  <?php foreach (test_groups() as $g => $label): $rows = $pheno[$g] ?? []; if (!$rows) continue; ?>
  <div class="card mb-3"><div class="card-header d-flex justify-content-between"><?= e($label) ?>
    <span class="d-flex gap-1"><?php foreach (['+', '-', ''] as $v): ?><button type="button" class="btn btn-sm btn-outline-secondary py-0 fill" data-g="<?= $g ?>" data-v="<?= $v ?>"><?= $v === '' ? 'ล้าง' : "ทั้งหมด $v" ?></button><?php endforeach; ?></span></div>
    <div class="card-body"><div class="row g-2">
      <?php foreach ($rows as $r): $free = $g === 'antimycotic' || $g === 'temperature'; ?>
        <div class="col-6 col-md-4 col-lg-3"><label class="form-label small mb-0 text-truncate d-block" title="<?= e($r['name']) ?>"><?= e($r['name']) ?></label>
          <?php if ($free): ?>
            <input class="form-control form-control-sm g-<?= $g ?>" name="r[<?= (int) $r['id'] ?>]" value="<?= e($r['result']) ?>" list="rlist" maxlength="30">
          <?php else: ?>
            <select class="form-select form-select-sm g-<?= $g ?>" name="r[<?= (int) $r['id'] ?>]">
              <?php foreach (array_unique([...$choices, $r['result'] ?? '']) as $c): ?><option value="<?= e($c) ?>" <?= (string) $r['result'] === $c ? 'selected' : '' ?>><?= $c === '' ? '–' : e($c) ?></option><?php endforeach; ?>
            </select>
          <?php endif; ?></div>
      <?php endforeach; ?>
    </div></div></div>
  <?php endforeach; ?>
  <datalist id="rlist"><option>+</option><option>-</option><option>w</option><option>v</option><option>yes</option><option>no</option><option>variable</option></datalist>
  <button class="btn btn-pyo mb-4"><i class="bi bi-save"></i> บันทึกผลทดสอบ</button>
</form>
<?php ob_start(); ?>
<script>
document.querySelectorAll('.fill').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.g-' + b.dataset.g).forEach(el => el.value = b.dataset.v);
}));
</script>
<?php $scripts = ob_get_clean(); ?>
