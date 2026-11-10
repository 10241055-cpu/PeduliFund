<?php
// Entri publik untuk proses pendaftaran akun baru pengguna PeduliFund (UI-002)

require_once 'app/bootstrap.php';

$controller = new AuthController(new UserCredentialsDB($pdo));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->prosesSignUp();
} else {
    $controller->tampilkanSignUp();
}
