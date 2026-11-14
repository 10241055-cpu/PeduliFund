<?php
// Entri proses verifikasi wajah (POST UI-007)

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo));
$controller->prosesVerifikasiWajah();
