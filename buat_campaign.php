<?php

require_once 'app/bootstrap.php';

$controller = new CampaignController(new CampaignEntity($pdo));
$controller->tampilkanFormCampaign();