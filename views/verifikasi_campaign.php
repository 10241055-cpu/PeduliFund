<?php
// UI-013: Verifikasi campaign oleh admin — daftar Pending, pratinjau dokumen, dan keputusan.
$judulHalaman = 'Verifikasi Campaign - PeduliFund';
require __DIR__ . '/layout/header.php';
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="mb-4">
            <h1 class="pf-h1">Verifikasi Campaign</h1>
            <p class="pf-subjudul mb-0 mt-1">Tinjau dokumen campaign baru, lalu setujui agar tayang publik atau tolak dengan alasan.</p>
        </div>

        <?php if ($daftarMenunggu === []): ?>
            <div class="pf-card p-4 text-center">
                <p class="mb-0">Tidak ada campaign yang menunggu verifikasi.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($daftarMenunggu as $campaign): ?>
                    <?php $idCampaign = (int) $campaign['id_campaign']; ?>
                    <div class="pf-card p-4">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h2 class="h5 fw-bold mb-1 pf-teks-judul"><?= e($campaign['judul']) ?></h2>
                                    <span class="pf-badge <?= e(kelasBadgeStatus($campaign['status_campaign'])) ?>"><span class="pf-badge-pil"><?= e($campaign['status_campaign']) ?></span></span>
                                </div>
                                <p class="small text-muted mb-3">
                                    <?= e($campaign['jenis']) ?> &bull; Campaign #<?= e((string) $idCampaign) ?> &bull; Penggalang: <?= e($campaign['email']) ?>
                                </p>
                                <p class="mb-3"><?= e($campaign['deskripsi']) ?></p>
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <span class="pf-metrik-label d-block">Target Dana</span>
                                        <span class="pf-angka fw-semibold"><?= e(formatRupiah($campaign['target_dana'])) ?></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="pf-metrik-label d-block">Deadline</span>
                                        <span class="pf-angka fw-semibold"><?= e(formatTanggal($campaign['deadline'])) ?></span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="pf-metrik-label d-block mb-1">Dokumen Campaign</span>
                                    <a class="btn-pf-outline py-1 px-3" style="font-size: 11px;" target="_blank" rel="noopener"
                                       href="<?= e(BASE_URL) ?>/unduh_dokumen_campaign.php?id_campaign=<?= e((string) $idCampaign) ?>">Lihat Dokumen</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <?php if (!empty($campaign['gambar_campaign'])): ?>
                                    <span class="pf-metrik-label d-block mb-1">Gambar Campaign</span>
                                    <img class="rounded-3 border w-100 mb-3" style="max-height: 180px; object-fit: cover;"
                                         src="<?= e(BASE_URL) ?>/unduh_dokumen_campaign.php?id_campaign=<?= e((string) $idCampaign) ?>&jenis=pendukung"
                                         alt="Gambar campaign <?= e($campaign['judul']) ?>">
                                <?php endif; ?>
                                <form method="post" action="<?= e(BASE_URL) ?>/verifikasi_campaign.php" class="mt-auto">
                                    <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                                    <input type="hidden" name="id_campaign" value="<?= e((string) $idCampaign) ?>">
                                    <fieldset class="mb-2">
                                        <legend class="pf-metrik-label mb-1">Keputusan Verifikasi</legend>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="keputusan" id="keputusan-setujui-<?= e((string) $idCampaign) ?>" value="setujui" required>
                                                <label class="form-check-label" for="keputusan-setujui-<?= e((string) $idCampaign) ?>">Setujui &amp; tayangkan</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="keputusan" id="keputusan-tolak-<?= e((string) $idCampaign) ?>" value="tolak">
                                                <label class="form-check-label" for="keputusan-tolak-<?= e((string) $idCampaign) ?>">Tolak</label>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <label class="form-label small mb-1" for="alasan-<?= e((string) $idCampaign) ?>">Alasan penolakan (wajib bila menolak)</label>
                                    <textarea class="form-control" id="alasan-<?= e((string) $idCampaign) ?>" name="alasan_penolakan" rows="2"
                                              placeholder="Contoh: dokumen tidak sesuai dengan deskripsi campaign"></textarea>
                                    <button class="btn-pf-cta w-100 justify-content-center mt-3" type="submit">Kirim Keputusan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
