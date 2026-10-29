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

// Helper sanitasi output HTML (jika belum ada)
if (!function_exists('e')) {
    function e($teks): string {
        return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
    }
}

// Helper format rupiah (jika belum ada)
if (!function_exists('rupiah')) {
    function rupiah($angka): string {
        if (function_exists('formatRupiah')) {
            return formatRupiah($angka);
        }
        return 'Rp ' . number_format((float) $angka, 0, ',', '.');
    }
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

// Set judul halaman untuk header
$judul_halaman = 'Daftar Campaign';
$judulHalaman = $judul_halaman; // Kompatibilitas dengan layout views kelompok

// Ambil kata kunci pencarian jika ada
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

// Deteksi nama tabel (campaigns sesuai tugas, atau campaign sesuai database)
$namaTabel = 'campaigns';
$kolomUrut = 'created_at';
try {
    $pdo->query("SELECT 1 FROM campaigns LIMIT 1");
} catch (Exception $e) {
    $namaTabel = 'campaign';
    $kolomUrut = 'id_campaign';
}

// Query data campaign dengan prepared statement
if ($cari !== '') {
    // Cari judul menggunakan LIKE dan urutkan dari yang terbaru
    $sql = "SELECT * FROM {$namaTabel} WHERE judul LIKE :cari ORDER BY {$kolomUrut} DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cari' => '%' . $cari . '%']);
} else {
    // Ambil semua data dan urutkan dari yang terbaru
    $sql = "SELECT * FROM {$namaTabel} ORDER BY {$kolomUrut} DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
$campaigns = $stmt->fetchAll();

// Panggil header tampilan (mendukung includes/header.php atau views/layout/header.php)
if (file_exists(__DIR__ . '/../includes/header.php') && filesize(__DIR__ . '/../includes/header.php') > 0) {
    require __DIR__ . '/../includes/header.php';
} elseif (file_exists(__DIR__ . '/../views/layout/header.php')) {
    require __DIR__ . '/../views/layout/header.php';
} else {
    // Fallback HTML minimal jika header belum dibuat teman
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>' . e($judul_halaman) . '</title>';
    if (file_exists(__DIR__ . '/../assets/css/style.css')) {
        echo '<link rel="stylesheet" href="' . e(BASE_URL) . '/assets/css/style.css">';
    }
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body>';
}
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Daftar Campaign</h2>
        <a href="<?= e(BASE_URL) ?>/campaigns/create.php" class="btn btn-utama">+ Buat Campaign</a>
    </div>

    <!-- Form Pencarian -->
    <form action="<?= e(BASE_URL) ?>/campaigns/index.php" method="GET" class="form" style="display: flex; gap: 8px; margin-bottom: 20px;">
        <input type="text" name="cari" placeholder="Cari judul campaign..." value="<?= e($cari) ?>" style="flex: 1; padding: 8px;">
        <button type="submit" class="btn btn-utama">Cari</button>
        <?php if ($cari !== ''): ?>
            <a href="<?= e(BASE_URL) ?>/campaigns/index.php" class="btn">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Tabel Daftar Campaign -->
    <table class="tabel">
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Target</th>
                <th>Terkumpul</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($campaigns)): ?>
                <?php $no = 1; ?>
                <?php foreach ($campaigns as $row): ?>
                    <?php
                    // Ambil ID campaign (kompatibel id atau id_campaign)
                    $idCampaign = $row['id'] ?? $row['id_campaign'] ?? 0;

                    // Ambil kategori (kompatibel kategori atau jenis)
                    $kategoriCampaign = $row['kategori'] ?? $row['jenis'] ?? '-';

                    // Ambil status (kompatibel status atau status_campaign)
                    $statusCampaign = $row['status'] ?? $row['status_campaign'] ?? 'pending';

                    // Hitung persentase terkumpul (tangani pembagian jika target 0)
                    $target = (float) $row['target_dana'];
                    $terkumpul = (float) $row['dana_terkumpul'];
                    $persen = ($target > 0) ? min(100, (int) round(($terkumpul / $target) * 100)) : 0;

                    // Format tanggal deadline
                    $deadline = date('d M Y', strtotime($row['deadline']));

                    // Label status dengan huruf kapital di awal
                    $status_label = ucfirst(strtolower($statusCampaign));
                    $status_slug = strtolower($statusCampaign);
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= e($row['judul']) ?></td>
                        <td><?= e($kategoriCampaign) ?></td>
                        <td><?= e(rupiah($row['target_dana'])) ?></td>
                        <td>
                            <div><?= e(rupiah($row['dana_terkumpul'])) ?></div>
                            <div class="progress" style="margin-top: 5px;">
                                <div class="progress-isi" style="width: <?= $persen ?>%;"></div>
                            </div>
                            <small><?= $persen ?>%</small>
                        </td>
                        <td><?= e($deadline) ?></td>
                        <td>
                            <span class="badge badge-<?= e($status_slug) ?>">
                                <?= e($status_label) ?>
                            </span>
                        </td>
                        <td>
                            <div class="aksi">
                                <a href="<?= e(BASE_URL) ?>/campaigns/edit.php?id=<?= e($idCampaign) ?>" class="btn">Edit</a>
                                <form action="<?= e(BASE_URL) ?>/campaigns/delete.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus campaign ini?');" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= e($idCampaign) ?>">
                                    <button type="submit" class="btn btn-bahaya">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        <?php if ($cari !== ''): ?>
                            Tidak ada hasil untuk "<?= e($cari) ?>".
                        <?php else: ?>
                            Belum ada campaign.
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Panggil footer tampilan (mendukung includes/footer.php atau views/layout/footer.php)
if (file_exists(__DIR__ . '/../includes/footer.php') && filesize(__DIR__ . '/../includes/footer.php') > 0) {
    require __DIR__ . '/../includes/footer.php';
} elseif (file_exists(__DIR__ . '/../views/layout/footer.php')) {
    require __DIR__ . '/../views/layout/footer.php';
} else {
    echo '</body></html>';
}
?>
