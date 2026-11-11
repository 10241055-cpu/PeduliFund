<?php
// Tampilan UI-014: Donasi dan Pembayaran (SDD Bab 5.2.14 & Gambar 5.15)
$judulHalaman = 'Donasi dan Pembayaran';
require __DIR__ . '/layout/header.php';

$persen = hitungPersen($campaign['dana_terkumpul'], $campaign['target_dana']);
$nilaiLama = static function (string $nama) use ($old): string {
    return (string) ($old[$nama] ?? '');
};
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="mb-4">
            <h1 class="pf-h1">Donasi &amp; Pembayaran</h1>
            <p class="pf-subjudul mb-0 mt-1">Salurkan bantuan terbaik Anda untuk mendukung campaign ini.</p>
        </div>

        <?php if ($errors !== []): ?>
            <div class="alert alert-danger" role="alert">
                <p class="fw-semibold mb-1">Periksa kembali transaksi Anda:</p>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Kartu Ringkasan Campaign (SDD UI-014 Komponen 1) -->
        <div class="pf-card p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <span class="pf-badge pf-badge--netral mb-2">
                        <span class="pf-badge-pil"><?= e($campaign['jenis']) ?></span>
                    </span>
                    <h2 class="h4 pf-teks-judul fw-bold mb-1"><?= e($campaign['judul']) ?></h2>
                    <p class="text-secondary small mb-0"><?= e($campaign['deskripsi']) ?></p>
                </div>
                <div>
                    <span class="pf-badge <?= e(kelasBadgeStatus($campaign['status_campaign'])) ?>">
                        <span class="pf-badge-pil"><?= e($campaign['status_campaign']) ?></span>
                    </span>
                </div>
            </div>

            <hr style="border-color: var(--green-light); opacity: 0.5;">

            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Terkumpul: <strong class="pf-angka text-success"><?= e(formatRupiah($campaign['dana_terkumpul'])) ?></strong></span>
                        <span>Target: <strong class="pf-angka"><?= e(formatRupiah($campaign['target_dana'])) ?></strong></span>
                    </div>
                    <div class="progress" role="progressbar" aria-label="Progress dana terkumpul" aria-valuenow="<?= e((string) $persen) ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: <?= e((string) $persen) ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between small pf-teks-bantu mt-1">
                        <span><?= e((string) $persen) ?>% tercapai</span>
                        <span>Batas waktu: <?= e(formatTanggal($campaign['deadline'])) ?></span>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="pf-teks-bantu">Platform penggalangan dana terverifikasi 100% aman</span>
                </div>
            </div>
        </div>

        <!-- Formulir Donasi & Pembayaran -->
        <form action="<?= e(BASE_URL) ?>/proses_donasi.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
            <input type="hidden" name="id_campaign" value="<?= e((string) $campaign['id_campaign']) ?>">

            <!-- Pilihan Cepat & Input Nominal (SDD UI-014 Komponen 2) -->
            <div class="pf-card p-4 mb-4">
                <label class="form-label fs-6 fw-bold mb-2">Pilih Nominal Donasi</label>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($nominalCepat as $nominalPilihan): ?>
                        <button type="button" 
                                class="btn pf-tombol-nominal" 
                                data-nominal="<?= e((string) $nominalPilihan) ?>"
                                style="background-color: var(--green-pale); border: 1.5px solid var(--green-light); border-radius: var(--radius-pill); color: var(--green-deep); font-weight: 700; padding: 8px 18px; font-size: 13px;">
                            <?= e(formatRupiah($nominalPilihan)) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="mb-2">
                    <label class="form-label" for="nominal">Atau Masukkan Nominal Sendiri (Rp)</label>
                    <input class="form-control pf-angka" 
                           type="number" 
                           id="nominal" 
                           name="nominal" 
                           min="10000" 
                           step="1000" 
                           placeholder="Contoh: 50000" 
                           required 
                           value="<?= e($nilaiLama('nominal')) ?>">
                    <div class="form-text">Minimal donasi Rp 10.000. Seluruh donasi disalurkan tanpa potongan.</div>
                </div>
            </div>

            <!-- Metode Pembayaran (SDD UI-014 Komponen 3) -->
            <div class="pf-card p-4 mb-4">
                <label class="form-label fs-6 fw-bold mb-3">Metode Pembayaran (Payment Gateway Simulasi)</label>
                <div class="row g-3">
                    <?php foreach ($daftarMetode as $index => $metode): ?>
                        <div class="col-md-4">
                            <label class="d-block h-100 p-3 rounded-3" 
                                   style="background-color: var(--green-pale); border: 1.5px solid var(--green-light); cursor: pointer;">
                                <div class="form-check m-0">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="metode_pembayaran" 
                                           id="metode_<?= e((string) $index) ?>" 
                                           value="<?= e($metode) ?>" 
                                           <?= ($nilaiLama('metode_pembayaran') === $metode || ($nilaiLama('metode_pembayaran') === '' && $index === 0)) ? 'checked' : '' ?> 
                                           required>
                                    <label class="form-check-label fw-bold text-dark ms-2" for="metode_<?= e((string) $index) ?>">
                                        <?= e($metode) ?>
                                    </label>
                                </div>
                                <div class="small pf-teks-bantu mt-2 ms-4">
                                    <?php if ($metode === 'Transfer Bank'): ?>
                                        Virtual Account BCA, Mandiri, BNI, BRI
                                    <?php elseif ($metode === 'EWallet'): ?>
                                        GoPay, OVO, Dana, ShopeePay
                                    <?php else: ?>
                                        Scan kode QRIS instan
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Skenario Simulasi untuk Pengujian (SDD Bab 1.3 & SEQ-003) -->
                <div class="mt-4 pt-3" style="border-top: 1px dashed var(--green-light);">
                    <label class="form-label small fw-bold text-muted mb-2">Simulasi Hasil Transaksi (Khusus Pengujian Sistem):</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="simulasi_hasil" id="sim_berhasil" value="Berhasil" <?= $nilaiLama('simulasi_hasil') !== 'Gagal' ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold text-success" for="sim_berhasil">
                                Transaksi Berhasil (Saldo campaign bertambah)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="simulasi_hasil" id="sim_gagal" value="Gagal" <?= $nilaiLama('simulasi_hasil') === 'Gagal' ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold text-danger" for="sim_gagal">
                                Transaksi Gagal (Simulasi kegagalan sistem)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi (SDD UI-014 Komponen 4) -->
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button class="btn-pf-cta" type="submit">Bayar Donasi Sekarang</button>
                <a class="btn-pf-outline" href="<?= e(BASE_URL) ?>/dashboard_fundraiser.php">Batal</a>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var inputNominal = document.getElementById('nominal');
    var tombolCepat = document.querySelectorAll('.pf-tombol-nominal');

    tombolCepat.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            var nominal = this.getAttribute('data-nominal');
            if (inputNominal) {
                inputNominal.value = nominal;
                inputNominal.focus();
            }
        });
    });
});
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>

