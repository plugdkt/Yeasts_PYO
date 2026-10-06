<?php
$f = function ($name, $label, $col = 'col-md-4', $type = 'text', $attrs = '') use ($sp) {
    echo "<div class=\"$col\"><label class=\"form-label small\" for=\"f_$name\">" . e($label) . "</label>";
    if ($type === 'textarea') echo "<textarea class=\"form-control form-control-sm\" id=\"f_$name\" name=\"$name\" rows=\"3\" $attrs>" . e($sp[$name]) . '</textarea>';
    else echo "<input class=\"form-control form-control-sm\" type=\"$type\" id=\"f_$name\" name=\"$name\" value=\"" . e($sp[$name]) . "\" $attrs>";
    echo '</div>';
};
$syn_text = implode("\n", array_map(fn($y) => $y['name'] . ($y['mycobank_no'] ? ' | MB#' . $y['mycobank_no'] : ''), $synonyms));
$bib_text = implode("\n", array_column($bib, 'citation'));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h3 mb-0"><?= $sp['id'] ? 'แก้ไข <i>' . e($sp['genus'] . ' ' . $sp['epithet']) . '</i>' : 'เพิ่มชนิดใหม่' ?></h1>
  <?php if ($sp['id']): ?><div class="d-flex gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('species/' . $sp['id']) ?>"><i class="bi bi-eye"></i> ดูหน้าสาธารณะ</a>
    <a class="btn btn-sm btn-outline-secondary" href="<?= url('admin/phenotype/species/' . $sp['id']) ?>"><i class="bi bi-table"></i> ผลทดสอบ</a>
  </div><?php endif; ?>
</div>

<form method="post" action="<?= url('admin/species/' . ($sp['id'] ?: 'new')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="ncbi_synced" id="ncbi_synced" value="">
  <div class="card mb-3"><div class="card-header d-flex justify-content-between align-items-center">ชื่อและการจัดจำแนก
    <span class="small fw-normal text-muted">กรอก Genus + epithet แล้วกด "ดึงจาก NCBI" เพื่อเติม Classification อัตโนมัติ</span></div>
    <div class="card-body row g-2">
      <?php $f('genus', 'Genus *', 'col-md-3', 'text', 'required'); $f('epithet', 'Species epithet *', 'col-md-3', 'text', 'required'); ?>
      <?php $f('authority', 'Authority', 'col-md-4'); $f('year_described', 'ปีที่ตั้งชื่อ', 'col-md-2', 'number'); ?>
      <div class="col-12 d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnNcbi"><i class="bi bi-cloud-download"></i> ดึงจาก NCBI Taxonomy</button>
        <span id="ncbiMsg" class="small"></span>
      </div>
      <?php foreach (['kingdom' => 'Kingdom', 'subkingdom' => 'Subkingdom', 'phylum' => 'Phylum', 'subphylum' => 'Subphylum',
          'class_name' => 'Class', 'order_name' => 'Order', 'family' => 'Family'] as $k => $l) $f($k, $l, 'col-6 col-md-3'); ?>
      <?php $f('ncbi_taxid', 'NCBI TaxID', 'col-6 col-md-3', 'number'); ?>
      <?php $f('mycobank_no', 'MycoBank no.', 'col-6 col-md-3'); $f('current_name', 'Current name', 'col-md-9'); ?>
      <?php $f('basionym', 'Basionym', 'col-md-12'); ?>
      <input type="hidden" name="ncbi_lineage" id="f_ncbi_lineage" value="<?= e($sp['ncbi_lineage']) ?>">
      <div class="col-md-6"><label class="form-label small">Taxonomic synonym(s) — บรรทัดละชื่อ รูปแบบ <code>ชื่อ authority | MB#เลข</code></label>
        <textarea class="form-control form-control-sm" name="synonyms" rows="4"><?= e($syn_text) ?></textarea></div>
      <div class="col-md-6"><label class="form-label small">Bibliography — บรรทัดละ 1 เอกสาร (ใส่ DOI ในบรรทัดได้)</label>
        <textarea class="form-control form-control-sm" name="bibliography" rows="4"><?= e($bib_text) ?></textarea></div>
    </div></div>

  <div class="card mb-3"><div class="card-header">คำอธิบาย</div><div class="card-body row g-2">
    <?php $f('phylogenetic_placement', 'Phylogenetic placement', 'col-md-12', 'textarea');
          $f('comments', 'Comments', 'col-md-12', 'textarea');
          $f('ecology', 'นิเวศวิทยา (Ecology)', 'col-md-6', 'textarea');
          $f('applications', 'การใช้ประโยชน์ / ศักยภาพ', 'col-md-6', 'textarea'); ?>
    <div class="col-12 small text-muted"><i class="bi bi-info-circle"></i> กรุณาเขียนสรุปด้วยตนเองพร้อมอ้างอิง ไม่คัดลอกข้อความจากฐานข้อมูลอื่นทั้งย่อหน้า (ลิขสิทธิ์)</div>
  </div></div>

  <div class="card mb-3"><div class="card-header">Morphology, reproduction &amp; physiology</div><div class="card-body row g-2">
    <?php $f('growth_description', 'Growth and colony description', 'col-md-12', 'textarea');
          $f('cell_shape', 'Cell shape', 'col-md-4'); $f('cell_size', 'Cell size (µm)', 'col-md-4'); $f('filaments', 'Filaments', 'col-md-4');
          $f('asexual_reproduction', 'Asexual reproduction', 'col-md-6'); $f('ascospores', 'Ascospores / basidiospores', 'col-md-6');
          $f('sexual_reproduction', 'Sexual reproduction', 'col-md-12', 'textarea');
          $f('physiology_summary', 'Physiology summary', 'col-md-9', 'textarea'); $f('coq_system', 'Coenzyme Q system', 'col-md-3'); ?>
  </div></div>

  <div class="card mb-3"><div class="card-header">Molecular information — ลำดับ D1/D2 LSU อ้างอิง (type strain)</div><div class="card-body row g-2">
    <?php $f('d1d2_ref_accession', 'GenBank accession (D1/D2)', 'col-md-4', 'text', 'placeholder="เช่น NG_042623"');
          $f('d1d2_ref_strain', 'Type strain', 'col-md-4', 'text', 'placeholder="เช่น NRRL Y-12632"'); ?>
    <div class="col-md-4 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-primary w-100" id="btnD1d2"><i class="bi bi-search"></i> ค้นหา D1/D2 ของ type strain ใน NCBI</button></div>
    <div class="col-12" id="d1d2Out"></div>
  </div></div>

  <div class="d-flex justify-content-between mb-4">
    <button class="btn btn-pyo"><i class="bi bi-save"></i> บันทึก</button>
  </div>
</form>

<?php if ($sp['id']): ?>
  <?php partial('images_admin', ['type' => 'species', 'id' => $sp['id'], 'images' => $images]); ?>
  <?php if (is_admin()): ?>
  <form method="post" action="<?= url('admin/species/' . $sp['id'] . '/delete') ?>" onsubmit="return confirm('ลบชนิดนี้ถาวร? สายพันธุ์ที่ผูกไว้จะกลายเป็น \'ยังไม่ระบุชนิด\'')">
    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> ลบชนิดนี้</button></form>
  <?php endif; ?>
<?php endif; ?>

<?php ob_start(); ?>
<script>
document.getElementById('btnNcbi').addEventListener('click', async () => {
  const msg = document.getElementById('ncbiMsg');
  const g = document.getElementById('f_genus').value.trim(), s = document.getElementById('f_epithet').value.trim();
  const taxid = document.getElementById('f_ncbi_taxid').value.trim();
  if (!taxid && (!g || !s)) { msg.innerHTML = '<span class="text-danger">กรอก Genus และ epithet ก่อน</span>'; return; }
  msg.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังค้นหาที่ NCBI…';
  try {
    const qs = taxid ? 'taxid=' + encodeURIComponent(taxid) : 'name=' + encodeURIComponent(g + ' ' + s);
    const r = await (await fetch('<?= url('admin/api/ncbi-taxonomy') ?>?' + qs)).json();
    if (!r.ok) { msg.innerHTML = '<span class="text-danger">' + (r.error || 'ไม่พบข้อมูล') + '</span>'; return; }
    let n = 0;
    for (const [k, v] of Object.entries(r.fields)) {
      const el = document.getElementById('f_' + k);
      if (el && v && k !== 'genus') { el.value = v; n++; }
    }
    document.getElementById('ncbi_synced').value = '1';
    const esc = t => String(t).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    msg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> พบ <i>' + esc(r.name) + '</i> (' + esc(r.rank) + ', TaxID ' + esc(r.fields.ncbi_taxid) +
      ') เติมข้อมูล ' + n + ' ช่อง — ตรวจสอบแล้วกดบันทึก</span>';
    if (g && s && r.name.toLowerCase() !== (g + ' ' + s).toLowerCase()) {
      msg.innerHTML += '<div class="text-warning-emphasis mt-1"><i class="bi bi-exclamation-triangle"></i> ชื่อที่ NCBI ใช้ปัจจุบันคือ <i>' + esc(r.name) +
        '</i> — ชื่อที่กรอกอาจเป็น synonym ควรพิจารณาเปลี่ยน Genus/epithet และย้ายชื่อเดิมไปไว้ในช่อง synonyms</div>';
    }
  } catch (e) { msg.innerHTML = '<span class="text-danger">เชื่อมต่อไม่ได้</span>'; }
});

// ค้นลำดับ D1/D2 ของ type material แล้วให้ผู้ใช้เลือก
document.getElementById('btnD1d2').addEventListener('click', async () => {
  const out = document.getElementById('d1d2Out');
  const esc = t => String(t).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const name = (document.getElementById('f_genus').value + ' ' + document.getElementById('f_epithet').value).trim();
  if (!name.includes(' ')) { out.innerHTML = '<span class="small text-danger">กรอก Genus และ epithet ก่อน</span>'; return; }
  out.innerHTML = '<span class="small"><span class="spinner-border spinner-border-sm"></span> กำลังค้นหาใน GenBank…</span>';
  try {
    const r = await (await fetch('<?= url('admin/api/ncbi-d1d2') ?>?name=' + encodeURIComponent(name))).json();
    if (!r.ok) { out.innerHTML = '<span class="small text-danger">' + esc(r.error) + '</span>'; return; }
    out.innerHTML = '<div class="small text-muted mb-1">เลือกลำดับที่ต้องการ (RefSeq NG_ คือลำดับที่ NCBI ตรวจสอบแล้ว):</div>' +
      r.items.map((it, i) => `<div class="form-check small"><input class="form-check-input" type="radio" name="_d1d2pick" id="pk${i}" value="${i}">
        <label class="form-check-label" for="pk${i}"><b>${esc(it.accession)}</b> ${it.refseq ? '<span class="badge text-bg-success">RefSeq</span>' : ''}
        <span class="text-muted">${it.length} bp</span> — ${esc(it.title)}</label></div>`).join('');
    out.querySelectorAll('input[name=_d1d2pick]').forEach(el => el.addEventListener('change', () => {
      const it = r.items[el.value];
      document.getElementById('f_d1d2_ref_accession').value = it.accession;
      // ดึงรหัสสายพันธุ์จาก title เช่น "... NRRL Y-12632 28S rRNA" หรือ "culture CBS:5147 large subunit"
      const m = it.title.match(/\b((?:CBS|NRRL|ATCC|JCM|NBRC|DSM|IFO|CBS:|TBRC|BCRC|CGMCC)[\s:]*[A-Z]?-?\s?\d+[A-Za-z]?)/);
      if (m) document.getElementById('f_d1d2_ref_strain').value = m[1].replace(':', ' ').replace(/\s+/g, ' ');
    }));
  } catch (e) { out.innerHTML = '<span class="small text-danger">เชื่อมต่อไม่ได้</span>'; }
});
</script>
<?php $scripts = ob_get_clean(); ?>
