<?php
$judulHalaman = 'Daftar Campaign';
require __DIR__ . '/layout/header.php';
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h1 class="pf-h1">Daftar Campaign</h1>
                <p class="pf-subjudul mb-0 mt-1">Temukan campaign aktif yang membutuhkan dukungan Anda.</p>
            </div>
            <form class="row g-2 align-items-end pf-filter" method="get" action="<?= e(BASE_URL) ?>/daftar_campaign.php">
                <div class="col-auto">
                    <label class="visually-hidden" for="cari">Cari judul campaign</label>
                    <input class="form-control" type="search" id="cari" name="cari" placeholder="Cari judul" value="<?= e($cari) ?>">
                </div>
                <div class="col-auto">
                    <label class="visually-hidden" for="jenis">Filter jenis campaign</label>
                    <select class="form-select" id="jenis" name="jenis">
                        <option value="">Semua jenis</option>
                        <?php foreach ($daftarJenis as $jenisItem): ?>
                            <option value="<?= e($jenisItem) ?>" <?= $jenis === $jenisItem ? 'selected' : '' ?>><?= e($jenisItem) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn" type="submit">Terapkan</button>
                </div>
            </form>
        </div>

        <?php if ($daftarCampaign === []): ?>
            <div class="pf-card p-5 text-center">
                <p class="pf-subjudul mb-0">Tidak ada campaign aktif yang sesuai pencarian Anda.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($daftarCampaign as $campaign): ?>
                    <?php $persen = hitungPersen($campaign['dana_terkumpul'], $campaign['target_dana']); ?>
                    <div class="col-md-6 col-lg-4">
                        <a class="pf-card h-100 p-3 d-block text-decoration-none" href="<?= e(BASE_URL) ?>/detail_campaign.php?id=<?= e((string) $campaign['id_campaign']) ?>">
                            <?php if (!empty($campaign['gambar_campaign'])): ?>
                                <img class="rounded-3 mb-3 w-100" style="height: 150px; object-fit: cover;" src="<?= e(URL_UPLOAD_GAMBAR . $campaign['gambar_campaign']) ?>" alt="Gambar campaign <?= e($campaign['judul']) ?>">
                            <?php else: ?>
                                <div class="rounded-3 mb-3 d-flex align-items-center justify-content-center" style="height: 150px; background: var(--green-pale);">
                                    <span class="fw-bold" style="color: var(--green-primary);"><?= e($campaign['jenis']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="fw-semibold pf-teks-judul"><?= e($campaign['judul']) ?></div>
                            <div class="small mb-2" style="color: var(--text-muted);"><?= e($campaign['jenis']) ?></div>
                            <div class="progress" role="progressbar" aria-label="Progress dana" aria-valuenow="<?= e((string) $persen) ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: <?= e((string) $persen) ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="pf-angka pf-teks-bantu"><?= e(formatRupiah($campaign['dana_terkumpul'])) ?></small>
                                <small class="pf-angka pf-teks-bantu">Target <?= e(formatRupiah($campaign['target_dana'])) ?></small>
                            </div>
                            <small class="pf-teks-bantu">Berakhir <?= e(formatTanggal($campaign['deadline'])) ?></small>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
