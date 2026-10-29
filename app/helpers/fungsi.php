<?php

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function formatRupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function formatTanggal($tanggal): string
{
    $waktu = strtotime((string) $tanggal);
    if ($waktu === false) {
        return '';
    }

    $namaBulan = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    return date('j', $waktu) . ' ' . $namaBulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

function hitungPersen($terkumpul, $target): int
{
    $target = (float) $target;
    if ($target <= 0) {
        return 0;
    }

    return (int) min(100, max(0, round(((float) $terkumpul / $target) * 100)));
}

function kelasBadgeStatus($status): string
{
    $kelasStatus = [
        'Pending' => 'text-bg-warning',
        'Aktif' => 'text-bg-success',
        'Ditolak' => 'text-bg-danger',
        'Selesai' => 'text-bg-primary',
    ];

    return $kelasStatus[$status] ?? 'text-bg-secondary';
}

function alihkan($halaman): void
{
    header('Location: ' . BASE_URL . '/' . ltrim((string) $halaman, '/'));
    exit;
}

function setPesan($jenis, $teks): void
{
    $_SESSION['pesan'] = [
        'jenis' => $jenis,
        'teks' => $teks,
    ];
}

function ambilPesan(): ?array
{
    if (!isset($_SESSION['pesan'])) {
        return null;
    }

    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']);

    return $pesan;
}

function idPenggunaAktif(): int
{
    return ID_FUNDRAISER_SEMENTARA;
}