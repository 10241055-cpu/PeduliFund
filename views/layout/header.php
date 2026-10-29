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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(BASE_URL) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php"><?= e(APP_NAME) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuUtama" aria-controls="menuUtama" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuUtama">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Dashboard</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?php if ($pesan !== null): ?>
        <div class="alert alert-<?= e($pesan['jenis']) ?> alert-dismissible fade show" role="alert">
            <?= e($pesan['teks']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>