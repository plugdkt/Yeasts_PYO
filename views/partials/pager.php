<?php
// $pg = paginate(...), ใช้ query string ปัจจุบัน
if ($pg['pages'] <= 1) return;
$link = function ($p) { $q = $_GET; $q['page'] = $p; return '?' . http_build_query($q); };
$from = max(1, $pg['page'] - 3);
$to = min($pg['pages'], $pg['page'] + 3);
?>
<nav><ul class="pagination pagination-sm justify-content-center flex-wrap">
  <li class="page-item <?= $pg['page'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e($link($pg['page'] - 1)) ?>">‹</a></li>
  <?php if ($from > 1): ?><li class="page-item"><a class="page-link" href="<?= e($link(1)) ?>">1</a></li><?php if ($from > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; endif; ?>
  <?php for ($i = $from; $i <= $to; $i++): ?>
    <li class="page-item <?= $i === $pg['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= e($link($i)) ?>"><?= $i ?></a></li>
  <?php endfor; ?>
  <?php if ($to < $pg['pages']): ?><?php if ($to < $pg['pages'] - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?><li class="page-item"><a class="page-link" href="<?= e($link($pg['pages'])) ?>"><?= $pg['pages'] ?></a></li><?php endif; ?>
  <li class="page-item <?= $pg['page'] >= $pg['pages'] ? 'disabled' : '' ?>"><a class="page-link" href="<?= e($link($pg['page'] + 1)) ?>">›</a></li>
</ul></nav>
