<?php

require_once 'app/bootstrap.php';

wajibLogin('Fundraiser');

$campaignEntity = new CampaignEntity($pdo);
$pencairanEntity = new PencairanEntity($pdo);

$controller = new CampaignController($campaignEntity, $pencairanEntity);
$controller->hapusCampaign();
