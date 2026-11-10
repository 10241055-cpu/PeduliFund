<?php
declare(strict_types=1);

// Pengujian unit logika autentikasi (UserCredentialsDB dan Helper Sesi). Jalankan: php tests/uji_auth.php
require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../app/helpers/fungsi.php';
require_once __DIR__ . '/../app/models/UserCredentialsDB.php';

$jumlahGagal = 0;

$cek = static function (bool $kondisi, string $nama) use (&$jumlahGagal): void {
    echo ($kondisi ? '[OK]    ' : '[GAGAL] ') . $nama . PHP_EOL;
    if (!$kondisi) {
        $jumlahGagal++;
    }
};

$model = new UserCredentialsDB($pdo);

// 1. Cek email yang sudah ada harus mengembalikan false
$emailAda = 'fundraiser@pedulifund.test';
$cek(!$model->cekEmailTersedia($emailAda), 'Email yang sudah ada ditandai tidak tersedia');

// 2. Cek email acak yang belum ada harus mengembalikan true
$emailBaru = 'uji_auth_' . bin2hex(random_bytes(4)) . '@pedulifund.test';
$cek($model->cekEmailTersedia($emailBaru), 'Email baru ditandai tersedia');

// 3. Simpan akun baru (Sign Up) menghasilkan ID baru
$idBaru = $model->simpanAkunBaru($emailBaru, 'rahasia123', 'Donatur');
$cek($idBaru > 0, 'Pendaftaran akun baru berhasil menyimpan ke database');

// 4. Verifikasi kredensial akun baru dengan password yang benar
$akunValid = $model->verifikasiKredensial($emailBaru, 'rahasia123');
$cek($akunValid !== null && (int) $akunValid['id_user'] === $idBaru, 'Kredensial benar berhasil diverifikasi');
$cek($akunValid !== null && $akunValid['role'] === 'Donatur', 'Akun baru memiliki role default Donatur');
$cek($akunValid !== null && !isset($akunValid['password']), 'Hash password tidak diekspos di hasil verifikasi');

// 5. Verifikasi kredensial dengan password salah harus ditolak
$akunSalahPassword = $model->verifikasiKredensial($emailBaru, 'password_salah');
$cek($akunSalahPassword === null, 'Verifikasi dengan kata sandi salah ditolak');

// 6. Verifikasi kredensial dengan email tidak terdaftar harus ditolak
$akunTidakAda = $model->verifikasiKredensial('tidakada_' . bin2hex(random_bytes(4)) . '@pedulifund.test', 'sembarang');
$cek($akunTidakAda === null, 'Verifikasi email tidak terdaftar ditolak');

// 7. Ubah role akun
$cek($model->ubahRole($idBaru, 'Fundraiser'), 'Ubah role ke Fundraiser berhasil');
$akunDiperbarui = $model->ambilBerdasarkanId($idBaru);
$cek(($akunDiperbarui['role'] ?? '') === 'Fundraiser', 'Role berhasil terupdate di database');

// 8. Tolak role di luar whitelist
$cek(!$model->ubahRole($idBaru, 'SuperuserPalsu'), 'Role tidak valid ditolak');

// 9. Uji helper sesi autentikasi
unset($_SESSION['pengguna_aktif']);
$cek(!sudahLogin(), 'sudahLogin() mengembalikan false saat belum ada sesi');
$cek(idPenggunaAktif() === ID_FUNDRAISER_SEMENTARA, 'idPenggunaAktif() fallback ke default saat belum login');

$_SESSION['pengguna_aktif'] = [
    'id_user' => $idBaru,
    'email'   => $emailBaru,
    'role'    => 'Fundraiser',
];
$cek(sudahLogin(), 'sudahLogin() mengembalikan true setelah login');
$cek(idPenggunaAktif() === $idBaru, 'idPenggunaAktif() mengembalikan id_user dari sesi aktif');
$cek(peranPenggunaAktif() === 'Fundraiser', 'peranPenggunaAktif() mengembalikan role sesi');

// Bersihkan data uji dari database dan sesi
$pdo->prepare("DELETE FROM pengguna WHERE id_user = :id")->execute([':id' => $idBaru]);
unset($_SESSION['pengguna_aktif']);

echo $jumlahGagal === 0 ? 'Semua pengujian autentikasi lulus.' . PHP_EOL : $jumlahGagal . ' pengujian gagal.' . PHP_EOL;

exit($jumlahGagal > 0 ? 1 : 0);
