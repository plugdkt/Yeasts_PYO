-- เติมเลข D1/D2 อ้างอิงให้ชนิดตัวอย่างในฐานข้อมูลที่ติดตั้งไปแล้ว (เฉพาะแถวที่ยังว่าง)
UPDATE species s JOIN (
  SELECT 1 id, 'NG_042623.1' acc, 'NRRL Y-12632' strain UNION ALL SELECT 2, 'NG_055104.1', 'NRRL Y-5396' UNION ALL
  SELECT 3, 'NG_055419.1', 'NRRL Y-1614' UNION ALL SELECT 4, 'NG_057174.1', 'NRRL Y-366' UNION ALL
  SELECT 5, 'NG_058413.1', 'CBS 1146' UNION ALL SELECT 6, 'NG_055716.1', 'CBS 316' UNION ALL
  SELECT 7, 'NG_075437.1', 'NRRL Y-7111' UNION ALL SELECT 8, 'NG_054834.1', 'ATCC 750' UNION ALL
  SELECT 9, 'NG_056281.1', 'CBS 139' UNION ALL SELECT 10, 'NG_042626.1', 'NRRL Y-8284' UNION ALL
  SELECT 11, 'NG_042640.1', 'NRRL Y-2075'
) r ON r.id = s.id SET s.d1d2_ref_accession = r.acc, s.d1d2_ref_strain = r.strain WHERE s.is_demo = 1 AND s.d1d2_ref_accession IS NULL;
