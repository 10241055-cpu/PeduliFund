<?php
$judulHalaman = 'Dashboard Fundraiser';
require __DIR__ . '/layout/header.php';
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="pf-h1">Dashboard Fundraiser</h1>
                <p class="pf-subjudul mb-0 mt-1">Ringkasan campaign dan dana yang terkumpul.</p>
            </div>
            <a class="btn-pf-cta" href="<?= e(BASE_URL) ?>/buat_campaign.php">Buat Campaign</a>
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
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($daftarCampaign === []): ?>
                            <tr>
                                <td class="text-center py-4" colspan="6">Belum ada campaign yang sesuai.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($daftarCampaign as $campaign): ?>
                                <?php $persen = hitungPersen($campaign['dana_terkumpul'], $campaign['target_dana']); ?>
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
                                    <td><span class="pf-badge <?= e(kelasBadgeStatus($campaign['status_campaign'])) ?>"><span class="pf-badge-pil"><?= e($campaign['status_campaign']) ?></span></span></td>
                                    <td>
                                        <form method="post" action="<?= e(BASE_URL) ?>/hapus_campaign.php" onsubmit="return confirm('Hapus campaign ini?');" class="m-0">
                                            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                                            <input type="hidden" name="id_campaign" value="<?= e((string) $campaign['id_campaign']) ?>">
                                            <button class="btn-pf-hapus" type="submit">Hapus</button>
                                        </form>
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
