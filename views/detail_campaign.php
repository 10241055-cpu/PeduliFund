<?php
$judulHalaman = 'Detail Campaign';
require __DIR__ . '/layout/header.php';

$persen = hitungPersen($campaign['dana_terkumpul'], $campaign['target_dana']);
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <a class="btn-pf-outline mb-3" href="<?= e(BASE_URL) ?>/daftar_campaign.php">Kembali</a>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="pf-card p-4 h-100">
                    <?php if (!empty($campaign['gambar_campaign'])): ?>
                        <img class="rounded-3 mb-3 w-100" style="max-height: 320px; object-fit: cover;" src="<?= e(URL_UPLOAD_GAMBAR . $campaign['gambar_campaign']) ?>" alt="Gambar campaign <?= e($campaign['judul']) ?>">
                    <?php else: ?>
                        <div class="rounded-3 mb-3 d-flex align-items-center justify-content-center" style="height: 200px; background: var(--green-pale);">
                            <span class="fw-bold" style="color: var(--green-primary);"><?= e($campaign['jenis']) ?></span>
                        </div>
                    <?php endif; ?>
                    <span class="pf-badge <?= e($sudahBerakhir ? 'pf-badge--netral' : 'pf-badge--aktif') ?>"><span class="pf-badge-pil"><?= e($sudahBerakhir ? 'Ditutup' : 'Aktif') ?></span></span>
                    <h1 class="pf-h1 mt-2"><?= e($campaign['judul']) ?></h1>
                    <div class="small mb-3" style="color: var(--text-muted);"><?= e($campaign['jenis']) ?></div>
                    <p class="mb-0" style="color: var(--text-body);"><?= e($campaign['deskripsi']) ?></p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="pf-card p-4 mb-3">
                    <p class="pf-metrik-label">Dana Terkumpul</p>
                    <p class="pf-metrik-nilai"><?= e(formatRupiah($campaign['dana_terkumpul'])) ?></p>
                    <div class="progress" role="progressbar" aria-label="Progress dana" aria-valuenow="<?= e((string) $persen) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: <?= e((string) $persen) ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="pf-teks-bantu pf-angka"><?= e((string) $persen) ?>% dari target</small>
                        <small class="pf-teks-bantu pf-angka">Target <?= e(formatRupiah($campaign['target_dana'])) ?></small>
                    </div>
                    <small class="pf-teks-bantu d-block mt-1">Berakhir <?= e(formatTanggal($campaign['deadline'])) ?><?= $sudahBerakhir ? '' : ' — tersisa ' . e((string) $sisaHari) . ' hari lagi' ?></small>
                    <?php if ($sudahBerakhir): ?>
                        <button class="btn-pf-outline w-100 mt-3" disabled title="Campaign sudah ditutup">Donasi Sekarang</button>
                    <?php else: ?>
                        <a class="btn-pf-cta w-100 mt-3 justify-content-center" href="<?= e(BASE_URL) ?>/donasi.php?id=<?= e((string) $campaign['id_campaign']) ?>">Donasi Sekarang</a>
                    <?php endif; ?>
                </div>

                <div class="pf-card p-4">
                    <h2 class="h6 fw-bold mb-3">Donatur Terbaru</h2>
                    <?php if ($donaturTerbaru === []): ?>
                        <p class="pf-teks-bantu mb-0">Jadilah donatur pertama campaign ini.</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <?php foreach ($donaturTerbaru as $donasi): ?>
                                <li class="d-flex justify-content-between align-items-center">
                                    <span class="small text-truncate me-2"><?= e($donasi['email_donatur']) ?></span>
                                    <span class="pf-angka fw-semibold"><?= e(formatRupiah($donasi['nominal'])) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
