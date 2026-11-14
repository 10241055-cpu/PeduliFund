<?php

require_once 'app/bootstrap.php';

wajibLogin('Fundraiser');

// Gerbang UI-007: fundraiser yang belum lolos verifikasi wajah diarahkan ke halaman verifikasi dulu
$userModel = new UserCredentialsDB($pdo);
if ($userModel->butuhVerifikasiWajah(idPenggunaAktif())) {
    alihkan('verifikasi_wajah.php');
}

$campaignEntity = new CampaignEntity($pdo);
$pencairanEntity = new PencairanEntity($pdo);
$pencairanController = new PencairanController($pencairanEntity, $campaignEntity, $userModel, $pdo);

$controller = new CampaignController($campaignEntity, $pencairanEntity, $userModel, $pencairanController);
$controller->tampilkanDashboard();