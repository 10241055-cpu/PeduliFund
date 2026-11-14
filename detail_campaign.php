<?php
// Entri halaman detail campaign publik (UI-006)

require_once 'app/bootstrap.php';

$controller = new DonationController(new DonasiEntity($pdo), new CampaignEntity($pdo), $pdo);
$controller->tampilkanDetailCampaign();
