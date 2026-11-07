<?php
// Helper fungsi-fungsi global untuk PeduliFund sesuai kesepakatan SDD

/**
 * Sanitasi string untuk output HTML aman
 */
function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke format mata uang Rupiah
 * Contoh: 1500000 -> "Rp 1.500.000"
 */
function formatRupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/**
 * Format tanggal ke format Indonesia singkat
 * Contoh: "2026-12-31" -> "31 Des 2026"
 */
function formatTanggal($tanggal): string
{
    $waktu = strtotime((string) $tanggal);
    if ($waktu === false) {
        return '';
    }

    $daftarBulan = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];

    $hari = date('j', $waktu);
    $bulan = $daftarBulan[(int) date('n', $waktu)] ?? date('M', $waktu);
    $tahun = date('Y', $waktu);

    return $hari . ' ' . $bulan . ' ' . $tahun;
}

/**
 * Hitung persentase ketercapaian target donasi (0 sampai 100, aman jika target 0)
 */
function hitungPersen($terkumpul, $target): int
{
    $target = (float) $target;
    if ($target <= 0) {
        return 0;
    }

    $persen = (int) round(((float) $terkumpul / $target) * 100);

    return (int) min(100, max(0, $persen));
}

/**
 * Kembalikan kelas badge sesuai status campaign (design.md Bagian 2.3)
 * Pending => pil krem + titik oranye, Aktif => kapsul putih + pil sage,
 * Ditolak => pil blush + teks rose, Selesai => badge sage solid
 */
function kelasBadgeStatus($status): string
{
    $kelas = [
        'Pending' => 'pf-badge--pending',
        'Aktif'   => 'pf-badge--aktif',
        'Ditolak' => 'pf-badge--ditolak',
        'Selesai' => 'pf-badge--selesai',
    ];

    return $kelas[$status] ?? 'pf-badge--netral';
}

/**
 * Alihkan ke halaman internal PeduliFund lalu exit
 */
function alihkan($halaman): void
{
    header('Location: ' . BASE_URL . '/' . ltrim((string) $halaman, '/'));
    exit;
}

/**
 * Simpan pesan flash ke session
 * Jenis: 'success' atau 'danger'
 */
function setPesan($jenis, $teks): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['pesan'] = [
        'jenis' => $jenis,
        'teks'  => $teks,
    ];
}

/**
 * Ambil pesan flash lalu hapus dari session
 */
function ambilPesan(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['pesan'])) {
        return null;
    }

    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']);

    return $pesan;
}

/**
 * Ambil ID pengguna aktif (sementara mengembalikan ID_FUNDRAISER_SEMENTARA)
 */
function idPenggunaAktif(): int
{
    return ID_FUNDRAISER_SEMENTARA;
}

/**
 * Token anti-CSRF untuk semua form POST (dibuat sekali per sesi)
 */
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Tolak POST tanpa/menyandong token anti-CSRF yang tidak cocok
 */
function verifikasiCsrf(): void
{
    if (!hash_equals(tokenCsrf(), (string) ($_POST['csrf_token'] ?? ''))) {
        setPesan('danger', 'Sesi tidak valid, silakan coba kembali.');
        alihkan('dashboard_fundraiser.php');
    }
}