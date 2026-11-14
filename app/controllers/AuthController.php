<?php
// Controller AuthController untuk mengelola alur bisnis Login dan Sign Up (ACT-001 / SEQ-001)

class AuthController
{
    private UserCredentialsDB $modelPengguna;
    private ?VerifikasiFundraiserEntity $verifikasiEntity;

    public function __construct(UserCredentialsDB $modelPengguna, ?VerifikasiFundraiserEntity $verifikasiEntity = null)
    {
        $this->modelPengguna = $modelPengguna;
        $this->verifikasiEntity = $verifikasiEntity;
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

    /**
     * Menampilkan form verifikasi akun fundraiser (UI-008) beserta status pengajuan terakhir
     */
    public function tampilkanPengajuanVerifikasi(): void
    {
        wajibLogin();

        $judulHalaman = 'Verifikasi Akun Fundraiser';
        $pengajuan = $this->verifikasiEntity !== null
            ? $this->verifikasiEntity->ambilTerbaruByUser(idPenggunaAktif())
            : null;
        // Aturan wireframe UI-008: setelah ditolak, pengajuan ulang baru boleh 14 hari kemudian
        $bolehAjukan = $pengajuan === null
            || $pengajuan['status_dokumen'] !== 'Ditolak'
            || bolehAjukanUlang($pengajuan['tanggal_keputusan'], date('Y-m-d'));
        $old = $_SESSION['old'] ?? [];
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['old'], $_SESSION['errors']);
        $daftarJenis = VerifikasiFundraiserEntity::DAFTAR_JENIS;

        require __DIR__ . '/../../views/auth/ajukan_verifikasi.php';
    }

    /**
     * Memproses pengiriman pengajuan verifikasi akun fundraiser (UI-008): data diri + dokumen
     */
    public function kirimPengajuanVerifikasi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_donatur.php');
        }

        wajibLogin();
        verifikasiCsrf('ajukan_verifikasi.php');

        $idUser = idPenggunaAktif();
        $pengajuanLama = $this->verifikasiEntity !== null
            ? $this->verifikasiEntity->ambilTerbaruByUser($idUser)
            : null;

        if ($pengajuanLama !== null && $pengajuanLama['status_dokumen'] === 'Menunggu') {
            setPesan('info', 'Pengajuan Anda masih menunggu tinjauan admin.');
            alihkan('ajukan_verifikasi.php');
        }

        if ($pengajuanLama !== null
            && $pengajuanLama['status_dokumen'] === 'Ditolak'
            && !bolehAjukanUlang($pengajuanLama['tanggal_keputusan'], date('Y-m-d'))) {
            setPesan('info', 'Pengajuan ulang baru dapat dilakukan 14 hari setelah penolakan.');
            alihkan('ajukan_verifikasi.php');
        }

        $jenisFundraiser = trim((string) ($_POST['jenis_fundraiser'] ?? ''));
        $namaLengkap = trim((string) ($_POST['nama_lengkap'] ?? ''));
        $nomorIdentitas = trim((string) ($_POST['nomor_identitas'] ?? ''));
        $nomorTelepon = trim((string) ($_POST['nomor_telepon'] ?? ''));

        $errors = [];
        $old = [
            'jenis_fundraiser' => $jenisFundraiser,
            'nama_lengkap'     => $namaLengkap,
            'nomor_identitas'  => $nomorIdentitas,
            'nomor_telepon'    => $nomorTelepon,
        ];

        if (!in_array($jenisFundraiser, VerifikasiFundraiserEntity::DAFTAR_JENIS, true)) {
            $errors[] = 'Jenis fundraiser wajib dipilih (Individu atau Organisasi).';
        }
        if ($namaLengkap === '' || mb_strlen($namaLengkap) > 150) {
            $errors[] = 'Nama lengkap wajib diisi, maksimal 150 karakter.';
        }
        if ($nomorIdentitas === '' || mb_strlen($nomorIdentitas) > 50) {
            $errors[] = 'NIK atau NPWP wajib diisi, maksimal 50 karakter.';
        }
        if ($nomorTelepon === '' || mb_strlen($nomorTelepon) > 20) {
            $errors[] = 'Nomor telepon wajib diisi, maksimal 20 karakter.';
        }

        // Validasi unggahan di batas kepercayaan: wajib + opsional (kertas bermaterai / SK & NPWP)
        $berkasDokumen = $_FILES['dokumen_verifikasi'] ?? null;
        $berkasPendukung = $_FILES['dokumen_pendukung'] ?? null;
        $tanpaDokumen = !isset($berkasDokumen) || (int) ($berkasDokumen['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;
        $adaPendukung = isset($berkasPendukung) && (int) ($berkasPendukung['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($tanpaDokumen) {
            $errors[] = 'Dokumen utama (KTP/akta pendirian) wajib diunggah.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
            alihkan('ajukan_verifikasi.php');
        }

        $hasilDokumen = UnggahBerkas::simpan($berkasDokumen, DIR_UPLOAD_VERIFIKASI, ['pdf', 'jpg', 'jpeg', 'png'], UKURAN_MAKS_UPLOAD);
        if (!$hasilDokumen['berhasil']) {
            $_SESSION['errors'] = ['Dokumen utama: ' . $hasilDokumen['pesan']];
            $_SESSION['old'] = $old;
            alihkan('ajukan_verifikasi.php');
        }

        $namaPendukung = null;
        if ($adaPendukung) {
            $hasilPendukung = UnggahBerkas::simpan($berkasPendukung, DIR_UPLOAD_VERIFIKASI, ['pdf', 'jpg', 'jpeg', 'png'], UKURAN_MAKS_UPLOAD);
            if (!$hasilPendukung['berhasil']) {
                UnggahBerkas::hapus(DIR_UPLOAD_VERIFIKASI, $hasilDokumen['nama_file']);
                $_SESSION['errors'] = ['Dokumen pendukung: ' . $hasilPendukung['pesan']];
                $_SESSION['old'] = $old;
                alihkan('ajukan_verifikasi.php');
            }
            $namaPendukung = $hasilPendukung['nama_file'];
        }

        try {
            $this->verifikasiEntity->buatPengajuan([
                'id_user'            => $idUser,
                'jenis_fundraiser'   => $jenisFundraiser,
                'nama_lengkap'       => $namaLengkap,
                'nomor_identitas'    => $nomorIdentitas,
                'nomor_telepon'      => $nomorTelepon,
                'dokumen_verifikasi' => $hasilDokumen['nama_file'],
                'dokumen_pendukung'  => $namaPendukung,
            ]);

            setPesan('success', 'Pengajuan verifikasi terkirim dan menunggu tinjauan admin.');
            alihkan('dashboard_donatur.php');
        } catch (PDOException $e) {
            UnggahBerkas::hapus(DIR_UPLOAD_VERIFIKASI, $hasilDokumen['nama_file']);
            if ($namaPendukung !== null) {
                UnggahBerkas::hapus(DIR_UPLOAD_VERIFIKASI, $namaPendukung);
            }

            $_SESSION['errors'] = ['Pengajuan gagal disimpan ke database. Silakan coba kembali.'];
            $_SESSION['old'] = $old;
            alihkan('ajukan_verifikasi.php');
        }
    }

    /**
     * Menampilkan halaman verifikasi wajah (UI-007), gerbang fundraiser sebelum masuk dashboard
     */
    public function tampilkanVerifikasiWajah(): void
    {
        wajibLogin('Fundraiser');

        $judulHalaman = 'Verifikasi Wajah';
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        $sudahTerverifikasi = !$this->modelPengguna->butuhVerifikasiWajah(idPenggunaAktif());

        require __DIR__ . '/../../views/auth/verifikasi_wajah.php';
    }

    /**
     * Memproses verifikasi wajah (UI-007): unggah selfie lalu pencocokan simulasi
     */
    public function prosesVerifikasiWajah(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('verifikasi_wajah.php');
        }

        wajibLogin('Fundraiser');
        verifikasiCsrf('verifikasi_wajah.php');

        $idUser = idPenggunaAktif();
        $skenarioSimulasi = trim((string) ($_POST['simulasi_hasil'] ?? 'Berhasil'));

        $berkasWajah = $_FILES['foto_wajah'] ?? null;
        if (!isset($berkasWajah) || (int) ($berkasWajah['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $_SESSION['errors'] = ['Foto wajah wajib diambil terlebih dahulu.'];
            alihkan('verifikasi_wajah.php');
        }

        $hasilWajah = UnggahBerkas::simpan($berkasWajah, DIR_UPLOAD_VERIFIKASI, ['jpg', 'jpeg', 'png'], UKURAN_MAKS_UPLOAD);
        if (!$hasilWajah['berhasil']) {
            $_SESSION['errors'] = ['Foto wajah: ' . $hasilWajah['pesan']];
            alihkan('verifikasi_wajah.php');
        }

        // Pencocokan wajah disimulasikan seperti payment gateway (SDD 1.3): sistem menilai selfie yang diunggah
        if ($skenarioSimulasi === 'Berhasil') {
            $this->modelPengguna->simpanFotoWajah($idUser, $hasilWajah['nama_file']);
            setPesan('success', 'Verifikasi wajah berhasil. Selamat datang, penggalang dana!');
            alihkan('dashboard_fundraiser.php');
        }

        // Gagal: foto tidak disimpan, verifikasi diulang di halaman ini (wireframe UI-007 poin 4)
        UnggahBerkas::hapus(DIR_UPLOAD_VERIFIKASI, $hasilWajah['nama_file']);
        $_SESSION['errors'] = ['Verifikasi wajah gagal, wajah tidak cocok. Silakan ambil foto kembali.'];
        alihkan('verifikasi_wajah.php');
    }
}
