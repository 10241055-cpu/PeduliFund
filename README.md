# PeduliFund

## Deskripsi
Aplikasi platform crowdfunding dan donasi berbasis campaign untuk mendukung penggalangan dana sosial, pendidikan, kesehatan, dan bencana.

## Anggota Tim
- Jeshua Austin D (10241037) – Inisialisasi Repository GitHub, Setup Layout Dasar, & Konfigurasi Awal
- Nabil Hafidz M (10251055) – Perancangan Skema Database, Model Entity (CampaignEntity), & Helper Global
- Aliya Labibah (10241007) – Implementasi Fitur Daftar & Hapus Campaign (Eksplorasi Awal)
- Bella Alviana (10241015) – Controller (CampaignController), Validasi Server-side, & Helper Upload Berkas
- Muhammad Iksyan (10241049) – Refaktorisasi & Penataan Struktur Folder MVC, Harmonisasi Standar Modul 2, & Dokumentasi Proyek

## Teknologi
- PHP
- MySQL/MariaDB
- HTML, CSS, dan Bootstrap

## Struktur Dokumentasi
- docs/: dokumen desain perangkat lunak (Software Design Description)

## Persiapan
1. Clone repository.
2. Siapkan web server dan database lokal.
3. Buat database sesuai skrip di folder database (impor `schema.sql`, lalu `sample_data.sql`).
4. Sesuaikan konfigurasi database lokal.

## Catatan
## Cara Menjalankan (XAMPP)

1. Impor `database/schema.sql` lalu `database/sample_data.sql` ke MySQL (tabel: `pengguna`, `campaign`, `donasi`, `pencairan_dana`).
2. Beri Apache hak tulis folder unggahan: `chmod -R a+wx uploads/`
3. Buka `http://localhost/PeduliFund/` — akun contoh: `fundraiser@pedulifund.test`, `admin@pedulifund.test`, `donatur@pedulifund.test` (kata sandi `password`).
