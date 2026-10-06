# ข้อกำหนดระบบ (Spec): ฐานข้อมูลยีสต์จังหวัดพะเยา (Phayao Yeast Database, PYO-YDB)

> สถานะ: **v0.4** — M1–M4 พัฒนาแล้ว (prototype) ใช้ค่าเริ่มต้นใน §10 ระหว่างรอทีมตัดสินใจ (2026-10-06)
> ผู้ร่าง: Claude ร่วมกับ wittaya.su@up.ac.th

### บันทึกการเปลี่ยนแปลง
- **v0.4** — รูปภาพยีสต์ (§3.6): ข้อมูลประกอบรูป (ประเภท, อาหารเลี้ยงเชื้อ, อุณหภูมิ/ระยะเวลา, เทคนิค, กำลังขยาย, scale bar,
  ผู้ถ่าย, วันที่, เครดิต/สัญญาอนุญาต), อัปโหลดหลายไฟล์, ภาพย่ออัตโนมัติ, แก้ไข/เรียงลำดับ, แกลเลอรีจัดกลุ่มตามประเภท + ดูภาพใหญ่;
  migration `003_image_metadata.sql`
- **v0.3** — หัวข้อ Molecular information เน้น **GenBank accession number ของลำดับ D1/D2 LSU rRNA**:
  ระดับชนิดเพิ่มเลข D1/D2 อ้างอิงของ type strain (`d1d2_ref_accession`, `d1d2_ref_strain`) พร้อมปุ่มค้นจาก NCBI
  (`sequence_from_type[Filter]`, เรียง RefSeq NG_ ก่อน); ระดับสายพันธุ์แสดงเลข D1/D2 เด่น ลำดับเบสพับเก็บ;
  รายการสายพันธุ์/CSV มีคอลัมน์ D1/D2 accession; ตัวกรอง "มีเลข D1/D2"; เทมเพลตนำเข้าใช้ `d1d2_accession`, `d1d2_sequence`
  (ยังรับ `lsu_*` เดิม); migration: `database/migrations/001_*.sql`, `002_*.sql`
- **v0.2** — ตัดสินใจชั่วคราว: PHP ไม่ใช้ framework, พิกัดสาธารณะปัดเศษ 2 ตำแหน่ง (~1 กม.; ทีมที่ล็อกอินเห็นค่าเต็ม),
  รหัส `PYO-Y0001`, UI ภาษาไทย, ข้อมูลตัวอย่างใช้รหัส `DEMO-` และ `is_demo = 1`; เพิ่ม API key ของ NCBI (เก็บใน `config.local.php`);
  ค้นชื่อใน NCBI รองรับ synonym (เช่น *Candida krusei* → *Pichia kudriavzevii*);
  นำเข้าข้อมูลรองรับ CSV เท่านั้นในเฟสนี้ (Excel ให้ Save As CSV UTF-8) และรับวันที่ พ.ศ.
- **v0.1** — ร่างแรก

---

## 1. ที่มาและวัตถุประสงค์

รวบรวม จัดเก็บ และเผยแพร่ข้อมูลยีสต์ที่แยกได้จากแหล่งต่าง ๆ ในจังหวัดพะเยา ในรูปแบบฐานข้อมูลออนไลน์ โดยยึดโครงสร้างข้อมูลแบบ **The Yeasts Database** (https://theyeasts.org/details/774/1915) และเชื่อมโยงกับข้อมูลของ **NCBI** (Taxonomy, GenBank, BLAST)

วัตถุประสงค์
1. เป็นคลังข้อมูลกลางของสายพันธุ์ยีสต์ (isolate) ที่เก็บได้ในพะเยา พร้อมข้อมูลแหล่งที่เก็บ พิกัด ลำดับ DNA และผลทดสอบทางสรีรวิทยา
2. ให้สาธารณะและนักวิจัยค้นหา ดูรายละเอียด แผนที่ และสถิติได้
3. ให้ทีมวิจัยเพิ่ม/แก้ไขข้อมูลผ่านหน้าเว็บ และนำเข้าข้อมูลจำนวนมากจากไฟล์ Excel/CSV
4. ลดงานป้อนข้อมูลซ้ำ ด้วยการดึง taxonomy และลำดับ DNA จาก NCBI อัตโนมัติ

นอกขอบเขต (เฟสนี้)
- ระบบรัน BLAST บนเซิร์ฟเวอร์ของเราเอง (ใช้ NCBI BLAST ออนไลน์แทน)
- ระบบขอเบิก/ส่งเชื้อ (Material Transfer) — อาจทำในเฟสถัดไป
- การวิเคราะห์จีโนมทั้งชุด (genome assembly/annotation)

---

## 2. ผู้ใช้และสิทธิ์

| บทบาท | เข้าระบบ | สิทธิ์ |
|---|---|---|
| ผู้เยี่ยมชม (สาธารณะ) | ไม่ต้อง | ค้นหา ดูรายละเอียด แผนที่ สถิติ ดาวน์โหลด CSV/FASTA ของข้อมูลที่เปิดเผย |
| Editor (ทีมวิจัย) | ต้อง | เพิ่ม/แก้ไข species, strain, sequence, ผลทดสอบ, รูปภาพ, นำเข้า CSV, ซิงก์ NCBI |
| Admin | ต้อง | ทุกอย่างของ Editor + จัดการผู้ใช้ ลบข้อมูล ดู audit log |

ข้อกำหนดการเปิดเผยข้อมูล
- พิกัดละเอียดของจุดเก็บตัวอย่าง: **(ต้องตัดสินใจ — ดู §10 ข้อ 3)**
- สายพันธุ์ที่ตั้งค่า `availability = restricted` จะยังแสดงข้อมูลทั่วไป แต่ซ่อนช่องที่กำหนด

---

## 3. ข้อมูลที่จัดเก็บ

อ้างอิงหัวข้อของหน้ารายละเอียดใน theyeasts.org: *Classification, Phylogenetic placement, Comments, Strains information, Morphology and reproduction, Physiology, Molecular information, Bibliography*

### 3.1 Species (ชนิด)
- **Classification**: Kingdom → Subkingdom → Phylum → Subphylum → Class → Order → Family → Genus, ชื่อ epithet, authority, ปีที่ตั้งชื่อ
- **Nomenclature**: MycoBank no., NCBI TaxID, current name, basionym, synonyms (หลายรายการ)
- **ข้อความ**: phylogenetic placement, comments, ecology, การใช้ประโยชน์/ศักยภาพเทคโนโลยีชีวภาพ
- **Morphology & reproduction**: ลักษณะโคโลนี, รูปร่าง/ขนาดเซลล์, filaments (pseudohyphae/true hyphae), asexual/sexual reproduction, ascospores
- **Physiology**: สรุปข้อความ, CoQ system, ผลทดสอบ (ดู 3.4)
- **บรรณานุกรม**, **รูปภาพ**

### 3.2 Strain / Isolate (สายพันธุ์ที่แยกได้ในพะเยา) — หัวใจของระบบ
| กลุ่ม | ฟิลด์ |
|---|---|
| รหัส | รหัสหลัก (รูปแบบ `PYO-Y0001`), รหัสในคลังอื่น (TBRC, CBS, NRRL …) |
| การระบุชนิด | species, สถานะ (confirmed / tentative / unidentified), type strain?, ผู้ระบุ, วิธีระบุ |
| แหล่งที่มา | ประเภทแหล่ง (ดอกไม้, ผลไม้, ใบไม้, ดิน, แมลง, อาหารหมัก, น้ำ …), รายละเอียด substrate |
| สถานที่ | อำเภอ (9 อำเภอ), ตำบล, ชื่อสถานที่, lat/lng, ความสูง (ม.) |
| การเก็บ | วันที่เก็บ, ผู้เก็บ, ผู้แยก, วิธีแยก |
| การเก็บรักษา | วิธีเก็บ (−80 °C glycerol, freeze-dried …), สถานะการให้บริการ |
| อื่น ๆ | หมายเหตุ, รูปภาพ, ผลทดสอบระดับ strain |

### 3.3 Sequence (ลำดับ DNA) — เน้น D1/D2 LSU
- ข้อมูลหลักที่แสดงในหัวข้อ Molecular information คือ **เลข GenBank accession ของลำดับ D1/D2** ทั้งของสายพันธุ์ในพะเยา และของ type strain (อ้างอิงระดับชนิด)
- ลำดับบริเวณอื่น (ITS, TEF1 …) เก็บได้ แสดงเป็นข้อมูลรอง
- locus (ITS, D1/D2 LSU, TEF1, RPB2 …), GenBank accession, ลำดับเบส, ความยาว
- ผล BLAST: top hit, %identity, %coverage
- ข้อมูลที่ดึงจาก NCBI: title, วันที่ซิงก์

### 3.4 Phenotype tests (ตามหัวข้อของ theyeasts.org)
| กลุ่ม | ตัวอย่าง |
|---|---|
| Fermentation | D-Glucose, D-Galactose, Maltose, Sucrose, Lactose, Raffinose … |
| Carbon assimilation | ~47 รายการ (L-Arabinose, Cellobiose, Ethanol, Glycerol, D-Xylose …) |
| Nitrogen assimilation | Nitrate, Nitrite, Cadaverine, L-Lysine, Ethylamine … |
| Additional tests | 50 % glucose, 10 % NaCl, Urease, DBB, Starch formation, Vitamin-free, Cycloheximide … |
| Growth temperature | 4, 12, 15, 19, 21, 25, 30, 35, 37, 40, 42, 45 °C |
| Antimycotics | MIC/ความต้านทาน: Fluconazole, Amphotericin B, 5FC … |

รหัสผล: `+` บวก, `-` ลบ, `w` อ่อน, `d` ช้า (delayed), `v` แปรผัน, `s` ช้า (slow), `?` ไม่ทราบ หรือค่าตัวเลข (MIC)
บันทึกได้ทั้ง **ระดับ species** (ค่าอ้างอิงจากเอกสาร) และ **ระดับ strain** (ผลจริงจากแล็บ) แล้วแสดงเทียบกัน

### 3.5 ข้อมูลอ้างอิงคงที่
- 9 อำเภอของพะเยา (ชื่อไทย/อังกฤษ, พิกัดกลาง): เมืองพะเยา, จุน, เชียงคำ, เชียงม่วน, ดอกคำใต้, ปง, แม่ใจ, ภูซาง, ภูกามยาว
- ประเภทแหล่งที่มา (แก้ไขได้โดย admin)

### 3.6 รูปภาพ (Images)
ผูกกับ species หรือ strain ได้อย่างใดอย่างหนึ่ง หน้าชนิดแสดงรูปของชนิดเอง + รูปจากทุก strain ของชนิดนั้น

| ฟิลด์ | รายละเอียด |
|---|---|
| ประเภทรูป | โคโลนี (colony), เซลล์ (cells), เส้นใย (pseudohyphae/hyphae), สปอร์ (asci/ascospores/basidiospores), อื่น ๆ |
| อาหารเลี้ยงเชื้อ | เช่น YM agar, PDA, malt extract agar, Dalmau plate (corn meal) |
| เงื่อนไขการเลี้ยง | อุณหภูมิ (°C), ระยะเวลา (วัน) |
| การถ่ายภาพ | กล้อง/เทคนิค (bright field, phase contrast, DIC, stereo, ถ่ายจานเลี้ยงเชื้อ), กำลังขยาย, ขนาด scale bar |
| ผู้ถ่าย / วันที่ถ่าย | |
| ที่มาและสัญญาอนุญาต | ค่าเริ่มต้น = สัญญาอนุญาตของฐานข้อมูล; ถ้าเป็นรูปจากแหล่งอื่นต้องระบุเครดิต |
| คำบรรยาย | ข้อความอิสระ |

การจัดการไฟล์
- รับ JPG/PNG/WEBP ≤ 10 MB, อัปโหลดหลายไฟล์พร้อมกันได้ (ใช้ข้อมูลประกอบชุดเดียวกัน)
- สร้าง **ภาพย่อ (thumbnail)** อัตโนมัติ กว้าง 480 px สำหรับแกลเลอรี; เก็บไฟล์ต้นฉบับไว้ครบเพื่อใช้ทางวิทยาศาสตร์ (ถ้าเซิร์ฟเวอร์ไม่มี GD ให้ใช้ไฟล์ต้นฉบับแทน)
- แก้ไขข้อมูลประกอบรูปภายหลังได้ และเรียงลำดับรูปได้
- หน้าแสดงผล: จัดกลุ่มรูปตามประเภท, คลิกเพื่อดูภาพใหญ่พร้อมข้อมูลประกอบทั้งหมด
- ไฟล์รูปไม่อยู่ใน Git — ต้องรวมโฟลเดอร์ `public/uploads/` ในการสำรองข้อมูล (§7)

### 3.7 แผนภาพความสัมพันธ์ (ER)

```
districts 1─* strains *─1 species 1─* synonyms
sources   1─* strains        species *─* bibliography
strains 1─* sequences        species/strains 1─* images
phenotype_tests 1─* phenotype_results *─1 (species | strain)
users 1─* audit_log
```

---

## 4. ฟีเจอร์และหน้าจอ

### 4.1 ส่วนสาธารณะ
| # | หน้า | รายละเอียด |
|---|---|---|
| P1 | หน้าแรก | แนะนำโครงการ, ตัวเลขสรุป (จำนวน species / strain / sequence / อำเภอ), ช่องค้นหาด่วน, isolate ล่าสุด |
| P2 | ค้นหา/รายการ Species | ค้นด้วยข้อความ; กรองตาม family, genus, อำเภอที่พบ, แหล่งที่มา; เรียงลำดับ; แบ่งหน้า |
| P3 | รายละเอียด Species | จัดเรียงหัวข้อเหมือน theyeasts.org + สารบัญ "On this page"; รายการ strain ที่พบในพะเยา; แผนที่ย่อย; ลิงก์ NCBI Taxonomy/MycoBank |
| P4 | ค้นหา/รายการ Strain | กรองตาม species, อำเภอ, แหล่งที่มา, ช่วงวันที่เก็บ, มี/ไม่มี sequence |
| P5 | รายละเอียด Strain | ข้อมูลเก็บตัวอย่าง, แผนที่, sequence (ลิงก์ GenBank + ปุ่ม "BLAST ที่ NCBI"), ผลทดสอบ strain เทียบกับ species, แกลเลอรีรูปจัดกลุ่มตามประเภท |
| P6 | แผนที่ | Leaflet + OpenStreetMap; หมุดทุก strain, จัดกลุ่ม (cluster); กรองตาม species/แหล่งที่มา; ขอบเขตอำเภอ (ถ้ามี GeoJSON) |
| P7 | สถิติ/แดชบอร์ด | จำนวน isolate ต่ออำเภอ, ต่อแหล่งที่มา, top genera, จำนวนต่อปีที่เก็บ, สัดส่วน identified/unidentified |
| P8 | ดาวน์โหลด | CSV (strains, species), FASTA (sequences) — ตามเงื่อนไขกรองปัจจุบัน |
| P9 | เกี่ยวกับ / วิธีอ้างอิง | ข้อมูลโครงการ, ทีม, การอ้างอิงฐานข้อมูล, สัญญาอนุญาตข้อมูล |

ภาษา: UI ไทยเป็นหลัก ชื่อวิทยาศาสตร์และหัวข้อทางเทคนิคเป็นอังกฤษ (**ต้องการสลับ TH/EN ไหม — §10 ข้อ 5**)

### 4.2 ส่วนทีมวิจัย (หลังเข้าระบบ)
| # | ฟังก์ชัน |
|---|---|
| A1 | เข้า/ออกจากระบบ, เปลี่ยนรหัสผ่าน |
| A2 | CRUD Species (+ synonyms, บรรณานุกรม, รูป) |
| A3 | CRUD Strain (+ รูป, เลือกจุดบนแผนที่เพื่อกรอกพิกัด) |
| A3.1 | จัดการรูป: อัปโหลดหลายไฟล์พร้อมข้อมูลประกอบ (§3.6), แก้ไขข้อมูลรูป, เรียงลำดับ, ลบ |
| A4 | CRUD Sequence (วางลำดับ FASTA หรือใส่ accession แล้วดึงจาก NCBI) |
| A5 | กรอกผล phenotype แบบตาราง (ทั้งชุดในหน้าเดียว) |
| A6 | นำเข้า CSV/Excel: ตรวจสอบก่อนบันทึก (preview + แจ้งแถวที่ผิด) |
| A7 | จัดการผู้ใช้ (admin), audit log |

---

## 5. การเชื่อมต่อ NCBI

ใช้ **NCBI E-utilities** (https://eutils.ncbi.nlm.nih.gov/entrez/eutils/) ฝั่งเซิร์ฟเวอร์

| งาน | API | ผลลัพธ์ |
|---|---|---|
| ดึง taxonomy จากชื่อ species | `esearch.fcgi?db=taxonomy&term=<name>` → `efetch.fcgi?db=taxonomy&id=<taxid>` | TaxID, lineage (phylum…genus) เติมลงฟอร์มให้อัตโนมัติ |
| ดึงลำดับจาก accession | `efetch.fcgi?db=nuccore&id=<acc>&rettype=fasta` | ลำดับ, ความยาว, title |
| ตรวจ accession / organism | `esummary.fcgi?db=nuccore&id=<acc>` | organism, วันที่ |
| BLAST | ลิงก์ไป `blast.ncbi.nlm.nih.gov/Blast.cgi` พร้อมลำดับ | ผู้ใช้ดูผลที่ NCBI แล้วกรอก top hit กลับมา |
| ลิงก์ภายนอก | NCBI Taxonomy, GenBank, MycoBank | — |

ข้อกำหนด
- ระบุ `tool` และ `email` ทุก request; ใช้ `api_key` (ฟรี ขอได้ที่ NCBI Account Settings → API Key Management) เก็บใน `config.local.php` ห้าม commit
- ค้นชื่อแบบตรงตัวก่อน แล้วจึงค้นรวม synonym — ปฏิเสธผลที่ NCBI ตัดคำบางคำทิ้ง (เช่น "Pichia anomala" จะได้แมลงสกุล *Anomala*)
- ถ้าชื่อที่กรอกต่างจากชื่อปัจจุบันใน NCBI ให้เตือนผู้ใช้ (ไม่เปลี่ยนชื่อให้อัตโนมัติ)
- BLAST URL API ไม่ใช้ API key นี้ และจำกัดส่งงาน ≤1 ครั้ง/10 วินาที, ตรวจผล ≤1 ครั้ง/นาที
- จำกัดอัตรา ≤3 req/s (หรือ 10 req/s ถ้ามี API key); เก็บผลลง DB (`ncbi_synced_at`) แทนการเรียกซ้ำทุกครั้งที่เปิดหน้า
- ถ้า NCBI ล่ม ระบบต้องทำงานต่อได้ด้วยข้อมูลที่เก็บไว้
- (เฟสถัดไป) BLAST อัตโนมัติผ่าน BLAST URL API (`CMD=Put/Get`) แบบ background job

---

## 6. เทคโนโลยี

| ส่วน | เลือก | เหตุผล |
|---|---|---|
| Backend | **PHP 8.2** (ไม่ใช้ framework, PDO + prepared statements) | ตามที่ผู้ใช้เลือก; ติดตั้งบน hosting มหาวิทยาลัยได้ง่าย ไม่ต้องใช้ Composer |
| Database | **MySQL 8 / MariaDB 10.4+**, utf8mb4 | ตามที่ผู้ใช้เลือก |
| Frontend | Bootstrap 5, Leaflet (แผนที่), Chart.js (กราฟ) ผ่าน CDN | ไม่ต้อง build |
| Dev env | Docker Compose (web :8180, db :3317, phpMyAdmin :8280) หรือ XAMPP | พอร์ต 3306/8080 ในเครื่องถูกใช้อยู่แล้ว |
| Production | เซิร์ฟเวอร์มหาวิทยาลัยพะเยา (Apache + PHP + MySQL) **(ยืนยัน — §10 ข้อ 1)** | |

**ตัวเลือก Laravel**: ถ้าทีมดูแลระบบคุ้นกับ Laravel จะได้ระบบ auth/validation/migration ที่แข็งแรงกว่า แลกกับการต้องใช้ Composer และ PHP CLI บนเซิร์ฟเวอร์ (**§10 ข้อ 2**)

### โครงสร้างโฟลเดอร์ (ร่าง)
```
Yeasts_PYO/
├─ spec.md
├─ docker-compose.yml, docker/Dockerfile
├─ database/  schema.sql, seed.sql, migrations/
├─ src/       config.php, db.php, auth.php, ncbi.php, helpers.php
├─ views/     layout + หน้าต่าง ๆ, admin/
└─ public/    index.php (router), .htaccess, assets/, uploads/
```

---

## 7. ข้อกำหนดด้านคุณภาพ (Non-functional)

- **ความปลอดภัย**: password_hash (bcrypt), CSRF token ทุกฟอร์ม, prepared statements, escape output, ตรวจชนิด/ขนาดไฟล์อัปโหลด (≤10 MB, jpg/png/webp), session timeout
- **ประสิทธิภาพ**: หน้าค้นหาตอบภายใน 1 วินาทีที่ 10,000 strains; index ที่ฟิลด์กรอง; FULLTEXT search
- **รองรับมือถือ**: responsive ทุกหน้า
- **สำรองข้อมูล**: mysqldump รายวัน (สคริปต์ + cron) เก็บย้อนหลัง 30 วัน
- **Audit**: บันทึกผู้สร้าง/แก้ไข และเวลาทุก record
- **การอ้างอิงได้ถาวร**: URL ของแต่ละ record ไม่เปลี่ยน (`/species/{id}`, `/strain/{code}`)
- **มาตรฐานข้อมูล**: export ฟิลด์ที่ map กับ **Darwin Core** ได้ (เผื่อส่ง GBIF ในอนาคต)

---

## 8. การนำเข้าข้อมูลเดิม

1. ทีมเตรียม Excel ตามเทมเพลตที่ระบบให้ดาวน์โหลด (1 แถว = 1 strain)
2. อัปโหลด → ระบบตรวจ: รหัสซ้ำ, ชื่อ species ไม่พบ (เสนอสร้างใหม่ / ดึงจาก NCBI), อำเภอไม่ตรง, พิกัดอยู่นอกจังหวัดพะเยา, วันที่ผิดรูปแบบ
3. แสดง preview → ยืนยัน → บันทึก

---

## 9. แผนการพัฒนา (Milestones)

| เฟส | ขอบเขต | ผลลัพธ์ | สถานะ |
|---|---|---|---|
| M1 | Schema, seed (อำเภอ, แหล่งที่มา, phenotype catalog), layout, หน้าแรก, รายการ/รายละเอียด species & strain | ดูข้อมูลได้ | ✅ |
| M2 | Login, CRUD ทั้งหมด, อัปโหลดรูป, audit | ทีมกรอกข้อมูลได้ | ✅ |
| M3 | แผนที่, สถิติ, export CSV/FASTA | ฟีเจอร์สาธารณะครบ | ✅ |
| M4 | NCBI (taxonomy, accession → sequence, ลิงก์ BLAST), นำเข้า CSV | ลดงานกรอก | ✅ |
| M5 | ทดสอบกับข้อมูลจริง, คู่มือผู้ใช้/ผู้ดูแล, ติดตั้งบนเซิร์ฟเวอร์จริง, สำรองข้อมูลอัตโนมัติ | เปิดใช้งาน | ⏳ |

ยังไม่ได้ทำ (ตาม spec): สลับภาษา TH/EN, ขอบเขตอำเภอ GeoJSON บนแผนที่, สคริปต์ backup รายวัน, นำเข้าไฟล์ .xlsx โดยตรง, BLAST อัตโนมัติ

---

## 10. คำถามที่ต้องตัดสินใจ

1. **Hosting จริง**: จะติดตั้งที่ไหน (เซิร์ฟเวอร์ ม.พะเยา / cloud)? รองรับ PHP เวอร์ชันใดและ `mod_rewrite` ไหม?
2. **Plain PHP หรือ Laravel**?
3. **พิกัดจุดเก็บ**: แสดงสาธารณะแบบละเอียด, ปัดเศษ (~1 กม.), หรือแสดงแค่ระดับตำบล/อำเภอ?
4. **รูปแบบรหัส strain**: ใช้ `PYO-Y0001` หรือมีระบบรหัสเดิมอยู่แล้ว?
5. **ภาษา**: ไทยอย่างเดียว หรือสลับ TH/EN?
6. **ข้อมูลเดิม**: มีไฟล์ Excel/ลำดับ DNA อยู่แล้วกี่รายการ และคอลัมน์มีอะไรบ้าง? (ขอไฟล์ตัวอย่างเพื่อออกแบบเทมเพลตนำเข้า)
7. **ข้อมูลระดับ species** (ข้อความ comments, morphology ฯลฯ): ทีมเขียนเอง หรือคัดย่อจากแหล่งอ้างอิง? — ห้ามคัดลอกเนื้อหาจาก theyeasts.org ทั้งหมด (ลิขสิทธิ์) ควรเขียนสรุปเองพร้อมอ้างอิง
8. **สัญญาอนุญาตข้อมูล**: เช่น CC BY 4.0?
9. **ชื่อระบบ/โลโก้/สังกัด** ที่จะแสดงบนเว็บ
