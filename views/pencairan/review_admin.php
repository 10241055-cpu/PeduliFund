<?php
$judulHalaman = 'Review Pencairan (Admin)';
require __DIR__ . '/../layout/header.php';

$opsiFilter = [
    ''                   => 'Semua',
    'menunggu_pencairan' => 'Pengajuan Menunggu',
    'menunggu_lpj'       => 'Audit LPJ',
    'belum_unggah_lpj'   => 'Belum Unggah LPJ',
    'selesai'            => 'Terverifikasi',
];
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="pf-modul-judul mb-1">PORTAL ADMIN</div>
                <h1 class="pf-h1">Review Pencairan &amp; Audit LPJ</h1>
                <p class="pf-subjudul mb-0 mt-1">Tinjau permohonan pencairan dana dan verifikasi laporan transparansi fundraiser.</p>
            </div>
            <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_admin.php">Kembali ke Dashboard</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="pf-card p-4 h-100">
                    <p class="pf-metrik-label">Pengajuan Pencairan Menunggu</p>
                    <p class="pf-metrik-nilai" style="color: var(--pink-primary);"><?= e((string) $ringkasan['pencairan_menunggu']) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-4 h-100">
                    <p class="pf-metrik-label">Audit LPJ Menunggu</p>
                    <p class="pf-metrik-nilai"><?= e((string) $ringkasan['lpj_menunggu']) ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card p-4 h-100">
                    <p class="pf-metrik-label">Total Antrean Admin</p>
                    <p class="pf-metrik-nilai"><?= e((string) $ringkasan['total_antrean']) ?></p>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <?php foreach ($opsiFilter as $kode => $label): ?>
                <?php $url = BASE_URL . '/review_pencairan.php' . ($kode !== '' ? '?filter=' . urlencode($kode) : ''); ?>
                <a class="<?= $filter === $kode ? 'btn-pf-cta' : 'btn-pf-outline' ?> btn-pf-tabel" href="<?= e($url) ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($daftarPencairan === []): ?>
            <div class="pf-card p-5 text-center">
                <p class="pf-subjudul mb-0">Belum ada data pengajuan pencairan pada filter ini.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($daftarPencairan as $item): ?>
                    <?php
                    $terlambatLpj = $item['status_pengajuan'] === 'Disetujui'
                        && $item['status_transparansi'] !== 'Terverifikasi'
                        && !empty($item['batas_unggah_lpj'])
                        && $item['batas_unggah_lpj'] < $hariIni;
                    ?>
                    <div class="pf-card p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="pf-badge <?= e(kelasBadgeStatus($item['status_pengajuan'])) ?>">
                                        <span class="pf-badge-pil">Pengajuan: <?= e($item['status_pengajuan']) ?></span>
                                    </span>
                                    <?php if ($item['status_pengajuan'] === 'Disetujui'): ?>
                                        <span class="pf-badge <?= e(kelasBadgeStatus($item['status_transparansi'])) ?>">
                                            <span class="pf-badge-pil">LPJ: <?= e($item['status_transparansi']) ?></span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (($item['status_akun'] ?? 'Aktif') === 'Dibekukan'): ?>
                                        <span class="pf-badge pf-badge--ditolak">
                                            <span class="pf-badge-pil">Akun Dibekukan</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="h5 fw-bold mb-1" style="color: var(--green-deep);"><?= e($item['judul_campaign']) ?></h2>
                                <p class="small mb-0 text-muted">
                                    Fundraiser: <strong><?= e($item['email_fundraiser']) ?></strong> &bull;
                                    Kategori: <?= e($item['jenis_campaign']) ?> &bull;
                                    Deadline Campaign: <span class="pf-angka"><?= e(formatTanggal($item['deadline'])) ?></span>
                                </p>
                            </div>
                            <div class="text-md-end">
                                <p class="pf-metrik-label mb-1">Nominal Pencairan</p>
                                <p class="pf-metrik-nilai fs-4" style="color: var(--pink-primary);"><?= e(formatRupiah($item['nominal_pencairan'])) ?></p>
                            </div>
                        </div>

                        <div class="row g-3 p-3 rounded-4 mb-3" style="background-color: var(--green-pale);">
                            <div class="col-md-4">
                                <p class="pf-metrik-label mb-1">Bank &amp; Nomor Rekening</p>
                                <p class="fw-bold mb-0"><?= e($item['nama_bank']) ?> &bull; <span class="pf-angka"><?= e($item['nomor_rekening']) ?></span></p>
                            </div>
                            <div class="col-md-4">
                                <p class="pf-metrik-label mb-1">Atas Nama</p>
                                <p class="fw-bold mb-0"><?= e($item['atas_nama']) ?></p>
                            </div>
                            <div class="col-md-4">
                                <p class="pf-metrik-label mb-1">Tanggal Cair / Batas LPJ</p>
                                <p class="fw-semibold mb-0 pf-angka">
                                    <?php if (!empty($item['tanggal_pencairan'])): ?>
                                        Cair: <?= e(formatTanggal((string) $item['tanggal_pencairan'])) ?> &bull;
                                        Batas LPJ: <strong><?= e(formatTanggal((string) $item['batas_unggah_lpj'])) ?></strong>
                                    <?php else: ?>
                                        Belum dicairkan
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($terlambatLpj): ?>
                            <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                                Batas unggah LPJ (<strong class="pf-angka"><?= e(formatTanggal((string) $item['batas_unggah_lpj'])) ?></strong>) telah terlewati tanpa LPJ terverifikasi.
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($item['alasan_penolakan'])): ?>
                            <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                                <strong>Catatan Penolakan:</strong> <?= e($item['alasan_penolakan']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($item['status_pengajuan'] === 'Menunggu'): ?>
                            <form action="<?= e(BASE_URL) ?>/review_pencairan.php" method="post" class="border-top pt-3">
                                <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                                <input type="hidden" name="id_pencairan" value="<?= e((string) $item['id_pencairan']) ?>">
                                <input type="hidden" name="aksi" value="keputusan_pencairan">
                                <div class="mb-3">
                                    <label class="form-label" for="alasan_cair_<?= e((string) $item['id_pencairan']) ?>">Alasan Penolakan (wajib diisi jika menolak)</label>
                                    <textarea class="form-control" id="alasan_cair_<?= e((string) $item['id_pencairan']) ?>" name="alasan_penolakan" rows="2" placeholder="Tuliskan alasan jika pengajuan ditolak..."></textarea>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn-pf-cta btn-pf-tabel" type="submit" name="keputusan" value="setujui"
                                            onclick="return confirm('Setujui pencairan dan cairkan dana ke rekening fundraiser?');">
                                        Setujui &amp; Cairkan
                                    </button>
                                    <button class="btn-pf-hapus" type="submit" name="keputusan" value="tolak">
                                        Tolak Pengajuan
                                    </button>
                                </div>
                            </form>
                        <?php elseif ($item['status_pengajuan'] === 'Disetujui'): ?>
                            <div class="border-top pt-3">
                                <h3 class="h6 fw-bold mb-2" style="color: var(--green-deep);">Verifikasi LPJ dan Bukti Nota</h3>
                                <?php if (empty($item['dokumen_lpj']) && empty($item['bukti_nota'])): ?>
                                    <p class="small text-muted mb-0">Fundraiser belum mengunggah dokumen LPJ dan bukti nota.</p>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php if (!empty($item['dokumen_lpj'])): ?>
                                            <a class="btn-pf-outline btn-pf-tabel" target="_blank" rel="noopener"
                                               href="<?= e(BASE_URL) ?>/unduh_berkas_pencairan.php?id_pencairan=<?= e((string) $item['id_pencairan']) ?>&amp;jenis=dokumen_lpj">
                                                Pratinjau Dokumen LPJ
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($item['bukti_nota'])): ?>
                                            <a class="btn-pf-outline btn-pf-tabel" target="_blank" rel="noopener"
                                               href="<?= e(BASE_URL) ?>/unduh_berkas_pencairan.php?id_pencairan=<?= e((string) $item['id_pencairan']) ?>&amp;jenis=bukti_nota">
                                                Pratinjau Bukti Nota
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($item['tanggal_unggah_lpj'])): ?>
                                            <span class="pf-teks-bantu align-self-center pf-angka">
                                                Diunggah: <?= e(formatTanggal((string) $item['tanggal_unggah_lpj'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($item['status_transparansi'] === 'Menunggu'): ?>
                                        <form action="<?= e(BASE_URL) ?>/review_pencairan.php" method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                                            <input type="hidden" name="id_pencairan" value="<?= e((string) $item['id_pencairan']) ?>">
                                            <input type="hidden" name="aksi" value="keputusan_lpj">
                                            <div class="mb-3">
                                                <label class="form-label" for="alasan_lpj_<?= e((string) $item['id_pencairan']) ?>">Catatan Penolakan LPJ (wajib diisi jika menolak)</label>
                                                <textarea class="form-control" id="alasan_lpj_<?= e((string) $item['id_pencairan']) ?>" name="alasan_penolakan" rows="2" placeholder="Tuliskan bagian yang perlu direvisi jika LPJ tidak valid..."></textarea>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button class="btn-pf-cta btn-pf-tabel" type="submit" name="keputusan_lpj" value="terima">
                                                    Verifikasi Transparansi
                                                </button>
                                                <button class="btn-pf-hapus" type="submit" name="keputusan_lpj" value="tolak">
                                                    Tolak LPJ
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>

