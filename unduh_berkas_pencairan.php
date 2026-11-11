<?php
// Entri aman untuk pratinjau berkas LPJ dan bukti nota (hanya Admin atau Fundraiser pemilik campaign)

require_once 'app/bootstrap.php';

$controller = new PencairanController(
    new PencairanEntity($pdo),
    new CampaignEntity($pdo),
    new UserCredentialsDB($pdo),
    $pdo
);

$controller->unduhBerkasLpj();

