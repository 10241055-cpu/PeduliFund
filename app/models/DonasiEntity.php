<?php
// Model DonasiEntity untuk mengelola entitas transaksi donasi (SDD Bab 4.1.3 & Tabel 4.5)

class DonasiEntity
{
    public const DAFTAR_METODE = ['Transfer Bank', 'EWallet', 'QRIS'];
    public const DAFTAR_STATUS = ['Berhasil', 'Gagal'];
    // Batas atas menjaga dana_terkumpul campaign (DECIMAL(15,2)) tidak overflow
    public const NOMINAL_MINIMAL = 10000;
    public const NOMINAL_MAKSIMAL = 1000000000;

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mencatat transaksi donasi ke database (SDD SEQ-003: catatDonasi)
     *
     * @param array $data ['id_campaign' => int, 'id_donatur' => int, 'nominal' => float, 'metode_pembayaran' => string, 'status_pembayaran' => string]
     * @return int ID donasi baru
     */
    public function catatDonasi(array $data): int
    {
        $sql = "INSERT INTO donasi (
                    id_campaign, id_donatur, nominal, metode_pembayaran, status_pembayaran, tanggal_pembayaran
                ) VALUES (
                    :id_campaign, :id_donatur, :nominal, :metode_pembayaran, :status_pembayaran, NOW()
                )";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([
            ':id_campaign'       => (int) $data['id_campaign'],
            ':id_donatur'        => (int) $data['id_donatur'],
            ':nominal'           => (float) $data['nominal'],
            ':metode_pembayaran' => (string) $data['metode_pembayaran'],
            ':status_pembayaran' => (string) $data['status_pembayaran'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Mengambil satu data transaksi donasi berdasarkan ID
     */
    public function ambilByIdDonasi(int $idDonasi): ?array
    {
        $sql = "SELECT d.*, c.judul AS judul_campaign
                FROM donasi d
                JOIN campaign c ON d.id_campaign = c.id_campaign
                WHERE d.id_donasi = :id_donasi";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_donasi' => $idDonasi]);
        $donasi = $pernyataan->fetch();

        return $donasi !== false ? $donasi : null;
    }

    /**
     * Mengambil riwayat donasi pengguna tertentu (untuk UI-004 Dashboard Donatur)
     */
    public function ambilRiwayatByDonatur(int $idDonatur): array
    {
        $sql = "SELECT d.*, c.judul AS judul_campaign, c.gambar_campaign
                FROM donasi d
                JOIN campaign c ON d.id_campaign = c.id_campaign
                WHERE d.id_donatur = :id_donatur
                ORDER BY d.id_donasi DESC";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_donatur' => $idDonatur]);

        return $pernyataan->fetchAll();
    }

    /**
     * Mengambil daftar donatur terbaru pada sebuah campaign yang status pembayarannya berhasil (untuk UI-006)
     */
    public function ambilDonaturTerbaruByCampaign(int $idCampaign, int $limit = 5): array
    {
        $sql = "SELECT d.nominal, d.tanggal_pembayaran, p.email AS email_donatur
                FROM donasi d
                JOIN pengguna p ON d.id_donatur = p.id_user
                WHERE d.id_campaign = :id_campaign AND d.status_pembayaran = 'Berhasil'
                ORDER BY d.id_donasi DESC
                LIMIT :limit";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->bindValue(':id_campaign', $idCampaign, PDO::PARAM_INT);
        $pernyataan->bindValue(':limit', $limit, PDO::PARAM_INT);
        $pernyataan->execute();

        return $pernyataan->fetchAll();
    }
}

