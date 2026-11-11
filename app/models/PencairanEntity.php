<?php
// Model PencairanEntity untuk mengelola entitas pencairan_dana dan audit LPJ (SDD 4.1.4 & Tabel 4.6)

class PencairanEntity
{
    public const DAFTAR_STATUS_PENGAJUAN = ['Menunggu', 'Disetujui', 'Ditolak'];
    public const DAFTAR_STATUS_TRANSPARANSI = ['Menunggu', 'Terverifikasi', 'Ditolak'];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil satu data pencairan beserta detail campaign dan akun fundraiser
     */
    public function ambilById(int $idPencairan): ?array
    {
        $sql = 'SELECT p.*,
                       c.id_fundraiser, c.judul AS judul_campaign, c.jenis AS jenis_campaign,
                       c.target_dana, c.dana_terkumpul, c.deadline, c.status_campaign,
                       u.email AS email_fundraiser, u.status_akun, u.suspend_sampai
                FROM pencairan_dana p
                INNER JOIN campaign c ON c.id_campaign = p.id_campaign
                INNER JOIN pengguna u ON u.id_user = c.id_fundraiser
                WHERE p.id_pencairan = :id_pencairan
                LIMIT 1';

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_pencairan' => $idPencairan]);
        $hasil = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return $hasil !== false ? $hasil : null;
    }

    /**
     * Mengambil data pencairan terbaru untuk sekumpulan ID campaign (terindeks id_campaign)
     */
    public function ambilTerbaruByDaftarCampaign(array $daftarIdCampaign): array
    {
        $daftarId = array_values(array_unique(array_filter(array_map('intval', $daftarIdCampaign), static fn (int $id): bool => $id > 0)));
        if ($daftarId === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($daftarId), '?'));
        $sql = "SELECT * FROM pencairan_dana WHERE id_campaign IN ({$placeholders}) ORDER BY id_pencairan ASC";
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute($daftarId);

        $terpetakan = [];
        foreach ($pernyataan->fetchAll(PDO::FETCH_ASSOC) as $baris) {
            $terpetakan[(int) $baris['id_campaign']] = $baris;
        }

        return $terpetakan;
    }

    /**
     * Memeriksa apakah campaign sudah memiliki pengajuan pencairan Menunggu atau Disetujui
     */
    public function adaPengajuanAktifAtauDisetujui(int $idCampaign): bool
    {
        $sql = "SELECT COUNT(*)
                FROM pencairan_dana
                WHERE id_campaign = :id_campaign
                  AND status_pengajuan IN ('Menunggu', 'Disetujui')";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_campaign' => $idCampaign]);

        return ((int) $pernyataan->fetchColumn()) > 0;
    }

    /**
     * Memeriksa apakah campaign sudah memiliki pencairan yang Disetujui
     */
    public function sudahDicairkan(int $idCampaign): bool
    {
        $sql = "SELECT COUNT(*)
                FROM pencairan_dana
                WHERE id_campaign = :id_campaign
                  AND status_pengajuan = 'Disetujui'";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_campaign' => $idCampaign]);

        return ((int) $pernyataan->fetchColumn()) > 0;
    }

    /**
     * Mengambil pengajuan pencairan terakhir yang ditolak pada campaign tertentu
     */
    public function ambilPenolakanTerakhir(int $idCampaign): ?array
    {
        $sql = "SELECT *
                FROM pencairan_dana
                WHERE id_campaign = :id_campaign
                  AND status_pengajuan = 'Ditolak'
                ORDER BY id_pencairan DESC
                LIMIT 1";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_campaign' => $idCampaign]);
        $hasil = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return $hasil !== false ? $hasil : null;
    }

    /**
     * Menyimpan pengajuan pencairan dana baru dengan status awal Menunggu
     */
    public function simpanPengajuan(array $data): int
    {
        $sql = "INSERT INTO pencairan_dana (
                    id_campaign, nominal_pencairan, nama_bank, nomor_rekening, atas_nama,
                    status_pengajuan, status_transparansi
                ) VALUES (
                    :id_campaign, :nominal_pencairan, :nama_bank, :nomor_rekening, :atas_nama,
                    'Menunggu', 'Menunggu'
                )";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([
            ':id_campaign'       => (int) $data['id_campaign'],
            ':nominal_pencairan' => (float) $data['nominal_pencairan'],
            ':nama_bank'         => (string) $data['nama_bank'],
            ':nomor_rekening'    => (string) $data['nomor_rekening'],
            ':atas_nama'         => (string) $data['atas_nama'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Menyimpan keputusan admin atas pengajuan pencairan dana (Disetujui / Ditolak)
     */
    public function prosesKeputusanPencairan(
        int $idPencairan,
        bool $disetujui,
        ?string $alasanPenolakan,
        ?string $tanggalPencairan,
        ?string $batasUnggahLpj
    ): bool {
        if ($disetujui) {
            $sql = "UPDATE pencairan_dana
                    SET status_pengajuan = 'Disetujui',
                        alasan_penolakan = NULL,
                        tanggal_pencairan = :tanggal_pencairan,
                        batas_unggah_lpj = :batas_unggah_lpj
                    WHERE id_pencairan = :id_pencairan
                      AND status_pengajuan = 'Menunggu'";

            $pernyataan = $this->pdo->prepare($sql);
            $pernyataan->execute([
                ':tanggal_pencairan' => $tanggalPencairan,
                ':batas_unggah_lpj'  => $batasUnggahLpj,
                ':id_pencairan'      => $idPencairan,
            ]);
        } else {
            $sql = "UPDATE pencairan_dana
                    SET status_pengajuan = 'Ditolak',
                        alasan_penolakan = :alasan_penolakan
                    WHERE id_pencairan = :id_pencairan
                      AND status_pengajuan = 'Menunggu'";

            $pernyataan = $this->pdo->prepare($sql);
            $pernyataan->execute([
                ':alasan_penolakan' => $alasanPenolakan,
                ':id_pencairan'     => $idPencairan,
            ]);
        }

        return $pernyataan->rowCount() > 0;
    }

    /**
     * Menyimpan berkas LPJ dan bukti nota yang diunggah fundraiser
     */
    public function simpanLpj(int $idPencairan, string $dokumenLpj, string $buktiNota, string $tanggalUnggah): bool
    {
        $sql = "UPDATE pencairan_dana
                SET dokumen_lpj = :dokumen_lpj,
                    bukti_nota = :bukti_nota,
                    tanggal_unggah_lpj = :tanggal_unggah_lpj,
                    status_transparansi = 'Menunggu',
                    alasan_penolakan = NULL
                WHERE id_pencairan = :id_pencairan
                  AND status_pengajuan = 'Disetujui'
                  AND status_transparansi <> 'Terverifikasi'";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([
            ':dokumen_lpj'        => $dokumenLpj,
            ':bukti_nota'         => $buktiNota,
            ':tanggal_unggah_lpj' => $tanggalUnggah,
            ':id_pencairan'       => $idPencairan,
        ]);

        return $pernyataan->rowCount() > 0;
    }

    /**
     * Menyimpan keputusan admin atas audit LPJ dan bukti nota (Terverifikasi / Ditolak)
     */
    public function kirimKeputusanLpj(int $idPencairan, bool $valid, ?string $alasanPenolakan = null): bool
    {
        if ($valid) {
            $sql = "UPDATE pencairan_dana
                    SET status_transparansi = 'Terverifikasi',
                        alasan_penolakan = NULL
                    WHERE id_pencairan = :id_pencairan
                      AND status_pengajuan = 'Disetujui'
                      AND dokumen_lpj IS NOT NULL";

            $pernyataan = $this->pdo->prepare($sql);
            $pernyataan->execute([':id_pencairan' => $idPencairan]);
        } else {
            $sql = "UPDATE pencairan_dana
                    SET status_transparansi = 'Ditolak',
                        alasan_penolakan = :alasan_penolakan
                    WHERE id_pencairan = :id_pencairan
                      AND status_pengajuan = 'Disetujui'
                      AND dokumen_lpj IS NOT NULL";

            $pernyataan = $this->pdo->prepare($sql);
            $pernyataan->execute([
                ':alasan_penolakan' => $alasanPenolakan,
                ':id_pencairan'     => $idPencairan,
            ]);
        }

        return $pernyataan->rowCount() > 0;
    }

    /**
     * Mengambil daftar seluruh pencairan untuk halaman Review Pencairan Admin (UI-017)
     */
    public function ambilDaftarAdmin(string $filter = ''): array
    {
        $sql = 'SELECT p.*,
                       c.id_fundraiser, c.judul AS judul_campaign, c.jenis AS jenis_campaign,
                       c.target_dana, c.dana_terkumpul, c.deadline, c.status_campaign,
                       u.email AS email_fundraiser, u.status_akun, u.suspend_sampai
                FROM pencairan_dana p
                INNER JOIN campaign c ON c.id_campaign = p.id_campaign
                INNER JOIN pengguna u ON u.id_user = c.id_fundraiser';

        if ($filter === 'menunggu_pencairan') {
            $sql .= " WHERE p.status_pengajuan = 'Menunggu'";
        } elseif ($filter === 'menunggu_lpj') {
            $sql .= " WHERE p.status_pengajuan = 'Disetujui' AND p.dokumen_lpj IS NOT NULL AND p.status_transparansi = 'Menunggu'";
        } elseif ($filter === 'belum_unggah_lpj') {
            $sql .= " WHERE p.status_pengajuan = 'Disetujui' AND (p.dokumen_lpj IS NULL OR p.status_transparansi = 'Ditolak')";
        } elseif ($filter === 'selesai') {
            $sql .= " WHERE p.status_transparansi = 'Terverifikasi'";
        }

        $sql .= ' ORDER BY p.id_pencairan DESC';

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute();

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil ringkasan jumlah antrean pencairan dan audit LPJ untuk Dashboard Admin (UI-018)
     */
    public function ambilRingkasanAdmin(): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN status_pengajuan = 'Menunggu' THEN 1 ELSE 0 END), 0) AS pencairan_menunggu,
                    COALESCE(SUM(CASE WHEN status_pengajuan = 'Disetujui' AND dokumen_lpj IS NOT NULL AND status_transparansi = 'Menunggu' THEN 1 ELSE 0 END), 0) AS lpj_menunggu
                FROM pencairan_dana";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute();
        $hasil = $pernyataan->fetch(PDO::FETCH_ASSOC);

        $pencairanMenunggu = (int) ($hasil['pencairan_menunggu'] ?? 0);
        $lpjMenunggu = (int) ($hasil['lpj_menunggu'] ?? 0);

        return [
            'pencairan_menunggu' => $pencairanMenunggu,
            'lpj_menunggu'       => $lpjMenunggu,
            'total_antrean'      => $pencairanMenunggu + $lpjMenunggu,
        ];
    }

    /**
     * Mengambil daftar pencairan yang melewati batas unggah LPJ tanpa status Terverifikasi
     */
    public function ambilPencairanMelewatiBatasLpj(string $hariIni): array
    {
        $sql = "SELECT p.id_pencairan, p.id_campaign, p.batas_unggah_lpj,
                       c.id_fundraiser, u.status_akun, u.suspend_sampai
                FROM pencairan_dana p
                INNER JOIN campaign c ON c.id_campaign = p.id_campaign
                INNER JOIN pengguna u ON u.id_user = c.id_fundraiser
                WHERE p.status_pengajuan = 'Disetujui'
                  AND p.status_transparansi <> 'Terverifikasi'
                  AND p.batas_unggah_lpj IS NOT NULL
                  AND p.batas_unggah_lpj < :hari_ini";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':hari_ini' => $hariIni]);

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil ID pengguna yang masa pembekuannya selesai dan seluruh LPJ-nya sudah Terverifikasi
     */
    public function ambilAkunDibekukanSiapAktifkan(string $hariIni): array
    {
        $sql = "SELECT u.id_user
                FROM pengguna u
                WHERE u.status_akun = 'Dibekukan'
                  AND u.suspend_sampai IS NOT NULL
                  AND u.suspend_sampai < :hari_ini
                  AND NOT EXISTS (
                      SELECT 1
                      FROM pencairan_dana p
                      INNER JOIN campaign c ON c.id_campaign = p.id_campaign
                      WHERE c.id_fundraiser = u.id_user
                        AND p.status_pengajuan = 'Disetujui'
                        AND p.status_transparansi <> 'Terverifikasi'
                  )";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':hari_ini' => $hariIni]);

        return array_map('intval', $pernyataan->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Menghitung pencairan milik fundraiser yang sudah dicairkan tetapi LPJ-nya belum Terverifikasi
     */
    public function hitungLpjBelumTerverifikasiByFundraiser(int $idFundraiser): int
    {
        $sql = "SELECT COUNT(*)
                FROM pencairan_dana p
                INNER JOIN campaign c ON c.id_campaign = p.id_campaign
                WHERE c.id_fundraiser = :id_fundraiser
                  AND p.status_pengajuan = 'Disetujui'
                  AND p.status_transparansi <> 'Terverifikasi'";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_fundraiser' => $idFundraiser]);

        return (int) $pernyataan->fetchColumn();
    }

    /**
     * Mengambil pencairan yang sudah disetujui tetapi belum memiliki LPJ Terverifikasi milik fundraiser
     */
    public function ambilTagihanLpjFundraiser(int $idFundraiser): array
    {
        $sql = "SELECT p.*, c.judul AS judul_campaign
                FROM pencairan_dana p
                INNER JOIN campaign c ON c.id_campaign = p.id_campaign
                WHERE c.id_fundraiser = :id_fundraiser
                  AND p.status_pengajuan = 'Disetujui'
                  AND p.status_transparansi <> 'Terverifikasi'
                ORDER BY p.batas_unggah_lpj ASC, p.id_pencairan DESC";

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_fundraiser' => $idFundraiser]);

        return $pernyataan->fetchAll(PDO::FETCH_ASSOC);
    }
}

