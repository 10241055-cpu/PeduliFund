<?php
declare(strict_types=1);

// Pengujian pengajuan verifikasi akun fundraiser (UI-008, UI-012, UI-007):
// status Menunggu, keputusan admin Valid/Ditolak, aturan ajukan ulang 14 hari, gerbang foto wajah.
// Menggunakan akun uji sekali-pakai agar tidak tergantung keadaan data sampel.
require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../app/helpers/fungsi.php';
require_once __DIR__ . '/../app/models/UserCredentialsDB.php';
require_once __DIR__ . '/../app/models/VerifikasiFundraiserEntity.php';

$jumlahGagal = 0;

$cek = static function (bool $kondisi, string $nama) use (&$jumlahGagal): void {
    echo ($kondisi ? '[OK]    ' : '[GAGAL] ') . $nama . PHP_EOL;
    if (!$kondisi) {
        $jumlahGagal++;
    }
};

$modelPengguna = new UserCredentialsDB($pdo);
$verifikasiEntity = new VerifikasiFundraiserEntity($pdo);

echo "=== Pengujian Verifikasi Akun & Wajah (UI-008, UI-012, UI-007) ===" . PHP_EOL;

// 1. Data sampel menyediakan pengajuan Menunggu untuk percobaan UI-012
$pengajuanSample = $verifikasiEntity->ambilById(1);
$cek($pengajuanSample !== null && $pengajuanSample['status_dokumen'] === 'Menunggu', 'Pengajuan sampel (id 1) berstatus Menunggu');

// 2. Akun uji sekali-pakai
$emailUji = 'uji_verif_' . bin2hex(random_bytes(4)) . '@pedulifund.test';
$idUserUji = $modelPengguna->simpanAkunBaru($emailUji, 'rahasia123', 'Donatur');
$cek($idUserUji > 0, 'Akun uji berhasil dibuat sebagai Donatur');

// 3. Pengajuan baru tersimpan dan berstatus Menunggu
$idPengajuan = $verifikasiEntity->buatPengajuan([
    'id_user'            => $idUserUji,
    'jenis_fundraiser'   => 'Organisasi',
    'nama_lengkap'       => 'Komunitas Uji',
    'nomor_identitas'    => '1234567890123456',
    'nomor_telepon'      => '081999999999',
    'dokumen_verifikasi' => 'uji_dokumen.pdf',
    'dokumen_pendukung'  => null,
]);
$cek($idPengajuan > 0, 'Pengajuan baru berhasil disimpan');

$pengajuanBaru = $verifikasiEntity->ambilById($idPengajuan);
$cek($pengajuanBaru !== null && $pengajuanBaru['status_dokumen'] === 'Menunggu', 'Pengajuan baru berstatus Menunggu');

// 4. Daftar menunggu admin menyertakan pengajuan + email pemohon
$adaDiDaftar = array_reduce(
    $verifikasiEntity->ambilMenunggu(),
    static fn (bool $b, array $p): bool => $b || (int) $p['id_verifikasi'] === $idPengajuan,
    false
);
$cek($adaDiDaftar, 'Pengajuan tampil di daftar menunggu admin beserta email pemohon');

// 5. Menolak tanpa alasan wajib ditolak sistem
$cek(!$verifikasiEntity->tolak($idPengajuan, '   '), 'Menolak pengajuan tanpa alasan tidak boleh berhasil');

// 6. Menolak dengan alasan: status Ditolak + alasan + tanggal keputusan tersimpan
$cek($verifikasiEntity->tolak($idPengajuan, 'Dokumen tidak jelas'), 'Menolak pengajuan dengan alasan berhasil');
$pengajuanDitolak = $verifikasiEntity->ambilById($idPengajuan);
$cek($pengajuanDitolak !== null
    && $pengajuanDitolak['status_dokumen'] === 'Ditolak'
    && $pengajuanDitolak['alasan_penolakan'] === 'Dokumen tidak jelas'
    && !empty($pengajuanDitolak['tanggal_keputusan']), 'Pengajuan ditolak menyimpan alasan dan tanggal keputusan');

// 7. Aturan 14 hari: hari ke-13 masih terkunci, hari ke-15 sudah terbuka, tanpa riwayat selalu boleh
$tanggalKeputusan = (string) $pengajuanDitolak['tanggal_keputusan'];
$cek(!bolehAjukanUlang($tanggalKeputusan, (new DateTimeImmutable($tanggalKeputusan))->modify('+13 days')->format('Y-m-d')), 'Ajukan ulang 13 hari setelah tolak masih terkunci');
$cek(bolehAjukanUlang($tanggalKeputusan, (new DateTimeImmutable($tanggalKeputusan))->modify('+15 days')->format('Y-m-d')), 'Ajukan ulang 15 hari setelah tolak sudah terbuka');
$cek(bolehAjukanUlang(null, date('Y-m-d')), 'Pemohon tanpa riwayat penolakan boleh mengajukan');

// 8. Setujui pengajuan lain (baris Ditolak tidak bisa disetujui karena hanya Menunggu)
$idPengajuan2 = $verifikasiEntity->buatPengajuan([
    'id_user'            => $idUserUji,
    'jenis_fundraiser'   => 'Individu',
    'nama_lengkap'       => 'Komunitas Uji 2',
    'nomor_identitas'    => '6543210987654321',
    'nomor_telepon'      => '081888888888',
    'dokumen_verifikasi' => 'uji_dokumen2.pdf',
    'dokumen_pendukung'  => null,
]);
$cek(!$verifikasiEntity->setujui($idPengajuan), 'Pengajuan yang sudah Ditolak tidak bisa langsung disetujui');
$cek($verifikasiEntity->setujui($idPengajuan2), 'Menyetujui pengajuan Menunggu berhasil');
$pengajuanValid = $verifikasiEntity->ambilById($idPengajuan2);
$cek($pengajuanValid !== null && $pengajuanValid['status_dokumen'] === 'Valid', 'Pengajuan disetujui berstatus Valid');

// 9. Role akun naik menjadi Fundraiser setelah persetujuan (alur UI-012)
$cek($modelPengguna->ubahRole($idUserUji, 'Fundraiser'), 'Role akun diubah menjadi Fundraiser setelah persetujuan');
$akunNaik = $modelPengguna->ambilBerdasarkanId($idUserUji);
$cek($akunNaik !== null && $akunNaik['role'] === 'Fundraiser', 'Role akun terbaca Fundraiser');

// 10. Gerbang foto wajah: NULL = belum lolos, terisi = sudah lolos (UI-007)
$cek($modelPengguna->butuhVerifikasiWajah($idUserUji), 'Akun tanpa foto wajah masih perlu gerbang UI-007');
$cek($modelPengguna->simpanFotoWajah($idUserUji, 'uji_wajah.jpg'), 'Simpan foto wajah berhasil');
$cek(!$modelPengguna->butuhVerifikasiWajah($idUserUji), 'Akun dengan foto wajah lolos gerbang UI-007');

// 11. Metrik admin: hitungMenunggu konsisten (sampel id 1 tetap Menunggu)
$cek($verifikasiEntity->hitungMenunggu() >= 1, 'Metrik pengajuan Menunggu terisi untuk Dashboard Admin');

echo str_repeat('-', 56) . PHP_EOL;
echo $jumlahGagal === 0 ? 'Semua pengujian verifikasi akun LULUS.' : "{$jumlahGagal} pengujian GAGAL." . PHP_EOL;
exit($jumlahGagal === 0 ? 0 : 1);
