<?php
// Entri gerbang verifikasi wajah (UI-007)

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo));
$controller->tampilkanVerifikasiWajah();
