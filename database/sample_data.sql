-- Database: pedulifund
-- Sample Data DML: Data Awal Pengujian PeduliFund

USE pedulifund;

-- Data contoh pengguna: id_user 1 (Fundraiser) dan id_user 2 (Donatur)
INSERT INTO pengguna (id_user, email, password, role)
VALUES 
(1, 'fundraiser@pedulifund.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fundraiser'),
(2, 'donatur@pedulifund.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Donatur')
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

-- Data contoh transaksi donasi (ACT-003 / Melakukan Donasi)
INSERT INTO donasi (id_donasi, id_campaign, id_donatur, nominal, metode_pembayaran, status_pembayaran, tanggal_pembayaran)
VALUES
(1, 1, 2, 5000000.00, 'Transfer Bank', 'Berhasil', '2026-10-25 10:15:00'),
(2, 1, 2, 2500000.00, 'QRIS', 'Berhasil', '2026-10-26 14:30:00'),
(3, 1, 2, 100000.00, 'EWallet', 'Gagal', '2026-10-27 09:00:00')
ON DUPLICATE KEY UPDATE
    id_campaign = VALUES(id_campaign),
    id_donatur = VALUES(id_donatur),
    nominal = VALUES(nominal),
    metode_pembayaran = VALUES(metode_pembayaran),
    status_pembayaran = VALUES(status_pembayaran),
    tanggal_pembayaran = VALUES(tanggal_pembayaran);
