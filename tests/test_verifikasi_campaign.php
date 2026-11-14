<?php
declare(strict_types=1);

// Pengujian verifikasi campaign & alur revisi (UI-013, UI-010 revisi):
// daftar menunggu, publikasikan, tolak dengan alasan wajib, revisi kembali ke Pending, proteksi pemilik.
// Menggunakan akun uji sekali-pakai agar tidak tergantung keadaan data sampel.
require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../app/helpers/fungsi.php';
require_once __DIR__ . '/../app/models/UserCredentialsDB.php';
require_once __DIR__ . '/../app/models/CampaignEntity.php';

$jumlahGagal = 0;

$cek = static function (bool $kondisi, string $nama) use (&$jumlahGagal): void {
    echo ($kondisi ? '[OK]    ' : '[GAGAL] ') . $nama . PHP_EOL;
    if (!$kondisi) {
        $jumlahGagal++;
    }
};

$modelPengguna = new UserCredentialsDB($pdo);
$campaignEntity = new CampaignEntity($pdo);

echo "=== Pengujian Verifikasi Campaign (UI-013) ===" . PHP_EOL;

// 1. Akun uji sekali-pakai berperan Fundraiser
$emailUji = 'uji_campaign_' . bin2hex(random_bytes(4)) . '@pedulifund.test';
$idFundraiser = $modelPengguna->simpanAkunBaru($emailUji, 'rahasia123', 'Fundraiser');
$cek($idFundraiser > 0, 'Akun uji Fundraiser berhasil dibuat');

// 2. Campaign baru tersimpan berstatus Pending
$idCampaign1 = $campaignEntity->simpanCampaign([
    'id_fundraiser'    => $idFundraiser,
    'judul'            => 'Campaign Uji Publikasi',
    'jenis'            => 'Sosial',
    'deskripsi'        => 'Deskripsi campaign uji publikasi.',
    'target_dana'      => 1000000.0,
    'deadline'         => (new DateTimeImmutable('+30 days'))->format('Y-m-d'),
    'dokumen_campaign' => 'uji_dokumen_campaign.pdf',
    'gambar_campaign'  => null,
]);
$cek($idCampaign1 > 0, 'Campaign baru berhasil disimpan');

$belumDiputuskan = $campaignEntity->ambilByIdUntukAdmin($idCampaign1);
$cek($belumDiputuskan !== null && $belumDiputuskan['status_campaign'] === 'Pending', 'Campaign baru berstatus Pending');

// 3. Daftar menunggu admin menyertakan campaign + email pemilik
$adaDiDaftar = array_reduce(
    $campaignEntity->ambilCampaignMenungguVerifikasi(),
    static fn (bool $b, array $c): bool => $b || (int) $c['id_campaign'] === $idCampaign1,
    false
);
$cek($adaDiDaftar, 'Campaign Pending tampil di daftar verifikasi admin beserta email pemilik');

// 4. Menyetujui: status jadi Aktif dan tayang di daftar campaign publik
$cek($campaignEntity->publikasikanKePublik($idCampaign1), 'Menyetujui campaign Pending berhasil');
$disetujui = $campaignEntity->ambilByIdUntukAdmin($idCampaign1);
$cek($disetujui !== null && $disetujui['status_campaign'] === 'Aktif', 'Campaign disetujui berstatus Aktif');
$adaDiPublik = array_reduce(
    $campaignEntity->ambilCampaignPublik(),
    static fn (bool $b, array $c): bool => $b || (int) $c['id_campaign'] === $idCampaign1,
    false
);
$cek($adaDiPublik, 'Campaign Aktif tampil di daftar campaign publik');
$cek(!$campaignEntity->publikasikanKePublik($idCampaign1), 'Campaign yang sudah Aktif tidak bisa disetujui lagi');

// 5. Menolak tanpa alasan wajib ditolak sistem
$idCampaign2 = $campaignEntity->simpanCampaign([
    'id_fundraiser'    => $idFundraiser,
    'judul'            => 'Campaign Uji Penolakan',
    'jenis'            => 'Bencana',
    'deskripsi'        => 'Deskripsi campaign uji penolakan.',
    'target_dana'      => 2000000.0,
    'deadline'         => (new DateTimeImmutable('+30 days'))->format('Y-m-d'),
    'dokumen_campaign' => 'uji_dokumen_campaign2.pdf',
    'gambar_campaign'  => null,
]);
$cek(!$campaignEntity->mintaRevisiFormulir($idCampaign2, '   '), 'Menolak campaign tanpa alasan tidak boleh berhasil');

// 6. Menolak dengan alasan: status Ditolak + alasan tersimpan
$cek($campaignEntity->mintaRevisiFormulir($idCampaign2, 'Dokumen tidak lengkap'), 'Menolak campaign dengan alasan berhasil');
$ditolak = $campaignEntity->ambilByIdUntukAdmin($idCampaign2);
$cek($ditolak !== null
    && $ditolak['status_campaign'] === 'Ditolak'
    && $ditolak['alasan_penolakan'] === 'Dokumen tidak lengkap', 'Campaign ditolak menyimpan alasan penolakan');
$cek(!$campaignEntity->publikasikanKePublik($idCampaign2), 'Campaign Ditolak tidak bisa disetujui tanpa direvisi');

// 7. Revisi oleh pemilik: data diperbarui dan status kembali ke Pending
$judulRevisi = 'Campaign Uji Penolakan (Revisi)';
$cek($campaignEntity->perbaruiCampaignRevisi($idCampaign2, $idFundraiser, [
    'judul'            => $judulRevisi,
    'jenis'            => 'Bencana',
    'deskripsi'        => 'Deskripsi sudah diperbaiki.',
    'target_dana'      => 2500000.0,
    'deadline'         => (new DateTimeImmutable('+30 days'))->format('Y-m-d'),
    'dokumen_campaign' => 'uji_dokumen_campaign2.pdf',
    'gambar_campaign'  => null,
]) !== null, 'Revisi campaign oleh pemilik berhasil');
$revisi = $campaignEntity->ambilByIdUntukAdmin($idCampaign2);
$cek($revisi !== null && $revisi['status_campaign'] === 'Pending' && $revisi['judul'] === $judulRevisi, 'Revisi mengubah data dan mengembalikan status ke Pending');
$cek($revisi !== null && $revisi['alasan_penolakan'] === null, 'Alasan penolakan dibersihkan setelah revisi');

// 8. Revisi oleh akun lain ditolak sistem (proteksi pemilik)
$emailLain = 'uji_campaign_lain_' . bin2hex(random_bytes(4)) . '@pedulifund.test';
$idFundraiserLain = $modelPengguna->simpanAkunBaru($emailLain, 'rahasia123', 'Fundraiser');
$cek($campaignEntity->perbaruiCampaignRevisi($idCampaign2, $idFundraiserLain, [
    'judul'            => 'Judul Bajakan',
    'jenis'            => 'Bencana',
    'deskripsi'        => 'Deskripsi bajakan.',
    'target_dana'      => 1.0,
    'deadline'         => (new DateTimeImmutable('+30 days'))->format('Y-m-d'),
    'dokumen_campaign' => 'uji_dokumen_campaign2.pdf',
    'gambar_campaign'  => null,
]) === null, 'Revisi campaign oleh akun selain pemilik ditolak');

// 9. Campaign hasil revisi kembali tampil di daftar verifikasi admin
$adaKembali = array_reduce(
    $campaignEntity->ambilCampaignMenungguVerifikasi(),
    static fn (bool $b, array $c): bool => $b || (int) $c['id_campaign'] === $idCampaign2,
    false
);
$cek($adaKembali, 'Campaign hasil revisi kembali menunggu verifikasi admin');

// 10. Bersihkan data uji agar tabel sampel tetap rapi
$pdo->prepare('DELETE FROM campaign WHERE id_fundraiser IN (?, ?)')->execute([$idFundraiser, $idFundraiserLain]);
$pdo->prepare('DELETE FROM pengguna WHERE id_user IN (?, ?)')->execute([$idFundraiser, $idFundraiserLain]);

echo str_repeat('-', 56) . PHP_EOL;
echo $jumlahGagal === 0 ? 'Semua pengujian verifikasi campaign LULUS.' : "{$jumlahGagal} pengujian GAGAL." . PHP_EOL;
exit($jumlahGagal === 0 ? 0 : 1);
