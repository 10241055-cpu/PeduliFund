<?php
$judulHalaman = 'Dashboard Fundraiser';
require __DIR__ . '/layout/header.php';

$akunDibekukan = ($akunFundraiser['status_akun'] ?? 'Aktif') === 'Dibekukan';
$adaTagihanLpj = !empty($tagihanLpj);
$bisaBuatCampaign = !$akunDibekukan && !$adaTagihanLpj;
?>
<?php if ($akunDibekukan): ?>
    <div class="alert alert-danger mb-4" role="alert">
        <div class="fw-bold mb-1">Akun Fundraiser Dibekukan (Suspend 1 Bulan)</div>
        <p class="mb-0 small">
            Akun Anda sedang dibekukan<?php if (!empty($akunFundraiser['suspend_sampai'])): ?> hingga <strong><?= e(formatTanggal($akunFundraiser['suspend_sampai'])) ?></strong><?php endif; ?>
            karena keterlambatan unggah LPJ yang valid. Campaign aktif dihentikan dan pembuatan campaign baru ditutup hingga masa pembekuan selesai serta LPJ terverifikasi.
        </p>
    </div>
<?php endif; ?>

<?php if ($adaTagihanLpj): ?>
    <div class="alert alert-warning mb-4" role="alert">
        <div class="fw-bold mb-2">Kewajiban Unggah LPJ &amp; Bukti Nota</div>
        <p class="small mb-2">Selesaikan unggah dan verifikasi LPJ berikut agar Anda dapat membuka campaign baru dan terhindar dari pembekuan akun:</p>
        <div class="d-flex flex-column gap-2">
            <?php foreach ($tagihanLpj as $tagihan): ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 bg-white rounded-3 p-2 px-3">
                    <div>
                        <span class="fw-bold" style="color: var(--green-deep);"><?= e($tagihan['judul_campaign']) ?></span>
                        <span class="small ms-2 pf-angka">Batas: <strong><?= e(formatTanggal((string) $tagihan['batas_unggah_lpj'])) ?></strong></span>
                        <?php if ($tagihan['status_transparansi'] === 'Ditolak' && !empty($tagihan['alasan_penolakan'])): ?>
                            <div class="small text-danger mt-1">Alasan penolakan LPJ: <?= e($tagihan['alasan_penolakan']) ?></div>
                        <?php elseif (!empty($tagihan['dokumen_lpj']) && $tagihan['status_transparansi'] === 'Menunggu'): ?>
                            <div class="small text-muted mt-1">LPJ sudah diunggah dan sedang diaudit oleh admin.</div>
                        <?php endif; ?>
                    </div>
                    <a class="btn-pf-cta btn-pf-tabel" href="<?= e(BASE_URL) ?>/unggah_lpj.php?id_pencairan=<?= e((string) $tagihan['id_pencairan']) ?>">
                        <?= $tagihan['status_transparansi'] === 'Ditolak' ? 'Unggah Ulang LPJ' : (!empty($tagihan['dokumen_lpj']) ? 'Lihat Status LPJ' : 'Unggah LPJ') ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="pf-h1">Dashboard Fundraiser</h1>
                <p class="pf-subjudul mb-0 mt-1">Ringkasan campaign dan dana yang terkumpul.</p>
            </div>
            <?php if ($bisaBuatCampaign): ?>
                <a class="btn-pf-cta" href="<?= e(BASE_URL) ?>/buat_campaign.php">Buat Campaign</a>
            <?php else: ?>
                <button class="btn-pf-outline" type="button" disabled title="Selesaikan kewajiban LPJ / masa pembekuan terlebih dahulu">Buat Campaign</button>
            <?php endif; ?>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-card h-100 p-4">
                    <p class="pf-metrik-label">Jumlah Campaign</p>
                    <p class="pf-metrik-nilai"><?= e((string) $ringkasan['jumlah_campaign']) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card h-100 p-4">
                    <p class="pf-metrik-label">Total Dana Terkumpul</p>
                    <p class="pf-metrik-nilai"><?= e(formatRupiah($ringkasan['total_dana_terkumpul'])) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card h-100 p-4">
                    <p class="pf-metrik-label">Campaign Aktif</p>
                    <p class="pf-metrik-nilai"><?= e((string) $ringkasan['campaign_aktif']) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <h2 class="pf-modul-judul">Daftar Campaign</h2>
            <form class="row g-2 align-items-end pf-filter" method="get" action="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">
                <div class="col-auto">
                    <label class="visually-hidden" for="cari">Cari judul campaign</label>
                    <input class="form-control" type="search" id="cari" name="cari" placeholder="Cari judul" value="<?= e($cari) ?>">
                </div>
                <div class="col-auto">
                    <label class="visually-hidden" for="status">Filter status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua status</option>
                        <?php foreach (CampaignEntity::DAFTAR_STATUS as $status): ?>
                            <option value="<?= e($status) ?>" <?= $filterStatus === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn" type="submit">Terapkan</button>
                </div>
            </form>
        </div>
        <div class="pf-card pf-card-tabel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Campaign</th>
                            <th scope="col">Target</th>
                            <th scope="col">Terkumpul</th>
                            <th scope="col">Deadline</th>
                            <th scope="col">Status</th>
                            <th scope="col">Pencairan &amp; LPJ</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftarCampaign === []): ?>
                            <tr>
                                <td class="text-center py-4" colspan="7">Belum ada campaign yang sesuai.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarCampaign as $campaign): ?>
                                <?php
                                $persen = hitungPersen($campaign['dana_terkumpul'], $campaign['target_dana']);
                                $idCampaign = (int) $campaign['id_campaign'];
                                $pencairan = $pencairanPerCampaign[$idCampaign] ?? null;
                                $layakCair = $campaign['status_campaign'] === 'Selesai'
                                    && (string) $campaign['deadline'] < $hariIni
                                    && (float) $campaign['dana_terkumpul'] > 0
                                    && ($pencairan === null || $pencairan['status_pengajuan'] === 'Ditolak');
                                $sudahDisetujui = $pencairan !== null && $pencairan['status_pengajuan'] === 'Disetujui';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold pf-teks-judul"><?= e($campaign['judul']) ?></div>
                                        <div class="small"><?= e($campaign['jenis']) ?></div>
                                    </td>
                                    <td class="pf-angka"><?= e(formatRupiah($campaign['target_dana'])) ?></td>
                                    <td>
                                        <div class="pf-angka"><?= e(formatRupiah($campaign['dana_terkumpul'])) ?></div>
                                        <div class="progress mt-1" role="progressbar" aria-label="Progress dana" aria-valuenow="<?= e((string) $persen) ?>" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: <?= e((string) $persen) ?>%"></div>
                                        </div>
                                        <small class="pf-teks-bantu"><?= e((string) $persen) ?>%</small>
                                    </td>
                                    <td class="pf-angka"><?= e(formatTanggal($campaign['deadline'])) ?></td>
                                    <td>
                                        <span class="pf-badge <?= e(kelasBadgeStatus($campaign['status_campaign'])) ?>"><span class="pf-badge-pil"><?= e($campaign['status_campaign']) ?></span></span>
                                        <?php if ($campaign['status_campaign'] === 'Ditolak' && !empty($campaign['alasan_penolakan'])): ?>
                                            <div class="pf-teks-bantu mt-1">Alasan: <?= e($campaign['alasan_penolakan']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($pencairan === null): ?>
                                            <span class="pf-teks-bantu">Belum diajukan</span>
                                        <?php elseif ($pencairan['status_pengajuan'] === 'Menunggu'): ?>
                                            <span class="pf-badge pf-badge--pending"><span class="pf-badge-pil">Menunggu Review</span></span>
                                        <?php elseif ($pencairan['status_pengajuan'] === 'Ditolak'): ?>
                                            <span class="pf-badge pf-badge--ditolak"><span class="pf-badge-pil">Pencairan Ditolak</span></span>
                                            <?php if (!empty($pencairan['alasan_penolakan'])): ?>
                                                <div class="pf-teks-bantu mt-1"><?= e($pencairan['alasan_penolakan']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if ($pencairan['status_transparansi'] === 'Terverifikasi'): ?>
                                                <span class="pf-badge pf-badge--selesai"><span class="pf-badge-pil">LPJ Terverifikasi</span></span>
                                            <?php elseif ($pencairan['status_transparansi'] === 'Ditolak'): ?>
                                                <span class="pf-badge pf-badge--ditolak"><span class="pf-badge-pil">LPJ Ditolak</span></span>
                                                <?php if (!empty($pencairan['alasan_penolakan'])): ?>
                                                    <div class="pf-teks-bantu mt-1"><?= e($pencairan['alasan_penolakan']) ?></div>
                                                <?php endif; ?>
                                            <?php elseif (!empty($pencairan['dokumen_lpj'])): ?>
                                                <span class="pf-badge pf-badge--pending"><span class="pf-badge-pil">LPJ Diaudit</span></span>
                                            <?php else: ?>
                                                <span class="pf-badge pf-badge--aktif"><span class="pf-badge-pil">Dicairkan</span></span>
                                                <div class="pf-teks-bantu pf-angka mt-1">Batas LPJ: <?= e(formatTanggal((string) $pencairan['batas_unggah_lpj'])) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <?php if ($campaign['status_campaign'] === 'Aktif'): ?>
                                                <a class="btn-pf-outline py-1 px-3" style="font-size: 11px;" href="<?= e(BASE_URL) ?>/donasi.php?id=<?= e((string) $campaign['id_campaign']) ?>">Donasi</a>
                                            <?php endif; ?>
                                            <?php if ($campaign['status_campaign'] === 'Ditolak'): ?>
                                                <a class="btn-pf-cta btn-pf-tabel" href="<?= e(BASE_URL) ?>/buat_campaign.php?id=<?= e((string) $idCampaign) ?>">Perbaiki</a>
                                            <?php endif; ?>
                                            <?php if ($layakCair): ?>
                                                <a class="btn-pf-cta btn-pf-tabel" href="<?= e(BASE_URL) ?>/pengajuan_pencairan.php?id_campaign=<?= e((string) $idCampaign) ?>">
                                                    <?= $pencairan !== null ? 'Ajukan Ulang' : 'Ajukan Pencairan' ?>
                                                </a>
                                            <?php elseif ($sudahDisetujui): ?>
                                                <a class="<?= ($pencairan['status_transparansi'] === 'Terverifikasi' || (!empty($pencairan['dokumen_lpj']) && $pencairan['status_transparansi'] === 'Menunggu')) ? 'btn-pf-outline' : 'btn-pf-cta' ?> btn-pf-tabel"
                                                   href="<?= e(BASE_URL) ?>/unggah_lpj.php?id_pencairan=<?= e((string) $pencairan['id_pencairan']) ?>">
                                                    <?= $pencairan['status_transparansi'] === 'Terverifikasi' ? 'Detail LPJ' : ($pencairan['status_transparansi'] === 'Ditolak' ? 'Unggah Ulang LPJ' : (!empty($pencairan['dokumen_lpj']) ? 'Detail LPJ' : 'Unggah LPJ')) ?>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!$sudahDisetujui): ?>
                                                <form method="post" action="<?= e(BASE_URL) ?>/hapus_campaign.php" onsubmit="return confirm('Hapus campaign ini?');" class="m-0">
                                                    <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                                                    <input type="hidden" name="id_campaign" value="<?= e((string) $idCampaign) ?>">
                                                    <button class="btn-pf-hapus" type="submit">Hapus</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
