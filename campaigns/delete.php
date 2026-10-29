<?php
// Hubungkan ke file koneksi dan konfigurasi
require_once __DIR__ . '/../config/koneksi.php';

// Fallback jika file konfigurasi atau helper belum dimuat
if (file_exists(__DIR__ . '/../config/konfigurasi.php')) {
    require_once __DIR__ . '/../config/konfigurasi.php';
}
if (file_exists(__DIR__ . '/../app/helpers/fungsi.php')) {
    require_once __DIR__ . '/../app/helpers/fungsi.php';
}

// Pastikan session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan konstanta BASE_URL tersedia
if (!defined('BASE_URL')) {
    define('BASE_URL', '/P3L');
}

// Helper redirect (jika belum ada)
if (!function_exists('redirect')) {
    function redirect($path): void {
        if (function_exists('alihkan')) {
            alihkan($path);
        }
        header('Location: ' . BASE_URL . '/' . ltrim((string) $path, '/'));
        exit;
    }
}

// Pastikan hanya menerima request method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('campaigns/index.php');
}

// Validasi id harus berupa integer positif
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['pesan'] = 'Campaign tidak ditemukan.';
    if (function_exists('setPesan')) {
        setPesan('danger', 'Campaign tidak ditemukan.');
    }
    redirect('campaigns/index.php');
}

// Deteksi nama tabel & kolom primary key
$namaTabel = 'campaigns';
$kolomId = 'id';
try {
    $pdo->query("SELECT id FROM campaigns LIMIT 1");
} catch (Exception $e) {
    $namaTabel = 'campaign';
    $kolomId = 'id_campaign';
}

// Cek apakah data campaign ada di database
$stmt = $pdo->prepare("SELECT {$kolomId} FROM {$namaTabel} WHERE {$kolomId} = :id");
$stmt->execute([':id' => $id]);
$campaign = $stmt->fetch();

if ($campaign) {
    // Hapus campaign dengan prepared statement
    $deleteStmt = $pdo->prepare("DELETE FROM {$namaTabel} WHERE {$kolomId} = :id");
    $deleteStmt->execute([':id' => $id]);
    $_SESSION['pesan'] = 'Campaign berhasil dihapus.';
    if (function_exists('setPesan')) {
        setPesan('success', 'Campaign berhasil dihapus.');
    }
} else {
    // Jika data tidak ditemukan
    $_SESSION['pesan'] = 'Campaign tidak ditemukan.';
    if (function_exists('setPesan')) {
        setPesan('danger', 'Campaign tidak ditemukan.');
    }
}

// Redirect kembali ke halaman daftar campaign
redirect('campaigns/index.php');
