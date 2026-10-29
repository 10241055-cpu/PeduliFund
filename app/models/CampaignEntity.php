<?php

class CampaignEntity
{
    public const DAFTAR_JENIS = ['Sosial', 'Pendidikan', 'Kesehatan', 'Bencana', 'Lingkungan', 'Lainnya'];
    public const DAFTAR_STATUS = ['Pending', 'Aktif', 'Ditolak', 'Selesai'];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function ambilRingkasanFundraiser(int $idFundraiser): array
    {
        $pernyataan = $this->pdo->prepare(
            "SELECT COUNT(*) AS jumlah_campaign,
                    COALESCE(SUM(dana_terkumpul), 0) AS total_dana_terkumpul,
                    COALESCE(SUM(CASE WHEN status_campaign = 'Aktif' THEN 1 ELSE 0 END), 0) AS campaign_aktif
             FROM campaign
             WHERE id_fundraiser = :id_fundraiser"
        );
        $pernyataan->execute(['id_fundraiser' => $idFundraiser]);
        $ringkasan = $pernyataan->fetch();

        return [
            'jumlah_campaign' => (int) $ringkasan['jumlah_campaign'],
            'total_dana_terkumpul' => (float) $ringkasan['total_dana_terkumpul'],
            'campaign_aktif' => (int) $ringkasan['campaign_aktif'],
        ];
    }

    public function ambilDaftarByFundraiser(int $idFundraiser, string $cari = '', string $statusFilter = ''): array
    {
        $sql = 'SELECT * FROM campaign WHERE id_fundraiser = :id_fundraiser';
        $parameter = ['id_fundraiser' => $idFundraiser];

        if ($cari !== '') {
            $sql .= ' AND judul LIKE :cari';
            $parameter['cari'] = '%' . $cari . '%';
        }

        if ($statusFilter !== '') {
            $sql .= ' AND status_campaign = :status_campaign';
            $parameter['status_campaign'] = $statusFilter;
        }

        $sql .= ' ORDER BY id_campaign DESC';
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute($parameter);

        return $pernyataan->fetchAll();
    }

    public function simpanCampaign(array $data): int
    {
        $pernyataan = $this->pdo->prepare(
            "INSERT INTO campaign (
                id_fundraiser, judul, jenis, deskripsi, target_dana, dana_terkumpul,
                deadline, dokumen_campaign, gambar_campaign, status_campaign
            ) VALUES (
                :id_fundraiser, :judul, :jenis, :deskripsi, :target_dana, 0,
                :deadline, :dokumen_campaign, :gambar_campaign, 'Pending'
            )"
        );
        $pernyataan->execute([
            'id_fundraiser' => $data['id_fundraiser'],
            'judul' => $data['judul'],
            'jenis' => $data['jenis'],
            'deskripsi' => $data['deskripsi'],
            'target_dana' => $data['target_dana'],
            'deadline' => $data['deadline'],
            'dokumen_campaign' => $data['dokumen_campaign'],
            'gambar_campaign' => $data['gambar_campaign'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}