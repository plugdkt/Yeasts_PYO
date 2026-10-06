-- =========================================================
-- Phayao Yeast Database (Yeasts_PYO) — schema
-- MySQL 5.7+ / MariaDB 10.3+, utf8mb4
-- โครงสร้างข้อมูลอ้างอิงจาก The Yeasts Database (theyeasts.org)
-- =========================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- ผู้ใช้ (ทีมวิจัย) ----------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(150) NOT NULL,
  email         VARCHAR(150),
  role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- อำเภอในจังหวัดพะเยา ----------
CREATE TABLE IF NOT EXISTS districts (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  name_th  VARCHAR(100) NOT NULL,
  name_en  VARCHAR(100) NOT NULL,
  lat      DECIMAL(9,6),
  lng      DECIMAL(9,6)
) ENGINE=InnoDB;

-- ---------- ชนิด (species) — เทียบกับหน้า details ของ theyeasts.org ----------
CREATE TABLE IF NOT EXISTS species (
  id                     INT AUTO_INCREMENT PRIMARY KEY,
  -- Classification
  kingdom                VARCHAR(60) DEFAULT 'Fungi',
  subkingdom             VARCHAR(60) DEFAULT 'Dikarya',
  phylum                 VARCHAR(60),
  subphylum              VARCHAR(60),
  class_name             VARCHAR(60),
  order_name             VARCHAR(60),
  family                 VARCHAR(60),
  genus                  VARCHAR(80) NOT NULL,
  epithet                VARCHAR(80) NOT NULL,
  authority              VARCHAR(200),
  year_described         SMALLINT,
  mycobank_no            VARCHAR(20),
  ncbi_taxid             INT,
  current_name           VARCHAR(300),
  basionym               VARCHAR(300),
  -- Text sections
  phylogenetic_placement TEXT,
  comments               TEXT,
  ecology                TEXT,
  applications           TEXT,       -- การใช้ประโยชน์ / ศักยภาพเทคโนโลยีชีวภาพ
  -- Morphology and reproduction
  growth_description     TEXT,
  cell_shape             VARCHAR(200),
  cell_size              VARCHAR(100),
  filaments              VARCHAR(200),
  asexual_reproduction   VARCHAR(200),
  sexual_reproduction    TEXT,
  ascospores             VARCHAR(200),
  physiology_summary     TEXT,
  coq_system             VARCHAR(20),
  -- Molecular: ลำดับ D1/D2 LSU อ้างอิงของ type strain (GenBank accession)
  d1d2_ref_accession     VARCHAR(30),
  d1d2_ref_strain        VARCHAR(100),
  -- NCBI cache
  ncbi_lineage           TEXT,
  ncbi_synced_at         DATETIME,
  is_demo                TINYINT(1) NOT NULL DEFAULT 0,
  created_by             INT,
  updated_by             INT,
  created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_species (genus, epithet),
  KEY idx_family (family),
  FULLTEXT KEY ft_species (genus, epithet, comments, ecology, applications)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS synonyms (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  species_id  INT NOT NULL,
  name        VARCHAR(300) NOT NULL,
  mycobank_no VARCHAR(20),
  FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- แหล่งที่แยก (substrate/habitat category) ----------
CREATE TABLE IF NOT EXISTS sources (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  name_th  VARCHAR(100) NOT NULL,
  name_en  VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ---------- สายพันธุ์ (strain / isolate) ที่เก็บได้ในพะเยา ----------
CREATE TABLE IF NOT EXISTS strains (
  id                    INT AUTO_INCREMENT PRIMARY KEY,
  strain_code           VARCHAR(40) NOT NULL UNIQUE,     -- เช่น PYO-Y0001
  other_codes           VARCHAR(255),                    -- TBRC, CBS, NRRL ...
  species_id            INT,
  identification_status ENUM('confirmed','tentative','unidentified') DEFAULT 'tentative',
  is_type_strain        TINYINT(1) DEFAULT 0,
  source_id             INT,
  substrate             VARCHAR(255),                    -- รายละเอียด เช่น "ดอกทองกวาว"
  district_id           INT,
  subdistrict           VARCHAR(100),
  locality              VARCHAR(255),
  latitude              DECIMAL(9,6),
  longitude             DECIMAL(9,6),
  elevation_m           INT,
  collection_date       DATE,
  collector             VARCHAR(150),
  isolator              VARCHAR(150),
  isolation_method      VARCHAR(255),
  identified_by         VARCHAR(150),
  identification_method VARCHAR(255),
  storage               VARCHAR(255),                    -- -80°C glycerol, freeze-dried ...
  availability          ENUM('available','restricted','not_available') DEFAULT 'available',
  remarks               TEXT,
  is_demo               TINYINT(1) NOT NULL DEFAULT 0,
  created_by            INT,
  updated_by            INT,
  created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (species_id)  REFERENCES species(id)   ON DELETE SET NULL,
  FOREIGN KEY (source_id)   REFERENCES sources(id)   ON DELETE SET NULL,
  FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
  KEY idx_coll_date (collection_date)
) ENGINE=InnoDB;

-- ---------- ลำดับนิวคลีโอไทด์ / GenBank ----------
CREATE TABLE IF NOT EXISTS sequences (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  strain_id       INT NOT NULL,
  locus           VARCHAR(50) NOT NULL,          -- ITS, D1/D2 LSU, TEF1, RPB2 ...
  accession       VARCHAR(30),
  sequence        MEDIUMTEXT,
  length_bp       INT,
  blast_top_hit   VARCHAR(255),
  blast_identity  DECIMAL(5,2),
  blast_coverage  DECIMAL(5,2),
  ncbi_title      VARCHAR(500),
  ncbi_synced_at  DATETIME,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (strain_id) REFERENCES strains(id) ON DELETE CASCADE,
  KEY idx_acc (accession)
) ENGINE=InnoDB;

-- ---------- การทดสอบทางสรีรวิทยา (catalog) ----------
CREATE TABLE IF NOT EXISTS phenotype_tests (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  test_group  ENUM('fermentation','carbon','nitrogen','additional','temperature','antimycotic') NOT NULL,
  name        VARCHAR(120) NOT NULL,
  code        VARCHAR(20),
  sort_order  INT DEFAULT 0,
  UNIQUE KEY uq_test (test_group, name)
) ENGINE=InnoDB;

-- ผลการทดสอบ: ระดับ species (ข้อมูลอ้างอิง) หรือ strain (ผลจริงจากแล็บ)
-- result: + positive, - negative, w weak, d delayed, v variable, s slow, ? unknown, หรือค่าอื่น (เช่น MIC)
CREATE TABLE IF NOT EXISTS phenotype_results (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  test_id     INT NOT NULL,
  species_id  INT,
  strain_id   INT,
  result      VARCHAR(30) NOT NULL,
  method      VARCHAR(100),
  FOREIGN KEY (test_id)    REFERENCES phenotype_tests(id) ON DELETE CASCADE,
  FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE CASCADE,
  FOREIGN KEY (strain_id)  REFERENCES strains(id) ON DELETE CASCADE,
  UNIQUE KEY uq_sp (test_id, species_id),
  UNIQUE KEY uq_st (test_id, strain_id)
) ENGINE=InnoDB;

-- ---------- บรรณานุกรม ----------
CREATE TABLE IF NOT EXISTS bibliography (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  citation  TEXT NOT NULL,
  year      SMALLINT,
  doi       VARCHAR(150),
  pmid      VARCHAR(20),
  url       VARCHAR(500)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS species_bibliography (
  species_id INT NOT NULL,
  bib_id     INT NOT NULL,
  PRIMARY KEY (species_id, bib_id),
  FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE CASCADE,
  FOREIGN KEY (bib_id)     REFERENCES bibliography(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- รูปภาพ ----------
CREATE TABLE IF NOT EXISTS images (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  species_id  INT,
  strain_id   INT,
  file_path   VARCHAR(255) NOT NULL,
  caption     VARCHAR(255),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE CASCADE,
  FOREIGN KEY (strain_id)  REFERENCES strains(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- บันทึกการแก้ไข ----------
CREATE TABLE IF NOT EXISTS audit_log (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT,
  action     VARCHAR(20) NOT NULL,
  entity     VARCHAR(30) NOT NULL,
  entity_id  INT,
  detail     VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
