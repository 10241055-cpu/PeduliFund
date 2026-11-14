<?php
// Entri pratinjau dokumen campaign untuk admin (UI-013)

require_once 'app/bootstrap.php';

wajibLogin('Admin');

$controller = new CampaignController(new CampaignEntity($pdo));
$controller->unduhDokumenCampaign();
