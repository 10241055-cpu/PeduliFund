<?php

$host = 'localhost';
$namaDatabase = 'pedulifund';
$namaPengguna = 'root';
$kataSandi = '';
$charset = 'utf8mb4';
$dsn = "mysql:host={$host};dbname={$namaDatabase};charset={$charset}";

try {
    $pdo = new PDO($dsn, $namaPengguna, $kataSandi, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('Koneksi database gagal.');
}