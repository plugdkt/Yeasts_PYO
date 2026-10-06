<h1 class="h3 mb-3">ผู้ใช้งาน</h1>
<div class="row g-3">
  <div class="col-lg-8"><div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead class="table-light"><tr><th>ชื่อผู้ใช้</th><th>ชื่อ</th><th>สิทธิ์</th><th>ตั้งรหัสผ่านใหม่</th><th></th></tr></thead>
    <tbody><?php foreach ($users as $u): $me = $u['id'] === current_user()['id']; ?>
      <tr><td><?= e($u['username']) ?><?= $me ? ' <span class="badge text-bg-light">คุณ</span>' : '' ?></td>
        <td class="small"><?= e($u['full_name']) ?><div class="text-muted"><?= e($u['email']) ?></div></td>
        <td><?php if ($me): ?><?= e($u['role']) ?><?php else: ?>
          <form method="post" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="act" value="role"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
              <?php foreach (['editor', 'admin'] as $r): ?><option <?= $u['role'] === $r ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select></form><?php endif; ?></td>
        <td><form method="post" class="d-flex gap-1"><?= csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
          <input type="password" name="password" class="form-control form-control-sm" minlength="8" placeholder="≥ 8 ตัว" autocomplete="new-password" required>
          <button class="btn btn-sm btn-outline-secondary">ตั้ง</button></form></td>
        <td><?php if (!$me): ?><form method="post" onsubmit="return confirm('ลบผู้ใช้นี้?')"><?= csrf_field() ?><input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
          <button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i></button></form><?php endif; ?></td></tr>
    <?php endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-header">เพิ่มผู้ใช้</div><div class="card-body">
    <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="create">
      <div class="mb-2"><label class="form-label small">ชื่อผู้ใช้</label><input class="form-control form-control-sm" name="username" pattern="\w{3,50}" required autocomplete="off"></div>
      <div class="mb-2"><label class="form-label small">ชื่อ-นามสกุล</label><input class="form-control form-control-sm" name="full_name" required></div>
      <div class="mb-2"><label class="form-label small">อีเมล</label><input class="form-control form-control-sm" type="email" name="email"></div>
      <div class="mb-2"><label class="form-label small">รหัสผ่านเริ่มต้น</label><input class="form-control form-control-sm" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
      <div class="mb-3"><label class="form-label small">สิทธิ์</label><select class="form-select form-select-sm" name="role"><option value="editor">editor — เพิ่ม/แก้ไขข้อมูล</option><option value="admin">admin — ทุกอย่าง + ลบ + จัดการผู้ใช้</option></select></div>
      <button class="btn btn-sm btn-pyo w-100">เพิ่มผู้ใช้</button>
    </form>
  </div></div></div>
</div>
