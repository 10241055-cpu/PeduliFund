<?php
// Entri halaman unggah LPJ dan bukti nota oleh Fundraiser (UI-016)

require_once 'app/bootstrap.php';

wajibLogin('Fundraiser');

$controller = new PencairanController(
    new PencairanEntity($pdo),
    new CampaignEntity($pdo),
    new UserCredentialsDB($pdo),
    $pdo
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->unggahLPJDonasi();
} else {
    $controller->tampilkanFormUnggahLpj();
}

