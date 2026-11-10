<?php
$judulHalaman = 'Dashboard Donatur - PeduliFund';
require __DIR__ . '/layout/header.php';
$pengguna = penggunaAktif();
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <div class="pf-modul-judul mb-1">PORTAL DONATUR</div>
                <h1 class="pf-h1">Dashboard Donatur</h1>
                <p class="pf-subjudul mb-0 mt-1">
                    Selamat datang, <strong><?= e($pengguna['email'] ?? 'Donatur') ?></strong>! Pantau kebaikan dan donasi Anda di sini.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn-pf-cta" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">
                    Lihat Campaign
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <span class="pf-metrik-label">Total Donasi Diberikan</span>
                    <span class="pf-metrik-nilai text-success mt-1">Rp 0</span>
                    <span class="pf-metrik-sub">Akumulasi donasi tersalurkan</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <span class="pf-metrik-label">Campaign Didukung</span>
                    <span class="pf-metrik-nilai mt-1">0</span>
                    <span class="pf-metrik-sub">Program kebaikan yang diikuti</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <span class="pf-metrik-label">Donasi Terakhir</span>
                    <span class="pf-metrik-nilai fs-4 mt-1">-</span>
                    <span class="pf-metrik-sub">Belum ada donasi keluar</span>
                </div>
            </div>
        </div>

        <div class="pf-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0" style="color: var(--green-deep);">Riwayat Donasi Saya</h2>
            </div>
            <div class="text-center py-5 text-muted">
                <p class="mb-0">Anda belum melakukan donasi ke program mana pun.</p>
                <a href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php" class="btn-pf-cta mt-3">
                    Mulai Salurkan Donasi
                </a>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
