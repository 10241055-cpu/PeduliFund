<?php
// Controller CampaignController untuk mengelola alur bisnis UI-009 dan UI-010
// Sesuai kesepakatan nomor 7, 8, dan 10 SDD

class CampaignController
{
    private CampaignEntity $campaignEntity;
    private ?PencairanEntity $pencairanEntity;
    private ?UserCredentialsDB $userModel;
    private ?PencairanController $pencairanController;
    private ?VerifikasiFundraiserEntity $verifikasiEntity;

    public function __construct(
        CampaignEntity $campaignEntity,
        ?PencairanEntity $pencairanEntity = null,
        ?UserCredentialsDB $userModel = null,
        ?PencairanController $pencairanController = null,
        ?VerifikasiFundraiserEntity $verifikasiEntity = null
    ) {
        $this->campaignEntity = $campaignEntity;
        $this->pencairanEntity = $pencairanEntity;
        $this->userModel = $userModel;
        $this->pencairanController = $pencairanController;
        $this->verifikasiEntity = $verifikasiEntity;
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
     * Menampilkan form pembuatan/revisi campaign (UI-010); ?id= memuat campaign milik fundraiser
     */
    public function tampilkanFormCampaign(): void
    {
        $this->pastikanBolehMembuatCampaign();

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        // Mode revisi: campaign milik pemilik yang masih boleh diubah (Pending/Ditolak/Aktif)
        $idEdit = (int) ($_GET['id'] ?? 0);
        $campaignEdit = $idEdit > 0
            ? $this->campaignEntity->ambilByIdCampaign($idEdit, idPenggunaAktif())
            : null;
        if ($campaignEdit !== null && !in_array($campaignEdit['status_campaign'], ['Pending', 'Ditolak', 'Aktif'], true)) {
            $campaignEdit = null;
        }
        // Prefill dari data campaign bila pengguna datang tanpa sisa input lama
        if ($campaignEdit !== null && $old === []) {
            $old = [
                'judul'       => (string) $campaignEdit['judul'],
                'jenis'       => (string) $campaignEdit['jenis'],
                'deskripsi'   => (string) $campaignEdit['deskripsi'],
                'target_dana' => (string) $campaignEdit['target_dana'],
                'deadline'    => (string) $campaignEdit['deadline'],
            ];
        }

        $daftarJenis = CampaignEntity::DAFTAR_JENIS;

        require __DIR__ . '/../../views/form_campaign.php';
    }

    /**
     * Memproses pengiriman form campaign (POST): campaign baru berstatus Pending,
     * revisi kembali ke Pending untuk diverifikasi ulang (SDD 3.1.2)
     */
    public function kirimCampaign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_fundraiser.php');
        }

        verifikasiCsrf();
        $this->pastikanBolehMembuatCampaign();

        $idEdit = (int) ($_POST['id_campaign'] ?? 0);
        $campaignEdit = $idEdit > 0
            ? $this->campaignEntity->ambilByIdCampaign($idEdit, idPenggunaAktif())
            : null;
        if ($idEdit > 0 && $campaignEdit === null) {
            setPesan('danger', 'Campaign tidak ditemukan atau bukan milik Anda.');
            alihkan('dashboard_fundraiser.php');
        }

        $judul = trim((string) ($_POST['judul'] ?? ''));
        $jenis = trim((string) ($_POST['jenis'] ?? ''));
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
        $targetDana = trim((string) ($_POST['target_dana'] ?? ''));
        $deadline = trim((string) ($_POST['deadline'] ?? ''));

        $old = [
            'id_campaign' => (string) $idEdit,
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

        // Validasi 6 & 7: dokumen_campaign wajib untuk campaign baru; pada revisi boleh mempertahankan lama
        $berkasDokumen = $_FILES['dokumen_campaign'] ?? null;
        $berkasGambar = $_FILES['gambar_campaign'] ?? null;

        $tanpaDokumen = !isset($berkasDokumen) || (int) ($berkasDokumen['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE;
        if ($tanpaDokumen && ($idEdit === 0 || empty($campaignEdit['dokumen_campaign']))) {
            $errors[] = 'Dokumen campaign wajib diunggah.';
        }

        $adaGambar = isset($berkasGambar) && (int) ($berkasGambar['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
            alihkan($idEdit > 0 ? 'buat_campaign.php?id=' . $idEdit : 'buat_campaign.php');
        }

        // Unggah dokumen_campaign (wajib: pdf, jpg, jpeg, png; maks 2 MB)
        $namaDokumen = (string) ($campaignEdit['dokumen_campaign'] ?? '');
        if (!$tanpaDokumen) {
            $hasilDokumen = UnggahBerkas::simpan(
                $berkasDokumen,
                DIR_UPLOAD_DOKUMEN,
                ['pdf', 'jpg', 'jpeg', 'png'],
                UKURAN_MAKS_UPLOAD
            );

            if (!$hasilDokumen['berhasil']) {
                $_SESSION['errors'] = ['Dokumen campaign: ' . $hasilDokumen['pesan']];
                $_SESSION['old'] = $old;
                alihkan($idEdit > 0 ? 'buat_campaign.php?id=' . $idEdit : 'buat_campaign.php');
            }

            $namaDokumen = $hasilDokumen['nama_file'];
        }

        $namaGambar = $campaignEdit !== null && !empty($campaignEdit['gambar_campaign'])
            ? (string) $campaignEdit['gambar_campaign']
            : null;

        // Unggah gambar_campaign jika diisi (opsional: jpg, jpeg, png; maks 2 MB)
        if ($adaGambar) {
            $hasilGambar = UnggahBerkas::simpan(
                $berkasGambar,
                DIR_UPLOAD_GAMBAR,
                ['jpg', 'jpeg', 'png'],
                UKURAN_MAKS_UPLOAD
            );

            if (!$hasilGambar['berhasil']) {
                if (!$tanpaDokumen) {
                    UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $namaDokumen);
                }

                $_SESSION['errors'] = ['Gambar campaign: ' . $hasilGambar['pesan']];
                $_SESSION['old'] = $old;
                alihkan($idEdit > 0 ? 'buat_campaign.php?id=' . $idEdit : 'buat_campaign.php');
            }

            $namaGambar = $hasilGambar['nama_file'];
        }

        $dataTersimpan = [
            'judul'            => $judul,
            'jenis'            => $jenis,
            'deskripsi'        => $deskripsi,
            'target_dana'      => (float) $targetDana,
            'deadline'         => $deadline,
            'dokumen_campaign' => $namaDokumen,
            'gambar_campaign'  => $namaGambar,
        ];

        try {
            if ($idEdit > 0) {
                $berhasil = $this->campaignEntity->perbaruiCampaignRevisi($idEdit, idPenggunaAktif(), $dataTersimpan) !== null;
                $pesanSukses = 'Revisi campaign tersimpan dan kembali menunggu verifikasi admin.';
            } else {
                $dataTersimpan['id_fundraiser'] = idPenggunaAktif();
                $this->campaignEntity->simpanCampaign($dataTersimpan);
                $berhasil = true;
                $pesanSukses = 'Campaign berhasil dikirim dan menunggu verifikasi admin.';
            }

            if (!$berhasil) {
                throw new PDOException('Tidak ada baris yang berubah.');
            }

            // Berkas lama ikut dibuang bila digantikan agar folder upload tidak menumpuk sampah
            if ($campaignEdit !== null && $namaDokumen !== (string) $campaignEdit['dokumen_campaign']) {
                UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, (string) $campaignEdit['dokumen_campaign']);
            }
            if ($campaignEdit !== null
                && $namaGambar !== null
                && $namaGambar !== (string) ($campaignEdit['gambar_campaign'] ?? '')) {
                UnggahBerkas::hapus(DIR_UPLOAD_GAMBAR, (string) $campaignEdit['gambar_campaign']);
            }

            setPesan('success', $pesanSukses);
            alihkan('dashboard_fundraiser.php');
        } catch (PDOException $e) {
            if ($namaDokumen !== '' && !$tanpaDokumen) {
                UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $namaDokumen);
            }
            if ($adaGambar) {
                UnggahBerkas::hapus(DIR_UPLOAD_GAMBAR, $namaGambar);
            }

            $_SESSION['errors'] = ['Campaign gagal disimpan ke database. Silakan coba kembali.'];
            $_SESSION['old'] = $old;
            alihkan($idEdit > 0 ? 'buat_campaign.php?id=' . $idEdit : 'buat_campaign.php');
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
     * Halaman verifikasi campaign admin (UI-013) + keputusan Setujui/Tolak (POST)
     */
    public function verifikasiCampaign(): void
    {
        $this->prosesKeputusanCampaign();

        $daftarMenunggu = $this->campaignEntity->ambilCampaignMenungguVerifikasi();

        require __DIR__ . '/../../views/verifikasi_campaign.php';
    }

    /**
     * Memproses keputusan admin atas campaign Pending (SDD Bab 4.1.2: validasiCampaign)
     */
    private function prosesKeputusanCampaign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        verifikasiCsrf('verifikasi_campaign.php');

        $idCampaign = (int) ($_POST['id_campaign'] ?? 0);
        $keputusan = trim((string) ($_POST['keputusan'] ?? ''));

        if ($idCampaign <= 0 || !in_array($keputusan, ['setujui', 'tolak'], true)) {
            setPesan('danger', 'Keputusan verifikasi tidak valid.');
            alihkan('verifikasi_campaign.php');
        }

        if ($keputusan === 'setujui') {
            if ($this->campaignEntity->publikasikanKePublik($idCampaign)) {
                setPesan('success', 'Campaign disetujui, berstatus Aktif, dan tayang di daftar publik.');
            } else {
                setPesan('danger', 'Campaign tidak ditemukan atau sudah pernah diputuskan.');
            }
        } else {
            $alasan = trim((string) ($_POST['alasan_penolakan'] ?? ''));
            if ($alasan === '') {
                setPesan('danger', 'Alasan penolakan wajib diisi.');
                alihkan('verifikasi_campaign.php');
            }

            if ($this->campaignEntity->mintaRevisiFormulir($idCampaign, $alasan)) {
                setPesan('success', 'Campaign ditolak; fundraiser dapat merevisi formulirnya.');
            } else {
                setPesan('danger', 'Campaign tidak ditemukan atau sudah pernah diputuskan.');
            }
        }

        alihkan('verifikasi_campaign.php');
    }

    /**
     * Halaman verifikasi akun admin (UI-012) + keputusan Setujui/Tolak (POST)
     */
    public function verifikasiAkun(): void
    {
        $this->prosesKeputusanAkun();

        $daftarMenunggu = $this->verifikasiEntity !== null
            ? $this->verifikasiEntity->ambilMenunggu()
            : [];

        require __DIR__ . '/../../views/verifikasi_akun.php';
    }

    /**
     * Memproses keputusan admin atas pengajuan verifikasi akun (UI-012)
     */
    private function prosesKeputusanAkun(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'
            || ($_POST['jenis_verifikasi'] ?? '') !== 'akun'
            || $this->verifikasiEntity === null
            || $this->userModel === null) {
            return;
        }

        verifikasiCsrf('verifikasi_akun.php');

        $idVerifikasi = (int) ($_POST['id_verifikasi'] ?? 0);
        $keputusan = trim((string) ($_POST['keputusan'] ?? ''));

        $pengajuan = $idVerifikasi > 0 ? $this->verifikasiEntity->ambilById($idVerifikasi) : null;
        if ($pengajuan === null || !in_array($keputusan, ['setujui', 'tolak'], true)) {
            setPesan('danger', 'Pengajuan verifikasi tidak valid.');
            alihkan('verifikasi_akun.php');
        }

        if ($keputusan === 'setujui') {
            if ($this->verifikasiEntity->setujui($idVerifikasi)) {
                // Persetujuan dokumen menaikkan peran pemohon menjadi Fundraiser (SDD UI-012 poin 3)
                $this->userModel->ubahRole((int) $pengajuan['id_user'], 'Fundraiser');
                setPesan('success', 'Pengajuan disetujui; akun pemohon kini berperan sebagai Fundraiser.');
            } else {
                setPesan('danger', 'Pengajuan tidak ditemukan atau sudah pernah diputuskan.');
            }
        } else {
            $alasan = trim((string) ($_POST['alasan_penolakan'] ?? ''));
            if ($alasan === '') {
                setPesan('danger', 'Alasan penolakan wajib diisi.');
                alihkan('verifikasi_akun.php');
            }

            if ($this->verifikasiEntity->tolak($idVerifikasi, $alasan)) {
                setPesan('success', 'Pengajuan ditolak; pemohon dapat mengajukan ulang 14 hari lagi.');
            } else {
                setPesan('danger', 'Pengajuan tidak ditemukan atau sudah pernah diputuskan.');
            }
        }

        alihkan('verifikasi_akun.php');
    }

    /**
     * Pratinjau dokumen campaign khusus admin (pendukung UI-013)
     */
    public function unduhDokumenCampaign(): void
    {
        $this->layaniUnduhDokumen('dokumen_campaign');
    }

    /**
     * Pratinjau dokumen pengajuan verifikasi akun khusus admin (pendukung UI-012)
     */
    public function unduhDokumenVerifikasi(): void
    {
        $this->layaniUnduhDokumen('dokumen_verifikasi');
    }

    /**
     * Melayani berkas pratinjau untuk admin: kampanye (dokumen_campaign) atau verifikasi akun
     */
    private function layaniUnduhDokumen(string $jenisBerkas): void
    {
        if (peranPenggunaAktif() !== 'Admin') {
            http_response_code(403);
            exit('Hanya admin yang dapat mengakses berkas ini.');
        }

        if ($jenisBerkas === 'dokumen_campaign') {
            $id = (int) ($_GET['id_campaign'] ?? 0);
            $baris = $id > 0 ? $this->campaignEntity->ambilByIdUntukAdmin($id) : null;
            $kolomDokumen = 'dokumen_campaign';
            $kolomPendukung = 'gambar_campaign';
        } else {
            $id = (int) ($_GET['id_verifikasi'] ?? 0);
            $baris = $id > 0 && $this->verifikasiEntity !== null
                ? $this->verifikasiEntity->ambilById($id)
                : null;
            $kolomDokumen = 'dokumen_verifikasi';
            $kolomPendukung = 'dokumen_pendukung';
        }

        if ($baris === null) {
            http_response_code(404);
            exit('Data tidak ditemukan.');
        }

        $pilihan = trim((string) ($_GET['jenis'] ?? ''));
        $namaFile = $pilihan === 'pendukung'
            ? (string) ($baris[$kolomPendukung] ?? '')
            : (string) ($baris[$kolomDokumen] ?? '');

        if ($namaFile === '' || basename($namaFile) !== $namaFile) {
            http_response_code(404);
            exit('Berkas tidak tersedia.');
        }

        // Gambar campaign tinggal di folder gambar; dokumen campaign & berkas verifikasi di folder masing-masing
        $folder = $jenisBerkas === 'dokumen_campaign'
            ? ($pilihan === 'pendukung' ? DIR_UPLOAD_GAMBAR : DIR_UPLOAD_DOKUMEN)
            : DIR_UPLOAD_VERIFIKASI;
        $lokasiFile = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFile;
        if (!is_file($lokasiFile)) {
            http_response_code(404);
            exit('Berkas tidak ditemukan di penyimpanan.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($lokasiFile);
        $mimeAman = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($mime, $mimeAman, true)) {
            http_response_code(403);
            exit('Tipe berkas tidak diizinkan untuk ditampilkan.');
        }

        // Berkas berisi data pribadi: jangan biarkan peramban/proxy menyimpannya
        header('Cache-Control: private, no-store');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($lokasiFile));
        header('Content-Disposition: inline; filename="' . $namaFile . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($lokasiFile);
        exit;
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
