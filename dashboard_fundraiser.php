<?php

require_once 'app/bootstrap.php';

wajibLogin('Fundraiser');
wajibVerifikasiWajah($pdo);

$userModel = new UserCredentialsDB($pdo);
$campaignEntity = new CampaignEntity($pdo);
$pencairanEntity = new PencairanEntity($pdo);
$pencairanController = new PencairanController($pencairanEntity, $campaignEntity, $userModel, $pdo);

$controller = new CampaignController($campaignEntity, $pencairanEntity, $userModel, $pencairanController);
$controller->tampilkanDashboard();
