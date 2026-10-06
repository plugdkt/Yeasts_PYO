<div class="row justify-content-center"><div class="col-md-6 col-lg-4">
  <div class="card mt-3"><div class="card-body">
    <h1 class="h5 mb-3">เปลี่ยนรหัสผ่าน</h1>
    <form method="post"><?= csrf_field() ?>
      <div class="mb-2"><label class="form-label small">รหัสผ่านปัจจุบัน</label><input class="form-control" type="password" name="current" required autocomplete="current-password"></div>
      <div class="mb-2"><label class="form-label small">รหัสผ่านใหม่ (≥ 8 ตัว)</label><input class="form-control" type="password" name="new" minlength="8" required autocomplete="new-password"></div>
      <div class="mb-3"><label class="form-label small">ยืนยันรหัสผ่านใหม่</label><input class="form-control" type="password" name="new2" minlength="8" required autocomplete="new-password"></div>
      <button class="btn btn-pyo w-100">บันทึก</button>
    </form>
  </div></div>
</div></div>
