<?php
$judulHalaman = $judulHalaman ?? 'Masuk ke PeduliFund';
require __DIR__ . '/../layout/header.php';
?>
<section class="bagian-halaman py-4">
    <div class="pf-tray">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="pf-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="pf-modul-judul justify-content-center mb-2">AUTENTIKASI</div>
                        <h1 class="pf-h1 fs-3">Masuk ke Akun</h1>
                        <p class="pf-subjudul mb-0 mt-1">Silakan masukkan email dan kata sandi Anda</p>
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

                    <form action="<?= e(BASE_URL) ?>/login.php" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">

                        <div class="mb-3">
                            <label class="form-label" for="email">Alamat Email</label>
                            <input class="form-control" type="email" id="email" name="email"
                                   placeholder="nama@email.com"
                                   value="<?= e((string) ($old['email'] ?? '')) ?>" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password">Kata Sandi</label>
                            <input class="form-control" type="password" id="password" name="password"
                                   placeholder="Masukkan kata sandi" required>
                        </div>

                        <div class="d-grid mb-3">
                            <button class="btn-pf-cta justify-content-center" type="submit">
                                Masuk ke PeduliFund
                            </button>
                        </div>

                        <div class="text-center pt-2 border-top">
                            <p class="pf-subjudul mb-0">
                                Belum memiliki akun?
                                <a href="<?= e(BASE_URL) ?>/signup.php" class="text-decoration-none fw-bold" style="color: var(--pink-primary);">
                                    Daftar sebagai Donatur
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
