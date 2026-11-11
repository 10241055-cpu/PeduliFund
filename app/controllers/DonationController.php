<?php
// Controller DonationController untuk mengelola alur donasi dan pembayaran (SDD Bab 4.1.3 & SEQ-003)

class DonationController
{
    private DonasiEntity $donasiEntity;
    private CampaignEntity $campaignEntity;

    public function __construct(DonasiEntity $donasiEntity, CampaignEntity $campaignEntity)
    {
        $this->donasiEntity = $donasiEntity;
        $this->campaignEntity = $campaignEntity;
    }

    /**
     * Menampilkan formulir donasi dan pembayaran (UI-014)
     */
    public function tampilkanFormDonasi(): void
    {
        $idCampaign = (int) ($_GET['id'] ?? $_GET['id_campaign'] ?? 0);

        if ($idCampaign <= 0) {
            setPesan('danger', 'Campaign tidak ditemukan.');
            alihkan($this->tujuanDashboard());
        }

        $campaign = $this->campaignEntity->ambilCampaignAktifById($idCampaign);

        if ($campaign === null) {
            setPesan('danger', 'Campaign tidak ditemukan atau belum aktif menerima donasi.');
            alihkan($this->tujuanDashboard());
        }

        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old']);

        $daftarMetode = DonasiEntity::DAFTAR_METODE;
        $nominalCepat = [10000, 25000, 50000, 100000];

        require __DIR__ . '/../../views/form_donasi.php';
    }

    /**
     * Memproses transaksi donasi dan simulasi pembayaran (SDD ACT-003 & SEQ-003)
     */
    public function prosesDonasi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            alihkan($this->tujuanDashboard());
        }

        verifikasiCsrf();

        $idCampaign = (int) ($_POST['id_campaign'] ?? 0);
        $nominalRaw = trim((string) ($_POST['nominal'] ?? ''));
        $metodePembayaran = trim((string) ($_POST['metode_pembayaran'] ?? ''));
        $skenarioSimulasi = trim((string) ($_POST['simulasi_hasil'] ?? 'Berhasil'));

        $campaign = $this->campaignEntity->ambilCampaignAktifById($idCampaign);
        if ($campaign === null) {
            setPesan('danger', 'Campaign yang dituju tidak valid atau sudah tidak aktif.');
            alihkan($this->tujuanDashboard());
        }

        // Bersihkan pemisah ribuan jika ada
        $nominalBersih = str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $nominalRaw);
        $nominal = is_numeric($nominalBersih) ? (float) $nominalBersih : 0.0;

        $errors = [];
        if ($nominal < 10000) {
            $errors[] = 'Nominal donasi minimal adalah Rp 10.000.';
        }

        if (!in_array($metodePembayaran, DonasiEntity::DAFTAR_METODE, true)) {
            $errors[] = 'Pilih metode pembayaran yang valid.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = [
                'nominal'           => $nominalRaw,
                'metode_pembayaran' => $metodePembayaran,
                'simulasi_hasil'    => $skenarioSimulasi,
            ];
            alihkan('donasi.php?id=' . $idCampaign);
        }

        // Eksekusi simulasi payment gateway buatan tim (SDD Bab 1.3)
        $statusPembayaran = $this->prosesSimulasiPaymentGateway($skenarioSimulasi);

        // Catat seluruh riwayat transaksi ke DonasiEntity (baik berhasil maupun gagal)
        $this->donasiEntity->catatDonasi([
            'id_campaign'       => $idCampaign,
            'id_donatur'        => idPenggunaAktif(),
            'nominal'           => $nominal,
            'metode_pembayaran' => $metodePembayaran,
            'status_pembayaran' => $statusPembayaran,
        ]);

        if ($statusPembayaran === 'Berhasil') {
            // Perbarui akumulasi dana terkumpul pada CampaignEntity (SDD SEQ-003)
            $this->campaignEntity->perbaruiProgresDana($idCampaign, $nominal);

            setPesan(
                'success',
                'Pembayaran simulasi berhasil! Terima kasih, donasi Anda sebesar ' . formatRupiah($nominal) . ' telah disalurkan ke campaign "' . $campaign['judul'] . '".'
            );
            alihkan($this->tujuanDashboard());
        } else {
            // Penanganan jika pembayaran simulasi gagal (SDD SEQ-003 alur alternatif)
            setPesan(
                'danger',
                'Simulasi pembayaran gagal. Donasi tidak diproses dan dana campaign tidak bertambah. Silakan coba kembali.'
            );
            alihkan('donasi.php?id=' . $idCampaign);
        }
    }

    /**
     * Layanan payment gateway simulasi buatan tim (SDD Bab 1.3 Tabel 1.1)
     */
    private function prosesSimulasiPaymentGateway(string $skenario): string
    {
        return $skenario === 'Gagal' ? 'Gagal' : 'Berhasil';
    }

    /**
     * Halaman landasan setelah aksi donasi mengikuti peran (dashboard_fundraiser dilindungi wajibLogin Fundraiser)
     */
    private function tujuanDashboard(): string
    {
        return peranPenggunaAktif() === 'Fundraiser' ? 'dashboard_fundraiser.php' : 'dashboard_donatur.php';
    }
}

