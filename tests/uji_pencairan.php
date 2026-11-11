<?php
declare(strict_types=1);

// Pengujian unit alur bisnis Pencairan Dana & Audit LPJ (ACT-004). Jalankan: php tests/uji_pencairan.php
require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../app/helpers/fungsi.php';
require_once __DIR__ . '/../app/models/UserCredentialsDB.php';
require_once __DIR__ . '/../app/models/CampaignEntity.php';
require_once __DIR__ . '/../app/models/PencairanEntity.php';
require_once __DIR__ . '/../app/controllers/PencairanController.php';

$jumlahGagal = 0;

$cek = static function (bool $kondisi, string $nama) use (&$jumlahGagal): void {
    echo ($kondisi ? '[OK]    ' : '[GAGAL] ') . $nama . PHP_EOL;
    if (!$kondisi) {
        $jumlahGagal++;
    }
};

$pdoUji = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdoUji->exec('PRAGMA foreign_keys = ON;');

$pdoUji->exec("
    CREATE TABLE pengguna (
        id_user INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        role TEXT NOT NULL,
        status_akun TEXT NOT NULL DEFAULT 'Aktif',
        suspend_sampai TEXT NULL
    );
    CREATE TABLE campaign (
        id_campaign INTEGER PRIMARY KEY AUTOINCREMENT,
        id_fundraiser INTEGER NOT NULL,
        judul TEXT NOT NULL,
        jenis TEXT NOT NULL,
        target_dana REAL NOT NULL,
        dana_terkumpul REAL DEFAULT 0.00,
        deadline TEXT NOT NULL,
        dokumen_campaign TEXT NOT NULL,
        status_campaign TEXT DEFAULT 'Pending',
        deskripsi TEXT NOT NULL,
        gambar_campaign TEXT NULL,
        FOREIGN KEY (id_fundraiser) REFERENCES pengguna (id_user) ON DELETE CASCADE
    );
    CREATE TABLE pencairan_dana (
        id_pencairan INTEGER PRIMARY KEY AUTOINCREMENT,
        id_campaign INTEGER NOT NULL,
        nominal_pencairan REAL NOT NULL,
        nama_bank TEXT NOT NULL,
        nomor_rekening TEXT NOT NULL,
        atas_nama TEXT NOT NULL,
        status_pengajuan TEXT NOT NULL DEFAULT 'Menunggu',
        alasan_penolakan TEXT NULL,
        tanggal_pencairan TEXT NULL,
        batas_unggah_lpj TEXT NULL,
        dokumen_lpj TEXT NULL,
        bukti_nota TEXT NULL,
        tanggal_unggah_lpj TEXT NULL,
        status_transparansi TEXT NOT NULL DEFAULT 'Menunggu',
        FOREIGN KEY (id_campaign) REFERENCES campaign (id_campaign) ON DELETE CASCADE
    );
");

$userModel = new UserCredentialsDB($pdoUji);
$campaignEntity = new CampaignEntity($pdoUji);
$pencairanEntity = new PencairanEntity($pdoUji);
$controller = new PencairanController($pencairanEntity, $campaignEntity, $userModel, $pdoUji);

// Siapkan data awal: 1 fundraiser + 3 campaign (selesai, aktif kedaluwarsa, aktif berjalan)
$idFundraiser = $userModel->simpanAkunBaru('fundraiser_uji@pedulifund.test', 'rahasia123', 'Fundraiser');
$pdoUji->exec("
    INSERT INTO campaign (id_fundraiser, judul, jenis, target_dana, dana_terkumpul, deadline, dokumen_campaign, status_campaign, deskripsi)
    VALUES
    ({$idFundraiser}, 'Beasiswa Anak Desa', 'Pendidikan', 10000000, 4200000, '2026-10-01', 'doc1.pdf', 'Selesai', 'Deskripsi 1'),
    ({$idFundraiser}, 'Bantuan Kesehatan', 'Kesehatan', 5000000, 1500000, '2026-10-15', 'doc2.pdf', 'Aktif', 'Deskripsi 2'),
    ({$idFundraiser}, 'Renovasi Sekolah', 'Pendidikan', 15000000, 2000000, '2026-12-31', 'doc3.pdf', 'Aktif', 'Deskripsi 3')
");

// 1. Campaign aktif melewati deadline otomatis ditutup menjadi Selesai
$ditutup = $campaignEntity->tutupCampaignKedaluwarsa($idFundraiser, '2026-11-11');
$cek($ditutup === 1, 'Campaign aktif yang melewati deadline otomatis ditutup menjadi Selesai');
$cek($campaignEntity->cekStatusCampaign(2) === 'Selesai', 'Status campaign kedaluwarsa berubah menjadi Selesai');
$cek($campaignEntity->cekStatusCampaign(3) === 'Aktif', 'Status campaign sebelum deadline tetap Aktif');

// 2. Pengajuan pencairan pertama lalu ditolak admin
$idCair1 = $pencairanEntity->simpanPengajuan([
    'id_campaign'       => 1,
    'nominal_pencairan' => 4200000.0,
    'nama_bank'         => 'Bank BCA',
    'nomor_rekening'    => '1234567890',
    'atas_nama'         => 'Yayasan Peduli',
]);
$cek($idCair1 > 0, 'Pengajuan pencairan pertama berhasil disimpan');
$cek($pencairanEntity->adaPengajuanAktifAtauDisetujui(1), 'Pengajuan berstatus Menunggu memblokir pengajuan ganda');

$ditolak = $pencairanEntity->prosesKeputusanPencairan($idCair1, false, 'Nomor rekening tidak sesuai buku tabungan', null, null);
$cek($ditolak, 'Admin dapat menolak pengajuan pencairan beserta alasan');
$cek(!$pencairanEntity->adaPengajuanAktifAtauDisetujui(1), 'Setelah ditolak, campaign dapat diajukan ulang');
$penolakan = $pencairanEntity->ambilPenolakanTerakhir(1);
$cek(($penolakan['alasan_penolakan'] ?? '') === 'Nomor rekening tidak sesuai buku tabungan', 'Alasan penolakan terakhir terbaca untuk fundraiser');

// 3. Pengajuan ulang dan disetujui admin
$idCair2 = $pencairanEntity->simpanPengajuan([
    'id_campaign'       => 1,
    'nominal_pencairan' => 4200000.0,
    'nama_bank'         => 'Bank Mandiri',
    'nomor_rekening'    => '9876543210',
    'atas_nama'         => 'Yayasan Peduli',
]);
$ringkasanSebelum = $pencairanEntity->ambilRingkasanAdmin();
$cek($ringkasanSebelum['pencairan_menunggu'] === 1, 'Ringkasan admin menghitung pengajuan menunggu dengan benar');

$disetujui = $pencairanEntity->prosesKeputusanPencairan($idCair2, true, null, '2026-11-11 10:00:00', '2026-12-11');
$cek($disetujui, 'Admin dapat menyetujui pengajuan pencairan');
$cek($pencairanEntity->sudahDicairkan(1), 'Campaign ditandai sudah dicairkan');
$cek($pencairanEntity->hitungLpjBelumTerverifikasiByFundraiser($idFundraiser) === 1, 'Kewajiban LPJ belum terverifikasi terhitung 1');

// 4. Fundraiser unggah LPJ, ditolak admin, lalu unggah ulang & disetujui
$cek($pencairanEntity->simpanLpj($idCair2, 'lpj_1.pdf', 'nota_1.jpg', '2026-11-15 09:00:00'), 'Fundraiser berhasil mengunggah LPJ awal');
$cek($pencairanEntity->ambilRingkasanAdmin()['lpj_menunggu'] === 1, 'Antrean audit LPJ bertambah setelah unggah');

$cek($pencairanEntity->kirimKeputusanLpj($idCair2, false, 'Lampiran nota kurang jelas'), 'Admin dapat menolak LPJ dengan catatan revisi');
$dataCair2 = $pencairanEntity->ambilById($idCair2);
$cek(($dataCair2['status_transparansi'] ?? '') === 'Ditolak', 'Status transparansi berubah menjadi Ditolak');

$cek($pencairanEntity->simpanLpj($idCair2, 'lpj_2.pdf', 'nota_2.jpg', '2026-11-16 09:00:00'), 'Fundraiser dapat mengunggah ulang LPJ setelah ditolak');
$cek($pencairanEntity->kirimKeputusanLpj($idCair2, true), 'Admin memverifikasi LPJ hasil unggah ulang');
$cek($pencairanEntity->hitungLpjBelumTerverifikasiByFundraiser($idFundraiser) === 0, 'Setelah LPJ terverifikasi, kewajiban LPJ fundraiser menjadi 0');

// 5. Uji sanksi keterlambatan LPJ (> 1 bulan) -> bekukan akun & hentikan campaign aktif, lalu pulihkan setelah suspend + LPJ valid
$idCair3 = $pencairanEntity->simpanPengajuan([
    'id_campaign'       => 2,
    'nominal_pencairan' => 1500000.0,
    'nama_bank'         => 'Bank BRI',
    'nomor_rekening'    => '1122334455',
    'atas_nama'         => 'Yayasan Peduli',
]);
$pencairanEntity->prosesKeputusanPencairan($idCair3, true, null, '2026-11-01 10:00:00', '2026-12-01');

// Tepat pada tanggal batas belum dibekukan
$cek($controller->periksaDanTerapkanSanksiLpj('2026-12-01') === 0, 'Tepat di tanggal batas unggah LPJ akun belum dibekukan');
// Lewat tanggal batas -> dibekukan 1 bulan & campaign aktif ke-3 dihentikan
$cek($controller->periksaDanTerapkanSanksiLpj('2026-12-02') === 1, 'Melewati batas LPJ membekukan akun fundraiser');
$akun = $userModel->ambilBerdasarkanId($idFundraiser);
$cek(($akun['status_akun'] ?? '') === 'Dibekukan', 'Status akun menjadi Dibekukan');
$cek(($akun['suspend_sampai'] ?? '') === '2027-01-02', 'Masa suspend ditetapkan 1 bulan ke depan');
$cek($campaignEntity->cekStatusCampaign(3) === 'Selesai', 'Campaign aktif milik fundraiser ikut dihentikan saat akun dibekukan');

// Masa suspend lewat tetapi LPJ belum terverifikasi -> tetap dibekukan
$controller->periksaDanTerapkanSanksiLpj('2027-01-05');
$akun = $userModel->ambilBerdasarkanId($idFundraiser);
$cek(($akun['status_akun'] ?? '') === 'Dibekukan', 'Akun tetap dibekukan jika masa suspend habis namun LPJ belum valid');

// LPJ akhirnya diunggah dan diverifikasi -> akun aktif kembali karena masa suspend sudah lewat
$pencairanEntity->simpanLpj($idCair3, 'lpj_3.pdf', 'nota_3.jpg', '2027-01-06 10:00:00');
$pencairanEntity->kirimKeputusanLpj($idCair3, true);
$controller->periksaDanTerapkanSanksiLpj('2027-01-06');
$akun = $userModel->ambilBerdasarkanId($idFundraiser);
$cek(($akun['status_akun'] ?? '') === 'Aktif' && $akun['suspend_sampai'] === null, 'Akun aktif kembali setelah masa suspend selesai dan LPJ terverifikasi');

echo $jumlahGagal === 0 ? 'Semua pengujian pencairan dana lulus.' . PHP_EOL : $jumlahGagal . ' pengujian gagal.' . PHP_EOL;

exit($jumlahGagal > 0 ? 1 : 0);

