<?php
// $images — แกลเลอรีจัดกลุ่มตามประเภท + กล่องดูภาพใหญ่ (Bootstrap modal)
if (!$images): ?>
  <p class="text-muted">ยังไม่มีรูปภาพ</p>
<?php return; endif;
$types = image_types();
$lic = config('data_license');
foreach (images_grouped($images) as $t => $group): ?>
  <h3 class="h6 fw-semibold mt-3"><?= e($types[$t]) ?> <span class="text-muted fw-normal small">(<?= count($group) ?>)</span></h3>
  <div class="row g-2 gallery">
  <?php foreach ($group as $im):
    $info = [
        'src' => url($im['file_path']), 'caption' => $im['caption'], 'type' => $types[$im['image_type']] ?? '',
        'strain' => $im['strain_code'] ?? null, 'cond' => image_conditions($im), 'scale' => $im['scale_bar'],
        'by' => $im['photographer'], 'date' => $im['taken_date'] ? thai_date($im['taken_date']) : null,
        'credit' => $im['credit'], 'license' => $im['license'] ?: $lic,
        'size' => $im['width'] ? $im['width'] . '×' . $im['height'] . ' px' : null,
    ]; ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a href="<?= url($im['file_path']) ?>" class="d-block gal-item" data-info="<?= e(json_encode($info, JSON_UNESCAPED_UNICODE)) ?>">
        <img src="<?= image_thumb_url($im) ?>" alt="<?= e($im['caption'] ?: $types[$im['image_type']] ?? '') ?>" loading="lazy"></a>
      <div class="small mt-1 lh-sm">
        <?php if (!empty($im['strain_code'])): ?><span class="badge text-bg-light"><?= e($im['strain_code']) ?></span><?php endif; ?>
        <?= e($im['caption']) ?>
        <div class="text-muted"><?= e(image_conditions($im)) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<div class="modal fade" id="galModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
  <div class="modal-header py-2"><h5 class="modal-title h6" id="galTitle"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body p-0 bg-dark text-center"><img id="galImg" src="" alt="" style="max-width:100%;max-height:75vh"></div>
  <div class="modal-footer justify-content-start small" id="galMeta"></div>
</div></div></div>
<?php
page_scripts(<<<'JS'
<script>
(() => {
  const modal = new bootstrap.Modal(document.getElementById('galModal'));
  const esc = t => String(t ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  document.querySelectorAll('.gal-item').forEach(a => a.addEventListener('click', ev => {
    ev.preventDefault();
    const d = JSON.parse(a.dataset.info);
    document.getElementById('galImg').src = d.src;
    document.getElementById('galTitle').innerHTML = esc(d.type) + (d.strain ? ' · ' + esc(d.strain) : '');
    const rows = [['คำบรรยาย', d.caption], ['เงื่อนไข', d.cond], ['Scale bar', d.scale], ['ผู้ถ่าย', d.by], ['วันที่ถ่าย', d.date],
      ['เครดิต', d.credit], ['สัญญาอนุญาต', d.license], ['ขนาด', d.size]].filter(r => r[1]);
    document.getElementById('galMeta').innerHTML = rows.map(r => '<span class="me-3"><b>' + r[0] + ':</b> ' + esc(r[1]) + '</span>').join('') +
      '<a class="ms-auto" href="' + esc(d.src) + '" target="_blank"><i class="bi bi-box-arrow-up-right"></i> ไฟล์ต้นฉบับ</a>';
    modal.show();
  }));
})();
</script>
JS);
