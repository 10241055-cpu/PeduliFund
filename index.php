<?php
// Entri utama aplikasi PeduliFund: pengunjung melihat Beranda publik (UI-001),
// pengguna yang sudah masuk diarahkan ke dashboard sesuai perannya

require_once __DIR__ . '/app/bootstrap.php';

if (sudahLogin()) {
    $peran = peranPenggunaAktif();
    if ($peran === 'Donatur') {
        alihkan('dashboard_donatur.php');
    } elseif ($peran === 'Admin') {
        alihkan('dashboard_admin.php');
    } else {
        alihkan('dashboard_fundraiser.php');
    }
} else {
    require __DIR__ . '/views/beranda.php';
}
