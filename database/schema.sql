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
    role ENUM('Admin', 'Donatur', 'Fundraiser') NOT NULL,
    status_akun ENUM('Aktif', 'Dibekukan') NOT NULL DEFAULT 'Aktif',
    suspend_sampai DATE NULL,
    -- Di luar kamus SDD: gerbang UI-007 menuntut hasil verifikasi wajah tersimpan (NULL = belum lolos)
    foto_wajah VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel verifikasi_fundraiser (SDD Bab 4.3 Tabel 4.3)
CREATE TABLE IF NOT EXISTS verifikasi_fundraiser (
    id_verifikasi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    -- Kolom data diri & tanggal melebihi kamus SDD karena dituntut wireframe UI-008 (data diri + aturan ajukan ulang 14 hari)
    jenis_fundraiser ENUM('Individu', 'Organisasi') NOT NULL,
    nama_lengkap VARCHAR(150) NOT NULL,
    nomor_identitas VARCHAR(50) NOT NULL,
    nomor_telepon VARCHAR(20) NOT NULL,
    dokumen_verifikasi VARCHAR(255) NOT NULL,
    dokumen_pendukung VARCHAR(255) NULL,
    status_dokumen ENUM('Menunggu', 'Valid', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
    alasan_penolakan TEXT NULL,
    tanggal_pengajuan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tanggal_keputusan DATETIME NULL,
    CONSTRAINT fk_verifikasi_pengguna
        FOREIGN KEY (id_user) REFERENCES pengguna (id_user)
        ON DELETE CASCADE ON UPDATE CASCADE
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
    -- Di luar kamus SDD: wireframe UI-013 menuntut alasan penolakan wajib tersimpan (mintaRevisiFormulir)
    alasan_penolakan TEXT NULL,
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

-- Tabel pencairan_dana
CREATE TABLE IF NOT EXISTS pencairan_dana (
    id_pencairan INT AUTO_INCREMENT PRIMARY KEY,
    id_campaign INT NOT NULL,
    nominal_pencairan DECIMAL(15,2) NOT NULL,
    nama_bank VARCHAR(100) NOT NULL,
    nomor_rekening VARCHAR(30) NOT NULL,
    atas_nama VARCHAR(150) NOT NULL,
    status_pengajuan ENUM('Menunggu', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
    alasan_penolakan TEXT NULL,
    tanggal_pencairan DATETIME NULL,
    batas_unggah_lpj DATE NULL,
    dokumen_lpj VARCHAR(255) NULL,
    bukti_nota VARCHAR(255) NULL,
    tanggal_unggah_lpj DATETIME NULL,
    status_transparansi ENUM('Menunggu', 'Terverifikasi', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
    CONSTRAINT fk_pencairan_campaign
        FOREIGN KEY (id_campaign) REFERENCES campaign (id_campaign)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
