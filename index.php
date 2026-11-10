<?php
// Entri utama aplikasi PeduliFund: mengarahkan ke dashboard bila sudah masuk, atau ke halaman login bila belum

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
    alihkan('login.php');
}
