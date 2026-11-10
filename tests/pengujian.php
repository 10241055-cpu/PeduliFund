<?php
declare(strict_types=1);

// Pengujian CLI helper UnggahBerkas (batas keamanan upload). Jalankan: php tests/pengujian.php
require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../app/helpers/UnggahBerkas.php';

$jumlahGagal = 0;

$cek = static function (bool $kondisi, string $nama) use (&$jumlahGagal): void {
    echo ($kondisi ? '[OK]    ' : '[GAGAL] ') . $nama . PHP_EOL;
    if (!$kondisi) {
        $jumlahGagal++;
    }
};

$folder = DIR_UPLOAD_DOKUMEN;
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

// PNG valid 1x1 piksel (base64), tanpa butuh ekstensi GD
$pngAsli = base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
);

// 1. PNG asli diterima, tersimpan, lalu bisa dihapus lewat helper yang sama
$filePng = $folder . 'uji_png_valid.png';
file_put_contents($filePng, $pngAsli);
$hasil = UnggahBerkas::simpan(
    ['error' => UPLOAD_ERR_OK, 'size' => strlen($pngAsli), 'name' => 'uji.png', 'tmp_name' => $filePng],
    $folder,
    ['png'],
    UKURAN_MAKS_UPLOAD
);
$cek($hasil['berhasil'], 'PNG valid diterima');
$cek($hasil['berhasil'] && is_file($folder . $hasil['nama_file']), 'PNG valid tersimpan di folder tujuan');
if ($hasil['berhasil']) {
    UnggahBerkas::hapus($folder, $hasil['nama_file']);
    $cek(!is_file($folder . $hasil['nama_file']), 'hapus() menghapus berkas hasil upload');

    // 2. hapus() menolak nama berkas traversal (nama berisi ../)
    $sentinel = dirname($folder) . '/uji_sentinel_luar.txt';
    file_put_contents($sentinel, 'x');
    UnggahBerkas::hapus($folder, '..' . DIRECTORY_SEPARATOR . basename($sentinel));
    $cek(is_file($sentinel), 'hapus() menolak jalur traversal di luar folder');
    @unlink($sentinel);
}
@unlink($filePng);

// 3. Berkas teks yang di-rename .png ditolak (MIME tidak cocok ekstensi)
$filePalsu = $folder . 'uji_palsu.png';
file_put_contents($filePalsu, '<?php echo 1;');
$hasil = UnggahBerkas::simpan(
    ['error' => UPLOAD_ERR_OK, 'size' => 13, 'name' => 'uji_palsu.png', 'tmp_name' => $filePalsu],
    $folder,
    ['png'],
    UKURAN_MAKS_UPLOAD
);
$cek(!$hasil['berhasil'], 'Teks ber-ekstensi .png ditolak (MIME tidak cocok)');
@unlink($filePalsu);

// 4. Berkas lebih dari batas ditolak (cek ukuran)
$fileBesar = $folder . 'uji_besar.jpg';
file_put_contents($fileBesar, str_repeat('a', 3000000));
$hasil = UnggahBerkas::simpan(
    ['error' => UPLOAD_ERR_OK, 'size' => 3000000, 'name' => 'uji_besar.jpg', 'tmp_name' => $fileBesar],
    $folder,
    ['jpg', 'jpeg', 'png'],
    2097152
);
$cek(!$hasil['berhasil'], 'Berkas > batas ukuran ditolak');
@unlink($fileBesar);

// 5. Request tanpa berkas ditolak
$hasil = UnggahBerkas::simpan(
    ['error' => UPLOAD_ERR_NO_FILE, 'size' => 0, 'name' => '', 'tmp_name' => ''],
    $folder,
    ['png'],
    UKURAN_MAKS_UPLOAD
);
$cek(!$hasil['berhasil'], 'Tanpa berkas ditolak');

echo $jumlahGagal === 0 ? 'Semua pengujian lulus.' : $jumlahGagal . ' pengujian gagal.';

exit($jumlahGagal > 0 ? 1 : 0);
