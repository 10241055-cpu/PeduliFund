<?php
// Helper UnggahBerkas untuk mengelola upload dan penghapusan file di PeduliFund
// Sesuai kesepakatan nomor 6 SDD

class UnggahBerkas
{
    /**
     * Menyimpan berkas yang diunggah dengan validasi status upload, ukuran, ekstensi, dan MIME type.
     *
     * @param array  $berkas            Data elemen $_FILES['field_name']
     * @param string $folderTujuan     Direktori tujuan penyimpanan berkas (berakhiran slash)
     * @param array  $ekstensiDiizinkan Daftar ekstensi yang diperbolehkan (misal ['pdf', 'jpg', 'png'])
     * @param int    $ukuranMaks        Ukuran maksimal berkas dalam byte
     * @return array                    ['berhasil' => bool, 'nama_file' => string, 'pesan' => string]
     */
    public static function simpan(array $berkas, string $folderTujuan, array $ekstensiDiizinkan, int $ukuranMaks): array
    {
        $hasil = [
            'berhasil'  => false,
            'nama_file' => '',
            'pesan'     => '',
        ];

        // 1. Periksa status error upload
        if (!isset($berkas['error']) || $berkas['error'] !== UPLOAD_ERR_OK) {
            $kodeError = (int) ($berkas['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($kodeError === UPLOAD_ERR_NO_FILE) {
                $hasil['pesan'] = 'Tidak ada berkas yang diunggah.';
            } elseif ($kodeError === UPLOAD_ERR_INI_SIZE || $kodeError === UPLOAD_ERR_FORM_SIZE) {
                $hasil['pesan'] = 'Ukuran berkas melebihi batas maksimal server.';
            } else {
                $hasil['pesan'] = 'Berkas gagal diunggah karena kesalahan sistem.';
            }
            return $hasil;
        }

        // 2. Periksa ukuran berkas tidak melebihi $ukuranMaks
        $ukuranBerkas = (int) ($berkas['size'] ?? 0);
        if ($ukuranBerkas > $ukuranMaks) {
            $maksMB = round($ukuranMaks / 1048576, 1);
            $hasil['pesan'] = "Ukuran dokumen melebihi {$maksMB} MB.";
            return $hasil;
        }

        // 3. Periksa ekstensi berkas (huruf kecil)
        $namaAsli = (string) ($berkas['name'] ?? '');
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));
        $ekstensiDiizinkanNorm = array_map('strtolower', $ekstensiDiizinkan);

        if ($ekstensi === '' || !in_array($ekstensi, $ekstensiDiizinkanNorm, true)) {
            $hasil['pesan'] = 'Format berkas tidak diizinkan. Hanya menerima format: ' . implode(', ', $ekstensiDiizinkan) . '.';
            return $hasil;
        }

        // 4. Periksa MIME type sebenarnya memakai finfo sesuai ekstensi
        if (!isset($berkas['tmp_name']) || !file_exists($berkas['tmp_name'])) {
            $hasil['pesan'] = 'Berkas temporer tidak ditemukan.';
            return $hasil;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeSebenarnya = $finfo->file($berkas['tmp_name']);

        $petaMime = [
            'pdf'  => ['application/pdf'],
            'jpg'  => ['image/jpeg', 'image/pjpeg'],
            'jpeg' => ['image/jpeg', 'image/pjpeg'],
            'png'  => ['image/png', 'image/x-png'],
        ];

        $mimeValid = $petaMime[$ekstensi] ?? [];
        if (!in_array($mimeSebenarnya, $mimeValid, true)) {
            $hasil['pesan'] = "Tipe berkas ({$mimeSebenarnya}) tidak valid untuk format .{$ekstensi}.";
            return $hasil;
        }

        // 5. Nama file baru acak: bin2hex(random_bytes(8)) . '_' . time() . '.' . ekstensi
        $namaFile = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ekstensi;

        // Pastikan folder tujuan tersedia
        if (!is_dir($folderTujuan)) {
            mkdir($folderTujuan, 0755, true);
        }

        $lokasiTujuan = rtrim($folderTujuan, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFile;

        // 6. Pindahkan berkas dengan move_uploaded_file (atau copy fallback jika dalam lingkungan pengujian non-HTTP)
        $dipindahkan = is_uploaded_file($berkas['tmp_name'])
            ? move_uploaded_file($berkas['tmp_name'], $lokasiTujuan)
            : @copy($berkas['tmp_name'], $lokasiTujuan);

        if (!$dipindahkan) {
            $hasil['pesan'] = 'Berkas gagal disimpan ke folder tujuan.';
            return $hasil;
        }

        // 7. Berhasil disimpan
        $hasil['berhasil']  = true;
        $hasil['nama_file'] = $namaFile;
        $hasil['pesan']     = '';

        return $hasil;
    }

    /**
     * Menghapus file fisik dari folder jika ada.
     *
     * @param string $folder   Direktori penyimpanan berkas
     * @param string $namaFile Nama berkas yang ingin dihapus
     * @return void
     */
    public static function hapus(string $folder, string $namaFile): void
    {
        if ($namaFile === '' || basename($namaFile) !== $namaFile) {
            return;
        }

        $lokasiFile = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFile;
        if (is_file($lokasiFile)) {
            @unlink($lokasiFile);
        }
    }
}