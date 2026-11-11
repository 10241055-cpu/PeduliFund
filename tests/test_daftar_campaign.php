<?php
// Pengujian daftar & detail campaign publik (UI-005, UI-006):
// hanya campaign Aktif tampil, escape LIKE bekerja, Pending/Ditolak tak terlihat publik

require_once __DIR__ . '/../app/bootstrap.php';

$jumlahLulus = 0;
$jumlahGagal = 0;

function ujiDaftar(string $nama, bool $kondisi): void
{
    global $jumlahLulus, $jumlahGagal;
    if ($kondisi) {
        echo "[OK]    {$nama}\n";
        $jumlahLulus++;
    } else {
        echo "[GAGAL] {$nama}\n";
        $jumlahGagal++;
    }
}

$pdo = new PDO('mysql:host=127.0.0.1;dbname=pedulifund;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$campaignEntity = new CampaignEntity($pdo);
$donasiEntity = new DonasiEntity($pdo);

echo "=== Pengujian Daftar & Detail Campaign Publik (UI-005, UI-006) ===\n";

$daftar = $campaignEntity->ambilCampaignPublik();
ujiDaftar('Daftar publik tidak kosong (ada campaign Aktif)', count($daftar) >= 1);
$semuaAktif = true;
foreach ($daftar as $baris) {
    if ($baris['status_campaign'] !== 'Aktif' || $baris['deadline'] < date('Y-m-d')) {
        $semuaAktif = false;
    }
}
ujiDaftar('Seluruh baris berstatus Aktif dan belum melewati deadline', $semuaAktif);

$idTersedia = array_map(static fn (array $c): int => (int) $c['id_campaign'], $daftar);
ujiDaftar('Campaign Pending (2) dan Selesai (3) tidak tampil di daftar publik',
    !in_array(2, $idTersedia, true) && !in_array(3, $idTersedia, true));

ujiDaftar("Pencarian '%' (karakter wildcard) menghasilkan daftar kosong karena di-escape",
    $campaignEntity->ambilCampaignPublik('%') === []);

ujiDaftar("Pencarian 'Renovasi' menemukan campaign Panti Asuhan",
    array_filter($campaignEntity->ambilCampaignPublik('Renovasi'), static fn (array $c): bool => (int) $c['id_campaign'] === 1) !== []);

ujiDaftar("Filter jenis 'Sosial' hanya memuat campaign jenis Sosial",
    array_reduce($campaignEntity->ambilCampaignPublik('', 'Sosial'), static fn (bool $b, array $c): bool => $b && $c['jenis'] === 'Sosial', true));

ujiDaftar('Detail publik menampilkan campaign Selesai (3) agar bisa diaudit transparansi',
    $campaignEntity->ambilCampaignPublikById(3) !== null);

ujiDaftar('Detail publik menolak campaign Pending (2)',
    $campaignEntity->ambilCampaignPublikById(2) === null);

$terbaru = $donasiEntity->ambilDonaturTerbaruByCampaign(1);
ujiDaftar('Donatur terbaru campaign 1 berjumlah 2 (donasi Gagal tidak ikut)', count($terbaru) === 2);
ujiDaftar('Donatur terbaru tersortir terbaru dulu (QRIS 2.5 jt di posisi pertama)',
    (isset($terbaru[0]['nominal']) && (float) $terbaru[0]['nominal'] === 2500000.0));

echo "--------------------------------------------------------\n";
echo $jumlahGagal === 0 ? "Semua pengujian campaign publik LULUS.\n" : "{$jumlahGagal} pengujian GAGAL.\n";
exit($jumlahGagal === 0 ? 0 : 1);
