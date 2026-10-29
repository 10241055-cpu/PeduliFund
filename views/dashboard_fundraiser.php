<?php
$judulHalaman = 'Dashboard Fundraiser';
require __DIR__ . '/layout/header.php';
?>
<section class="bagian-halaman">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard Fundraiser</h1>
            <p class="text-secondary mb-0">Ringkasan campaign dan dana yang terkumpul.</p>
        </div>
        <a class="btn btn-primary" href="<?= e(BASE_URL) ?>/buat_campaign.php">Buat Campaign</a>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card kartu-ringkasan h-100">
                <div class="card-body">
                    <p class="text-secondary mb-1">Jumlah Campaign</p>
                    <p class="h3 mb-0"><?= e((string) $ringkasan['jumlah_campaign']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kartu-ringkasan h-100">
                <div class="card-body">
                    <p class="text-secondary mb-1">Total Dana Terkumpul</p>
                    <p class="h3 mb-0"><?= e(formatRupiah($ringkasan['total_dana_terkumpul'])) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card kartu-ringkasan h-100">
                <div class="card-body">
                    <p class="text-secondary mb-1">Campaign Aktif</p>
                    <p class="h3 mb-0"><?= e((string) $ringkasan['campaign_aktif']) ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bagian-halaman">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h4 mb-0">Daftar Campaign</h2>
        <form class="row g-2" method="get" action="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">
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
                <button class="btn btn-outline-primary" type="submit">Terapkan</button>
            </div>
        </form>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Campaign</th>
                        <th scope="col">Target</th>
                        <th scope="col">Terkumpul</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($daftarCampaign === []): ?>
                        <tr>
                            <td class="text-center text-secondary py-4" colspan="5">Belum ada campaign yang sesuai.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarCampaign as $campaign): ?>
                            <?php $persen = hitungPersen($campaign['dana_terkumpul'], $campaign['target_dana']); ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($campaign['judul']) ?></div>
                                    <div class="small text-secondary"><?= e($campaign['jenis']) ?></div>
                                </td>
                                <td><?= e(formatRupiah($campaign['target_dana'])) ?></td>
                                <td>
                                    <div><?= e(formatRupiah($campaign['dana_terkumpul'])) ?></div>
                                    <div class="progress mt-1" role="progressbar" aria-label="Progress dana" aria-valuenow="<?= e((string) $persen) ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar" style="width: <?= e((string) $persen) ?>%"></div>
                                    </div>
                                    <small class="text-secondary"><?= e((string) $persen) ?>%</small>
                                </td>
                                <td><?= e(formatTanggal($campaign['deadline'])) ?></td>
                                <td><span class="badge <?= e(kelasBadgeStatus($campaign['status_campaign'])) ?>"><?= e($campaign['status_campaign']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>