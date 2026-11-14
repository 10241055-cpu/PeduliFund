<?php
// Entri proses pengiriman pengajuan verifikasi akun (POST UI-008)

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo), new VerifikasiFundraiserEntity($pdo));
$controller->kirimPengajuanVerifikasi();
