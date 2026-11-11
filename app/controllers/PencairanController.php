<?php
// Controller PencairanController untuk mengelola alur bisnis Pencairan Dana & Audit LPJ (ACT-004 / SEQ-004)

class PencairanController
{
    private PencairanEntity $pencairanEntity;
    private CampaignEntity $campaignEntity;
    private UserCredentialsDB $userModel;
    private ?PDO $pdo;

    public function __construct(
        PencairanEntity $pencairanEntity,
        CampaignEntity $campaignEntity,
        UserCredentialsDB $userModel,
        ?PDO $pdo = null
    ) {
        $this->pencairanEntity = $pencairanEntity;
        $this->campaignEntity = $campaignEntity;
        $this->userModel = $userModel;
        $this->pdo = $pdo;
    }

    /**
     * Menampilkan formulir pengajuan pencairan dana untuk campaign selesai (UI-015)
     */
    public function tampilkanFormPengajuan(): void
    {
        $this->periksaDanTerapkanSanksiLpj();

        $idCampaign = (int) ($_GET['id_campaign'] ?? 0);
        $campaign = $idCampaign > 0
            ? $this->campaignEntity->ambilByIdCampaign($idCampaign, idPenggunaAktif())
            : null;

        $pesanError = $this->cekKelayakanPengajuan($campaign, $this->hariIni());
        if ($pesanError !== null) {
            setPesan('danger', $pesanError);
            alihkan('dashboard_fundraiser.php');
        }

        $penolakanTerakhir = $this->pencairanEntity->ambilPenolakanTerakhir($idCampaign);
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        require __DIR__ . '/../../views/pencairan/form_pengajuan.php';
    }

    /**
     * Memproses pengajuan pencairan dana dari fundraiser (POST UI-015)
     */
    public function ajukanPencairan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_fundraiser.php');
        }

        verifikasiCsrf('dashboard_fundraiser.php');
        $this->periksaDanTerapkanSanksiLpj();

        $idCampaign = (int) ($_POST['id_campaign'] ?? 0);
        $campaign = $idCampaign > 0
            ? $this->campaignEntity->ambilByIdCampaign($idCampaign, idPenggunaAktif())
            : null;

        $pesanError = $this->cekKelayakanPengajuan($campaign, $this->hariIni());
        if ($pesanError !== null) {
            setPesan('danger', $pesanError);
            alihkan('dashboard_fundraiser.php');
        }

        $namaBank = trim((string) ($_POST['nama_bank'] ?? ''));
        $nomorRekening = preg_replace('/[\s-]+/', '', trim((string) ($_POST['nomor_rekening'] ?? '')));
        $atasNama = trim((string) ($_POST['atas_nama'] ?? ''));

        $old = [
            'nama_bank'      => $namaBank,
            'nomor_rekening' => $nomorRekening,
            'atas_nama'      => $atasNama,
        ];

        $errors = [];

        if ($namaBank === '') {
            $errors[] = 'Nama bank tujuan wajib diisi.';
        } elseif (mb_strlen($namaBank) > 100) {
            $errors[] = 'Nama bank maksimal 100 karakter.';
        }

        if ($nomorRekening === '' || preg_match('/^[0-9]{6,30}$/', $nomorRekening) !== 1) {
            $errors[] = 'Nomor rekening wajib diisi dengan 6 hingga 30 digit angka.';
        }

        if ($atasNama === '') {
            $errors[] = 'Nama pemilik rekening (atas nama) wajib diisi.';
        } elseif (mb_strlen($atasNama) > 150) {
            $errors[] = 'Nama pemilik rekening maksimal 150 karakter.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
            alihkan('pengajuan_pencairan.php?id_campaign=' . $idCampaign);
        }

        try {
            // Nominal pencairan selalu diambil dari database (seluruh dana terkumpul)
            $this->pencairanEntity->simpanPengajuan([
                'id_campaign'       => $idCampaign,
                'nominal_pencairan' => (float) $campaign['dana_terkumpul'],
                'nama_bank'         => $namaBank,
                'nomor_rekening'    => $nomorRekening,
                'atas_nama'         => $atasNama,
            ]);

            setPesan('success', 'Pengajuan pencairan dana berhasil dikirim dan menunggu peninjauan admin.');
            alihkan('dashboard_fundraiser.php');
        } catch (PDOException $e) {
            $_SESSION['errors'] = ['Pengajuan pencairan gagal disimpan. Silakan coba kembali.'];
            $_SESSION['old'] = $old;
            alihkan('pengajuan_pencairan.php?id_campaign=' . $idCampaign);
        }
    }

    /**
     * Menampilkan formulir unggah LPJ dan bukti nota (UI-016)
     */
    public function tampilkanFormUnggahLpj(): void
    {
        $this->periksaDanTerapkanSanksiLpj();

        $idPencairan = (int) ($_GET['id_pencairan'] ?? 0);
        $pencairan = $this->ambilPencairanMilikFundraiser($idPencairan);

        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        $hariIni = $this->hariIni();
        $melewatiBatas = !empty($pencairan['batas_unggah_lpj']) && $pencairan['batas_unggah_lpj'] < $hariIni;
        $bisaUnggah = $pencairan['status_transparansi'] !== 'Terverifikasi'
            && (empty($pencairan['dokumen_lpj']) || $pencairan['status_transparansi'] === 'Ditolak');

        require __DIR__ . '/../../views/pencairan/unggah_lpj.php';
    }

    /**
     * Memproses unggahan berkas LPJ dan bukti nota dari fundraiser (POST UI-016)
     */
    public function unggahLPJDonasi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_fundraiser.php');
        }

        verifikasiCsrf('dashboard_fundraiser.php');
        $this->periksaDanTerapkanSanksiLpj();

        $idPencairan = (int) ($_POST['id_pencairan'] ?? 0);
        $pencairan = $this->ambilPencairanMilikFundraiser($idPencairan);

        if ($pencairan['status_transparansi'] === 'Terverifikasi') {
            setPesan('danger', 'LPJ untuk pencairan ini sudah diverifikasi oleh admin.');
            alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
        }

        if (!empty($pencairan['dokumen_lpj']) && $pencairan['status_transparansi'] === 'Menunggu') {
            setPesan('danger', 'LPJ yang Anda unggah masih menunggu proses audit admin.');
            alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
        }

        $berkasLpj = $_FILES['dokumen_lpj'] ?? null;
        $berkasNota = $_FILES['bukti_nota'] ?? null;
        $errors = [];

        if (!isset($berkasLpj) || (int) ($berkasLpj['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Dokumen LPJ wajib diunggah.';
        }

        if (!isset($berkasNota) || (int) ($berkasNota['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Bukti nota wajib diunggah.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
        }

        $ekstensiDiizinkan = ['pdf', 'jpg', 'jpeg', 'png'];
        $hasilLpj = UnggahBerkas::simpan($berkasLpj, DIR_UPLOAD_LPJ, $ekstensiDiizinkan, UKURAN_MAKS_UPLOAD);
        if (!$hasilLpj['berhasil']) {
            $_SESSION['errors'] = ['Dokumen LPJ: ' . $hasilLpj['pesan']];
            alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
        }

        $hasilNota = UnggahBerkas::simpan($berkasNota, DIR_UPLOAD_LPJ, $ekstensiDiizinkan, UKURAN_MAKS_UPLOAD);
        if (!$hasilNota['berhasil']) {
            UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $hasilLpj['nama_file']);
            $_SESSION['errors'] = ['Bukti nota: ' . $hasilNota['pesan']];
            alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
        }

        $namaLpjBaru = $hasilLpj['nama_file'];
        $namaNotaBaru = $hasilNota['nama_file'];
        $lpjLama = (string) ($pencairan['dokumen_lpj'] ?? '');
        $notaLama = (string) ($pencairan['bukti_nota'] ?? '');

        try {
            $tersimpan = $this->pencairanEntity->simpanLpj(
                $idPencairan,
                $namaLpjBaru,
                $namaNotaBaru,
                date('Y-m-d H:i:s')
            );

            if (!$tersimpan) {
                UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $namaLpjBaru);
                UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $namaNotaBaru);
                setPesan('danger', 'LPJ tidak dapat disimpan untuk status pencairan saat ini.');
                alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
            }

            // Hapus berkas lama hanya setelah pembaruan database berhasil
            if ($lpjLama !== '') {
                UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $lpjLama);
            }
            if ($notaLama !== '') {
                UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $notaLama);
            }

            setPesan('success', 'LPJ dan bukti nota berhasil diunggah dan menunggu verifikasi admin.');
            alihkan('dashboard_fundraiser.php');
        } catch (PDOException $e) {
            UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $namaLpjBaru);
            UnggahBerkas::hapus(DIR_UPLOAD_LPJ, $namaNotaBaru);
            $_SESSION['errors'] = ['LPJ gagal disimpan ke database. Silakan coba kembali.'];
            alihkan('unggah_lpj.php?id_pencairan=' . $idPencairan);
        }
    }

    /**
     * Menampilkan halaman review pencairan dan audit LPJ untuk admin (UI-017)
     */
    public function tampilkanReviewAdmin(): void
    {
        $this->periksaDanTerapkanSanksiLpj();

        $daftarFilter = ['', 'menunggu_pencairan', 'menunggu_lpj', 'belum_unggah_lpj', 'selesai'];
        $filter = trim((string) ($_GET['filter'] ?? ''));
        if (!in_array($filter, $daftarFilter, true)) {
            $filter = '';
        }

        $ringkasan = $this->pencairanEntity->ambilRingkasanAdmin();
        $daftarPencairan = $this->pencairanEntity->ambilDaftarAdmin($filter);
        $hariIni = $this->hariIni();

        require __DIR__ . '/../../views/pencairan/review_admin.php';
    }

    /**
     * Memproses keputusan admin atas permohonan pencairan dana (POST UI-017)
     */
    public function prosesKeputusanPencairan(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('review_pencairan.php');
        }

        verifikasiCsrf('review_pencairan.php');
        $this->periksaDanTerapkanSanksiLpj();

        $idPencairan = (int) ($_POST['id_pencairan'] ?? 0);
        $keputusan = trim((string) ($_POST['keputusan'] ?? ''));
        $alasanPenolakan = trim((string) ($_POST['alasan_penolakan'] ?? ''));

        $pencairan = $idPencairan > 0 ? $this->pencairanEntity->ambilById($idPencairan) : null;
        if ($pencairan === null || $pencairan['status_pengajuan'] !== 'Menunggu') {
            setPesan('danger', 'Pengajuan pencairan tidak ditemukan atau sudah diproses.');
            alihkan('review_pencairan.php');
        }

        if (!in_array($keputusan, ['setujui', 'tolak'], true)) {
            setPesan('danger', 'Keputusan pencairan tidak valid.');
            alihkan('review_pencairan.php');
        }

        if ($keputusan === 'tolak' && $alasanPenolakan === '') {
            setPesan('danger', 'Alasan penolakan wajib diisi apabila permohonan pencairan ditolak.');
            alihkan('review_pencairan.php');
        }

        try {
            if ($keputusan === 'setujui') {
                $waktuSekarang = new DateTimeImmutable();
                $tanggalPencairan = $waktuSekarang->format('Y-m-d H:i:s');
                $batasUnggahLpj = $waktuSekarang->modify('+1 month')->format('Y-m-d');

                $this->pencairanEntity->prosesKeputusanPencairan(
                    $idPencairan,
                    true,
                    null,
                    $tanggalPencairan,
                    $batasUnggahLpj
                );

                setPesan(
                    'success',
                    'Pengajuan pencairan disetujui dan dana sebesar '
                    . formatRupiah($pencairan['nominal_pencairan'])
                    . ' telah dicairkan. Batas unggah LPJ: ' . formatTanggal($batasUnggahLpj) . '.'
                );
            } else {
                $this->pencairanEntity->prosesKeputusanPencairan(
                    $idPencairan,
                    false,
                    $alasanPenolakan,
                    null,
                    null
                );

                setPesan('success', 'Pengajuan pencairan ditolak dan alasan telah dicatat untuk fundraiser.');
            }
        } catch (PDOException $e) {
            setPesan('danger', 'Keputusan pencairan gagal disimpan. Silakan coba kembali.');
        }

        alihkan('review_pencairan.php');
    }

    /**
     * Memproses keputusan admin atas audit LPJ dan bukti nota (POST UI-017)
     */
    public function kirimKeputusanLPJ(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('review_pencairan.php');
        }

        verifikasiCsrf('review_pencairan.php');

        $idPencairan = (int) ($_POST['id_pencairan'] ?? 0);
        $keputusan = trim((string) ($_POST['keputusan_lpj'] ?? ''));
        $alasanPenolakan = trim((string) ($_POST['alasan_penolakan'] ?? ''));

        $pencairan = $idPencairan > 0 ? $this->pencairanEntity->ambilById($idPencairan) : null;
        if (
            $pencairan === null
            || $pencairan['status_pengajuan'] !== 'Disetujui'
            || empty($pencairan['dokumen_lpj'])
            || $pencairan['status_transparansi'] !== 'Menunggu'
        ) {
            setPesan('danger', 'Dokumen LPJ tidak ditemukan atau sudah selesai diverifikasi.');
            alihkan('review_pencairan.php');
        }

        if (!in_array($keputusan, ['terima', 'tolak'], true)) {
            setPesan('danger', 'Keputusan verifikasi LPJ tidak valid.');
            alihkan('review_pencairan.php');
        }

        if ($keputusan === 'tolak' && $alasanPenolakan === '') {
            setPesan('danger', 'Alasan penolakan wajib diisi apabila LPJ ditolak.');
            alihkan('review_pencairan.php');
        }

        try {
            $valid = $keputusan === 'terima';
            $this->pencairanEntity->kirimKeputusanLpj(
                $idPencairan,
                $valid,
                $valid ? null : $alasanPenolakan
            );

            $this->periksaDanTerapkanSanksiLpj();

            if ($valid) {
                setPesan('success', 'LPJ dan bukti nota dinyatakan valid. Status transparansi kini Terverifikasi.');
            } else {
                setPesan('success', 'LPJ ditolak. Fundraiser diminta mengunggah ulang sesuai catatan penolakan.');
            }
        } catch (PDOException $e) {
            setPesan('danger', 'Keputusan verifikasi LPJ gagal disimpan. Silakan coba kembali.');
        }

        alihkan('review_pencairan.php');
    }

    /**
     * Mengalirkan berkas LPJ atau bukti nota hanya kepada Admin atau Fundraiser pemilik campaign
     */
    public function unduhBerkasLpj(): void
    {
        wajibLogin();

        $idPencairan = (int) ($_GET['id_pencairan'] ?? 0);
        $jenis = trim((string) ($_GET['jenis'] ?? ''));

        if (!in_array($jenis, ['dokumen_lpj', 'bukti_nota'], true)) {
            http_response_code(400);
            exit('Jenis berkas tidak valid.');
        }

        $pencairan = $idPencairan > 0 ? $this->pencairanEntity->ambilById($idPencairan) : null;
        if ($pencairan === null) {
            http_response_code(404);
            exit('Data pencairan tidak ditemukan.');
        }

        $peran = peranPenggunaAktif();
        $berhak = $peran === 'Admin'
            || ($peran === 'Fundraiser' && (int) $pencairan['id_fundraiser'] === idPenggunaAktif());

        if (!$berhak) {
            http_response_code(403);
            exit('Anda tidak memiliki izin mengakses berkas ini.');
        }

        $namaFile = (string) ($pencairan[$jenis] ?? '');
        if ($namaFile === '' || basename($namaFile) !== $namaFile) {
            http_response_code(404);
            exit('Berkas tidak tersedia.');
        }

        $lokasiFile = rtrim(DIR_UPLOAD_LPJ, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFile;
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

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($lokasiFile));
        header('Content-Disposition: inline; filename="' . $namaFile . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($lokasiFile);
        exit;
    }

    /**
     * Memeriksa keterlambatan batas unggah LPJ dan membekukan akun + menghentikan campaign aktif (SDD 3.1.4)
     */
    public function periksaDanTerapkanSanksiLpj(?string $hariIni = null): int
    {
        $tanggalAcuan = $hariIni ?? $this->hariIni();
        $suspendSampai = (new DateTimeImmutable($tanggalAcuan))->modify('+1 month')->format('Y-m-d');

        $daftarTerlambat = $this->pencairanEntity->ambilPencairanMelewatiBatasLpj($tanggalAcuan);
        $idFundraiserDibekukan = [];
        foreach ($daftarTerlambat as $baris) {
            if (($baris['status_akun'] ?? 'Aktif') === 'Aktif') {
                $idFundraiserDibekukan[(int) $baris['id_fundraiser']] = true;
            }
        }

        $akunSiapAktif = $this->pencairanEntity->ambilAkunDibekukanSiapAktifkan($tanggalAcuan);
        if ($idFundraiserDibekukan === [] && $akunSiapAktif === []) {
            return 0;
        }

        $kelolaTransaksi = $this->pdo !== null && !$this->pdo->inTransaction();
        if ($kelolaTransaksi) {
            $this->pdo->beginTransaction();
        }

        try {
            foreach (array_keys($idFundraiserDibekukan) as $idFundraiser) {
                $this->userModel->bekukanAkun($idFundraiser, $suspendSampai);
                $this->campaignEntity->hentikanCampaignAktifByFundraiser($idFundraiser);
            }

            foreach ($akunSiapAktif as $idUser) {
                $this->userModel->aktifkanAkun($idUser);
            }

            if ($kelolaTransaksi) {
                $this->pdo->commit();
            }
        } catch (Throwable $e) {
            if ($kelolaTransaksi && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return count($idFundraiserDibekukan);
    }

    /**
     * Memastikan kelayakan campaign untuk diajukan pencairan dana (SDD 3.1.4)
     */
    private function cekKelayakanPengajuan(?array $campaign, string $hariIni): ?string
    {
        if ($campaign === null) {
            return 'Campaign tidak ditemukan.';
        }

        if (($campaign['status_campaign'] ?? '') !== 'Selesai') {
            return 'Pencairan dana hanya dapat diajukan jika status campaign sudah Selesai.';
        }

        if ((string) ($campaign['deadline'] ?? '') >= $hariIni) {
            return 'Pencairan dana hanya dapat diajukan setelah tanggal deadline campaign terlewati.';
        }

        if ((float) ($campaign['dana_terkumpul'] ?? 0) <= 0) {
            return 'Campaign belum memiliki dana terkumpul untuk dicairkan.';
        }

        if ($this->pencairanEntity->adaPengajuanAktifAtauDisetujui((int) $campaign['id_campaign'])) {
            return 'Campaign ini sudah memiliki pengajuan pencairan yang sedang diproses atau telah disetujui.';
        }

        return null;
    }

    /**
     * Mengambil data pencairan milik fundraiser aktif yang berstatus Disetujui
     */
    private function ambilPencairanMilikFundraiser(int $idPencairan): array
    {
        $pencairan = $idPencairan > 0 ? $this->pencairanEntity->ambilById($idPencairan) : null;
        if ($pencairan === null || (int) $pencairan['id_fundraiser'] !== idPenggunaAktif()) {
            setPesan('danger', 'Data pencairan tidak ditemukan.');
            alihkan('dashboard_fundraiser.php');
        }

        if ($pencairan['status_pengajuan'] !== 'Disetujui') {
            setPesan('danger', 'Unggah LPJ hanya tersedia untuk pencairan yang sudah disetujui admin.');
            alihkan('dashboard_fundraiser.php');
        }

        return $pencairan;
    }

    private function hariIni(): string
    {
        return date('Y-m-d');
    }
}

