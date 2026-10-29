-- Database: pedulifund
-- Sesuai Kamus Data SDD PeduliFund

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

-- Data contoh pengguna: id_user 1, role Fundraiser
INSERT INTO pengguna (id_user, email, password, role)
VALUES (1, 'fundraiser@pedulifund.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fundraiser')
ON DUPLICATE KEY UPDATE 
    email = VALUES(email),
    password = VALUES(password),
    role = VALUES(role);

-- Data contoh 3 campaign milik id_fundraiser 1 (dokumen_campaign: 'contoh_dokumen.pdf')
INSERT INTO campaign (id_campaign, id_fundraiser, judul, jenis, target_dana, dana_terkumpul, deadline, dokumen_campaign, status_campaign, deskripsi, gambar_campaign)
VALUES
(1, 1, 'Bantu Renovasi Panti Asuhan', 'Sosial', 25000000.00, 7500000.00, '2026-12-31', 'contoh_dokumen.pdf', 'Aktif', 'Penggalangan dana untuk renovasi fasilitas asrama dan ruang belajar anak-anak panti asuhan.', NULL),
(2, 1, 'Bantuan Korban Banjir', 'Bencana', 50000000.00, 0.00, '2026-12-15', 'contoh_dokumen.pdf', 'Pending', 'Penyaluran bantuan logistik, pakaian layak pakai, dan makanan siap saji untuk warga terdampak banjir.', NULL),
(3, 1, 'Beasiswa Anak Desa', 'Pendidikan', 10000000.00, 4200000.00, '2026-10-01', 'contoh_dokumen.pdf', 'Selesai', 'Program bantuan biaya sekolah dan perlengkapan belajar bagi anak-anak berprestasi di pelosok desa.', NULL)
ON DUPLICATE KEY UPDATE
    id_fundraiser = VALUES(id_fundraiser),
    judul = VALUES(judul),
    jenis = VALUES(jenis),
    target_dana = VALUES(target_dana),
    dana_terkumpul = VALUES(dana_terkumpul),
    deadline = VALUES(deadline),
    dokumen_campaign = VALUES(dokumen_campaign),
    status_campaign = VALUES(status_campaign),
    deskripsi = VALUES(deskripsi),
    gambar_campaign = VALUES(gambar_campaign);