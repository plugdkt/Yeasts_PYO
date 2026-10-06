<?php $use_map = true; ?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
  <div><h1 class="h3 mb-0">แผนที่จุดเก็บตัวอย่าง</h1>
    <div class="text-muted small">แสดง <?= count($points) ?> จุด<?= is_logged_in() ? '' : ' · พิกัดแสดงแบบปัดเศษ (~1 กม.) เพื่อปกป้องแหล่งเก็บ' ?></div></div>
</div>
<?php partial('strain_filters', get_defined_vars() + ['action' => url('map')]); ?>
<div id="bigmap" class="map-full shadow-sm"></div>
<?php $scripts = '<script>PYOMap("bigmap", ' . json_encode($points, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ');</script>'; ?>
