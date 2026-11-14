<?php
// UI-012: Verifikasi akun fundraiser oleh admin — daftar pengajuan Menunggu, pratinjau dokumen, dan keputusan.
$judulHalaman = 'Verifikasi Akun - PeduliFund';
require __DIR__ . '/layout/header.php';
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="mb-4">
            <h1 class="pf-h1">Verifikasi Akun Fundraiser</h1>
            <p class="pf-subjudul mb-0 mt-1">Tinjau dokumen pengajuan, lalu setujui agar peran akun naik menjadi Fundraiser atau tolak dengan alasan.</p>
        </div>

        <?php if ($daftarMenunggu === []): ?>
            <div class="pf-card p-4 text-center">
                <p class="mb-0">Tidak ada pengajuan verifikasi akun yang menunggu.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($daftarMenunggu as $pengajuan): ?>
                    <?php $idVerifikasi = (int) $pengajuan['id_verifikasi']; ?>
                    <div class="pf-card p-4">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h2 class="h5 fw-bold mb-1 pf-teks-judul"><?= e($pengajuan['nama_lengkap']) ?></h2>
                                    <span class="pf-badge <?= e(kelasBadgeStatus($pengajuan['status_dokumen'])) ?>"><span class="pf-badge-pil"><?= e($pengajuan['status_dokumen']) ?></span></span>
                                </div>
                                <p class="small text-muted mb-3">
                                    <?= e($pengajuan['email']) ?> &bull; Pengajuan #<?= e((string) $idVerifikasi) ?>
                                    <?php if (!empty($pengajuan['tanggal_pengajuan'])): ?>
                                        &bull; <?= e(formatTanggal($pengajuan['tanggal_pengajuan'])) ?>
                                    <?php endif; ?>
                                </p>
                                <div class="row g-2 small mb-3">
                                    <div class="col-md-4">
                                        <span class="pf-metrik-label d-block">Jenis Fundraiser</span>
                                        <span class="fw-semibold"><?= e($pengajuan['jenis_fundraiser']) ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="pf-metrik-label d-block">Nomor Identitas</span>
                                        <span class="pf-angka"><?= e($pengajuan['nomor_identitas']) ?></span>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="pf-metrik-label d-block">Nomor Telepon</span>
                                        <span class="pf-angka"><?= e($pengajuan['nomor_telepon']) ?></span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn-pf-outline py-1 px-3" style="font-size: 11px;" target="_blank" rel="noopener"
                                       href="<?= e(BASE_URL) ?>/unduh_dokumen_verifikasi.php?id_verifikasi=<?= e((string) $idVerifikasi) ?>">Lihat Dokumen Identitas</a>
                                    <?php if (!empty($pengajuan['dokumen_pendukung'])): ?>
                                        <a class="btn-pf-outline py-1 px-3" style="font-size: 11px;" target="_blank" rel="noopener"
                                           href="<?= e(BASE_URL) ?>/unduh_dokumen_verifikasi.php?id_verifikasi=<?= e((string) $idVerifikasi) ?>&jenis=pendukung">Lihat Dokumen Pendukung</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-5 d-flex flex-column">
                                <form method="post" action="<?= e(BASE_URL) ?>/verifikasi_akun.php" class="mt-auto">
                                    <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                                    <input type="hidden" name="jenis_verifikasi" value="akun">
                                    <input type="hidden" name="id_verifikasi" value="<?= e((string) $idVerifikasi) ?>">
                                    <fieldset class="mb-2">
                                        <legend class="pf-metrik-label mb-1">Keputusan Verifikasi</legend>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="keputusan" id="keputusan-setujui-<?= e((string) $idVerifikasi) ?>" value="setujui" required>
                                                <label class="form-check-label" for="keputusan-setujui-<?= e((string) $idVerifikasi) ?>">Setujui</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="keputusan" id="keputusan-tolak-<?= e((string) $idVerifikasi) ?>" value="tolak">
                                                <label class="form-check-label" for="keputusan-tolak-<?= e((string) $idVerifikasi) ?>">Tolak</label>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <label class="form-label small mb-1" for="alasan-<?= e((string) $idVerifikasi) ?>">Alasan penolakan (wajib bila menolak)</label>
                                    <textarea class="form-control" id="alasan-<?= e((string) $idVerifikasi) ?>" name="alasan_penolakan" rows="2"
                                              placeholder="Contoh: foto identitas tidak terbaca"></textarea>
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
