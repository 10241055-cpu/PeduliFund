<?php
$judulHalaman = 'Unggah LPJ dan Bukti Nota';
require __DIR__ . '/../layout/header.php';
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="pf-h1">Unggah LPJ dan Bukti Nota</h1>
                <p class="pf-subjudul mb-0 mt-1">Pencairan telah disetujui. Laporkan penggunaan dana untuk verifikasi transparansi.</p>
            </div>
            <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Kembali</a>
        </div>

        <div class="alert <?= $melewatiBatas && $pencairan['status_transparansi'] !== 'Terverifikasi' ? 'alert-danger' : 'alert-warning' ?> mb-4" role="alert">
            <div class="fw-bold mb-1">Batas Waktu &amp; Sanksi Pelaporan LPJ</div>
            <p class="mb-0 small">
                Batas unggah LPJ dan bukti nota paling lambat 1 bulan sejak tanggal pencairan
                (<strong class="pf-angka"><?= e(formatTanggal((string) $pencairan['batas_unggah_lpj'])) ?></strong>).
                Keterlambatan tanpa LPJ valid akan membekukan akun fundraiser (suspend 1 bulan) dan menghentikan campaign aktif.
            </p>
        </div>

        <?php if ($pencairan['status_transparansi'] === 'Ditolak'): ?>
            <div class="alert alert-danger mb-4" role="alert">
                <div class="fw-bold mb-1">LPJ Sebelumnya Ditolak Admin</div>
                <p class="mb-0 small">Alasan penolakan: <?= e((string) ($pencairan['alasan_penolakan'] ?? '-')) ?>. Silakan unggah ulang berkas LPJ dan bukti nota yang sesuai.</p>
            </div>
        <?php elseif ($pencairan['status_transparansi'] === 'Terverifikasi'): ?>
            <div class="alert alert-success mb-4" role="alert">
                <div class="fw-bold mb-1">Transparansi Terverifikasi</div>
                <p class="mb-0 small">LPJ dan bukti nota telah diverifikasi oleh admin. Anda dapat kembali membuat campaign baru.</p>
            </div>
        <?php elseif (!empty($pencairan['dokumen_lpj'])): ?>
            <div class="alert alert-success mb-4" role="alert">
                <div class="fw-bold mb-1">LPJ Sedang Diaudit Admin</div>
                <p class="mb-0 small">Berkas LPJ telah diunggah pada <span class="pf-angka"><?= e(formatTanggal((string) $pencairan['tanggal_unggah_lpj'])) ?></span> dan sedang menunggu verifikasi admin.</p>
            </div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="alert alert-danger mb-4" role="alert">
                <p class="fw-semibold mb-1">Periksa kembali berkas yang diunggah:</p>
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
                    <h2 class="h5 fw-bold mb-3" style="color: var(--green-deep);">Ringkasan Pencairan</h2>
                    <div class="mb-3">
                        <p class="pf-metrik-label mb-1">Campaign</p>
                        <p class="fw-bold mb-0 pf-teks-judul"><?= e($pencairan['judul_campaign']) ?></p>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Nominal Dicairkan</p>
                            <p class="fw-bold mb-0 pf-angka" style="color: var(--green-primary);"><?= e(formatRupiah($pencairan['nominal_pencairan'])) ?></p>
                        </div>
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Status Transparansi</p>
                            <span class="pf-badge <?= e(kelasBadgeStatus($pencairan['status_transparansi'])) ?>">
                                <span class="pf-badge-pil"><?= e($pencairan['status_transparansi']) ?></span>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p class="pf-metrik-label mb-1">Rekening Tujuan</p>
                        <p class="mb-0 fw-semibold"><?= e($pencairan['nama_bank']) ?> &bull; <span class="pf-angka"><?= e($pencairan['nomor_rekening']) ?></span></p>
                        <small class="pf-teks-bantu">a.n. <?= e($pencairan['atas_nama']) ?></small>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Tanggal Cair</p>
                            <p class="mb-0 pf-angka"><?= e(formatTanggal((string) $pencairan['tanggal_pencairan'])) ?></p>
                        </div>
                        <div class="col-6">
                            <p class="pf-metrik-label mb-1">Batas Unggah LPJ</p>
                            <p class="mb-0 pf-angka fw-bold"><?= e(formatTanggal((string) $pencairan['batas_unggah_lpj'])) ?></p>
                        </div>
                    </div>

                    <?php if (!empty($pencairan['dokumen_lpj']) || !empty($pencairan['bukti_nota'])): ?>
                        <hr class="my-3">
                        <p class="pf-metrik-label mb-2">Berkas Terunggah</p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (!empty($pencairan['dokumen_lpj'])): ?>
                                <a class="btn-pf-outline btn-pf-tabel" target="_blank" rel="noopener"
                                   href="<?= e(BASE_URL) ?>/unduh_berkas_pencairan.php?id_pencairan=<?= e((string) $pencairan['id_pencairan']) ?>&amp;jenis=dokumen_lpj">
                                    Lihat Dokumen LPJ
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($pencairan['bukti_nota'])): ?>
                                <a class="btn-pf-outline btn-pf-tabel" target="_blank" rel="noopener"
                                   href="<?= e(BASE_URL) ?>/unduh_berkas_pencairan.php?id_pencairan=<?= e((string) $pencairan['id_pencairan']) ?>&amp;jenis=bukti_nota">
                                    Lihat Bukti Nota
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="pf-card p-4">
                    <h2 class="h5 fw-bold mb-3" style="color: var(--green-deep);">
                        <?= $bisaUnggah ? 'Formulir Unggah LPJ &amp; Bukti Nota' : 'Status Berkas LPJ' ?>
                    </h2>
                    <?php if ($bisaUnggah): ?>
                        <form action="<?= e(BASE_URL) ?>/unggah_lpj.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                            <input type="hidden" name="id_pencairan" value="<?= e((string) $pencairan['id_pencairan']) ?>">

                            <div class="mb-3">
                                <label class="form-label" for="dokumen_lpj">Dokumen Laporan Pertanggungjawaban (LPJ)</label>
                                <input class="form-control" type="file" id="dokumen_lpj" name="dokumen_lpj" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Wajib, format PDF/JPG/JPEG/PNG, maksimal 2 MB.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="bukti_nota">Bukti Nota / Kwitansi Pengeluaran</label>
                                <input class="form-control" type="file" id="bukti_nota" name="bukti_nota" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Wajib, format PDF/JPG/JPEG/PNG, maksimal 2 MB.</div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn-pf-cta" type="submit">Unggah LPJ &amp; Bukti Nota</button>
                                <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Batal</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="pf-subjudul mb-0">
                            <?= $pencairan['status_transparansi'] === 'Terverifikasi'
                                ? 'LPJ dan bukti nota Anda sudah terverifikasi oleh admin.'
                                : 'LPJ dan bukti nota Anda sedang ditinjau oleh admin. Anda dapat mengunggah ulang apabila admin meminta revisi.' ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>

