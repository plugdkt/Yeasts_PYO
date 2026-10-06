<?php // $type (species|strain), $id, $images ?>
<div class="card mb-3"><div class="card-header">รูปภาพ</div><div class="card-body">
  <div class="row g-2 gallery mb-3">
    <?php foreach ($images as $im): ?>
      <div class="col-6 col-md-3"><img src="<?= url($im['file_path']) ?>" alt="">
        <div class="d-flex justify-content-between align-items-center small mt-1"><span class="text-muted text-truncate"><?= e($im['caption']) ?></span>
          <form method="post" action="<?= url('admin/image/' . $im['id'] . '/delete') ?>" onsubmit="return confirm('ลบรูปนี้?')"><?= csrf_field() ?>
            <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button></form></div></div>
    <?php endforeach; ?>
    <?php if (!$images): ?><div class="text-muted small">ยังไม่มีรูป</div><?php endif; ?>
  </div>
  <form method="post" enctype="multipart/form-data" action="<?= url("admin/image/$type/$id") ?>" class="row g-2 align-items-end">
    <?= csrf_field() ?>
    <div class="col-md-5"><input class="form-control form-control-sm" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></div>
    <div class="col-md-5"><input class="form-control form-control-sm" name="caption" placeholder="คำบรรยาย เช่น เซลล์บน YM agar 3 วัน, 25°C"></div>
    <div class="col-md-2"><button class="btn btn-sm btn-outline-success w-100"><i class="bi bi-upload"></i> อัปโหลด</button></div>
  </form>
</div></div>
