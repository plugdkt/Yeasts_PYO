# ฐานข้อมูลยีสต์จังหวัดพะเยา (Phayao Yeast Database)

ระบบฐานข้อมูลยีสต์ที่แยกได้ในจังหวัดพะเยา — PHP 8.2 + MySQL/MariaDB, ไม่ใช้ framework
ข้อกำหนดระบบ: [spec.md](spec.md)

## เริ่มใช้งาน (Docker)

```bash
docker compose up -d --build
```

| บริการ | URL |
|---|---|
| เว็บไซต์ | http://localhost:8180 |
| phpMyAdmin | http://localhost:8280 (user `yeast` / `yeast_pass`) |
| MySQL จากเครื่อง | `127.0.0.1:3317` |

1. เปิด http://localhost:8180/setup เพื่อสร้างผู้ดูแลระบบคนแรก (หน้านี้ปิดเองเมื่อมีผู้ใช้แล้ว)
2. ฐานข้อมูลถูกสร้างจาก `database/schema.sql` + `database/seed.sql` อัตโนมัติในครั้งแรก
3. ล้างฐานข้อมูลแล้วเริ่มใหม่: `docker compose down -v && docker compose up -d`

## ค่าตั้งเฉพาะเครื่อง

คัดลอก `config.local.example.php` เป็น `config.local.php` (ไฟล์นี้อยู่ใน `.gitignore`) แล้วใส่
- `ncbi.api_key` — ขอฟรีที่ NCBI Account Settings → API Key Management (เพิ่มโควตาเป็น 10 req/s)
- `ncbi.email` — อีเมลผู้ดูแลระบบ (NCBI ขอให้ระบุ)
- `db`, `base_url` เมื่อติดตั้งบนเซิร์ฟเวอร์จริง

## ติดตั้งบนเซิร์ฟเวอร์ (Apache + PHP 8.1+ + MySQL)

1. ต้องมี extension `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `gd` (สร้างภาพย่อ — ถ้าไม่มีระบบจะใช้ไฟล์ต้นฉบับแทน), `exif` และเปิด `mod_rewrite` (`AllowOverride All`)
   ตั้ง `upload_max_filesize = 10M`, `post_max_size` ≥ 100M, `max_file_uploads = 20` เพื่ออัปโหลดรูปหลายไฟล์พร้อมกัน
2. ตั้ง DocumentRoot ไปที่โฟลเดอร์ `public/` (ถ้าทำไม่ได้ ให้วางทั้งโปรเจกต์ไว้นอก web root แล้ว symlink `public/`)
3. สร้างฐานข้อมูล แล้ว import `database/schema.sql` และ `database/seed.sql`
4. สร้าง `config.local.php` ตามด้านบน และให้ web server เขียนโฟลเดอร์ `public/uploads/` ได้
5. ก่อนเปิดใช้งานจริง ลบข้อมูลตัวอย่างด้วย `database/remove_demo.sql`
6. **สำรองข้อมูล** ต้องเก็บทั้งฐานข้อมูล (mysqldump) และโฟลเดอร์ `public/uploads/` (รูปภาพไม่อยู่ใน Git)

## อัปเดตฐานข้อมูลที่ติดตั้งไว้แล้ว

รันไฟล์ใน `database/migrations/` ที่ยังไม่เคยรัน ตามลำดับเลข เช่น

```bash
docker exec -i yeastpyo_db mariadb -uyeast -pyeast_pass yeast_pyo < database/migrations/003_image_metadata.sql
```

## โครงสร้าง

```
public/index.php        router (ทุก request ผ่านไฟล์นี้)
src/public_pages.php    หน้าสาธารณะ + export + login/setup
src/admin_pages.php     ส่วนทีมวิจัย: CRUD, ผลทดสอบ, อัปโหลดรูป, นำเข้า CSV, ผู้ใช้
src/ncbi.php            NCBI E-utilities (taxonomy, GenBank, ลิงก์ BLAST, D1/D2 type strain)
src/images.php          รูปภาพ: ตรวจไฟล์, ภาพย่อ, ข้อมูลประกอบรูป
views/                  template (layout.php, หน้าต่าง ๆ, admin/, partials/)
database/               schema.sql, seed.sql, remove_demo.sql
```

## หน้าหลัก

| URL | หน้า |
|---|---|
| `/species`, `/species/{id}` | รายการ/รายละเอียดชนิด (โครงสร้างแบบ The Yeasts Database) |
| `/strains`, `/strain/{code}` | รายการ/รายละเอียดสายพันธุ์ |
| `/map` | แผนที่จุดเก็บตัวอย่าง (กรองได้เหมือนหน้า strains) |
| `/stats` | สถิติและตารางการกระจายชนิด × อำเภอ |
| `/export/strains.csv`, `/export/species.csv`, `/export/sequences.fasta` | ดาวน์โหลด (รับตัวกรองเดียวกับหน้า strains) |
| `/admin` | ส่วนทีมวิจัย |
