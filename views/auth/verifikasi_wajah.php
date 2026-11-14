<?php
// Gerbang verifikasi wajah untuk fundraiser (UI-007, SDD 5.2.7)
$judulHalaman = $judulHalaman ?? 'Verifikasi Wajah';
require __DIR__ . '/../layout/header.php';
?>
<section class="bagian-halaman py-4">
    <div class="pf-tray">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="pf-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="pf-h1 fs-3">Verifikasi Wajah</h1>
                        <p class="pf-subjudul mb-0 mt-1">Langkah tambahan keamanan akun penggalang dana</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($sudahTerverifikasi): ?>
                        <div class="text-center py-3">
                            <p class="mb-3" style="color: var(--text-body);">Wajah Anda sudah terverifikasi sebelumnya.</p>
                            <a class="btn-pf-cta" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Lanjut ke Dashboard</a>
                        </div>
                    <?php else: ?>
                        <form action="<?= e(BASE_URL) ?>/proses_verifikasi_wajah.php" method="post" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                            <div class="text-center mb-4">
                                <svg viewBox="0 0 200 140" role="img" aria-label="Area pratinjau kamera" style="width: 220px; height: auto;">
                                    <rect x="10" y="10" width="180" height="120" rx="18" fill="var(--green-pale)" stroke="var(--green-light)" stroke-width="3"/>
                                    <circle cx="100" cy="58" r="26" fill="var(--green-light)"/>
                                    <path d="M64 118 c6-22 66-22 72 0" fill="var(--green-light)"/>
                                    <rect x="84" y="112" width="32" height="10" rx="5" fill="var(--pink-primary)"/>
                                </svg>
                                <div class="form-text">Pratinjau kamera langsung belum tersedia; di perangkat seluler tombol di bawah membuka kamera.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="foto_wajah">Ambil Foto Wajah</label>
                                <input class="form-control" type="file" id="foto_wajah" name="foto_wajah" accept="image/jpeg,image/png" capture="user" required>
                                <div class="form-text">Foto dapat diulang sebelum diverifikasi. JPG/PNG, maks 2 MB.</div>
                            </div>
                            <div class="pf-card p-3 mb-4" style="background: var(--green-pale);">
                                <p class="small fw-semibold mb-2" style="color: var(--green-deep);">Hasil Pencocokan (simulasi sistem)</p>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="simulasi_hasil" id="wajah_berhasil" value="Berhasil" checked>
                                        <label class="form-check-label small fw-semibold text-success" for="wajah_berhasil">
                                            Wajah Cocok
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="simulasi_hasil" id="wajah_gagal" value="Gagal">
                                        <label class="form-check-label small fw-semibold text-danger" for="wajah_gagal">
                                            Wajah Tidak Cocok
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button class="btn-pf-cta justify-content-center" type="submit">Verifikasi</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
