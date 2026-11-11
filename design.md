# Design System & Style Guide — PeduliFund (`design.md`)

Dokumen ini merupakan panduan sistem desain visual (*Design System & UI Guidelines*) resmi dan terpadu untuk **seluruh antarmuka platform PeduliFund** (mencakup portal publik, alur donasi, portal fundraiser, hingga dashboard manajemen admin). 

Layar **UI-001 Beranda** dijadikan sebagai **contoh implementasi acuan dasar** (*baseline reference implementation*) yang memperlihatkan bagaimana seluruh token, komponen, pola interaksi, dan ritme desain ini diterapkan secara konkret pada produk nyata yang bersih (*clean production state*).

Gaya antarmuka PeduliFund mengusung estetika *Soft Rounded Dashboard UI* yang bersih, ramah, dan tepercaya: kontainer baki berlapis (*nested soft trays*), sudut membulat terukur (*deliberate border radius*), lencana status berbentuk kapsul (*pill status badges*), serta palet warna organik terkalibrasi: dominasi warna **Sage Green & Soft Mint** alami untuk membangun rasa tenang serta transparansi, dipadukan dengan aksen **Rose Pink** yang kontras khusus untuk tombol aksi utama (*Call-to-Action*), indikator fokus, dan detail mikro (*micro-interactions*).

---

## 1. Dial Kehidupan Visual & Karakteristik Global (Liveliness Dials)

Setiap layar di dalam ekosistem PeduliFund wajib mematuhi konfigurasi tiga dial visual berikut:

* **ENERGY: 2 (Balanced)** — Kontras harmonis, tegas, dan berwibawa antara permukaan terang *Soft Mint* (`#F5F9F4`), baki sage lembut (`#E6EFE5`), teks kontras tinggi *Deep Sage* (`#264634`), dan aksen fokal *Rose Pink* (`#E66884`) pada tombol aksi utama. Memberikan kesan ramah bagi donatur tanpa mengurangi kredibilitas platform pengelola dana publik.
* **RHYTHM: 2 (Varied)** — Tata letak dinamis dan berjenjang yang disesuaikan dengan fungsi layar: area *Hero/Overview* terpusat dengan strip metrik ringkas; panel informasi asimetris atau modular (seperti kluster widget metrik dan kartu ringkasan); serta alur sekuensial langkah proses (*stepper*).
* **MOTION: 1 (Calm)** — Transisi mikro yang halus dan elegan (`150ms - 200ms ease`) pada kondisi `:hover`, `:focus-visible`, dan interaksi tombol aksi. Tanpa animasi looping berlebihan, serta sepenuhnya mendukung `prefers-reduced-motion: reduce`.

---

## 2. Palet Token Warna Terpadu (Color Tokens)

Token warna ini berlaku universal di seluruh modul aplikasi PeduliFund dan diekstraksi langsung dari implementasi UI-001 Beranda:

### 2.1 Warna Latar & Permukaan Struktural (Sage Green Spectrum)
| Token CSS | Nilai Hex | Fungsi & Penggunaan Spesifik di Seluruh UI |
| :--- | :--- | :--- |
| `--bg-outer` | `#E4ECE2` | Latar bingkai luar kanvas presentasi / pembungkus viewport |
| `--bg-canvas` / `--pf-bg-main` | `#F5F9F4` | Latar belakang aplikasi utama (`<body>` / *app window* / *page background*) |
| `--bg-tray` / `--pf-bg-container` | `#E6EFE5` | Baki kontainer lembut pembungkus grup kartu, panel dashboard, dan section form |
| `--surface-white` | `#FFFFFF` | Permukaan kartu utama, navbar melayang, widget data, tabel, dan modal dialog |
| `--green-deep` | `#264634` | Teks judul utama (`H1`–`H3`), teks logo merek, header tabel, dan bingkai luar |
| `--green-primary` | `#568265` | Elemen fitur aktif, bingkai aplikasi, aksen grafis, badge sage, dan batang progres |
| `--green-mid` | `#739B80` | Batang grafik sekunder, ikon pendukung, dan elemen data pendamping |
| `--green-light` | `#C5D9C8` | Garis tepi (*border*) tombol sekunder, border kartu, dan pemisah halus |
| `--green-pale` | `#EDF4EC` | Latar belakang kapsul navigasi, input form, strip fitur, dan badge netral |
| `--text-body` | `#4D6A58` | Teks paragraf, deskripsi campaign, label form, dan isi data tabel |
| `--text-muted` | `#7E9987` | Teks bantuan (*helper text*), placeholder, timestamp, dan hak cipta |

### 2.2 Warna Aksen Tunggal (Rose & Blush Pink — Aksi & Detail Fokal)
Warna pink **tidak boleh** digunakan sebagai latar belakang area luas, melainkan eksklusif sebagai penarik fokus mata (*focal point*):

| Token CSS | Nilai Hex | Fungsi & Penggunaan Spesifik di Seluruh UI |
| :--- | :--- | :--- |
| `--pink-primary` | `#E66884` | Tombol CTA utama (`Yuk Donasi`, `Daftar`, `Bayar Sekarang`, `Kirim LPJ`), lencana aktif, ikon bulat aksi, dan titik aksen |
| `--pink-hover` | `#D2526E` | Status hover dan interaksi aktif pada seluruh tombol CTA pink |
| `--pink-soft` | `#FADCE3` | Garis tepi elemen rose, border halus highlight, dan aksen latar sekunder |
| `--pink-blush` | `#FFF0F3` | Latar belakang badge tag kecil, latar ikon statistik, dan highlight angka metrik |

### 2.3 Status Indikator Sistem (Konsisten Antar-Modul)
Digunakan untuk merepresentasikan status data pada *campaign*, donasi, pencairan dana, maupun verifikasi identitas:
* **AKTIF / VALID / TERVERIFIKASI / BERHASIL:** Kapsul latar putih dengan pil rose `#E66884` atau pil sage `#568265` bertuliskan huruf kapital tebal.
* **AUDIT LPJ / SELESAI / SELESAI DICATAT:** Badge sage `#568265` dengan label putih `#FFFFFF`.
* **PENDING / MENUNGGU VERIFIKASI:** Latar `#FDECC8` dengan teks `#8A5A2B` dan indikator oranye.
* **DITOLAK / DIBEKUKAN / PERLU REVISI:** Latar `#FFF0F3` dengan teks `#E66884` dan garis tepi `#FADCE3`.

---

## 3. Tipografi Global (Typography System)

* **Font Keluarga Utama:** `'Plus Jakarta Sans'`, sans-serif (mendukung keterbacaan angka nominal Rupiah dan bentuk geometris ramah).
* **Hierarki Teks Standar:**
  * **Page / Hero Headline (H1):** `32px` – `36px`, `font-weight: 800`, warna `--green-deep` (`#264634`), `line-height: 1.2`, `letter-spacing: -0.8px`.
  * **Section / Page Module Title (H2):** `20px` – `24px`, `font-weight: 800`, warna `--green-deep`. Tanpa ornamen titik di kiri-kanan judul.
  * **Card / Widget Title (H3):** `16px` – `18px`, `font-weight: 800`, warna `--green-deep`, `line-height: 1.35`.
  * **Brand Wordmark:** `18px` – `20px`, `font-weight: 800`, warna `--green-deep` dengan pembeda kata `Fund` (`--green-primary`) dan titik penutup pink (`.`).
  * **Nilai Metrik Statistik (Data Numbers):** `20px` – `28px`, `font-weight: 800`, warna `--green-primary` atau `--pink-primary` (`font-variant-numeric: tabular-nums`).
  * **Teks Isi (Body Text):** `13.5px` – `15px`, `font-weight: 500`, warna `--text-body` (`#4D6A58`), `line-height: 1.6`.
  * **Label Kapsul, Tab, & Tombol:** `11px` – `13px`, `font-weight: 700` s.d. `800`, huruf kapital (*UPPERCASE*).

---

## 4. Karakteristik Komponen Dasar (Universal UI Components)

### 4.1 Arsitektur Kontainer Berlapis (*Nested Trays*)
Prinsip utama tata letak PeduliFund adalah mengelompokkan kartu-kartu putih di dalam baki permukaan lembut agar tidak mengambang tanpa konteks:
* **Bingkai Jendela Aplikasi (`.app-window`):** Latar `#F5F9F4`, garis tepi `4px solid #568265`, `border-radius: 32px`, bayangan lembut `0 20px 50px rgba(38, 70, 52, 0.10)`. Lebar **fluid** mengikuti viewport dengan jarak `24px` dari tiap sisi (bukan terkunci di tengah), dan margin/padding dimampatkan pada layar `<768px`.
* **Baki Bagian / Panel (`.pf-tray`, `.hero-tray`, `.section-block`):** Latar `--bg-tray` (`#E6EFE5`), `border-radius: 28px`, padding `28px` s.d. `40px`.
* **Kartu Konten Putih (`.pf-card`, `.step-card`):** Latar `#FFFFFF`, `border-radius: 22px`, padding `20px` s.d. `28px`, bayangan lembut `box-shadow: 0 12px 28px rgba(38, 70, 52, 0.06)`.

### 4.2 Bilah Navigasi (*Navbar & Header*)
* Desain mengambang penuh (*floating pill*) dengan `border-radius: 999px`, latar `#FFFFFF`, dan bayangan kartu halus.
* **Logo:** Wordmark `PeduliFund.` dengan kata `Fund` berwarna hijau sage dan titik penutup pink.
* **Menu Kapsul:** Pembungkus hijau pucat (`#EDF4EC`), item aktif berlatar putih dengan dot indikator pink `7px`.
* **Aksi Otentikasi / Profil:** Seluruh tombol aksi (Masuk, Daftar) kapsul solid *Rose Pink* dengan ukuran seragam — satu gaya, tidak bercampur outline.

### 4.3 Tombol & Tombol Ikon Bulat (*Buttons & Micro-Actions*)
* **Tombol CTA Utama (`.btn-pf-cta`, `.btn-yuk-donasi`):** Kapsul penuh (`border-radius: 999px`), latar `#E66884`, teks `#FFFFFF`, bayangan `0 10px 25px rgba(230, 104, 132, 0.4)`. Dilengkapi opsi lingkaran dalam putih (`34px x 34px`) dengan ikon panah rose pink.
* **Tombol Ikon Bulat (`.circle-pink-btn`):** Ukuran `38px` – `44px`, `border-radius: 50%`, latar `#E66884`, ikon putih, bayangan `0 8px 20px rgba(230, 104, 132, 0.32)`.
* **Tombol Kapsul Status Switch (`.status-pill-switch`):** Latar putih dengan pil toggle kecil `AKTIF` berlatar rose pink atau sage green.

### 4.4 Form Input & Kontrol Data
* Input teks, dropdown, dan pencarian menggunakan latar `#EDF4EC` atau `#FFFFFF` dengan garis tepi `1.5px solid #C5D9C8`, `border-radius: 12px` (atau `999px` untuk search pill).
* Saat fokus (`:focus`), border berganti ke `--green-primary` atau `--pink-primary` dengan ring halus.

### 4.5 Indikator Progres Donasi & Metrik
* **Linear Progress Bar:** Tinggi `8px` – `10px`, `border-radius: 999px`, latar `#EDF4EC`, dengan bar pengisi warna rose pink (`#E66884`) atau sage green (`#568265`).
* **Circular Progress / Dial Meter:** Cincin dial persentase capaian target 100% transparan dengan titik aksen pink.

### 4.6 Alur Langkah Berurutan (*Stepper / Process Cards*)
* Tiga kartu langkah terstruktur menggunakan lingkaran ganda:
  * Lingkaran luar berdiameter `66px` dengan garis putus-putus (*dashed border*).
  * Lingkaran dalam berdiameter `50px` dengan angka tebal (`1`, `2`, `3`).
  * Aksen warna berirama bergantian antara *Rose Pink* (`#E66884`) dan *Sage Green* (`#568265`).

---

## 5. Implementasi CSS Referensi Universal (`pedulifund-theme.css`)

Satu-satunya berkas stylesheet tema aplikasi adalah `assets/css/pedulifund-theme.css` (berisi token Bagian 2, tipografi Bagian 3, dan komponen Bagian 4); berkas `style.css` lama sudah digabung dan dihapus. Bootstrap 5.3 dimuat dari berkas lokal `assets/vendor/bootstrap-5.3.3/` (bukan CDN) sebagai fondasi grid/tabel, di lapisan di bawah tema.

```css
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
  /* Sage & Mint Palette */
  --bg-outer: #E4ECE2;
  --bg-canvas: #F5F9F4;
  --bg-tray: #E6EFE5;
  --surface-white: #FFFFFF;
  --green-deep: #264634;
  --green-primary: #568265;
  --green-mid: #739B80;
  --green-light: #C5D9C8;
  --green-pale: #EDF4EC;

  /* Rose & Blush Pink Palette */
  --pink-primary: #E66884;
  --pink-hover: #D2526E;
  --pink-soft: #FADCE3;
  --pink-blush: #FFF0F3;

  /* Typography Colors */
  --text-main: #264634;
  --text-body: #4D6A58;
  --text-muted: #7E9987;

  /* Radius Scale */
  --radius-window: 32px;
  --radius-tray: 28px;
  --radius-card: 22px;
  --radius-input: 12px;
  --radius-pill: 999px;

  /* Elevation Shadows */
  --shadow-card: 0 12px 28px rgba(38, 70, 52, 0.06);
  --shadow-pink: 0 8px 20px rgba(230, 104, 132, 0.32);
  --shadow-green: 0 10px 24px rgba(86, 130, 101, 0.25);
}

body {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background-color: var(--bg-canvas);
  color: var(--text-main);
  margin: 0;
  padding: 0;
}

/* Kontainer Tray Berlapis */
.pf-tray {
  background-color: var(--bg-tray);
  border-radius: var(--radius-tray);
  padding: 28px 36px;
}

/* Kartu Putih Membulat */
.pf-card {
  background-color: var(--surface-white);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  border: none;
}

/* Tombol CTA Rose Pink */
.btn-pf-cta {
  background-color: var(--pink-primary);
  color: #FFFFFF;
  font-weight: 800;
  border-radius: var(--radius-pill);
  padding: 12px 28px;
  border: none;
  box-shadow: var(--shadow-pink);
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.btn-pf-cta:hover {
  background-color: var(--pink-hover);
  transform: translateY(-1px);
}
```

---

## 6. Dokumentasi Lengkap Implementasi Contoh: Layar UI-001 Beranda

Layar **UI-001 Beranda** adalah **studi kasus acuan konkret** (*concrete baseline reference*) dari penerapan panduan ini. Berikut adalah seluruh inventaris komponen, tata letak, dan detail mikro yang diimplementasikan pada layar UI-001 yang ada di kanvas:

### 6.1 Bilah Identitas & Navigasi (*Navbar*)
* **Brand Wordmark & Logo:**
  * Teks `Peduli` (`#264634`) + `Fund` (`#568265`) + titik penutup pink (`#E66884`).
* **Menu Navigasi Kapsul:**
  * Kontainer hijau pucat (`#EDF4EC`, `border-radius: 999px`, padding `6px`).
  * Item aktif (`BERANDA`) dengan latar putih (`#FFFFFF`), teks gelap tebal, dan dot pink (`7px`).
  * Item tautan sekunder: `TENTANG` dan `CARA KERJA` (`#4D6A58`).
* **Aksi Otentikasi Pengguna:**
  * Tombol `LOGIN`: Kapsul berlatar `#EDF4EC`, teks `#264634`, border `1.5px solid #C5D9C8`, dengan ikon kunci/masuk rose pink `#E66884`.
  * Tombol `DAFTAR`: Kapsul solid berlatar `#E66884`, teks `#FFFFFF`, dengan ikon user registrasi putih.

### 6.2 Zona Hero & Aksi Inti (*Hero Section Tray*)
* **Baki Kontainer Hero:** Latar `--bg-tray` (`#E6EFE5`), `border-radius: 28px`, padding `36px 40px`, dilengkapi ornamen latar gelombang organik (`.wave-bg-1` sage gradient & `.wave-bg-2` blush pink gradient).
* **Bar Indikator Atas Hero:**
  * Lencana status switch: Kapsul putih dengan pil toggle `AKTIF` berlatar rose pink `#E66884` dan teks label `Platform Galang Dana Terverifikasi`.
  * Tiga tombol ikon bulat pink (`.circle-pink-btn`, `38px x 38px`, border-radius 50%, latar `#E66884`) mewakili 3 pilar: (1) Perisai (Verifikasi Akun), (2) Dokumen (Transparansi LPJ), (3) Hati (Penyaluran Tepat Sasaran).
* **Judul & Subjudul Hero:**
  * H1 Headline: `YUK DONASI,` (`#264634`) baris atas, dan `SALURKAN KEBAIKAN` (`#568265`) baris bawah.
  * Subjudul: Teks deskripsi ramah dan ringkas (`15px`, `#4D6A58`).
* **Tombol CTA Utama (`.btn-yuk-donasi`):**
  * Kapsul penuh memanjang (`border-radius: 999px`, latar `#E66884`, teks `#FFFFFF`).
  * Dilengkapi tombol lingkaran dalam putih (`34px x 34px`) dengan ikon panah rose pink (`#E66884`).
* **Strip Metrik Transparansi 3 Kolom:**
  * Kartu putih mengambang (`max-width: 820px`, `border-radius: 22px`, bayangan lembut).
  * Terdiri dari 3 sel terpisah dengan border pemisah halus:
    1. **100% Fundraiser Terverifikasi:** Ikon perisai centang di dalam lingkaran blush pink (`#FFF0F3`) dengan border `#FADCE3`.
    2. **0% Potongan Cair Penuh ke Rekening:** Ikon kartu kredit/transfer.
    3. **Wajib LPJ Audit Nota Maks. 1 Bulan:** Ikon dokumen audit centang.

### 6.3 Zona Edukasi ("Apa Itu PeduliFund?")
* **Judul Modul:** Teks `APA ITU PEDULIFUND?` — tebal hijau tua, tanpa ornamen titik.
* **Tata Letak Asimetris (2 Kolom):**
  * **Kolom Kiri — Kluster Widget Visual:**
    * **Kartu Kembar Mini:**
      * Kartu Sage Green: Pil toggle `VALID` berlatar rose pink, ikon user verifikasi, label *Verifikasi KTP & Wajah*.
      * Kartu Rose Pink: Pil toggle `LPJ OK` berlatar sage green, ikon dokumen audit, label *Bukti Nota & Audit*.
    * **Widget Dial Target:** Balok horizontal sage green (`#568265`) memuat cincin dial melingkar (`TARGET 100%`) dengan titik aksen pink luar, teks pemantauan real-time via Bank/E-Wallet/QRIS, serta bilah progres donasi terisi penuh warna rose pink (`#E66884`).
  * **Kolom Kanan — Kartu Narasi Keunggulan:**
    * Tag kapsul: `✨ TENTANG PLATFORM KAMI` (latar `#FFF0F3`, teks `#E66884`).
    * Judul penjelasan: `Menghubungkan Donatur dan Fundraiser Secara Aman & Terbuka`.
    * Paragraf pengantar misi platform.
    * Dua baris fitur unggulan berlatar `#EDF4EC` dengan kotak ikon centang pink (`26px x 26px`) dan sakelar mini status hijau `[ON] AKTIF`.

### 6.4 Zona Alur Layanan ("Cara Kerja")
* **Judul Modul:** Teks `CARA KERJA` — tebal hijau tua, tanpa ornamen titik.
* **Tiga Kartu Langkah Putih Sejajar:**
  * Masing-masing kartu menggunakan lingkaran nomor ganda:
    * **Langkah 1:** Lingkaran luar putus-putus rose pink + lingkaran dalam solid rose pink (`#E66884`) dengan angka `1`. Judul: *Pilih atau Buat Campaign*. Tag status: *Terverifikasi Admin*.
    * **Langkah 2:** Lingkaran luar putus-putus sage green + lingkaran dalam solid sage green (`#568265`) dengan angka `2`. Judul: *Salurkan Donasi Terbaik*. Tag status: *Update Progres Otomatis*.
    * **Langkah 3:** Lingkaran luar putus-putus rose pink + lingkaran dalam solid rose pink (`#E66884`) dengan angka `3`. Judul: *Pencairan & Laporan LPJ*. Tag status: *Transparansi 100%*.

### 6.5 Zona Penutup (*Footer*)
* Kartu baki putih melayang (`border-radius: 22px`, bayangan lembut).
* Identitas merek: Wordmark `PeduliFund.`.
* Tautan informasi kebijakan: `Beranda`, `Tentang Kami`, `Syarat & Ketentuan LPJ` dengan pemisah/penanda dot pink bulat.
* Label hak cipta: `© PeduliFund 2026` (`#7E9987`).

---

## 7. Panduan Replikasi untuk Layar-Layar Berikutnya

Bagi perancang dan pengembang yang membangun layar baru (UI-002 s.d. UI-018):
1. **Gunakan Fondasi Baki yang Sama:** Bungkus setiap modul kerja atau daftar data ke dalam `.pf-tray` sebelum menempatkan kartu putih `.pf-card`.
2. **Pertahankan Batasan Aksen Pink:** Jangan gunakan warna pink untuk kartu besar atau latar belakang tabel; gunakan hanya untuk tombol eksekusi (`Simpan`, `Donasi`, `Verifikasi`), badge fokal, dan detail indikator aktif.
3. **Standar Status Seragam:** Gunakan skema badge status kapsul (Aktif, Menunggu, Ditolak, LPJ Selesai) persis seperti yang telah dicontohkan di UI-001.
4. **Tipografi & Angka Rupiah:** Selalu gunakan `'Plus Jakarta Sans'` dengan `font-variant-numeric: tabular-nums` untuk setiap nominal donasi atau angka statistik.
