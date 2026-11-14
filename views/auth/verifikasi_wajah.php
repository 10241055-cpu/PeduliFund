<?php
// Gerbang verifikasi wajah untuk fundraiser (UI-007, SDD 5.2.7):
// kamera live dengan garis oval wajah. PHP native tidak punya pencocokan wajah
// server-side; deteksi memakai FaceDetector peramban bila tersedia, dan selalu
// ada unggah manual sebagai jalan keluar semua peramban.
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
                        <form id="form-wajah" action="<?= e(BASE_URL) ?>/proses_verifikasi_wajah.php" method="post" enctype="multipart/form-data" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e(tokenCsrf()) ?>">
                            <?php // Berkas yang benar-benar dikirim; diisi hasil capture kamera atau pilihan unggah manual ?>
                            <input type="file" id="foto_wajah" name="foto_wajah" accept="image/jpeg" hidden>

                            <div id="area-kamera" style="position: relative; border-radius: var(--radius-card); overflow: hidden; aspect-ratio: 4 / 3; background: var(--bg-outer);">
                                <video id="pratinjau" autoplay playsinline muted style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"></video>
                                <svg viewBox="0 0 100 75" preserveAspectRatio="xMidYMid slice" style="position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none;" aria-hidden="true">
                                    <ellipse cx="50" cy="37.5" rx="15" ry="21" fill="none" stroke="var(--surface-white)" stroke-width="1.4" stroke-dasharray="6 4" opacity="0.95"/>
                                    <path d="M50 63 v6 M42 66 h16" stroke="var(--surface-white)" stroke-width="1.4" stroke-linecap="round" opacity="0.7"/>
                                </svg>
                                <img id="potret" alt="Foto wajah yang diambil" hidden style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;">
                            </div>

                            <p id="status-wajah" class="small fw-semibold text-center mt-2 mb-3" role="status">Kamera menyalakan sendiri... posisikan wajah di dalam garis.</p>

                            <div class="d-flex justify-content-center gap-2 mb-3">
                                <button id="tombol-kamera" type="button" class="btn-pf-outline" hidden>Coba Kamera Lagi</button>
                                <button id="tombol-foto" type="button" class="btn-pf-cta" disabled>Ambil Foto</button>
                                <button id="tombol-ulang" type="button" class="btn-pf-outline" hidden>Ambil Ulang</button>
                            </div>

                            <div class="pf-card p-3 mb-4" style="background: var(--green-pale);">
                                <label class="form-label small fw-semibold mb-1" for="foto_manual" style="color: var(--green-deep);">Kamera tidak tersedia? Unggah foto wajah manual</label>
                                <input class="form-control form-control-sm" type="file" id="foto_manual" accept="image/jpeg,image/png" capture="user">
                                <div class="form-text mb-0">JPG/PNG, maksimal 2 MB. Foto dapat diulang sebelum diverifikasi.</div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-wajah');
    if (!form) {
        return; // halaman panel "sudah terverifikasi": tidak ada form kamera
    }

    var video = document.getElementById('pratinjau');
    var potret = document.getElementById('potret');
    var statusWajah = document.getElementById('status-wajah');
    var tombolKamera = document.getElementById('tombol-kamera');
    var tombolFoto = document.getElementById('tombol-foto');
    var tombolUlang = document.getElementById('tombol-ulang');
    var inputWajah = document.getElementById('foto_wajah');
    var inputManual = document.getElementById('foto_manual');

    var kameraAktif = false;
    var fotoSiap = false;
    var detektor = null;
    var canvasHasil = null;
    var urlPotret = null;

    function aturStatus(pesan, kelasBootstrap) {
        statusWajah.textContent = pesan;
        statusWajah.className = 'small fw-semibold text-center mt-2 mb-3' + (kelasBootstrap ? ' ' + kelasBootstrap : '');
    }

    function mulaiKamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            aturStatus('Peramban tidak mendukung kamera; silakan unggah foto manual di bawah.', 'text-danger');
            tombolKamera.hidden = false;
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        }).then(function (aliran) {
            video.srcObject = aliran;
            kameraAktif = true;
            tombolKamera.hidden = true;
            tombolFoto.disabled = false;
            aturStatus('Kamera aktif. Posisikan wajah di dalam garis, lalu ambil foto.');

            // Deteksi wajah hanya ada di Chromium; browser lain tetap jalan manual
            if (window.FaceDetector) {
                try {
                    detektor = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
                    setInterval(pindaiWajah, 700);
                } catch (galat) {
                    detektor = null;
                }
            }
        }).catch(function () {
            kameraAktif = false;
            tombolKamera.hidden = false;
            aturStatus('Kamera tidak dapat diakses (izin ditolak atau tidak ada); silakan unggah foto manual di bawah.', 'text-danger');
        });
    }

    function pindaiWajah() {
        if (!kameraAktif || detektor === null || !video.videoWidth) {
            return;
        }

        detektor.detect(video).then(function (wajah) {
            if (wajah.length > 0) {
                aturStatus('Wajah terdeteksi.', 'text-success');
                if (!fotoSiap) {
                    ambilFoto(); // wajah muncul -> potret otomatis, Verifikasi tetap ditekan pengguna
                }
            }
        }).catch(function () {
            // deteksi gagal sekali-sekali (frame hitam dsb.): abaikan, coba lagi di detak berikutnya
        });
    }

    function ambilFoto() {
        if (!video.videoWidth) {
            aturStatus('Kamera belum siap, coba beberapa saat lagi.', 'text-danger');
            return;
        }

        canvasHasil = document.createElement('canvas');
        canvasHasil.width = video.videoWidth;
        canvasHasil.height = video.videoHeight;
        canvasHasil.getContext('2d').drawImage(video, 0, 0);

        canvasHasil.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            var pengangkut = new DataTransfer();
            pengangkut.items.add(new File([blob], 'wajah_peramban.jpg', { type: 'image/jpeg' }));
            inputWajah.files = pengangkut.files;

            if (urlPotret) {
                URL.revokeObjectURL(urlPotret);
            }
            urlPotret = URL.createObjectURL(blob);
            potret.src = urlPotret;
            potret.hidden = false;

            fotoSiap = true;
            tombolFoto.hidden = true;
            tombolUlang.hidden = false;
            aturStatus('Foto siap. Periksa lalu tekan Verifikasi.', 'text-success');
        }, 'image/jpeg', 0.85);
    }

    function ambilUlang() {
        inputWajah.value = '';
        potret.hidden = true;
        if (urlPotret) {
            URL.revokeObjectURL(urlPotret);
            urlPotret = null;
        }
        canvasHasil = null;
        fotoSiap = false;
        tombolUlang.hidden = true;
        tombolFoto.hidden = false;
        aturStatus('Kamera aktif. Posisikan wajah di dalam garis, lalu ambil foto.');
    }

    tombolKamera.addEventListener('click', mulaiKamera);
    tombolFoto.addEventListener('click', ambilFoto);
    tombolUlang.addEventListener('click', ambilUlang);

    // Unggah manual masuk ke jalur yang sama dengan hasil capture
    inputManual.addEventListener('change', function () {
        if (!inputManual.files.length) {
            return;
        }

        var pengangkut = new DataTransfer();
        pengangkut.items.add(inputManual.files[0]);
        inputWajah.files = pengangkut.files;

        if (urlPotret) {
            URL.revokeObjectURL(urlPotret);
        }
        urlPotret = URL.createObjectURL(inputManual.files[0]);
        potret.src = urlPotret;
        potret.hidden = false;

        fotoSiap = true;
        tombolUlang.hidden = false;
        aturStatus('Foto manual siap. Periksa lalu tekan Verifikasi.', 'text-success');
    });

    // SDD 5.2.7 poin 4: tanpa wajah -> pesan gagal, verifikasi diulang di halaman ini
    form.addEventListener('submit', function (peristiwa) {
        if (!inputWajah.files.length) {
            peristiwa.preventDefault();
            aturStatus('Ambil foto wajah terlebih dahulu (atau unggah manual).', 'text-danger');
            return;
        }

        if (detektor === null || !canvasHasil) {
            return; // tanpa FaceDetector: verifikasi manual pengguna (self-declaration)
        }

        detektor.detect(canvasHasil).then(function (wajah) {
            if (wajah.length > 0) {
                form.submit();
            } else {
                aturStatus('Wajah tidak cocok, ambil foto kembali.', 'text-danger');
            }
        }).catch(function () {
            form.submit(); // deteksi gagal: jangan blokir pengguna karena keterbatasan platform
        });

        peristiwa.preventDefault();
    });

    mulaiKamera();
});
</script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
