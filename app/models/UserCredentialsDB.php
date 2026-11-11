<?php
// Model UserCredentialsDB untuk mengelola data akun pengguna pada tabel pengguna sesuai SDD 4.1.1

class UserCredentialsDB
{
    public const DAFTAR_ROLE = ['Admin', 'Donatur', 'Fundraiser'];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Memeriksa apakah alamat email masih tersedia (belum terdaftar)
     */
    public function cekEmailTersedia(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM pengguna WHERE LOWER(email) = LOWER(:email)";
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':email' => trim($email)]);

        return ((int) $pernyataan->fetchColumn()) === 0;
    }

    /**
     * Menyimpan data pendaftaran akun baru dengan role default Donatur
     */
    public function simpanAkunBaru(string $email, string $password, string $role = 'Donatur'): int
    {
        if (!in_array($role, self::DAFTAR_ROLE, true)) {
            $role = 'Donatur';
        }

        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO pengguna (email, password, role) VALUES (:email, :password, :role)";
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([
            ':email'    => strtolower(trim($email)),
            ':password' => $hashPassword,
            ':role'     => $role,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Memverifikasi email dan password pengguna saat proses login
     */
    public function verifikasiKredensial(string $email, string $password): ?array
    {
        $pengguna = $this->ambilBerdasarkanEmail($email);
        if ($pengguna === null) {
            return null;
        }

        if (!password_verify($password, (string) $pengguna['password'])) {
            return null;
        }

        // Hapus hash password sebelum data dikembalikan demi keamanan
        unset($pengguna['password']);

        return $pengguna;
    }

    /**
     * Mengambil satu baris data pengguna berdasarkan alamat email
     */
    public function ambilBerdasarkanEmail(string $email): ?array
    {
        $sql = "SELECT id_user, email, password, role, status_akun, suspend_sampai FROM pengguna WHERE LOWER(email) = LOWER(:email) LIMIT 1";
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':email' => trim($email)]);
        $hasil = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return $hasil !== false ? $hasil : null;
    }

    /**
     * Mengambil profil akun pengguna berdasarkan ID
     */
    public function ambilBerdasarkanId(int $idUser): ?array
    {
        $sql = "SELECT id_user, email, role, status_akun, suspend_sampai FROM pengguna WHERE id_user = :id_user LIMIT 1";
        $pernyataan = $this->pdo->prepare($sql);
        $pernyataan->execute([':id_user' => $idUser]);
        $hasil = $pernyataan->fetch(PDO::FETCH_ASSOC);

        return $hasil !== false ? $hasil : null;
    }

    /**
     * Memperbarui peran (role) akun pengguna
     */
    public function ubahRole(int $idUser, string $roleBaru): bool
    {
        if (!in_array($roleBaru, self::DAFTAR_ROLE, true)) {
            return false;
        }

        $sql = "UPDATE pengguna SET role = :role WHERE id_user = :id_user";
        $pernyataan = $this->pdo->prepare($sql);

        return $pernyataan->execute([
            ':role'    => $roleBaru,
            ':id_user' => $idUser,
        ]);
    }

    /**
     * Membekukan akun fundraiser hingga tanggal tertentu akibat keterlambatan LPJ (SDD 4.1.4)
     */
    public function bekukanAkun(int $idUser, string $suspendSampai): bool
    {
        $sql = "UPDATE pengguna
                SET status_akun = 'Dibekukan', suspend_sampai = :suspend_sampai
                WHERE id_user = :id_user";
        $pernyataan = $this->pdo->prepare($sql);

        return $pernyataan->execute([
            ':suspend_sampai' => $suspendSampai,
            ':id_user'        => $idUser,
        ]);
    }

    /**
     * Mengaktifkan kembali akun pengguna setelah masa pembekuan dan kewajiban LPJ terpenuhi
     */
    public function aktifkanAkun(int $idUser): bool
    {
        $sql = "UPDATE pengguna
                SET status_akun = 'Aktif', suspend_sampai = NULL
                WHERE id_user = :id_user";
        $pernyataan = $this->pdo->prepare($sql);

        return $pernyataan->execute([':id_user' => $idUser]);
    }
}
