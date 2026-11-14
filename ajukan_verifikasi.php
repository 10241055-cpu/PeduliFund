<?php
// Entri form pengajuan verifikasi akun fundraiser (UI-008)

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo), new VerifikasiFundraiserEntity($pdo));
$controller->tampilkanPengajuanVerifikasi();
