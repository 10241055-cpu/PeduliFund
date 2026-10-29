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
 * Kembalikan kelas badge Bootstrap sesuai status campaign
 * Pending => 'text-bg-warning', Aktif => 'text-bg-success', Ditolak => 'text-bg-danger', Selesai => 'text-bg-primary'
 */
function kelasBadgeStatus($status): string
{
    $kelas = [
        'Pending' => 'text-bg-warning',
        'Aktif'   => 'text-bg-success',
        'Ditolak' => 'text-bg-danger',
        'Selesai' => 'text-bg-primary',
    ];

    return $kelas[$status] ?? 'text-bg-secondary';
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