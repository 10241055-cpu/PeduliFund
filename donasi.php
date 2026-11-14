<?php

require_once 'app/bootstrap.php';

wajibLogin();

$controller = new DonationController(new DonasiEntity($pdo), new CampaignEntity($pdo), $pdo);
$controller->tampilkanFormDonasi();

