<?php

require_once 'app/bootstrap.php';

$campaignEntity = new CampaignEntity($pdo);
$pencairanEntity = new PencairanEntity($pdo);

$controller = new CampaignController($campaignEntity, $pencairanEntity);
$controller->hapusCampaign();
