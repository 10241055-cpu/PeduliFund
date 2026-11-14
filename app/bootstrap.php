<?php

// Jam aplikasi selalu WIB agar perbandingan deadline/batas LPJ konsisten di semua komputer anggota
date_default_timezone_set('Asia/Jakarta');

// Cookie sesi tidak bisa dibaca JavaScript (httponly) dan tidak dikirim lintas-situs (Lax)
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/../config/konfigurasi.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/helpers/fungsi.php';
require_once __DIR__ . '/helpers/UnggahBerkas.php';
require_once __DIR__ . '/models/UserCredentialsDB.php';
require_once __DIR__ . '/models/CampaignEntity.php';
require_once __DIR__ . '/models/DonasiEntity.php';
require_once __DIR__ . '/models/PencairanEntity.php';
require_once __DIR__ . '/models/VerifikasiFundraiserEntity.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CampaignController.php';
require_once __DIR__ . '/controllers/DonationController.php';
require_once __DIR__ . '/controllers/PencairanController.php';
