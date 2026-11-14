<?php
// Entri dashboard admin (UI-018)

require_once 'app/bootstrap.php';

wajibLogin('Admin');

$campaignEntity = new CampaignEntity($pdo);
$pencairanEntity = new PencairanEntity($pdo);
$pencairanController = new PencairanController($pencairanEntity, $campaignEntity, new UserCredentialsDB($pdo), $pdo);
$pencairanController->periksaDanTerapkanSanksiLpj();

$jumlahAkunMenunggu = (new VerifikasiFundraiserEntity($pdo))->hitungMenunggu();
$jumlahCampaignMenunggu = $campaignEntity->hitungCampaignMenunggu();
$ringkasanPencairan = $pencairanEntity->ambilRingkasanAdmin();

require_once 'views/dashboard_admin.php';
