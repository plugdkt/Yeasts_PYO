<?php
// $name, $label, $options [value => text], $value, $empty (ข้อความตัวเลือกว่าง), $class
$value = (string) ($value ?? '');
?>
<label class="form-label"><?= e($label) ?></label>
<select name="<?= e($name) ?>" class="form-select <?= e($class ?? 'form-select-sm') ?>">
  <option value=""><?= e($empty ?? 'ทั้งหมด') ?></option>
  <?php foreach ($options as $v => $t): ?>
    <option value="<?= e($v) ?>" <?= (string) $v === $value ? 'selected' : '' ?>><?= e($t) ?></option>
  <?php endforeach; ?>
</select>
