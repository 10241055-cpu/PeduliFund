<?php
$judulHalaman = 'Dashboard Donatur - PeduliFund';
require __DIR__ . '/layout/header.php';
$pengguna = penggunaAktif();
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h1 class="pf-h1">Dashboard Donatur</h1>
                <p class="pf-subjudul mb-0 mt-1">
                    Selamat datang, <strong><?= e($pengguna['email'] ?? 'Donatur') ?></strong>! Pantau kebaikan dan donasi Anda di sini.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/ajukan_verifikasi.php">
                    Jadi Penggalang Dana
                </a>
                <a class="btn-pf-cta" href="<?= e(BASE_URL) ?>/daftar_campaign.php">
                    Lihat Campaign
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <p class="pf-metrik-label">Total Donasi Diberikan</p>
                    <p class="pf-metrik-nilai"><?= e(formatRupiah($totalDonasi)) ?></p>
                    <span class="pf-metrik-sub">Akumulasi donasi tersalurkan</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <p class="pf-metrik-label">Campaign Didukung</p>
                    <p class="pf-metrik-nilai"><?= e((string) $jumlahCampaign) ?></p>
                    <span class="pf-metrik-sub">Program kebaikan yang diikuti</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <p class="pf-metrik-label">Donasi Terakhir</p>
                    <p class="pf-metrik-nilai"><?= e($donasiTerakhir !== null ? formatRupiah($donasiTerakhir['nominal']) : '-') ?></p>
                    <span class="pf-metrik-sub"><?= e($donasiTerakhir !== null ? formatTanggal($donasiTerakhir['tanggal_pembayaran']) : 'Belum ada donasi keluar') ?></span>
                </div>
            </div>
        </div>

        <div class="pf-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0" style="color: var(--green-deep);">Riwayat Donasi Saya</h2>
            </div>
            <?php if ($riwayatDonasi === []): ?>
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">Anda belum melakukan donasi ke program mana pun.</p>
                    <a href="<?= e(BASE_URL) ?>/daftar_campaign.php" class="btn-pf-cta mt-3">
                        Mulai Salurkan Donasi
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Campaign</th>
                                <th scope="col">Nominal</th>
                                <th scope="col">Metode</th>
                                <th scope="col">Status</th>
                                <th scope="col">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayatDonasi as $donasi): ?>
                                <tr>
                                    <td class="fw-semibold pf-teks-judul"><?= e($donasi['judul_campaign']) ?></td>
                                    <td class="pf-angka"><?= e(formatRupiah($donasi['nominal'])) ?></td>
                                    <td><?= e($donasi['metode_pembayaran']) ?></td>
                                    <td>
                                        <span class="pf-badge <?= e(kelasBadgeStatus($donasi['status_pembayaran'] === 'Berhasil' ? 'Aktif' : 'Ditolak')) ?>">
                                            <span class="pf-badge-pil"><?= e($donasi['status_pembayaran']) ?></span>
                                        </span>
                                    </td>
                                    <td class="pf-angka"><?= e(formatTanggal($donasi['tanggal_pembayaran'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
