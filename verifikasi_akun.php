<?php
// Entri verifikasi akun fundraiser oleh admin (UI-012)

require_once 'app/bootstrap.php';

wajibLogin('Admin');

$controller = new CampaignController(
    new CampaignEntity($pdo),
    null,
    new UserCredentialsDB($pdo),
    null,
    new VerifikasiFundraiserEntity($pdo)
);
$controller->verifikasiAkun();
