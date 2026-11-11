<?php

require_once 'app/bootstrap.php';

$controller = new DonationController(new DonasiEntity($pdo), new CampaignEntity($pdo));
$controller->prosesDonasi();

