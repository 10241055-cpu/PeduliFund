<?php

class CampaignController
{
    private CampaignEntity $campaignEntity;

    public function __construct(CampaignEntity $campaignEntity)
    {
        $this->campaignEntity = $campaignEntity;
    }

    public function tampilkanDashboard(): void
    {
        $cari = trim((string) ($_GET['cari'] ?? ''));
        $filterStatus = (string) ($_GET['status'] ?? '');
        if (!in_array($filterStatus, CampaignEntity::DAFTAR_STATUS, true)) {
            $filterStatus = '';
        }

        $ringkasan = $this->campaignEntity->ambilRingkasanFundraiser(idPenggunaAktif());
        $daftarCampaign = $this->campaignEntity->ambilDaftarByFundraiser(idPenggunaAktif(), $cari, $filterStatus);

        require __DIR__ . '/../../views/dashboard_fundraiser.php';
    }

    public function tampilkanFormCampaign(): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);
        $daftarJenis = CampaignEntity::DAFTAR_JENIS;

        require __DIR__ . '/../../views/form_campaign.php';
    }

    public function kirimCampaign(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('buat_campaign.php');
        }

        $judul = trim((string) ($_POST['judul'] ?? ''));
        $jenis = (string) ($_POST['jenis'] ?? '');
        $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
        $targetDana = trim((string) ($_POST['target_dana'] ?? ''));
        $deadline = trim((string) ($_POST['deadline'] ?? ''));
        $old = [
            'judul' => $judul,
            'jenis' => $jenis,
            'deskripsi' => $deskripsi,
            'target_dana' => $targetDana,
            'deadline' => $deadline,
        ];
        $errors = [];

        if ($judul === '') {
            $errors[] = 'Judul campaign wajib diisi.';
        } elseif (preg_match_all('/./us', $judul, $cocok) === false || count($cocok[0]) > 255) {
            $errors[] = 'Judul campaign maksimal 255 karakter.';
        }

        if (!in_array($jenis, CampaignEntity::DAFTAR_JENIS, true)) {
            $errors[] = 'Jenis campaign tidak valid.';
        }

        if ($deskripsi === '') {
            $errors[] = 'Deskripsi campaign wajib diisi.';
        }

        if (!is_numeric($targetDana) || (float) $targetDana <= 0 || !is_finite((float) $targetDana)) {
            $errors[] = 'Target dana harus berupa angka lebih dari 0.';
        }

        $tanggalValid = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $deadline) === 1
            ? DateTime::createFromFormat('!Y-m-d', $deadline)
            : false;
        $tanggalHariIni = new DateTime('today');
        if ($tanggalValid === false || $tanggalValid->format('Y-m-d') !== $deadline || $tanggalValid < $tanggalHariIni) {
            $errors[] = 'Deadline harus menggunakan tanggal yang valid dan tidak boleh sebelum hari ini.';
        }

        $berkasDokumen = $_FILES['dokumen_campaign'] ?? null;
        $berkasGambar = $_FILES['gambar_campaign'] ?? null;

        if ($berkasDokumen === null || (int) ($berkasDokumen['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Dokumen campaign wajib diunggah.';
        }

        if ($berkasDokumen !== null && (int) ($berkasDokumen['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $pesanValidasi = $this->validasiBerkas($berkasDokumen, ['pdf', 'jpg', 'jpeg', 'png']);
            if ($pesanValidasi !== '') {
                $errors[] = 'Dokumen campaign: ' . $pesanValidasi;
            }
        }

        if ($berkasGambar !== null && (int) ($berkasGambar['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $pesanValidasi = $this->validasiBerkas($berkasGambar, ['jpg', 'jpeg', 'png']);
            if ($pesanValidasi !== '') {
                $errors[] = 'Gambar campaign: ' . $pesanValidasi;
            }
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }

        $hasilDokumen = UnggahBerkas::simpan($berkasDokumen, DIR_UPLOAD_DOKUMEN, ['pdf', 'jpg', 'jpeg', 'png'], UKURAN_MAKS_UPLOAD);
        if (!$hasilDokumen['berhasil']) {
            $_SESSION['errors'] = ['Dokumen campaign: ' . $hasilDokumen['pesan']];
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }

        $namaGambar = null;
        if ($berkasGambar !== null && (int) $berkasGambar['error'] !== UPLOAD_ERR_NO_FILE) {
            $hasilGambar = UnggahBerkas::simpan($berkasGambar, DIR_UPLOAD_GAMBAR, ['jpg', 'jpeg', 'png'], UKURAN_MAKS_UPLOAD);
            if (!$hasilGambar['berhasil']) {
                UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $hasilDokumen['nama_file']);
                $_SESSION['errors'] = ['Gambar campaign: ' . $hasilGambar['pesan']];
                $_SESSION['old'] = $old;
                alihkan('buat_campaign.php');
            }
            $namaGambar = $hasilGambar['nama_file'];
        }

        try {
            $this->campaignEntity->simpanCampaign([
                'id_fundraiser' => idPenggunaAktif(),
                'judul' => $judul,
                'jenis' => $jenis,
                'deskripsi' => $deskripsi,
                'target_dana' => $targetDana,
                'deadline' => $deadline,
                'dokumen_campaign' => $hasilDokumen['nama_file'],
                'gambar_campaign' => $namaGambar,
            ]);
        } catch (PDOException $e) {
            UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $hasilDokumen['nama_file']);
            if ($namaGambar !== null) {
                UnggahBerkas::hapus(DIR_UPLOAD_GAMBAR, $namaGambar);
            }
            $_SESSION['errors'] = ['Campaign gagal disimpan. Silakan coba kembali.'];
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }

        setPesan('success', 'Campaign berhasil dikirim dan menunggu verifikasi admin.');
        alihkan('dashboard_fundraiser.php');
    }

    private function validasiBerkas(array $berkas, array $ekstensiDiizinkan): string
    {
        $error = (int) ($berkas['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return $error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE
                ? 'Ukuran berkas maksimal 2 MB.'
                : 'Berkas gagal diunggah.';
        }

        if ((int) ($berkas['size'] ?? 0) > UKURAN_MAKS_UPLOAD) {
            return 'Ukuran berkas maksimal 2 MB.';
        }

        $ekstensi = strtolower(pathinfo((string) ($berkas['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ekstensi, $ekstensiDiizinkan, true)) {
            return 'Format berkas tidak diizinkan.';
        }

        return '';
    }
}