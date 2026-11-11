<?php
// Entri halaman review pencairan dan audit LPJ oleh Admin (UI-017)

require_once 'app/bootstrap.php';

wajibLogin('Admin');

$controller = new PencairanController(
    new PencairanEntity($pdo),
    new CampaignEntity($pdo),
    new UserCredentialsDB($pdo),
    $pdo
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = (string) ($_POST['aksi'] ?? '');
    if ($aksi === 'keputusan_lpj') {
        $controller->kirimKeputusanLPJ();
    } else {
        $controller->prosesKeputusanPencairan();
    }
} else {
    $controller->tampilkanReviewAdmin();
}

