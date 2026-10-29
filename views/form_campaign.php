<?php
$judulHalaman = 'Buat Campaign';
require __DIR__ . '/layout/header.php';
$nilaiLama = static function (string $nama) use ($old): string {
    return e((string) ($old[$nama] ?? ''));
};
?>
<section class="bagian-halaman">
    <div class="mb-4">
        <h1 class="h3 mb-1">Buat Campaign</h1>
        <p class="text-secondary mb-0">Campaign baru akan berstatus Pending setelah dikirim.</p>
    </div>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <p class="fw-semibold mb-1">Periksa kembali data campaign:</p>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= e(BASE_URL) ?>/kirim_campaign.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label" for="judul">Judul Campaign</label>
            <input class="form-control" type="text" id="judul" name="judul" maxlength="255" required value="<?= $nilaiLama('judul') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label" for="jenis">Jenis Campaign</label>
            <select class="form-select" id="jenis" name="jenis" required>
                <option value="">Pilih jenis</option>
                <?php foreach ($daftarJenis as $jenis): ?>
                    <option value="<?= e($jenis) ?>" <?= ($old['jenis'] ?? '') === $jenis ? 'selected' : '' ?>><?= e($jenis) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?= $nilaiLama('deskripsi') ?></textarea>
        </div>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="target_dana">Target Dana (Rp)</label>
                <input class="form-control" type="number" id="target_dana" name="target_dana" min="0.01" step="0.01" required value="<?= $nilaiLama('target_dana') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="deadline">Deadline</label>
                <input class="form-control" type="date" id="deadline" name="deadline" min="<?= e(date('Y-m-d')) ?>" required value="<?= $nilaiLama('deadline') ?>">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="dokumen_campaign">Dokumen Campaign</label>
            <input class="form-control" type="file" id="dokumen_campaign" name="dokumen_campaign" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="form-text">Wajib, format PDF/JPG/JPEG/PNG, maksimal 2 MB.</div>
        </div>
        <div class="mb-4">
            <label class="form-label" for="gambar_campaign">Gambar Campaign (opsional)</label>
            <input class="form-control" type="file" id="gambar_campaign" name="gambar_campaign" accept=".jpg,.jpeg,.png">
            <div class="form-text">Format JPG/JPEG/PNG, maksimal 2 MB.</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Kirim Campaign</button>
            <a class="btn btn-outline-secondary" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Batal</a>
        </div>
    </form>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>