<?php
// Controller CampaignController untuk mengelola alur bisnis UI-009 dan UI-010
// Sesuai kesepakatan nomor 7, 8, dan 10 SDD

class CampaignController
{
    private CampaignEntity $campaignEntity;
    private ?PencairanEntity $pencairanEntity;
    private ?UserCredentialsDB $userModel;
    private ?PencairanController $pencairanController;

    public function __construct(
        CampaignEntity $campaignEntity,
        ?PencairanEntity $pencairanEntity = null,
        ?UserCredentialsDB $userModel = null,
        ?PencairanController $pencairanController = null
    ) {
        $this->campaignEntity = $campaignEntity;
        $this->pencairanEntity = $pencairanEntity;
        $this->userModel = $userModel;
        $this->pencairanController = $pencairanController;
    }

    /**
     * Menampilkan dashboard fundraiser (UI-009)
     */
    public function tampilkanDashboard(): void
    {
        $idFundraiser = idPenggunaAktif();
        $hariIni = date('Y-m-d');

        $this->campaignEntity->tutupCampaignKedaluwarsa($idFundraiser, $hariIni);
        if ($this->pencairanController !== null) {
            $this->pencairanController->periksaDanTerapkanSanksiLpj($hariIni);
        }

        $cari = trim((string) ($_GET['cari'] ?? ''));
        $filterStatus = trim((string) ($_GET['status'] ?? ''));

        // Hanya diterima jika ada di CampaignEntity::DAFTAR_STATUS, jika tidak jadikan kosong
        if (!in_array($filterStatus, CampaignEntity::DAFTAR_STATUS, true)) {
            $filterStatus = '';
        }

        $ringkasan = $this->campaignEntity->ambilRingkasanFundraiser($idFundraiser);
        $daftarCampaign = $this->campaignEntity->ambilDaftarByFundraiser($idFundraiser, $cari, $filterStatus);
        $akunFundraiser = $this->userModel !== null ? $this->userModel->ambilBerdasarkanId($idFundraiser) : null;

        $daftarIdCampaign = array_map(static fn (array $c): int => (int) $c['id_campaign'], $daftarCampaign);
        $pencairanPerCampaign = $this->pencairanEntity !== null
            ? $this->pencairanEntity->ambilTerbaruByDaftarCampaign($daftarIdCampaign)
            : [];
        $tagihanLpj = $this->pencairanEntity !== null
            ? $this->pencairanEntity->ambilTagihanLpjFundraiser($idFundraiser)
            : [];

        require __DIR__ . '/../../views/dashboard_fundraiser.php';
    }

    /**
     * Menampilkan form pembuatan campaign baru (UI-010)
     */
    public function tampilkanFormCampaign(): void
    {
        $this->pastikanBolehMembuatCampaign();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        $daftarJenis = CampaignEntity::DAFTAR_JENIS;

        require __DIR__ . '/../../views/form_campaign.php';
    }

    /**
     * Memproses pengiriman form campaign (POST) dan menyimpannya berstatus Pending
     */
    public function kirimCampaign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_fundraiser.php');
        }

        verifikasiCsrf();
        $this->pastikanBolehMembuatCampaign();

        $judul = trim((string) ($_POST['judul'] ?? ''));
        $jenis = trim((string) ($_POST['jenis'] ?? ''));
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
        $targetDana = trim((string) ($_POST['target_dana'] ?? ''));
        $deadline = trim((string) ($_POST['deadline'] ?? ''));

        $old = [
            'judul'       => $judul,
            'jenis'       => $jenis,
            'deskripsi'   => $deskripsi,
            'target_dana' => $targetDana,
            'deadline'    => $deadline,
        ];

        $errors = [];

        // Validasi 1: Judul wajib, maksimal 255 karakter
        if ($judul === '') {
            $errors[] = 'Judul campaign wajib diisi.';
        } elseif (mb_strlen($judul) > 255) {
            $errors[] = 'Judul campaign maksimal 255 karakter.';
        }

        // Validasi 2: Jenis harus anggota DAFTAR_JENIS
        if ($jenis === '' || !in_array($jenis, CampaignEntity::DAFTAR_JENIS, true)) {
            $errors[] = 'Jenis campaign tidak valid.';
        }

        // Validasi 3: Deskripsi wajib
        if ($deskripsi === '') {
            $errors[] = 'Deskripsi campaign wajib diisi.';
        }

        // Validasi 4: Target dana angka lebih dari 0
        if ($targetDana === '' || !is_numeric($targetDana) || (float) $targetDana <= 0) {
            $errors[] = 'Target dana harus berupa angka lebih dari 0.';
        }

        // Validasi 5: Deadline format Y-m-d dan tidak boleh sebelum hari ini
        $tglDeadline = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $deadline) === 1
            ? DateTime::createFromFormat('!Y-m-d', $deadline)
            : false;
        $hariIni = new DateTime('today');

        if ($tglDeadline === false || $tglDeadline->format('Y-m-d') !== $deadline) {
            $errors[] = 'Deadline harus menggunakan format tanggal yang valid (YYYY-MM-DD).';
        } elseif ($tglDeadline < $hariIni) {
            $errors[] = 'Deadline tidak boleh sebelum hari ini.';
        }

        // Validasi 6 & 7: Keberadaan dokumen_campaign (wajib) dan gambar_campaign (opsional)
        $berkasDokumen = $_FILES['dokumen_campaign'] ?? null;
        $berkasGambar = $_FILES['gambar_campaign'] ?? null;

        if (!isset($berkasDokumen) || (int) ($berkasDokumen['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Dokumen campaign wajib diunggah.';
        }

        $adaGambar = isset($berkasGambar) && (int) ($berkasGambar['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }

        // Unggah dokumen_campaign (wajib: pdf, jpg, jpeg, png; maks 2 MB)
        $hasilDokumen = UnggahBerkas::simpan(
            $berkasDokumen,
            DIR_UPLOAD_DOKUMEN,
            ['pdf', 'jpg', 'jpeg', 'png'],
            UKURAN_MAKS_UPLOAD
        );

        if (!$hasilDokumen['berhasil']) {
            $_SESSION['errors'] = ['Dokumen campaign: ' . $hasilDokumen['pesan']];
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }

        $namaDokumen = $hasilDokumen['nama_file'];
        $namaGambar = null;

        // Unggah gambar_campaign jika diisi (opsional: jpg, jpeg, png; maks 2 MB)
        if ($adaGambar) {
            $hasilGambar = UnggahBerkas::simpan(
                $berkasGambar,
                DIR_UPLOAD_GAMBAR,
                ['jpg', 'jpeg', 'png'],
                UKURAN_MAKS_UPLOAD
            );

            if (!$hasilGambar['berhasil']) {
                UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $namaDokumen);

                $_SESSION['errors'] = ['Gambar campaign: ' . $hasilGambar['pesan']];
                $_SESSION['old'] = $old;
                alihkan('buat_campaign.php');
            }

            $namaGambar = $hasilGambar['nama_file'];
        }

        try {
            $this->campaignEntity->simpanCampaign([
                'id_fundraiser'    => idPenggunaAktif(),
                'judul'            => $judul,
                'jenis'            => $jenis,
                'deskripsi'        => $deskripsi,
                'target_dana'      => (float) $targetDana,
                'deadline'         => $deadline,
                'dokumen_campaign' => $namaDokumen,
                'gambar_campaign'  => $namaGambar,
            ]);

            setPesan('success', 'Campaign berhasil dikirim dan menunggu verifikasi admin.');
            alihkan('dashboard_fundraiser.php');
        } catch (PDOException $e) {
            UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $namaDokumen);
            if ($namaGambar !== null) {
                UnggahBerkas::hapus(DIR_UPLOAD_GAMBAR, $namaGambar);
            }

            $_SESSION['errors'] = ['Campaign gagal disimpan ke database. Silakan coba kembali.'];
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }
    }

    /**
     * Menghapus campaign milik fundraiser aktif beserta berkas terunggahnya (POST)
     */
    public function hapusCampaign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_fundraiser.php');
        }

        verifikasiCsrf();

        $idCampaign = (int) ($_POST['id_campaign'] ?? 0);
        $campaign = $idCampaign > 0
            ? $this->campaignEntity->ambilByIdCampaign($idCampaign, idPenggunaAktif())
            : null;

        if ($campaign === null) {
            setPesan('danger', 'Campaign tidak ditemukan.');
            alihkan('dashboard_fundraiser.php');
        }

        if ($this->pencairanEntity !== null && $this->pencairanEntity->sudahDicairkan($idCampaign)) {
            setPesan('danger', 'Campaign yang dananya sudah dicairkan tidak dapat dihapus demi jejak audit LPJ.');
            alihkan('dashboard_fundraiser.php');
        }

        try {
            $this->campaignEntity->hapusCampaign($idCampaign, idPenggunaAktif());

            // Berkas ikut dihapus agar folder upload tidak menumpuk sampah
            UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, (string) $campaign['dokumen_campaign']);
            if (!empty($campaign['gambar_campaign'])) {
                UnggahBerkas::hapus(DIR_UPLOAD_GAMBAR, (string) $campaign['gambar_campaign']);
            }

            setPesan('success', 'Campaign berhasil dihapus.');
        } catch (PDOException $e) {
            setPesan('danger', 'Campaign gagal dihapus. Silakan coba kembali.');
        }

        alihkan('dashboard_fundraiser.php');
    }

    /**
     * Menolak pembuatan campaign baru bila akun dibekukan atau ada LPJ pencairan belum terverifikasi (SDD 3.1.2 & 3.1.4)
     */
    private function pastikanBolehMembuatCampaign(): void
    {
        if ($this->pencairanController !== null) {
            $this->pencairanController->periksaDanTerapkanSanksiLpj();
        }

        $idFundraiser = idPenggunaAktif();
        if ($this->userModel !== null) {
            $akun = $this->userModel->ambilBerdasarkanId($idFundraiser);
            if ($akun !== null && ($akun['status_akun'] ?? 'Aktif') === 'Dibekukan') {
                $infoTanggal = !empty($akun['suspend_sampai'])
                    ? ' hingga ' . formatTanggal($akun['suspend_sampai'])
                    : '';
                setPesan('danger', 'Akun Anda sedang dibekukan' . $infoTanggal . ' karena keterlambatan LPJ dan belum dapat membuat campaign baru.');
                alihkan('dashboard_fundraiser.php');
            }
        }

        if ($this->pencairanEntity !== null && $this->pencairanEntity->hitungLpjBelumTerverifikasiByFundraiser($idFundraiser) > 0) {
            setPesan('danger', 'Selesaikan unggah dan verifikasi LPJ pencairan sebelumnya terlebih dahulu sebelum membuat campaign baru.');
            alihkan('dashboard_fundraiser.php');
        }
    }
}
