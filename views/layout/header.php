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
        <a class="pf-logo" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php" aria-label="Beranda PeduliFund">
            <span class="pf-wordmark">Peduli<span class="pf-fund">Fund</span><span class="pf-titik">.</span></span>
        </a>
        <nav class="pf-menu" aria-label="Menu utama">
            <a class="pf-menu-item pf-menu-item-aktif" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Dashboard</a>
        </nav>
    </header>
    <main class="pf-main">
    <?php if ($pesan !== null): ?>
        <div class="alert alert-<?= e($pesan['jenis']) ?> alert-dismissible fade show" role="alert">
            <?= e($pesan['teks']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
