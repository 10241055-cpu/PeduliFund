<?php
// Entri verifikasi campaign oleh admin (UI-013)

require_once 'app/bootstrap.php';

wajibLogin('Admin');

$controller = new CampaignController(
    new CampaignEntity($pdo),
    null,
    new UserCredentialsDB($pdo),
    null,
    new VerifikasiFundraiserEntity($pdo)
);
$controller->verifikasiCampaign();
