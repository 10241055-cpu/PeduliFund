<?php
$judulHalaman = 'Pengajuan Pencairan Dana';
require __DIR__ . '/../layout/header.php';
$nilaiLama = static function (string $nama) use ($old): string {
    return e((string) ($old[$nama] ?? ''));
};
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="pf-modul-judul mb-1">PENCAIRAN DANA</div>
                <h1 class="pf-h1">Pengajuan Pencairan Dana</h1>
                <p class="pf-subjudul mb-0 mt-1">Ajukan pencairan seluruh dana terkumpul ke rekening tujuan Anda.</p>
            </div>
            <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Kembali</a>
        </div>

        <?php if (!empty($penolakanTerakhir)): ?>
            <div class="alert alert-danger mb-4" role="alert">
                <div class="fw-bold mb-1">Pengajuan Sebelumnya Ditolak Admin</div>
                <p class="mb-0 small">Alasan penolakan: <?= e((string) ($penolakanTerakhir['alasan_penolakan'] ?? '-')) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="alert alert-danger mb-4" role="alert">
                <p class="fw-semibold mb-1">Periksa kembali data pengajuan pencairan:</p>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="pf-card p-4 h-100">
                    <h2 class="h5 fw-bold mb-3" style="color: var(--green-deep);">Ringkasan Campaign</h2>
                    <div class="mb-3">
                        <p class="pf-metrik-label mb-1">Judul Campaign</p>
                        <p class="fw-bold mb-0 pf-teks-judul"><?= e($campaign['judul']) ?></p>
                        <small class="pf-teks-bantu"><?= e($campaign['jenis']) ?></small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Target Dana</p>
                            <p class="fw-bold mb-0 pf-angka"><?= e(formatRupiah($campaign['target_dana'])) ?></p>
                        </div>
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Dana Terkumpul</p>
                            <p class="fw-bold mb-0 pf-angka" style="color: var(--pink-primary);"><?= e(formatRupiah($campaign['dana_terkumpul'])) ?></p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Deadline</p>
                            <p class="fw-semibold mb-0 pf-angka"><?= e(formatTanggal($campaign['deadline'])) ?></p>
                        </div>
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Status</p>
                            <span class="pf-badge <?= e(kelasBadgeStatus($campaign['status_campaign'])) ?>">
                                <span class="pf-badge-pil"><?= e($campaign['status_campaign']) ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="pf-card p-4">
                    <h2 class="h5 fw-bold mb-3" style="color: var(--green-deep);">Data Rekening Tujuan</h2>
                    <form action="<?= e(BASE_URL) ?>/pengajuan_pencairan.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                        <input type="hidden" name="id_campaign" value="<?= e((string) $campaign['id_campaign']) ?>">

                        <div class="mb-3">
                            <label class="form-label" for="nominal_pencairan">Nominal Pencairan</label>
                            <input class="form-control pf-angka" type="text" id="nominal_pencairan" value="<?= e(formatRupiah($campaign['dana_terkumpul'])) ?>" readonly>
                            <div class="form-text">Sesuai ketentuan PeduliFund, seluruh dana terkumpul akan dicairkan (0% potongan) meskipun target belum tercapai.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="nama_bank">Nama Bank Tujuan</label>
                            <input class="form-control" type="text" id="nama_bank" name="nama_bank" maxlength="100" placeholder="Contoh: Bank BCA / Bank Mandiri / Bank BSI" required value="<?= $nilaiLama('nama_bank') ?>">
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="nomor_rekening">Nomor Rekening</label>
                                <input class="form-control pf-angka" type="text" id="nomor_rekening" name="nomor_rekening" inputmode="numeric" pattern="[0-9]{6,30}" maxlength="30" placeholder="Masukkan angka nomor rekening" required value="<?= $nilaiLama('nomor_rekening') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="atas_nama">Atas Nama Pemilik Rekening</label>
                                <input class="form-control" type="text" id="atas_nama" name="atas_nama" maxlength="150" placeholder="Nama lengkap sesuai buku tabungan" required value="<?= $nilaiLama('atas_nama') ?>">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn-pf-cta" type="submit">Kirim Pengajuan</button>
                            <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>

