<?php
$judulHalaman = $judulHalaman ?? 'Daftar Akun PeduliFund';
require __DIR__ . '/../layout/header.php';
?>
<section class="bagian-halaman py-4">
    <div class="pf-tray">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="pf-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="pf-modul-judul justify-content-center mb-2">REGISTRASI</div>
                        <h1 class="pf-h1 fs-3">Buat Akun Donatur</h1>
                        <p class="pf-subjudul mb-0 mt-1">Bergabung bersama ribuan donatur untuk menyalurkan kebaikan</p>
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

                    <form action="<?= e(BASE_URL) ?>/signup.php" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">

                        <div class="mb-3">
                            <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                            <input class="form-control" type="text" id="nama_lengkap" name="nama_lengkap"
                                   placeholder="Contoh: Budi Santoso"
                                   value="<?= e((string) ($old['nama_lengkap'] ?? '')) ?>" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">Alamat Email</label>
                            <input class="form-control" type="email" id="email" name="email"
                                   placeholder="nama@email.com"
                                   value="<?= e((string) ($old['email'] ?? '')) ?>" required>
                            <div class="form-text">Gunakan email aktif yang Anda miliki.</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="password">Kata Sandi</label>
                                <input class="form-control" type="password" id="password" name="password"
                                       placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="konfirmasi_password">Ulangi Kata Sandi</label>
                                <input class="form-control" type="password" id="konfirmasi_password" name="konfirmasi_password"
                                       placeholder="Ketik ulang sandi" required minlength="6">
                            </div>
                        </div>

                        <div class="d-grid mb-3 mt-4">
                            <button class="btn-pf-cta justify-content-center" type="submit">
                                Daftar Sekarang
                            </button>
                        </div>

                        <div class="text-center pt-2 border-top">
                            <p class="pf-subjudul mb-0">
                                Sudah memiliki akun?
                                <a href="<?= e(BASE_URL) ?>/login.php" class="text-decoration-none fw-bold" style="color: var(--pink-primary);">
                                    Masuk di sini
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
