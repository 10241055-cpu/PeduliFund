-- Database: pedulifund
-- Schema DDL: Struktur Basis Data PeduliFund

CREATE DATABASE IF NOT EXISTS pedulifund
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pedulifund;

-- Tabel pengguna
CREATE TABLE IF NOT EXISTS pengguna (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Donatur', 'Fundraiser') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel campaign
CREATE TABLE IF NOT EXISTS campaign (
    id_campaign INT AUTO_INCREMENT PRIMARY KEY,
    id_fundraiser INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    jenis VARCHAR(100) NOT NULL,
    target_dana DECIMAL(15,2) NOT NULL,
    dana_terkumpul DECIMAL(15,2) DEFAULT 0.00,
    deadline DATE NOT NULL,
    dokumen_campaign VARCHAR(255) NOT NULL,
    status_campaign ENUM('Pending', 'Aktif', 'Ditolak', 'Selesai') DEFAULT 'Pending',
    deskripsi TEXT NOT NULL,
    gambar_campaign VARCHAR(255) NULL,
    CONSTRAINT fk_campaign_pengguna
        FOREIGN KEY (id_fundraiser) REFERENCES pengguna (id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel donasi (SDD Bagian 4.3 Tabel 4.5: Transaksi Donasi)
CREATE TABLE IF NOT EXISTS donasi (
    id_donasi INT AUTO_INCREMENT PRIMARY KEY,
    id_campaign INT NOT NULL,
    id_donatur INT NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    metode_pembayaran ENUM('Transfer Bank', 'EWallet', 'QRIS') NOT NULL,
    status_pembayaran ENUM('Berhasil', 'Gagal') NOT NULL,
    tanggal_pembayaran DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_donasi_campaign
        FOREIGN KEY (id_campaign) REFERENCES campaign (id_campaign)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_donasi_pengguna
        FOREIGN KEY (id_donatur) REFERENCES pengguna (id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
