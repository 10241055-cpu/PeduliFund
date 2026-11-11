<?php
$judulHalaman = 'Dashboard Admin - PeduliFund';
require __DIR__ . '/layout/header.php';
$pengguna = penggunaAktif();
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h1 class="pf-h1">Dashboard Admin</h1>
                <p class="pf-subjudul mb-0 mt-1">
                    Selamat datang, <strong><?= e($pengguna['email'] ?? 'Admin') ?></strong>! Tinjau dan kelola verifikasi sistem.
                </p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <span class="pf-metrik-label">Akun Menunggu</span>
                    <span class="pf-metrik-nilai mt-1">0</span>
                    <span class="pf-metrik-sub">Pengajuan verifikasi fundraiser</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <span class="pf-metrik-label">Campaign Menunggu</span>
                    <span class="pf-metrik-nilai mt-1" style="color: var(--pink-primary);"><?= e((string) ($jumlahCampaignMenunggu ?? 0)) ?></span>
                    <span class="pf-metrik-sub">Menunggu persetujuan admin</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-3 p-md-4">
                    <span class="pf-metrik-label">Pencairan Menunggu</span>
                    <span class="pf-metrik-nilai mt-1" style="color: var(--pink-primary);"><?= e((string) ($ringkasanPencairan['total_antrean'] ?? 0)) ?></span>
                    <span class="pf-metrik-sub">
                        <?= e((string) ($ringkasanPencairan['pencairan_menunggu'] ?? 0)) ?> pengajuan dana &bull;
                        <?= e((string) ($ringkasanPencairan['lpj_menunggu'] ?? 0)) ?> audit LPJ
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-card p-4 text-center">
                    <h3 class="h6 fw-bold mb-2">Verifikasi Akun</h3>
                    <p class="small text-muted mb-3">Tinjau kelengkapan dokumen pengajuan fundraiser</p>
                    <button class="btn-pf-outline w-100" disabled>Verifikasi Akun</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-4 text-center">
                    <h3 class="h6 fw-bold mb-2">Verifikasi Campaign</h3>
                    <p class="small text-muted mb-3">Tinjau dan setujui campaign baru dari fundraiser</p>
                    <a href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php" class="btn-pf-cta w-100 justify-content-center">Tinjau Campaign</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-4 text-center">
                    <h3 class="h6 fw-bold mb-2">Review Pencairan</h3>
                    <p class="small text-muted mb-3">Tinjau permohonan pencairan donasi dan audit LPJ</p>
                    <a href="<?= e(BASE_URL) ?>/review_pencairan.php" class="btn-pf-cta w-100 justify-content-center">Review Pencairan</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
