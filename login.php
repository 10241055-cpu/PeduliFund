<?php
// Entri publik untuk proses masuk (login) akun pengguna PeduliFund (UI-003)

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->prosesLogin();
} else {
    $controller->tampilkanLogin();
}
