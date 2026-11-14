<?php
// Entri dashboard donatur (UI-004)

require_once 'app/bootstrap.php';

wajibLogin('Donatur');

$riwayatDonasi = (new DonasiEntity($pdo))->ambilRiwayatByDonatur(idPenggunaAktif());
$donasiBerhasil = array_values(array_filter(
    $riwayatDonasi,
    static fn (array $d): bool => $d['status_pembayaran'] === 'Berhasil'
));

$totalDonasi = array_sum(array_map(static fn (array $d): float => (float) $d['nominal'], $donasiBerhasil));
$jumlahCampaign = count(array_unique(array_map(static fn (array $d): int => (int) $d['id_campaign'], $donasiBerhasil)));
$donasiTerakhir = $donasiBerhasil[0] ?? null;

require_once 'views/dashboard_donatur.php';
