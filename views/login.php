<div class="row justify-content-center"><div class="col-sm-8 col-md-5 col-lg-4">
  <div class="card mt-4"><div class="card-body p-4">
    <h1 class="h4 mb-3 text-center">เข้าสู่ระบบทีมวิจัย</h1>
    <form method="post" action="<?= url('login') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e(input('next')) ?>">
      <div class="mb-3"><label class="form-label">ชื่อผู้ใช้</label><input class="form-control" name="username" required autofocus autocomplete="username"></div>
      <div class="mb-3"><label class="form-label">รหัสผ่าน</label><input class="form-control" type="password" name="password" required autocomplete="current-password"></div>
      <button class="btn btn-pyo w-100">เข้าสู่ระบบ</button>
    </form>
    <p class="small text-muted mt-3 mb-0 text-center">สำหรับทีมวิจัยที่ได้รับบัญชีจากผู้ดูแลระบบ</p>
  </div></div>
</div></div>
