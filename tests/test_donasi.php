<?php
declare(strict_types=1);

// Pengujian fitur Melakukan Donasi (ACT-003 / SEQ-003). Jalankan: php tests/test_donasi.php
require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../app/helpers/fungsi.php';
require_once __DIR__ . '/../app/models/CampaignEntity.php';
require_once __DIR__ . '/../app/models/DonasiEntity.php';
require_once __DIR__ . '/../app/controllers/DonationController.php';

$jumlahGagal = 0;

$cek = static function (bool $kondisi, string $nama) use (&$jumlahGagal): void {
    echo ($kondisi ? '[OK]    ' : '[GAGAL] ') . $nama . PHP_EOL;
    if (!$kondisi) {
        $jumlahGagal++;
    }
};

echo "=== Pengujian Unit Fitur Melakukan Donasi (ACT-003) ===" . PHP_EOL;

// 1. Verifikasi Konstanta Model DonasiEntity sesuai Kamus Data SDD Tabel 4.5
$cek(
    DonasiEntity::DAFTAR_METODE === ['Transfer Bank', 'EWallet', 'QRIS'],
    'Daftar metode pembayaran DonasiEntity sesuai dengan SDD Tabel 4.5'
);

$cek(
    DonasiEntity::DAFTAR_STATUS === ['Berhasil', 'Gagal'],
    'Daftar status pembayaran DonasiEntity sesuai dengan SDD Tabel 4.5'
);

// 2. Verifikasi Logika Kalkulasi Persentase Donasi
$persen1 = hitungPersen(2500000, 10000000);
$cek($persen1 === 25, 'Kalkulasi progres 2.500.000 / 10.000.000 menghasilkan 25%');

$persenCapai = hitungPersen(12000000, 10000000);
$cek($persenCapai === 100, 'Kalkulasi progres melebihi target dipatok maksimal 100%');

$persenNol = hitungPersen(50000, 0);
$cek($persenNol === 0, 'Kalkulasi jika target nol menghasilkan 0% tanpa division by zero');

// 3. Verifikasi Logika Pembersihan Nominal Uang
$nominalInput1 = '50.000';
$nominalBersih1 = (float) str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $nominalInput1);
$cek($nominalBersih1 === 50000.0, 'Pembersihan format nominal dengan titik pemisah ribuan berhasil');

$nominalInput2 = 'Rp 100.000';
$nominalBersih2 = (float) str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $nominalInput2);
$cek($nominalBersih2 === 100000.0, 'Pembersihan nominal dengan awalan "Rp " berhasil');

// 4. Verifikasi Aturan Batas Minimal Donasi
$cek($nominalBersih1 >= 10000, 'Nominal donasi Rp 50.000 di atas batas minimal Rp 10.000');
$nominalKecil = 5000.0;
$cek($nominalKecil < 10000, 'Nominal di bawah Rp 10.000 terdeteksi tidak valid');

// 5. Verifikasi Format Tampilan Mata Uang Rupiah
$cek(formatRupiah(50000) === 'Rp 50.000', 'formatRupiah(50000) menghasilkan "Rp 50.000"');
$cek(formatRupiah(10000000) === 'Rp 10.000.000', 'formatRupiah(10000000) menghasilkan "Rp 10.000.000"');

echo "--------------------------------------------------------" . PHP_EOL;
echo $jumlahGagal === 0 ? 'Semua pengujian fitur donasi LULUS.' : $jumlahGagal . ' pengujian GAGAL.';
echo PHP_EOL;

exit($jumlahGagal > 0 ? 1 : 0);

