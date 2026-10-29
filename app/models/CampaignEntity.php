<?php
// Model CampaignEntity untuk mengelola entitas campaign

class CampaignEntity
{
    public const DAFTAR_JENIS = ['Sosial', 'Pendidikan', 'Kesehatan', 'Bencana', 'Lingkungan', 'Lainnya'];
    public const DAFTAR_STATUS = ['Pending', 'Aktif', 'Ditolak', 'Selesai'];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil ringkasan statistik campaign untuk fundraiser
     *
     * @param int $idFundraiser
     * @return array ['jumlah_campaign' => int, 'total_dana_terkumpul' => float, 'campaign_aktif' => int]
     */
    public function ambilRingkasanFundraiser(int $idFundraiser): array
    {
        $sql = "SELECT COUNT(*) AS jumlah_campaign,
                       COALESCE(SUM(dana_terkumpul), 0) AS total_dana_terkumpul,
                       COALESCE(SUM(CASE WHEN status_campaign = 'Aktif' THEN 1 ELSE 0 END), 0) AS campaign_aktif
                FROM campaign
                WHERE id_fundraiser = :id_fundraiser";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_fundraiser' => $idFundraiser]);
        $ringkasan = $pernyataan->fetch();

        return [
            'jumlah_campaign'      => (int) ($ringkasan['jumlah_campaign'] ?? 0),
            'total_dana_terkumpul' => (float) ($ringkasan['total_dana_terkumpul'] ?? 0.0),
            'campaign_aktif'       => (int) ($ringkasan['campaign_aktif'] ?? 0),
        ];
    }

    /**
     * Mengambil daftar campaign milik fundraiser tertentu dengan pencarian dan filter status
     *
     * @param int $idFundraiser
     * @param string $cari
     * @param string $statusFilter
     * @return array
     */
    public function ambilDaftarByFundraiser(int $idFundraiser, string $cari = '', string $statusFilter = ''): array
    {
        $sql = 'SELECT * FROM campaign WHERE id_fundraiser = :id_fundraiser';
        $parameter = [':id_fundraiser' => $idFundraiser];

        $cari = trim($cari);
        if ($cari !== '') {
            $sql .= ' AND judul LIKE :cari';
            $parameter[':cari'] = '%' . $cari . '%';
        }

        $statusFilter = trim($statusFilter);
        if ($statusFilter !== '' && in_array($statusFilter, self::DAFTAR_STATUS, true)) {
            $sql .= ' AND status_campaign = :status_campaign';
            $parameter[':status_campaign'] = $statusFilter;
        }

        $sql .= ' ORDER BY id_campaign DESC';
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute($parameter);

        return $pernyataan->fetchAll();
    }

    /**
     * Menyimpan campaign baru dengan status awal 'Pending' dan dana_terkumpul 0
     *
     * @param array $data
     * @return int ID campaign baru
     */
    public function simpanCampaign(array $data): int
    {
        $sql = "INSERT INTO campaign (
                    id_fundraiser, judul, jenis, deskripsi, target_dana, dana_terkumpul,
                    deadline, dokumen_campaign, gambar_campaign, status_campaign
                ) VALUES (
                    :id_fundraiser, :judul, :jenis, :deskripsi, :target_dana, 0.00,
                    :deadline, :dokumen_campaign, :gambar_campaign, 'Pending'
                )";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([
            ':id_fundraiser'     => (int) $data['id_fundraiser'],
            ':judul'             => (string) $data['judul'],
            ':jenis'             => (string) $data['jenis'],
            ':deskripsi'         => (string) $data['deskripsi'],
            ':target_dana'       => (float) $data['target_dana'],
            ':deadline'          => (string) $data['deadline'],
            ':dokumen_campaign'  => (string) $data['dokumen_campaign'],
            ':gambar_campaign'   => !empty($data['gambar_campaign']) ? (string) $data['gambar_campaign'] : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}