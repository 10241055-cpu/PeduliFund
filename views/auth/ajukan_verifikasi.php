<?php
// Form verifikasi akun fundraiser (UI-008, SDD 5.2.8): status pengajuan, data diri, unggah dokumen
$judulHalaman = $judulHalaman ?? 'Verifikasi Akun Fundraiser';
require __DIR__ . '/../layout/header.php';

$nilaiLama = static function (string $nama) use ($old): string {
    return e((string) ($old[$nama] ?? ''));
};
?>
<section class="bagian-halaman py-4">
    <div class="pf-tray">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                <div class="pf-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="pf-h1 fs-3">Verifikasi Akun Fundraiser</h1>
                        <p class="pf-subjudul mb-0 mt-1">Ajukan dokumen Anda untuk bisa menggalang dana</p>
                    </div>

                    <?php if ($errors !== []): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($pengajuan !== null): ?>
                        <div class="pf-card p-3 mb-4" style="background: var(--green-pale);">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h2 class="h6 fw-bold mb-0" style="color: var(--green-deep);">Status Pengajuan Terakhir</h2>
                                <span class="pf-badge <?= e(kelasBadgeStatus($pengajuan['status_dokumen'])) ?>">
                                    <span class="pf-badge-pil"><?= e($pengajuan['status_dokumen']) ?></span>
                                </span>
                            </div>
                            <p class="small mb-0 mt-2" style="color: var(--text-body);">
                                Diajukan <?= e(formatTanggal((string) $pengajuan['tanggal_pengajuan'])) ?>.
                                <?php if ($pengajuan['status_dokumen'] === 'Ditolak'): ?>
                                    Alasan: <?= e((string) ($pengajuan['alasan_penolakan'] ?? '-')) ?>.
                                    <?= $bolehAjukan ? 'Anda dapat mengajukan ulang sekarang.' : 'Pengajuan ulang dapat dilakukan 14 hari setelah penolakan.' ?>
                                <?php elseif ($pengajuan['status_dokumen'] === 'Valid'): ?>
                                    Akun Anda terverifikasi sebagai penggalang dana.
                                <?php else: ?>
                                    Menunggu tinjauan admin (UI-012).
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($bolehAjukan): ?>
                        <form action="<?= e(BASE_URL) ?>/kirim_ajukan_verifikasi.php" method="post" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                            <h3 class="h6 fw-bold mb-3" style="color: var(--green-deep);">Data Diri</h3>
                            <div class="row g-3 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label" for="jenis_fundraiser">Jenis Fundraiser</label>
                                    <select class="form-select" id="jenis_fundraiser" name="jenis_fundraiser" required>
                                        <option value="">Pilih jenis</option>
                                        <?php foreach ($daftarJenis as $jenis): ?>
                                            <option value="<?= e($jenis) ?>" <?= ($old['jenis_fundraiser'] ?? '') === $jenis ? 'selected' : '' ?>><?= e($jenis) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                                    <input class="form-control" type="text" id="nama_lengkap" name="nama_lengkap" maxlength="150" required value="<?= $nilaiLama('nama_lengkap') ?>">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="nomor_identitas">NIK / NPWP</label>
                                    <input class="form-control" type="text" id="nomor_identitas" name="nomor_identitas" maxlength="50" required value="<?= $nilaiLama('nomor_identitas') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="nomor_telepon">Nomor Telepon</label>
                                    <input class="form-control" type="text" id="nomor_telepon" name="nomor_telepon" maxlength="20" required value="<?= $nilaiLama('nomor_telepon') ?>">
                                </div>
                            </div>

                            <h3 class="h6 fw-bold mb-3" style="color: var(--green-deep);">Unggah Dokumen</h3>
                            <div class="mb-3">
                                <label class="form-label" for="dokumen_verifikasi">Dokumen Utama</label>
                                <input class="form-control" type="file" id="dokumen_verifikasi" name="dokumen_verifikasi" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">Individu: foto KTP. Organisasi: akta pendirian. PDF/JPG/PNG, maks 2 MB.</div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="dokumen_pendukung">Dokumen Pendukung (opsional)</label>
                                <input class="form-control" type="file" id="dokumen_pendukung" name="dokumen_pendukung" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Individu: foto wajah memegang KTP / surat perjanjian bermaterai. Organisasi: SK / NPWP organisasi. Maks 2 MB.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn-pf-cta" type="submit">Kirim Pengajuan</button>
                                <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_donatur.php">Batal</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="mb-3" style="color: var(--text-body);">Pengajuan ulang baru dapat dilakukan <strong>14 hari setelah tanggal penolakan</strong>.</p>
                            <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_donatur.php">Kembali ke Dashboard</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
