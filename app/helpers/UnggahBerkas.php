<?php

class UnggahBerkas
{
    public static function simpan(array $berkas, string $folderTujuan, array $ekstensiDiizinkan, int $ukuranMaks): array
    {
        $hasil = [
            'berhasil' => false,
            'nama_file' => '',
            'pesan' => '',
        ];

        if (!isset($berkas['error'], $berkas['name'], $berkas['size'], $berkas['tmp_name'])) {
            $hasil['pesan'] = 'Data berkas tidak lengkap.';
            return $hasil;
        }

        if ($berkas['error'] !== UPLOAD_ERR_OK) {
            $hasil['pesan'] = $berkas['error'] === UPLOAD_ERR_INI_SIZE || $berkas['error'] === UPLOAD_ERR_FORM_SIZE
                ? 'Ukuran berkas melebihi batas.'
                : 'Berkas gagal diunggah.';
            return $hasil;
        }

        if ((int) $berkas['size'] > $ukuranMaks) {
            $hasil['pesan'] = 'Ukuran berkas maksimal 2 MB.';
            return $hasil;
        }

        $ekstensi = strtolower(pathinfo((string) $berkas['name'], PATHINFO_EXTENSION));
        $ekstensiDiizinkan = array_map('strtolower', $ekstensiDiizinkan);
        if (!in_array($ekstensi, $ekstensiDiizinkan, true)) {
            $hasil['pesan'] = 'Format berkas tidak diizinkan.';
            return $hasil;
        }

        if (!is_uploaded_file($berkas['tmp_name'])) {
            $hasil['pesan'] = 'Berkas unggahan tidak valid.';
            return $hasil;
        }

        $namaFile = bin2hex(random_bytes(16)) . '.' . $ekstensi;
        $lokasiTujuan = rtrim($folderTujuan, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFile;

        if (!move_uploaded_file($berkas['tmp_name'], $lokasiTujuan)) {
            $hasil['pesan'] = 'Berkas gagal disimpan.';
            return $hasil;
        }

        $hasil['berhasil'] = true;
        $hasil['nama_file'] = $namaFile;

        return $hasil;
    }

    public static function hapus(string $folder, string $namaFile): void
    {
        if ($namaFile === '' || basename($namaFile) !== $namaFile) {
            return;
        }

        $lokasiFile = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namaFile;
        if (is_file($lokasiFile) && !unlink($lokasiFile)) {
            throw new RuntimeException('Berkas unggahan gagal dihapus.');
        }
    }
}