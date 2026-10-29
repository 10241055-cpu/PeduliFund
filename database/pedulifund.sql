CREATE DATABASE IF NOT EXISTS pedulifund
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE pedulifund;

CREATE TABLE IF NOT EXISTS pengguna (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Donatur', 'Fundraiser') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO pengguna (id_user, email, password, role)
VALUES (1, 'fundraiser-sementara@pedulifund.local', SHA2(UUID(), 256), 'Fundraiser');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;