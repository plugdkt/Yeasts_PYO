<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
  <div class="card mt-4"><div class="card-body p-4">
    <h1 class="h4 mb-1">ตั้งค่าครั้งแรก</h1>
    <p class="text-muted small">ยังไม่มีผู้ใช้ในระบบ — สร้างบัญชีผู้ดูแลระบบ (admin) คนแรก หน้านี้จะปิดอัตโนมัติหลังสร้างเสร็จ</p>
    <form method="post" action="<?= url('setup') ?>">
      <?= csrf_field() ?>
      <div class="mb-2"><label class="form-label">ชื่อ-นามสกุล</label><input class="form-control" name="full_name" required></div>
      <div class="mb-2"><label class="form-label">อีเมล</label><input class="form-control" type="email" name="email"></div>
      <div class="mb-2"><label class="form-label">ชื่อผู้ใช้ (a-z, 0-9, _)</label><input class="form-control" name="username" required pattern="\w{3,50}" autocomplete="username"></div>
      <div class="mb-2"><label class="form-label">รหัสผ่าน (อย่างน้อย 8 ตัว)</label><input class="form-control" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
      <div class="mb-3"><label class="form-label">ยืนยันรหัสผ่าน</label><input class="form-control" type="password" name="password2" required minlength="8" autocomplete="new-password"></div>
      <button class="btn btn-pyo w-100">สร้างผู้ดูแลระบบ</button>
    </form>
  </div></div>
</div></div>
