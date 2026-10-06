<div class="row justify-content-center"><div class="col-lg-9">
<h1 class="h3 mb-3">เกี่ยวกับฐานข้อมูล</h1>
<div class="card mb-3"><div class="card-body">
  <p><b><?= e(config('app_name_th')) ?></b> (<?= e(config('app_name')) ?>) เป็นคลังข้อมูลยีสต์ที่แยกได้จากแหล่งธรรมชาติและผลิตภัณฑ์พื้นบ้านในจังหวัดพะเยา
    เพื่อใช้เป็นข้อมูลอ้างอิงด้านความหลากหลายทางชีวภาพของจุลินทรีย์ และต่อยอดการใช้ประโยชน์ด้านเทคโนโลยีชีวภาพ</p>
  <p class="mb-0">โครงสร้างข้อมูลระดับชนิด (Classification, Morphology, Physiology, Molecular information) จัดเรียงตามแนวทางของ
    <a href="https://theyeasts.org/" target="_blank" rel="noopener">The Yeasts Database</a> และเชื่อมโยงข้อมูลอนุกรมวิธานและลำดับ DNA กับ
    <a href="https://www.ncbi.nlm.nih.gov/" target="_blank" rel="noopener">NCBI</a></p>
</div></div>

<div class="card mb-3"><div class="card-header">ข้อมูลที่จัดเก็บ</div><div class="card-body small">
  <ul class="mb-0">
    <li><b>ชนิด (Species)</b> — อนุกรมวิธาน, synonyms, ลักษณะสัณฐาน, การสืบพันธุ์, ผลทดสอบทางสรีรวิทยา, บรรณานุกรม</li>
    <li><b>สายพันธุ์ (Strain)</b> — แหล่งที่แยก, อำเภอ/ตำบล, พิกัด, วันที่เก็บ, ผู้เก็บ/ผู้แยก/ผู้ระบุ, วิธีเก็บรักษา</li>
    <li><b>ลำดับ DNA</b> — ITS, D1/D2 LSU ฯลฯ พร้อมเลข GenBank accession และผล BLAST</li>
  </ul>
</div></div>

<div class="card mb-3"><div class="card-header">การอ้างอิงฐานข้อมูล</div><div class="card-body small">
  <p class="mb-2">หากนำข้อมูลไปใช้ กรุณาอ้างอิง:</p>
  <div class="p-2 bg-light rounded font-monospace"><?= e(config('app_name')) ?> (<?= date('Y') ?>). <?= e(config('app_name_th')) ?>.
    University of Phayao. Available at: <?= e((isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . url('')) ?> (accessed <?= date('j M Y') ?>).</div>
  <p class="mt-2 mb-0">ข้อมูลเผยแพร่ภายใต้สัญญาอนุญาต <b><?= e(config('data_license')) ?></b> · ข้อมูลที่ดึงจาก NCBI เป็นสาธารณสมบัติตาม
    <a href="https://www.ncbi.nlm.nih.gov/home/about/policies/" target="_blank" rel="noopener">นโยบายของ NCBI</a></p>
</div></div>

<div class="card"><div class="card-header">ดาวน์โหลดข้อมูล</div><div class="card-body d-flex flex-wrap gap-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?= url('export/strains.csv') ?>"><i class="bi bi-filetype-csv"></i> สายพันธุ์ทั้งหมด (CSV, Darwin Core)</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= url('export/species.csv') ?>"><i class="bi bi-filetype-csv"></i> ชนิดทั้งหมด (CSV)</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?= url('export/sequences.fasta') ?>"><i class="bi bi-file-earmark-text"></i> ลำดับ DNA (FASTA)</a>
</div></div>
</div></div>
