-- =========================================================
-- ข้อมูลตั้งต้น: อำเภอ, ประเภทแหล่ง, รายการทดสอบทางสรีรวิทยา
-- + ข้อมูลตัวอย่าง (is_demo = 1, รหัส DEMO-xxxx) สำหรับทดสอบระบบ — ลบได้ด้วย remove_demo.sql
-- =========================================================
SET NAMES utf8mb4;

-- 9 อำเภอของจังหวัดพะเยา (พิกัดโดยประมาณของที่ว่าการอำเภอ)
INSERT INTO districts (id, name_th, name_en, lat, lng) VALUES
 (1, 'เมืองพะเยา', 'Mueang Phayao', 19.166300, 99.901900),
 (2, 'จุน',        'Chun',          19.335000, 100.135000),
 (3, 'เชียงคำ',    'Chiang Kham',   19.523000, 100.302000),
 (4, 'เชียงม่วน',  'Chiang Muan',   18.883000, 100.297000),
 (5, 'ดอกคำใต้',   'Dok Khamtai',   19.162000, 99.988000),
 (6, 'ปง',         'Pong',          19.150000, 100.275000),
 (7, 'แม่ใจ',      'Mae Chai',      19.346000, 99.813000),
 (8, 'ภูซาง',      'Phu Sang',      19.648000, 100.338000),
 (9, 'ภูกามยาว',   'Phu Kamyao',    19.266000, 99.958000);

INSERT INTO sources (name_th, name_en) VALUES
 ('ดอกไม้', 'Flower'), ('ผลไม้', 'Fruit'), ('ใบไม้ / ผิวใบ', 'Leaf / phylloplane'), ('ดิน', 'Soil'),
 ('แมลง', 'Insect'), ('อาหารหมักพื้นบ้าน', 'Traditional fermented food'), ('ลูกแป้ง / สุราพื้นบ้าน', 'Starter / local spirit'),
 ('น้ำผึ้ง / รังผึ้ง', 'Honey / bee nest'), ('น้ำ / ตะกอน', 'Water / sediment'), ('เปลือกไม้ / ขอนไม้', 'Bark / wood'),
 ('เห็ด', 'Mushroom'), ('อื่น ๆ', 'Other');

-- ---------- รายการทดสอบ (ตามหัวข้อของ The Yeasts Database) ----------
INSERT INTO phenotype_tests (test_group, name, code, sort_order) VALUES
 ('fermentation','D-Glucose','F1',1),('fermentation','D-Galactose','F2',2),('fermentation','Maltose','F3',3),
 ('fermentation','Methyl α-D-glucoside','F4',4),('fermentation','Sucrose','F5',5),('fermentation','α,α-Trehalose','F6',6),
 ('fermentation','Melibiose','F7',7),('fermentation','Lactose','F8',8),('fermentation','Cellobiose','F9',9),
 ('fermentation','Melezitose','F10',10),('fermentation','Raffinose','F11',11),('fermentation','Inulin','F12',12),
 ('fermentation','Starch','F13',13),('fermentation','D-Xylose','F14',14);

INSERT INTO phenotype_tests (test_group, name, sort_order) VALUES
 ('carbon','Glucose',1),('carbon','Inulin',2),('carbon','Sucrose',3),('carbon','Raffinose',4),('carbon','Melibiose',5),
 ('carbon','Galactose',6),('carbon','Lactose',7),('carbon','Trehalose',8),('carbon','Maltose',9),('carbon','Melezitose',10),
 ('carbon','Methyl-α-D-glucoside',11),('carbon','Soluble starch',12),('carbon','Cellobiose',13),('carbon','Salicin',14),
 ('carbon','Arbutin',15),('carbon','L-Sorbose',16),('carbon','L-Rhamnose',17),('carbon','D-Xylose',18),('carbon','L-Arabinose',19),
 ('carbon','D-Arabinose',20),('carbon','D-Ribose',21),('carbon','Methanol',22),('carbon','Ethanol',23),('carbon','Glycerol',24),
 ('carbon','Erythritol',25),('carbon','Ribitol',26),('carbon','Galactitol',27),('carbon','D-Mannitol',28),('carbon','D-Glucitol',29),
 ('carbon','myo-Inositol',30),('carbon','DL-Lactate',31),('carbon','Succinate',32),('carbon','Citrate',33),('carbon','D-Gluconate',34),
 ('carbon','D-Glucosamine',35),('carbon','2-Keto-D-gluconate',36),('carbon','5-Keto-D-gluconate',37),('carbon','D-Glucuronate',38),
 ('carbon','D-Galacturonate',39),('carbon','D-Glucarate',40),('carbon','Saccharate',41),('carbon','D-Galactonate',42),
 ('carbon','D-Glucono-1,5-lactone',43),('carbon','Propane-1,2-diol',44),('carbon','Butane-2,3-diol',45),('carbon','L-Arabinitol',46),
 ('carbon','Xylitol',47);

INSERT INTO phenotype_tests (test_group, name, sort_order) VALUES
 ('nitrogen','Nitrate',1),('nitrogen','Nitrite',2),('nitrogen','Ethylamine',3),('nitrogen','L-Lysine',4),('nitrogen','Cadaverine',5),
 ('nitrogen','Creatine',6),('nitrogen','Creatinine',7),('nitrogen','D-Glucosamine (N)',8),('nitrogen','N-Acetyl-D-glucosamine',9),
 ('nitrogen','Hexadecane',10);

INSERT INTO phenotype_tests (test_group, name, code, sort_order) VALUES
 ('additional','Growth on 50% glucose',NULL,1),('additional','Growth on 10% NaCl / 5% glucose',NULL,2),
 ('additional','Vitamin-free growth',NULL,3),('additional','Cycloheximide 0.01%',NULL,4),('additional','Cycloheximide 0.1%',NULL,5),
 ('additional','Starch formation',NULL,6),('additional','Acetic acid production','M2',7),('additional','Urease',NULL,8),
 ('additional','Diazonium Blue B reaction','M4',9),('additional','Gelatin liquefaction',NULL,10),('additional','Catalase activity',NULL,11),
 ('additional','β-Glucosidase activity',NULL,12),('additional','Reddish diffusing pigment',NULL,13),
 ('additional','Growth with Tween 20',NULL,14),('additional','Growth with Tween 40',NULL,15),('additional','Growth with Tween 60',NULL,16),
 ('additional','Growth with Tween 80',NULL,17);

INSERT INTO phenotype_tests (test_group, name, sort_order) VALUES
 ('temperature','4 °C',1),('temperature','12 °C',2),('temperature','15 °C',3),('temperature','19 °C',4),('temperature','21 °C',5),
 ('temperature','25 °C',6),('temperature','30 °C',7),('temperature','35 °C',8),('temperature','37 °C',9),('temperature','40 °C',10),
 ('temperature','42 °C',11),('temperature','45 °C',12);

INSERT INTO phenotype_tests (test_group, name, sort_order) VALUES
 ('antimycotic','Fluconazole MIC (µg/mL)',1),('antimycotic','Itraconazole MIC (µg/mL)',2),('antimycotic','Voriconazole MIC (µg/mL)',3),
 ('antimycotic','Posaconazole MIC (µg/mL)',4),('antimycotic','Amphotericin B MIC (µg/mL)',5),('antimycotic','Caspofungin MIC (µg/mL)',6),
 ('antimycotic','5-Fluorocytosine MIC (µg/mL)',7),('antimycotic','Terbinafine MIC (µg/mL)',8);

-- =========================================================
-- ข้อมูลตัวอย่าง (DEMO) — อนุกรมวิธานดึงจาก NCBI Taxonomy เมื่อ 2026-10-06
-- สายพันธุ์ DEMO-xxxx เป็นข้อมูลสมมติเพื่อทดสอบหน้าจอเท่านั้น
-- =========================================================
INSERT INTO species (id, kingdom, subkingdom, phylum, subphylum, class_name, order_name, family, genus, epithet, authority, year_described, ncbi_taxid, comments, is_demo) VALUES
 (1,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Saccharomycetes','Saccharomycetales','Saccharomycetaceae','Saccharomyces','cerevisiae','(Desm.) Meyen',1838,4932,'[ข้อมูลตัวอย่าง] ยีสต์หมักที่ใช้ในการผลิตขนมปัง เบียร์ ไวน์ และสุราพื้นบ้าน',1),
 (2,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Pichiomycetes','Pichiales','Pichiaceae','Pichia','kudriavzevii','Boidin, Pignal & Besson',1965,4909,'[ข้อมูลตัวอย่าง] ทนอุณหภูมิสูงและสภาวะกรด พบบ่อยในผลไม้และอาหารหมัก',1),
 (3,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Saccharomycetes','Saccharomycodales','Saccharomycodaceae','Hanseniaspora','uvarum','(Niehaus) Shehata, Mrak & Phaff ex M.T. Sm.',NULL,29833,'[ข้อมูลตัวอย่าง] ยีสต์ทรงมะนาว (apiculate) พบมากบนผิวผลไม้',1),
 (4,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Saccharomycetes','Phaffomycetales','Wickerhamomycetaceae','Hansenula','anomala','(E.C. Hansen) Syd. & P. Syd.',NULL,4927,'[ข้อมูลตัวอย่าง] NCBI ใช้ชื่อ Hansenula anomala ตามการจัดจำแนกใหม่',1),
 (5,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Saccharomycetes','Saccharomycetales','Saccharomycetaceae','Torulaspora','delbrueckii','(Lindner) Lindner',1904,4950,NULL,1),
 (6,'Fungi','Dikarya','Basidiomycota','Pucciniomycotina','Microbotryomycetes','Sporidiobolales','Sporidiobolaceae','Rhodotorula','mucilaginosa','(A. Jörg.) F.C. Harrison',1928,5537,'[ข้อมูลตัวอย่าง] ยีสต์สีแดง-ส้ม สร้างแคโรทีนอยด์',1),
 (7,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Pichiomycetes','Serinales','Metschnikowiaceae','Metschnikowia','pulcherrima','Pitt & M.W. Mill.',1968,27326,'[ข้อมูลตัวอย่าง] พบบ่อยในน้ำหวานดอกไม้และผลไม้',1),
 (8,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Pichiomycetes','Serinales','Debaryomycetaceae','Candida','tropicalis','(Castell.) Berkhout',1923,5482,NULL,1),
 (9,'Fungi','Dikarya','Basidiomycota','Agaricomycotina','Tremellomycetes','Tremellales','Rhynchogastremaceae','Papiliotrema','laurentii','(Kuff.) Xin Zhan Liu, F.Y. Bai, M. Groenew. & Boekhout',2015,5418,NULL,1),
 (10,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Saccharomycetes','Saccharomycetales','Saccharomycetaceae','Lachancea','thermotolerans','(Filippov) Kurtzman',2003,381046,NULL,1),
 (11,'Fungi','Dikarya','Ascomycota','Saccharomycotina','Pichiomycetes','Serinales','Debaryomycetaceae','Meyerozyma','guilliermondii','(Wick.) Kurtzman & M. Suzuki',2010,4929,NULL,1);

INSERT INTO synonyms (species_id, name) VALUES
 (2,'Issatchenkia orientalis Kudryavtsev'),(2,'Candida krusei (Castell.) Berkhout'),
 (4,'Wickerhamomyces anomalus (E.C. Hansen) Kurtzman, Robnett & Bas.-Powers'),(4,'Pichia anomala (E.C. Hansen) Kurtzman'),
 (11,'Pichia guilliermondii Wick.');

INSERT INTO strains (strain_code, species_id, identification_status, source_id, substrate, district_id, subdistrict, locality, latitude, longitude, elevation_m, collection_date, collector, isolation_method, identification_method, storage, is_demo) VALUES
 ('DEMO-0001',1,'confirmed',7,'ลูกแป้งข้าวหมาก',1,'แม่กา','ตัวอย่างสมมติ',19.0290,99.8950,460,'2025-06-12','ทีมวิจัย (demo)','enrichment YM broth','ITS + D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0002',2,'confirmed',2,'ผลลำไยสุก',1,'บ้านต๋อม','ตัวอย่างสมมติ',19.1880,99.9150,410,'2025-07-03','ทีมวิจัย (demo)','spread plate YM agar','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0003',3,'confirmed',2,'ผลลิ้นจี่',3,'หย่วน','ตัวอย่างสมมติ',19.5250,100.3000,390,'2025-05-20','ทีมวิจัย (demo)','spread plate YM agar','ITS sequencing','-80°C 20% glycerol',1),
 ('DEMO-0004',4,'tentative',1,'ดอกทองกวาว',2,'ห้วยข้าวก่ำ','ตัวอย่างสมมติ',19.3400,100.1300,420,'2025-02-14','ทีมวิจัย (demo)','enrichment YM broth','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0005',6,'confirmed',3,'ผิวใบชา',8,'ภูซาง','ตัวอย่างสมมติ',19.6500,100.3400,520,'2025-01-28','ทีมวิจัย (demo)','leaf washing','ITS + D1/D2 sequencing','freeze-dried',1),
 ('DEMO-0006',7,'confirmed',1,'น้ำหวานดอกกล้วย',7,'ศรีถ้อย','ตัวอย่างสมมติ',19.3500,99.8200,480,'2025-03-09','ทีมวิจัย (demo)','enrichment YM broth','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0007',1,'confirmed',6,'สาโท',5,'ดอกคำใต้','ตัวอย่างสมมติ',19.1600,99.9900,430,'2024-12-01','ทีมวิจัย (demo)','spread plate YM agar','ITS sequencing','-80°C 20% glycerol',1),
 ('DEMO-0008',8,'tentative',4,'ดินนาข้าว',6,'ปง','ตัวอย่างสมมติ',19.1500,100.2700,400,'2024-11-15','ทีมวิจัย (demo)','soil dilution','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0009',9,'confirmed',3,'ผิวใบไผ่',4,'สระ','ตัวอย่างสมมติ',18.8900,100.3000,350,'2025-08-02','ทีมวิจัย (demo)','leaf washing','ITS + D1/D2 sequencing','freeze-dried',1),
 ('DEMO-0010',10,'confirmed',2,'มะม่วงสุก',9,'ดงเจน','ตัวอย่างสมมติ',19.2700,99.9600,440,'2025-04-17','ทีมวิจัย (demo)','spread plate YM agar','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0011',11,'confirmed',5,'ผึ้งชันโรง',1,'แม่ใส','ตัวอย่างสมมติ',19.1200,99.8800,470,'2025-09-05','ทีมวิจัย (demo)','enrichment YM broth','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0012',2,'confirmed',6,'ผักกาดดอง',3,'เวียง','ตัวอย่างสมมติ',19.5200,100.2900,385,'2025-06-30','ทีมวิจัย (demo)','spread plate YM agar','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0013',5,'tentative',2,'ผลมะเกี๋ยง',7,'แม่ใจ','ตัวอย่างสมมติ',19.3450,99.8100,490,'2025-08-20','ทีมวิจัย (demo)','spread plate YM agar','D1/D2 sequencing','-80°C 20% glycerol',1),
 ('DEMO-0014',NULL,'unidentified',9,'ตะกอนกว๊านพะเยา',1,'เวียง','ตัวอย่างสมมติ',19.1700,99.8900,385,'2025-09-18','ทีมวิจัย (demo)','membrane filtration',NULL,'-80°C 20% glycerol',1),
 ('DEMO-0015',3,'confirmed',8,'น้ำผึ้งป่า',4,'เชียงม่วน','ตัวอย่างสมมติ',18.8800,100.2950,360,'2025-03-25','ทีมวิจัย (demo)','enrichment 50% glucose','ITS sequencing','-80°C 20% glycerol',1),
 ('DEMO-0016',6,'confirmed',10,'เปลือกไม้สัก',2,'จุน','ตัวอย่างสมมติ',19.3300,100.1400,430,'2025-01-10','ทีมวิจัย (demo)','enrichment YM broth','D1/D2 sequencing','freeze-dried',1);

-- ลำดับอ้างอิงจาก GenBank (type material) ผูกกับสายพันธุ์ตัวอย่าง เพื่อทดสอบปุ่มซิงก์/BLAST
INSERT INTO sequences (strain_id, locus, accession, blast_top_hit, blast_identity, blast_coverage)
SELECT id, 'ITS', 'NR_111007', 'Saccharomyces cerevisiae CBS 1171 (NR_111007)', 100.00, 100.00 FROM strains WHERE strain_code = 'DEMO-0001';

-- ผลทดสอบระดับชนิดตัวอย่าง (S. cerevisiae)
INSERT INTO phenotype_results (test_id, species_id, result, method)
SELECT t.id, 1, v.r, 'demo' FROM phenotype_tests t JOIN (
  SELECT 'fermentation' g, 'D-Glucose' n, '+' r UNION ALL SELECT 'fermentation','D-Galactose','v' UNION ALL
  SELECT 'fermentation','Sucrose','+' UNION ALL SELECT 'fermentation','Maltose','v' UNION ALL
  SELECT 'fermentation','Lactose','-' UNION ALL SELECT 'fermentation','Raffinose','+' UNION ALL
  SELECT 'carbon','Glucose','+' UNION ALL SELECT 'carbon','Lactose','-' UNION ALL SELECT 'carbon','D-Xylose','-' UNION ALL
  SELECT 'carbon','Ethanol','v' UNION ALL SELECT 'nitrogen','Nitrate','-' UNION ALL SELECT 'nitrogen','Nitrite','-' UNION ALL
  SELECT 'temperature','25 °C','yes' UNION ALL SELECT 'temperature','37 °C','v' UNION ALL
  SELECT 'additional','Diazonium Blue B reaction','-' UNION ALL SELECT 'additional','Cycloheximide 0.01%','-'
) v ON v.g = t.test_group AND v.n = t.name;

-- ลำดับ D1/D2 LSU อ้างอิงของ type strain (RefSeq "from TYPE material" ใน GenBank, ค้นเมื่อ 2026-10-06)
UPDATE species s JOIN (
  SELECT 1 id, 'NG_042623.1' acc, 'NRRL Y-12632' strain UNION ALL SELECT 2, 'NG_055104.1', 'NRRL Y-5396' UNION ALL
  SELECT 3, 'NG_055419.1', 'NRRL Y-1614' UNION ALL SELECT 4, 'NG_057174.1', 'NRRL Y-366' UNION ALL
  SELECT 5, 'NG_058413.1', 'CBS 1146' UNION ALL SELECT 6, 'NG_055716.1', 'CBS 316' UNION ALL
  SELECT 7, 'NG_075437.1', 'NRRL Y-7111' UNION ALL SELECT 8, 'NG_054834.1', 'ATCC 750' UNION ALL
  SELECT 9, 'NG_056281.1', 'CBS 139' UNION ALL SELECT 10, 'NG_042626.1', 'NRRL Y-8284' UNION ALL
  SELECT 11, 'NG_042640.1', 'NRRL Y-2075'
) r ON r.id = s.id SET s.d1d2_ref_accession = r.acc, s.d1d2_ref_strain = r.strain;
