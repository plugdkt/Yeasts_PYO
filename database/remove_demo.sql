-- ลบข้อมูลตัวอย่างทั้งหมดก่อนเปิดใช้งานจริง (ข้อมูลจริงที่กรอกเองจะไม่ถูกลบ)
DELETE FROM strains WHERE is_demo = 1;
DELETE FROM species WHERE is_demo = 1 AND id NOT IN (SELECT species_id FROM (SELECT DISTINCT species_id FROM strains WHERE species_id IS NOT NULL) x);
-- ชนิดตัวอย่างที่ถูกนำไปใช้กับสายพันธุ์จริงแล้ว: ยกเลิกสถานะ demo แทนการลบ
UPDATE species SET is_demo = 0 WHERE is_demo = 1;
