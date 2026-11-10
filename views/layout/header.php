<?php
$judulHalaman = isset($judulHalaman) ? $judulHalaman : APP_NAME;
$pesan = ambilPesan();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judulHalaman) ?></title>
    <link href="<?= e(BASE_URL) ?>/assets/vendor/bootstrap-5.3.3/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_URL) ?>/assets/css/pedulifund-theme.css" rel="stylesheet">
</head>
<body>
<div class="pf-app-window">
    <header class="pf-navbar">
        <a class="pf-logo" href="<?= e(BASE_URL) ?>/index.php" aria-label="Beranda PeduliFund">
            <span class="pf-wordmark">Peduli<span class="pf-fund">Fund</span><span class="pf-titik">.</span></span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <nav class="pf-menu" aria-label="Menu utama">
                <?php if (sudahLogin()): ?>
                    <?php
                    $peran = peranPenggunaAktif();
                    $urlDashboard = BASE_URL . '/dashboard_fundraiser.php';
                    if ($peran === 'Donatur') {
                        $urlDashboard = BASE_URL . '/dashboard_donatur.php';
                    } elseif ($peran === 'Admin') {
                        $urlDashboard = BASE_URL . '/dashboard_admin.php';
                    }
                    ?>
                    <a class="pf-menu-item pf-menu-item-aktif" href="<?= e($urlDashboard) ?>">Dashboard</a>
                <?php else: ?>
                    <a class="pf-menu-item <?= (isset($judulHalaman) && strpos($judulHalaman, 'Masuk') !== false) ? 'pf-menu-item-aktif' : '' ?>" href="<?= e(BASE_URL) ?>/login.php">Masuk</a>
                <?php endif; ?>
            </nav>

            <?php if (sudahLogin()): ?>
                <div class="d-none d-sm-flex align-items-center gap-2 ps-2 border-start">
                    <span class="pf-badge pf-badge--aktif">
                        <span class="pf-badge-pil"><?= e(peranPenggunaAktif() ?? 'User') ?></span>
                    </span>
                    <span class="small fw-semibold text-truncate" style="max-width: 140px; color: var(--text-body);">
                        <?= e(penggunaAktif()['email'] ?? '') ?>
                    </span>
                </div>
                <a class="btn-pf-outline py-1 px-3 fs-6" href="<?= e(BASE_URL) ?>/logout.php">Keluar</a>
            <?php else: ?>
                <a class="btn-pf-cta py-1 px-3 fs-6" href="<?= e(BASE_URL) ?>/signup.php">Daftar</a>
            <?php endif; ?>
        </div>
    </header>
    <main class="pf-main">
    <?php if ($pesan !== null): ?>
        <div class="alert alert-<?= e($pesan['jenis']) ?> alert-dismissible fade show" role="alert">
            <?= e($pesan['teks']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
