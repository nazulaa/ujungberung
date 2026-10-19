<?php
/**
 * functions.php
 * Helper sederhana untuk membaca & menulis data desa.
 *
 * CATATAN PENTING:
 * Contoh ini memakai file JSON (data/desa-data.json) sebagai penyimpanan
 * supaya bisa langsung dijalankan tanpa setup database. Kalau situs desa
 * Anda sudah punya database (MySQL dsb.), ganti isi load_desa_data() dan
 * save_desa_data() agar membaca/menulis ke tabel yang sesuai — struktur
 * array yang dikembalikan/diterima bisa tetap sama supaya index.php tidak
 * perlu diubah.
 */

define('DESA_DATA_FILE', __DIR__ . '/../data/desa-data.json');
define('DESA_DATA_LOCK_FILE', __DIR__ . '/../data/.desa-data.lock');

function load_desa_data(): array
{
    if (!file_exists(DESA_DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(DESA_DATA_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/**
 * Simpan seluruh data desa kembali ke file JSON.
 * Memakai file lock supaya aman kalau ada beberapa admin yang
 * menyimpan perubahan pada saat bersamaan.
 */
function save_desa_data(array $data): bool
{
    $fp = fopen(DESA_DATA_LOCK_FILE, 'c');
    if (!$fp) {
        return false;
    }
    flock($fp, LOCK_EX);
    $ok = file_put_contents(
        DESA_DATA_FILE,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ) !== false;
    flock($fp, LOCK_UN);
    fclose($fp);
    return $ok;
}

/**
 * Ambil satu section data desa dengan fallback aman.
 */
function desa_get(array $data, string $section, $default = [])
{
    return $data[$section] ?? $default;
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}