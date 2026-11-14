<?php
// Model VerifikasiFundraiserEntity untuk tabel verifikasi_fundraiser (SDD Bab 4.1.1 & Tabel 4.3)
// Menampung pengajuan verifikasi akun fundraiser (UI-008) dan keputusan admin (UI-012)

class VerifikasiFundraiserEntity
{
    public const DAFTAR_JENIS = ['Individu', 'Organisasi'];
    public const DAFTAR_STATUS = ['Menunggu', 'Valid', 'Ditolak'];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Mengambil pengajuan verifikasi terakhir milik satu pengguna
     */
    public function ambilTerbaruByUser(int $idUser): ?array
    {
        $pernyataan = $this->pdo->prepare(
            'SELECT * FROM verifikasi_fundraiser
             WHERE id_user = :id_user
             ORDER BY id_verifikasi DESC
             LIMIT 1'
        );
        $pernyataan->execute([':id_user' => $idUser]);
        $pengajuan = $pernyataan->fetch();

        return $pengajuan !== false ? $pengajuan : null;
    }

    /**
     * Mencatat pengajuan verifikasi akun baru berstatus Menunggu (UI-008)
     *
     * @param array $data ['id_user' => int, 'jenis_fundraiser' => string, 'nama_lengkap' => string,
     *                     'nomor_identitas' => string, 'nomor_telepon' => string,
     *                     'dokumen_verifikasi' => string, 'dokumen_pendukung' => ?string]
     */
    public function buatPengajuan(array $data): int
    {
        $sql = 'INSERT INTO verifikasi_fundraiser
                    (id_user, jenis_fundraiser, nama_lengkap, nomor_identitas, nomor_telepon,
                     dokumen_verifikasi, dokumen_pendukung)
                VALUES
                    (:id_user, :jenis_fundraiser, :nama_lengkap, :nomor_identitas, :nomor_telepon,
                     :dokumen_verifikasi, :dokumen_pendukung)';

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([
            ':id_user'            => (int) $data['id_user'],
            ':jenis_fundraiser'   => $data['jenis_fundraiser'],
            ':nama_lengkap'       => $data['nama_lengkap'],
            ':nomor_identitas'    => $data['nomor_identitas'],
            ':nomor_telepon'      => $data['nomor_telepon'],
            ':dokumen_verifikasi' => $data['dokumen_verifikasi'],
            ':dokumen_pendukung'  => $data['dokumen_pendukung'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Daftar pengajuan berstatus Menunggu untuk ditinjau admin (UI-012), terbaru dulu
     */
    public function ambilMenunggu(): array
    {
        $sql = 'SELECT v.*, p.email
                FROM verifikasi_fundraiser v
                INNER JOIN pengguna p ON p.id_user = v.id_user
                WHERE v.status_dokumen = :status
                ORDER BY v.id_verifikasi ASC';

        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':status' => 'Menunggu']);

        return $pernyataan->fetchAll();
    }

    /**
     * Mengambil satu pengajuan berdasarkan ID untuk keperluan admin
     */
    public function ambilById(int $idVerifikasi): ?array
    {
        $pernyataan = $this->pdo->prepare(
            'SELECT v.*, p.email FROM verifikasi_fundraiser v
             INNER JOIN pengguna p ON p.id_user = v.id_user
             WHERE v.id_verifikasi = :id_verifikasi LIMIT 1'
        );
        $pernyataan->execute([':id_verifikasi' => $idVerifikasi]);
        $pengajuan = $pernyataan->fetch();

        return $pengajuan !== false ? $pengajuan : null;
    }

    /**
     * Menyetujui pengajuan: dokumen Valid (role pengguna diubah terpisah lewat UserCredentialsDB::ubahRole)
     */
    public function setujui(int $idVerifikasi): bool
    {
        $pernyataan = $this->pdo->prepare(
            "UPDATE verifikasi_fundraiser
             SET status_dokumen = 'Valid', alasan_penolakan = NULL, tanggal_keputusan = NOW()
             WHERE id_verifikasi = :id_verifikasi AND status_dokumen = 'Menunggu'"
        );
        $pernyataan->execute([':id_verifikasi' => $idVerifikasi]);

        // execute() saja tidak cukup: transisi hanya sah bila benar-benar ada baris yang berpindah status
        return $pernyataan->rowCount() > 0;
    }

    /**
     * Menolak pengajuan dengan alasan wajib (UI-012); pemohon boleh ajukan ulang setelah 14 hari
     */
    public function tolak(int $idVerifikasi, string $alasan): bool
    {
        if (trim($alasan) === '') {
            return false;
        }

        $pernyataan = $this->pdo->prepare(
            "UPDATE verifikasi_fundraiser
             SET status_dokumen = 'Ditolak', alasan_penolakan = :alasan, tanggal_keputusan = NOW()
             WHERE id_verifikasi = :id_verifikasi AND status_dokumen = 'Menunggu'"
        );
        $pernyataan->execute([
            ':alasan'       => trim($alasan),
            ':id_verifikasi' => $idVerifikasi,
        ]);

        return $pernyataan->rowCount() > 0;
    }

    /**
     * Jumlah pengajuan berstatus Menunggu untuk kartu metrik Dashboard Admin (UI-018)
     */
    public function hitungMenunggu(): int
    {
        $pernyataan = $this->pdo->prepare(
            "SELECT COUNT(*) FROM verifikasi_fundraiser WHERE status_dokumen = 'Menunggu'"
        );
        $pernyataan->execute();

        return (int) $pernyataan->fetchColumn();
    }
}
