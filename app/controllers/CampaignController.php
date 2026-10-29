<?php
// Controller CampaignController untuk mengelola alur bisnis UI-009 dan UI-010
// Sesuai kesepakatan nomor 7, 8, dan 10 SDD

class CampaignController
{
    private CampaignEntity $campaignEntity;

    public function __construct(CampaignEntity $campaignEntity)
    {
        $this->campaignEntity = $campaignEntity;
    }

    /**
     * Menampilkan dashboard fundraiser (UI-009)
     */
    public function tampilkanDashboard(): void
    {
        $idFundraiser = idPenggunaAktif();

        // Baca parameter cari dan status
        $cari = trim((string) ($_GET['cari'] ?? ''));
        $filterStatus = trim((string) ($_GET['status'] ?? ''));

        // Hanya diterima jika ada di CampaignEntity::DAFTAR_STATUS, jika tidak jadikan kosong
        if (!in_array($filterStatus, CampaignEntity::DAFTAR_STATUS, true)) {
            $filterStatus = '';
        }

        // Ambil data ringkasan dan daftar campaign
        $ringkasan = $this->campaignEntity->ambilRingkasanFundraiser($idFundraiser);
        $daftarCampaign = $this->campaignEntity->ambilDaftarByFundraiser($idFundraiser, $cari, $filterStatus);

        require __DIR__ . '/../../views/dashboard_fundraiser.php';
    }

    /**
     * Menampilkan form pembuatan campaign baru (UI-010)
     */
    public function tampilkanFormCampaign(): void
    {
        // Ambil errors dan old dari session, lalu hapus dari session
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
        // Hanya terima request bertipe POST, selain itu alihkan ke dashboard
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan('dashboard_fundraiser.php');
        }

        // Ambil dan trim input
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

        // Jika ada kesalahan validasi awal, kembalikan ke form
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
                // Hapus dokumen yang sudah sempat diunggah
                UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $namaDokumen);

                $_SESSION['errors'] = ['Gambar campaign: ' . $hasilGambar['pesan']];
                $_SESSION['old'] = $old;
                alihkan('buat_campaign.php');
            }

            $namaGambar = $hasilGambar['nama_file'];
        }

        // Simpan campaign ke database
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
            // Jika simpan ke database gagal, hapus berkas yang sudah sempat terunggah
            UnggahBerkas::hapus(DIR_UPLOAD_DOKUMEN, $namaDokumen);
            if ($namaGambar !== null) {
                UnggahBerkas::hapus(DIR_UPLOAD_GAMBAR, $namaGambar);
            }

            $_SESSION['errors'] = ['Campaign gagal disimpan ke database. Silakan coba kembali.'];
            $_SESSION['old'] = $old;
            alihkan('buat_campaign.php');
        }
    }
}