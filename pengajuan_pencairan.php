<?php
// Entri halaman pengajuan pencairan dana oleh Fundraiser (UI-015)

require_once 'app/bootstrap.php';

wajibLogin('Fundraiser');

$controller = new PencairanController(
    new PencairanEntity($pdo),
    new CampaignEntity($pdo),
    new UserCredentialsDB($pdo),
    $pdo
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->ajukanPencairan();
} else {
    $controller->tampilkanFormPengajuan();
}

