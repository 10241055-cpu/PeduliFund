<?php
// Controller AuthController untuk mengelola alur bisnis Login dan Sign Up (ACT-001 / SEQ-001)

class AuthController
{
    private UserCredentialsDB $modelPengguna;

    public function __construct(UserCredentialsDB $modelPengguna)
    {
        $this->modelPengguna = $modelPengguna;
    }

    /**
     * Menampilkan halaman formulir login (UI-003)
     */
    public function tampilkanLogin(): void
    {
        if (sudahLogin()) {
            $this->alihkanSesuaiPeran(peranPenggunaAktif());
        }

        $judulHalaman = 'Masuk ke PeduliFund';
        $old = [];
        $errors = [];

        require __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Memproses pengiriman formulir login
     */
    public function prosesLogin(): void
    {
        verifikasiCsrf('login.php');

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];
        $old = ['email' => $email];

        if ($email === '') {
            $errors[] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format alamat email tidak valid.';
        }

        if ($password === '') {
            $errors[] = 'Kata sandi wajib diisi.';
        }

        if ($errors !== []) {
            $judulHalaman = 'Masuk ke PeduliFund';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        $pengguna = $this->modelPengguna->verifikasiKredensial($email, $password);

        if ($pengguna === null) {
            $errors[] = 'Email atau kata sandi yang Anda masukkan tidak sesuai.';
            $judulHalaman = 'Masuk ke PeduliFund';
            require __DIR__ . '/../../views/auth/login.php';
            return;
        }

        // Cegah fixation session dengan regenerasi session ID
        session_regenerate_id(true);

        $_SESSION['pengguna_aktif'] = [
            'id_user' => (int) $pengguna['id_user'],
            'email'   => $pengguna['email'],
            'role'    => $pengguna['role'],
        ];

        setPesan('success', 'Selamat datang kembali, ' . $pengguna['email'] . '!');
        $this->alihkanSesuaiPeran($pengguna['role']);
    }

    /**
     * Menampilkan halaman formulir pendaftaran akun donatur (UI-002)
     */
    public function tampilkanSignUp(): void
    {
        if (sudahLogin()) {
            $this->alihkanSesuaiPeran(peranPenggunaAktif());
        }

        $judulHalaman = 'Daftar Akun PeduliFund';
        $old = [];
        $errors = [];

        require __DIR__ . '/../../views/auth/signup.php';
    }

    /**
     * Memproses pendaftaran akun baru dengan role default Donatur
     */
    public function prosesSignUp(): void
    {
        verifikasiCsrf('signup.php');

        $namaLengkap = trim((string) ($_POST['nama_lengkap'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $konfirmasiPassword = (string) ($_POST['konfirmasi_password'] ?? '');

        $errors = [];
        $old = [
            'nama_lengkap' => $namaLengkap,
            'email'        => $email,
        ];

        if ($email === '') {
            $errors[] = 'Alamat email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format alamat email tidak valid.';
        } elseif (!$this->modelPengguna->cekEmailTersedia($email)) {
            $errors[] = 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Kata sandi minimal harus terdiri dari 6 karakter.';
        }

        if ($password !== $konfirmasiPassword) {
            $errors[] = 'Konfirmasi kata sandi tidak cocok dengan kata sandi.';
        }

        if ($errors !== []) {
            $judulHalaman = 'Daftar Akun PeduliFund';
            require __DIR__ . '/../../views/auth/signup.php';
            return;
        }

        // Simpan akun baru ke tabel pengguna dengan role Donatur sesuai SDD 3.2.1
        $idUserBaru = $this->modelPengguna->simpanAkunBaru($email, $password, 'Donatur');

        // Otomatis login pengguna baru setelah registrasi berhasil
        session_regenerate_id(true);
        $_SESSION['pengguna_aktif'] = [
            'id_user'      => $idUserBaru,
            'email'        => $email,
            'role'         => 'Donatur',
            'nama_lengkap' => $namaLengkap,
        ];

        setPesan('success', 'Pendaftaran berhasil! Selamat datang di PeduliFund.');
        alihkan('dashboard_donatur.php');
    }

    /**
     * Memproses keluar dari sistem (logout)
     */
    public function prosesLogout(): void
    {
        unset($_SESSION['pengguna_aktif']);
        session_regenerate_id(true);

        setPesan('success', 'Anda telah berhasil keluar dari akun.');
        alihkan('login.php');
    }

    /**
     * Mengarahkan pengguna ke dashboard sesuai perannya
     */
    private function alihkanSesuaiPeran(?string $role): void
    {
        if ($role === 'Fundraiser') {
            alihkan('dashboard_fundraiser.php');
        } elseif ($role === 'Admin') {
            alihkan('dashboard_admin.php');
        } else {
            alihkan('dashboard_donatur.php');
        }
    }
}
