<?php
// Beranda publik untuk pengunjung yang belum masuk (UI-001, SDD 5.2.1 & design.md zona 6.3-6.4)
$judulHalaman = 'Beranda';
require __DIR__ . '/layout/header.php';
?>
<section class="bagian-halaman">
    <div class="pf-tray">
        <div class="row justify-content-center text-center py-5">
            <div class="col-lg-9">
                <h1 class="pf-h1" style="font-size: 36px; letter-spacing: -0.8px;">Bersama Wujudkan Aksi Baik,<br>Satu Donasi Sekali Klik</h1>
                <p class="pf-subjudul mt-3">PeduliFund menghubungkan donatur dengan penggalang dana terverifikasi — transparan dari donasi, pencairan, hingga laporan pertanggungjawaban (LPJ).</p>
                <a class="btn-pf-cta mt-3 px-4" href="<?= e(BASE_URL) ?>/signup.php">Yuk Donasi</a>
            </div>
        </div>
    </div>
</section>

<section class="bagian-halaman" id="tentang">
    <div class="pf-tray">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <svg viewBox="0 0 400 300" role="img" aria-label="Ilustrasi donasi PeduliFund" style="width: 100%; height: auto;">
                    <rect x="30" y="50" width="250" height="200" rx="20" fill="var(--green-pale)" stroke="var(--green-light)" stroke-width="3"/>
                    <circle cx="330" cy="70" r="34" fill="var(--pink-soft)"/>
                    <circle cx="90" cy="255" r="22" fill="var(--pink-soft)"/>
                    <ellipse cx="155" cy="166" rx="52" ry="14" fill="var(--pink-primary)"/>
                    <ellipse cx="155" cy="146" rx="52" ry="14" fill="var(--pink-primary)" stroke="var(--surface-white)" stroke-width="3"/>
                    <ellipse cx="155" cy="126" rx="52" ry="14" fill="var(--pink-primary)" stroke="var(--surface-white)" stroke-width="3"/>
                    <text x="155" y="131" text-anchor="middle" font-size="13" font-weight="700" fill="var(--surface-white)">Rp</text>
                    <rect x="70" y="190" width="170" height="14" rx="7" fill="var(--green-light)"/>
                    <rect x="70" y="190" width="118" height="14" rx="7" fill="var(--green-primary)"/>
                    <rect x="252" y="140" width="104" height="110" rx="14" fill="var(--surface-white)" stroke="var(--green-light)" stroke-width="3"/>
                    <rect x="268" y="166" width="72" height="10" rx="5" fill="var(--green-light)"/>
                    <rect x="268" y="186" width="56" height="10" rx="5" fill="var(--green-light)"/>
                    <circle cx="304" cy="238" r="20" fill="var(--pink-soft)"/>
                    <path d="M294 238 l7 7 l13 -14" stroke="var(--green-primary)" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="col-lg-7">
                <h2 class="pf-modul-judul mb-3">Apa Itu PeduliFund?</h2>
                <p style="color: var(--text-body);">PeduliFund adalah platform crowdfunding yang mempertemukan <strong>penggalang dana</strong> dan <strong>donatur</strong> untuk aksi sosial di bidang sosial, pendidikan, kesehatan, bencana, dan lingkungan.</p>
                <p class="mb-0" style="color: var(--text-body);">Setiap campaign melewati verifikasi admin, setiap pencairan disetujui admin, dan setiap dana wajib melaporkan <strong>LPJ &amp; bukti nota</strong> yang diaudit — agar kepercayaan Anda terjaga dari awal hingga akhir.</p>
            </div>
        </div>
    </div>
</section>

<section class="bagian-halaman" id="cara-kerja">
    <div class="pf-tray">
        <h2 class="pf-modul-judul justify-content-center mb-4">Cara Kerja</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-card h-100 p-4">
                    <p class="pf-metrik-label">Langkah 1</p>
                    <h3 class="h6 fw-bold" style="color: var(--green-deep);">Daftar &amp; Terverifikasi</h3>
                    <p class="small mb-0" style="color: var(--text-body);">Buat akun donatur dalam semenit. Penggalang dana mengajukan verifikasi akun dan dokumen campaign kepada admin.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card h-100 p-4">
                    <p class="pf-metrik-label">Langkah 2</p>
                    <h3 class="h6 fw-bold" style="color: var(--green-deep);">Pilih Campaign &amp; Donasi</h3>
                    <p class="small mb-0" style="color: var(--text-body);">Temukan campaign aktif, lalu berdonasi via Transfer Bank, E-Wallet, atau QRIS — progres dana tampil real-time.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-card h-100 p-4">
                    <p class="pf-metrik-label">Langkah 3</p>
                    <h3 class="h6 fw-bold" style="color: var(--green-deep);">Dana Cair &amp; Transparan</h3>
                    <p class="small mb-0" style="color: var(--text-body);">Pencairan disetujui admin, dan penggalang wajib mengunggah LPJ beserta bukti nota untuk diaudit.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
