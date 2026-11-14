<?php
// Entri pratinjau dokumen verifikasi akun untuk admin (UI-012)

require_once 'app/bootstrap.php';

wajibLogin('Admin');

$controller = new CampaignController(
    new CampaignEntity($pdo),
    null,
    null,
    null,
    new VerifikasiFundraiserEntity($pdo)
);
$controller->unduhDokumenVerifikasi();
