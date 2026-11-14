<?php
// Entri utama aplikasi PeduliFund: pengunjung melihat Beranda publik (UI-001),
// pengguna yang sudah masuk diarahkan ke dashboard sesuai perannya

require_once __DIR__ . '/app/bootstrap.php';

if (sudahLogin()) {
    alihkan(dashboardUntukPeran(peranPenggunaAktif()));
} else {
    require __DIR__ . '/views/beranda.php';
}
