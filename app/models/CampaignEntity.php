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
            // % dan _ punya makna khusus di LIKE; jadikan literal agar hasil cari presisi
            $parameter[':cari'] = '%' . addcslashes($cari, '%_\\') . '%';
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

    /**
     * Mengambil satu campaign milik fundraiser tertentu; null jika tidak ada
     */
    public function ambilByIdCampaign(int $idCampaign, int $idFundraiser): ?array
    {
        $pernyataan = $this->pdo->prepare(
            'SELECT * FROM campaign WHERE id_campaign = :id_campaign AND id_fundraiser = :id_fundraiser'
        );
        $pernyataan->execute([':id_campaign' => $idCampaign, ':id_fundraiser' => $idFundraiser]);
        $campaign = $pernyataan->fetch();

        return $campaign !== false ? $campaign : null;
    }

    /**
     * Menghapus satu campaign milik fundraiser tertentu; true jika ada baris terhapus
     */
    public function hapusCampaign(int $idCampaign, int $idFundraiser): bool
    {
        $pernyataan = $this->pdo->prepare(
            'DELETE FROM campaign WHERE id_campaign = :id_campaign AND id_fundraiser = :id_fundraiser'
        );
        $pernyataan->execute([':id_campaign' => $idCampaign, ':id_fundraiser' => $idFundraiser]);

        return $pernyataan->rowCount() > 0;
    }

    /**
     * Mengambil satu data campaign aktif untuk keperluan donasi publik (SDD SEQ-003)
     */
    public function ambilCampaignAktifById(int $idCampaign): ?array
    {
        $pernyataan = $this->pdo->prepare(
            "SELECT * FROM campaign WHERE id_campaign = :id_campaign AND status_campaign = 'Aktif'"
        );
        $pernyataan->execute([':id_campaign' => $idCampaign]);
        $campaign = $pernyataan->fetch();

        return $campaign !== false ? $campaign : null;
    }

    /**
     * Memperbarui progres dana terkumpul pada campaign setelah donasi berhasil (SDD Bab 4.1.3 & SEQ-003)
     */
    public function perbaruiProgresDana(int $idCampaign, float $nominal): bool
    {
        $pernyataan = $this->pdo->prepare(
            'UPDATE campaign SET dana_terkumpul = dana_terkumpul + :nominal WHERE id_campaign = :id_campaign'
        );

        return $pernyataan->execute([
            ':nominal'     => $nominal,
            ':id_campaign' => $idCampaign,
        ]);
    }

    /**
     * Memeriksa status campaign berdasarkan ID (SDD 4.1.4)
     */
    public function cekStatusCampaign(int $idCampaign): ?string
    {
        $pernyataan = $this->pdo->prepare(
            'SELECT status_campaign FROM campaign WHERE id_campaign = :id_campaign LIMIT 1'
        );
        $pernyataan->execute([':id_campaign' => $idCampaign]);
        $status = $pernyataan->fetchColumn();

        return $status !== false ? (string) $status : null;
    }

    /**
     * Menutup campaign aktif milik fundraiser yang sudah melewati deadline menjadi Selesai
     */
    public function tutupCampaignKedaluwarsa(int $idFundraiser, string $hariIni): int
    {
        $pernyataan = $this->pdo->prepare(
            "UPDATE campaign
             SET status_campaign = 'Selesai'
             WHERE id_fundraiser = :id_fundraiser
               AND status_campaign = 'Aktif'
               AND deadline < :hari_ini"
        );
        $pernyataan->execute([
            ':id_fundraiser' => $idFundraiser,
            ':hari_ini'      => $hariIni,
        ]);

        return $pernyataan->rowCount();
    }

    /**
     * Menghentikan seluruh campaign aktif milik fundraiser ketika akun dibekukan (SDD 3.1.4)
     */
    public function hentikanCampaignAktifByFundraiser(int $idFundraiser): int
    {
        $pernyataan = $this->pdo->prepare(
            "UPDATE campaign
             SET status_campaign = 'Selesai'
             WHERE id_fundraiser = :id_fundraiser
               AND status_campaign = 'Aktif'"
        );
        $pernyataan->execute([':id_fundraiser' => $idFundraiser]);

        return $pernyataan->rowCount();
    }

    /**
     * Menghitung jumlah campaign berstatus Pending untuk ringkasan dashboard admin
     */
    public function hitungCampaignMenunggu(): int
    {
        $pernyataan = $this->pdo->prepare(
            "SELECT COUNT(*) FROM campaign WHERE status_campaign = 'Pending'"
        );
        $pernyataan->execute();

        return (int) $pernyataan->fetchColumn();
    }
}