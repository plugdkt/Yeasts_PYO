<?php // $type (species|strain), $id, $images ?>
<div class="card mb-3" id="images"><div class="card-header d-flex justify-content-between align-items-center">รูปภาพ
  <span class="small fw-normal text-muted"><?= count($images) ?> รูป</span></div><div class="card-body">

  <?php foreach ($images as $im): $cid = 'img' . (int) $im['id']; ?>
    <div class="border rounded p-2 mb-2">
      <div class="d-flex gap-3 align-items-start">
        <a href="<?= url($im['file_path']) ?>" target="_blank" class="flex-shrink-0"><img src="<?= image_thumb_url($im) ?>" alt="" style="width:120px;height:90px;object-fit:cover;border-radius:.35rem"></a>
        <div class="flex-grow-1 small">
          <span class="badge text-bg-secondary"><?= e(image_types()[$im['image_type']] ?? $im['image_type']) ?></span>
          <?php if ($im['width']): ?><span class="text-muted ms-1"><?= (int) $im['width'] ?>×<?= (int) $im['height'] ?> px</span><?php endif; ?>
          <div><?= e($im['caption']) ?></div>
          <div class="text-muted"><?= e(image_conditions($im)) ?></div>
          <?php if ($im['photographer'] || $im['credit']): ?><div class="text-muted"><?= e(implode(' · ', array_filter([$im['photographer'] ? 'ถ่ายโดย ' . $im['photographer'] : null, $im['credit']]))) ?></div><?php endif; ?>
        </div>
        <div class="d-flex flex-column gap-1">
          <button class="btn btn-sm btn-outline-secondary py-0" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $cid ?>"><i class="bi bi-pencil"></i> แก้ไข</button>
          <form method="post" action="<?= url('admin/image/' . $im['id'] . '/delete') ?>" onsubmit="return confirm('ลบรูปนี้ถาวร?')"><?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-danger py-0 w-100"><i class="bi bi-trash"></i> ลบ</button></form>
        </div>
      </div>
      <form method="post" action="<?= url('admin/image/' . $im['id'] . '/update') ?>" class="collapse row g-2 mt-1" id="<?= $cid ?>">
        <?= csrf_field() ?>
        <?php partial('image_meta_fields', ['im' => $im, 'uid' => $cid]); ?>
        <div class="col-12"><button class="btn btn-sm btn-pyo"><i class="bi bi-save"></i> บันทึกข้อมูลรูป</button></div>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (!$images): ?><p class="text-muted small">ยังไม่มีรูป</p><?php endif; ?>

  <hr>
  <div class="fw-semibold small mb-2"><i class="bi bi-upload"></i> อัปโหลดรูปใหม่ — เลือกได้หลายไฟล์ ข้อมูลด้านล่างจะใช้กับทุกไฟล์ในครั้งนี้</div>
  <form method="post" enctype="multipart/form-data" action="<?= url("admin/image/$type/$id") ?>" class="row g-2">
    <?= csrf_field() ?>
    <div class="col-12"><input class="form-control form-control-sm" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required>
      <div class="form-text">JPG, PNG, WEBP ไม่เกิน 10 MB ต่อไฟล์ (สูงสุด 20 ไฟล์ต่อครั้ง) · ระบบเก็บไฟล์ต้นฉบับและสร้างภาพย่อให้อัตโนมัติ</div></div>
    <?php partial('image_meta_fields', ['im' => ['image_type' => $type === 'strain' ? 'cell' : 'other'], 'uid' => 'new']); ?>
    <div class="col-12"><button class="btn btn-sm btn-outline-success"><i class="bi bi-upload"></i> อัปโหลด</button></div>
  </form>
  <datalist id="dl_media"><?php foreach (image_media() as $m): ?><option value="<?= e($m) ?>"><?php endforeach; ?></datalist>
  <datalist id="dl_tech"><?php foreach (image_techniques() as $m): ?><option value="<?= e($m) ?>"><?php endforeach; ?></datalist>
  <datalist id="dl_lic"><option value="CC BY 4.0"><option value="CC BY-NC 4.0"><option value="CC0"><option value="All rights reserved"></datalist>
</div></div>
