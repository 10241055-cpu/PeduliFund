<?php
// Entri publik untuk proses keluar (logout) akun pengguna PeduliFund

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo));
$controller->prosesLogout();
