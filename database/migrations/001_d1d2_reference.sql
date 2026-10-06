-- v0.3: เลข accession ลำดับ D1/D2 LSU อ้างอิงจาก type strain ของแต่ละชนิด
ALTER TABLE species
  ADD COLUMN d1d2_ref_accession VARCHAR(30)  NULL AFTER coq_system,
  ADD COLUMN d1d2_ref_strain    VARCHAR(100) NULL AFTER d1d2_ref_accession;
-- ทำให้ชื่อ locus เป็นแบบเดียวกัน
UPDATE sequences SET locus = 'D1/D2 LSU' WHERE locus IN ('D1/D2', 'LSU', 'D1D2', '26S', '28S');
