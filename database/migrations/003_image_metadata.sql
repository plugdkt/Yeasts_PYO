-- v0.4: ข้อมูลประกอบรูปภาพยีสต์ + ภาพย่อ
ALTER TABLE images
  ADD COLUMN image_type    ENUM('colony','cell','hyphae','spore','other') NOT NULL DEFAULT 'other' AFTER strain_id,
  ADD COLUMN thumb_path    VARCHAR(255) NULL AFTER file_path,
  ADD COLUMN width         INT NULL AFTER thumb_path,
  ADD COLUMN height        INT NULL AFTER width,
  ADD COLUMN medium        VARCHAR(100) NULL AFTER caption,
  ADD COLUMN incubation_temp VARCHAR(20) NULL AFTER medium,
  ADD COLUMN incubation_days VARCHAR(20) NULL AFTER incubation_temp,
  ADD COLUMN technique     VARCHAR(60)  NULL AFTER incubation_days,
  ADD COLUMN magnification VARCHAR(30)  NULL AFTER technique,
  ADD COLUMN scale_bar     VARCHAR(30)  NULL AFTER magnification,
  ADD COLUMN photographer  VARCHAR(150) NULL AFTER scale_bar,
  ADD COLUMN taken_date    DATE NULL AFTER photographer,
  ADD COLUMN credit        VARCHAR(255) NULL AFTER taken_date,
  ADD COLUMN license       VARCHAR(50)  NULL AFTER credit,
  ADD COLUMN sort_order    INT NOT NULL DEFAULT 0 AFTER license,
  ADD COLUMN created_by    INT NULL AFTER sort_order;
