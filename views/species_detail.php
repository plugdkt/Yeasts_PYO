<?php
$use_map = (bool) $points;
$ranks = ['Kingdom' => 'kingdom', 'Subkingdom' => 'subkingdom', 'Phylum' => 'phylum', 'Subphylum' => 'subphylum', 'Class' => 'class_name',
    'Order' => 'order_name', 'Family' => 'family', 'Genus' => 'genus'];
$sections = ['classification' => 'Classification', 'phylogeny' => 'Phylogenetic placement', 'comments' => 'Comments',
    'strains' => 'สายพันธุ์ที่พบในพะเยา', 'morphology' => 'Morphology and reproduction', 'physiology' => 'Physiology',
    'molecular' => 'Molecular information', 'images' => 'รูปภาพ', 'bibliography' => 'Bibliography'];
$text = fn($s) => $s ? nl2br(e($s)) : '<span class="text-muted">–</span>';
?>
<div class="row g-4">
  <aside class="col-lg-3 d-none d-lg-block">
    <nav class="toc card"><div class="card-body p-2">
      <div class="small text-muted px-2 mb-1 fw-semibold">ON THIS PAGE</div>
      <?php foreach ($sections as $k => $v): ?><a href="#<?= $k ?>"><?= e($v) ?></a><?php endforeach; ?>
    </div></nav>
  </aside>

  <div class="col-lg-9">
    <nav class="small mb-2"><a href="<?= url('species') ?>">ชนิดยีสต์</a> / <?= e($sp['family'] ?? '') ?></nav>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
      <div>
        <h1 class="h2 mb-1"><i><?= e($sp['genus'] . ' ' . $sp['epithet']) ?></i>
          <?= $sp['is_demo'] ? '<span class="badge text-bg-warning fs-6 align-middle">demo</span>' : '' ?></h1>
        <div class="text-muted"><?= e($sp['authority']) ?><?= $sp['year_described'] ? ' ' . (int) $sp['year_described'] : '' ?>
          <?php if ($sp['mycobank_no']): ?> · <a href="<?= e(mycobank_url($sp['mycobank_no'])) ?>" target="_blank" rel="noopener">MB#<?= e($sp['mycobank_no']) ?></a><?php endif; ?></div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <?php if ($sp['ncbi_taxid']): ?><a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="<?= e(ncbi_taxonomy_url((int) $sp['ncbi_taxid'])) ?>"><i class="bi bi-box-arrow-up-right"></i> NCBI Taxonomy</a><?php endif; ?>
        <?php if (is_logged_in()): ?><a class="btn btn-sm btn-pyo" href="<?= url('admin/species/' . $sp['id']) ?>"><i class="bi bi-pencil"></i> แก้ไข</a><?php endif; ?>
      </div>
    </div>
    <div class="small text-muted mt-2">Record ID: <?= (int) $sp['id'] ?> · สร้างเมื่อ <?= thai_date($sp['created_at']) ?><?= $sp['created_name'] ? ' โดย ' . e($sp['created_name']) : '' ?>
      · แก้ไขล่าสุด <?= thai_date($sp['updated_at']) ?><?= $sp['updated_name'] ? ' โดย ' . e($sp['updated_name']) : '' ?></div>

    <h2 class="section-title h5" id="classification">Classification</h2>
    <div class="taxo-path mb-3">
      <?php $first = true; foreach ($ranks as $label => $col): if (!$sp[$col]) continue; ?>
        <?php if (!$first): ?><span class="arrow">→</span><?php endif; $first = false; ?>
        <div><span class="rank"><?= $label ?></span><?= $col === 'genus' ? '<i>' . e($sp[$col]) . '</i>' : e($sp[$col]) ?></div>
      <?php endforeach; ?>
    </div>
    <dl class="row small mb-0">
      <?php if ($sp['current_name']): ?><dt class="col-sm-3">Current name</dt><dd class="col-sm-9"><?= e($sp['current_name']) ?></dd><?php endif; ?>
      <?php if ($sp['basionym']): ?><dt class="col-sm-3">Basionym</dt><dd class="col-sm-9"><?= e($sp['basionym']) ?></dd><?php endif; ?>
      <?php if ($synonyms): ?><dt class="col-sm-3">Taxonomic synonym(s)</dt><dd class="col-sm-9">
        <?php foreach ($synonyms as $y): ?><div><?= e($y['name']) ?><?= $y['mycobank_no'] ? ' [<a href="' . e(mycobank_url($y['mycobank_no'])) . '" target="_blank" rel="noopener">MB#' . e($y['mycobank_no']) . '</a>]' : '' ?></div><?php endforeach; ?></dd><?php endif; ?>
      <?php if ($sp['ncbi_taxid']): ?><dt class="col-sm-3">NCBI TaxID</dt><dd class="col-sm-9"><?= (int) $sp['ncbi_taxid'] ?>
        <?= $sp['ncbi_synced_at'] ? '<span class="text-muted">(ซิงก์ ' . thai_date($sp['ncbi_synced_at']) . ')</span>' : '' ?></dd><?php endif; ?>
    </dl>

    <h2 class="section-title h5" id="phylogeny">Phylogenetic placement</h2>
    <p><?= $text($sp['phylogenetic_placement']) ?></p>

    <h2 class="section-title h5" id="comments">Comments</h2>
    <p><?= $text($sp['comments']) ?></p>
    <?php if ($sp['ecology']): ?><h3 class="h6 fw-semibold">นิเวศวิทยา (Ecology)</h3><p><?= $text($sp['ecology']) ?></p><?php endif; ?>
    <?php if ($sp['applications']): ?><h3 class="h6 fw-semibold">การใช้ประโยชน์ / ศักยภาพ</h3><p><?= $text($sp['applications']) ?></p><?php endif; ?>

    <h2 class="section-title h5" id="strains">สายพันธุ์ที่พบในพะเยา <span class="badge text-bg-success"><?= count($strains) ?></span></h2>
    <?php if ($strains): ?>
      <div class="row g-3">
        <div class="<?= $points ? 'col-md-7' : 'col-12' ?>">
          <div class="table-responsive"><table class="table table-sm align-middle">
            <thead><tr><th>รหัส</th><th>แหล่งที่แยก</th><th>อำเภอ</th><th>วันที่เก็บ</th></tr></thead>
            <tbody><?php foreach ($strains as $s): ?>
              <tr><td><a href="<?= url('strain/' . $s['strain_code']) ?>"><?= e($s['strain_code']) ?></a><?= $s['is_type_strain'] ? ' <span title="type strain">♦</span>' : '' ?></td>
                <td class="small"><?= e($s['source_th'] ?? '') ?><?= $s['substrate'] ? ' – ' . e($s['substrate']) : '' ?></td>
                <td class="small"><?= e($s['district_th'] ?? '–') ?></td><td class="small"><?= thai_date($s['collection_date']) ?></td></tr>
            <?php endforeach; ?></tbody></table></div>
        </div>
        <?php if ($points): ?><div class="col-md-5"><div id="spmap" class="map-small"></div></div><?php endif; ?>
      </div>
    <?php else: ?><p class="text-muted">ยังไม่พบสายพันธุ์ของชนิดนี้ในพะเยา</p><?php endif; ?>
    <?php if (is_logged_in()): ?><a class="btn btn-sm btn-outline-success" href="<?= url('admin/strain/new', ['species' => $sp['id']]) ?>"><i class="bi bi-plus"></i> เพิ่มสายพันธุ์ของชนิดนี้</a><?php endif; ?>

    <h2 class="section-title h5" id="morphology">Morphology and reproduction</h2>
    <dl class="row small">
      <?php foreach (['growth_description' => 'Growth and colony description', 'cell_shape' => 'Cell shape', 'cell_size' => 'Cell size',
          'filaments' => 'Filaments', 'asexual_reproduction' => 'Asexual reproduction', 'sexual_reproduction' => 'Sexual reproduction',
          'ascospores' => 'Ascospores / basidiospores'] as $k => $label): ?>
        <dt class="col-sm-3"><?= $label ?></dt><dd class="col-sm-9"><?= $text($sp[$k]) ?></dd>
      <?php endforeach; ?>
    </dl>

    <h2 class="section-title h5" id="physiology">Physiology
      <?php if (is_logged_in()): ?><a class="btn btn-sm btn-outline-secondary ms-2" href="<?= url('admin/phenotype/species/' . $sp['id']) ?>"><i class="bi bi-pencil"></i> แก้ไขผลทดสอบ</a><?php endif; ?></h2>
    <?php if ($sp['physiology_summary']): ?><p><?= $text($sp['physiology_summary']) ?></p><?php endif; ?>
    <?php if ($sp['coq_system']): ?><p class="small"><b>Coenzyme Q system:</b> <?= e($sp['coq_system']) ?></p><?php endif; ?>
    <?php partial('pheno_table', ['pheno' => $pheno]); ?>

    <?php
    [$d1d2, $other_seq] = split_d1d2($sequences);
    $d1d2_by_strain = [];
    foreach ($d1d2 as $q) $d1d2_by_strain[$q['strain_code']][] = $q;
    $acc_link = fn($a) => '<a class="font-monospace fw-semibold" target="_blank" rel="noopener" href="' . e(genbank_url($a)) . '">' . e($a) . '</a>';
    ?>
    <h2 class="section-title h5" id="molecular">Molecular information <small class="text-muted fw-normal">— D1/D2 LSU rRNA accession number</small></h2>

    <div class="card mb-3"><div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">
      <div class="small text-muted">ลำดับอ้างอิงของ type strain</div>
      <?php if ($sp['d1d2_ref_accession']): ?>
        <div class="fs-5"><?= $acc_link($sp['d1d2_ref_accession']) ?></div>
        <?php if ($sp['d1d2_ref_strain']): ?><div class="small">Type strain: <b><?= e($sp['d1d2_ref_strain']) ?></b></div><?php endif; ?>
      <?php else: ?><span class="text-muted">ยังไม่ได้ระบุ</span><?php endif; ?>
    </div></div>

    <div class="table-responsive"><table class="table table-sm align-middle">
      <thead><tr><th>สายพันธุ์ในพะเยา</th><th>D1/D2 accession number</th><th class="text-end">bp</th><th>BLAST top hit</th></tr></thead>
      <tbody>
      <?php foreach ($strains as $s): $rows = $d1d2_by_strain[$s['strain_code']] ?? [null]; foreach ($rows as $q): ?>
        <tr><td><a href="<?= url('strain/' . $s['strain_code']) ?>#molecular"><?= e($s['strain_code']) ?></a></td>
          <td><?= $q && $q['accession'] ? $acc_link($q['accession']) : '<span class="text-muted small">' . ($q ? 'มีลำดับ ยังไม่มีเลข accession' : 'ยังไม่มี') . '</span>' ?></td>
          <td class="text-end"><?= $q && $q['length_bp'] ? number_format((int) $q['length_bp']) : '' ?></td>
          <td class="small"><?= e($q['blast_top_hit'] ?? '') ?><?= !empty($q['blast_identity']) ? ' (' . e($q['blast_identity']) . '%)' : '' ?></td></tr>
      <?php endforeach; endforeach; ?>
      <?php if (!$strains): ?><tr><td colspan="4" class="text-muted">ยังไม่มีสายพันธุ์ของชนิดนี้</td></tr><?php endif; ?>
      </tbody></table></div>
    <?php if ($other_seq): ?>
      <div class="small"><b>ลำดับบริเวณอื่น:</b>
        <?php foreach ($other_seq as $q): ?><span class="me-3"><?= e($q['strain_code']) ?> <?= e($q['locus']) ?>
          <?= $q['accession'] ? $acc_link($q['accession']) : '' ?></span><?php endforeach; ?></div>
    <?php endif; ?>
    <?php if ($sequences): ?>
      <a class="btn btn-sm btn-outline-secondary mt-2" href="<?= url('export/sequences.fasta', ['species' => $sp['id'], 'locus' => 'D1/D2 LSU']) ?>"><i class="bi bi-download"></i> ดาวน์โหลด FASTA (D1/D2)</a>
    <?php endif; ?>
    <?php if ($sp['ncbi_taxid']): ?>
      <div class="small mt-2">ข้อมูลเพิ่มเติมที่ NCBI:
        <a target="_blank" rel="noopener" href="https://www.ncbi.nlm.nih.gov/nuccore/?term=txid<?= (int) $sp['ncbi_taxid'] ?>[Organism:exp]">Nucleotide</a> ·
        <a target="_blank" rel="noopener" href="https://www.ncbi.nlm.nih.gov/datasets/genome/?taxon=<?= (int) $sp['ncbi_taxid'] ?>">Genomes (Datasets)</a> ·
        <a target="_blank" rel="noopener" href="https://pubmed.ncbi.nlm.nih.gov/?term=<?= urlencode('"' . $sp['genus'] . ' ' . $sp['epithet'] . '"') ?>">PubMed</a></div>
    <?php endif; ?>

    <h2 class="section-title h5" id="images">รูปภาพ</h2>
    <?php if ($images): ?><div class="row g-2 gallery"><?php foreach ($images as $im): ?>
      <div class="col-6 col-md-3"><a href="<?= url($im['file_path']) ?>" target="_blank"><img src="<?= url($im['file_path']) ?>" alt="<?= e($im['caption']) ?>" loading="lazy"></a>
        <div class="small text-muted"><?= e($im['caption']) ?></div></div>
    <?php endforeach; ?></div><?php else: ?><p class="text-muted">ยังไม่มีรูปภาพ</p><?php endif; ?>

    <h2 class="section-title h5" id="bibliography">Bibliography</h2>
    <?php if ($bib): ?><ol class="small"><?php foreach ($bib as $b): ?>
      <li class="mb-1"><?= e($b['citation']) ?><?= $b['doi'] && !str_contains($b['citation'], $b['doi']) ? ' doi:' . e($b['doi']) : '' ?>
        <?= $b['doi'] ? ' <a target="_blank" rel="noopener" href="https://doi.org/' . e($b['doi']) . '"><i class="bi bi-box-arrow-up-right"></i></a>' : '' ?></li>
    <?php endforeach; ?></ol><?php else: ?><p class="text-muted">–</p><?php endif; ?>
  </div>
</div>
<?php if ($points) $scripts = '<script>PYOMap("spmap", ' . json_encode($points, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ', {legend:false, scroll:false});</script>'; ?>
