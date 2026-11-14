<?php
// Entri halaman daftar campaign publik untuk pengunjung dan donatur (UI-005)

require_once 'app/bootstrap.php';

$controller = new DonationController(new DonasiEntity($pdo), new CampaignEntity($pdo), $pdo);
$controller->tampilkanDaftarCampaign();
