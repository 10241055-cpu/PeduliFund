<?php

// Otomatis mengambil nama folder utama proyek (misal: "Pedulifund" atau "P3L")
$namaFolder = basename(dirname(__DIR__));
define('BASE_URL', '/' . $namaFolder);

define('APP_NAME', 'PeduliFund');
define('ID_FUNDRAISER_SEMENTARA', 1);
define('UKURAN_MAKS_UPLOAD', 2097152);
define('DIR_UPLOAD_DOKUMEN', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'dokumen_campaign' . DIRECTORY_SEPARATOR);
define('DIR_UPLOAD_GAMBAR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'gambar_campaign' . DIRECTORY_SEPARATOR);
define('URL_UPLOAD_GAMBAR', BASE_URL . '/uploads/gambar_campaign/');
