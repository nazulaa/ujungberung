<?php
/**
 * config/auth.php
 * Pengecekan status login perangkat desa (admin), sejajar dengan
 * config/config.php dan config/db.php yang sudah ada di proyek Anda.
 *
 * Setelah login sukses (nanti di admin/login.php Anda), isi session berikut:
 *
 *   $_SESSION['admin_logged_in'] = true;
 *   $_SESSION['admin_name']      = 'Nama Perangkat Desa';
 *   $_SESSION['admin_role']      = 'kepala_desa' | 'sekretaris' | dst;
 *
 * index.php dan api/update-section.php hanya memanggil fungsi di file ini,
 * jadi kalau nanti login-nya dipindah/diganti, cukup ubah file ini saja.
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function current_admin_name(): string
{
    return $_SESSION['admin_name'] ?? 'Perangkat Desa';
}

/**
 * Dipanggil di awal endpoint API yang butuh proteksi admin.
 * Menghentikan request dengan HTTP 401 kalau belum login.
 */
function require_admin_or_401(): void
{
    if (!is_admin_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Anda harus login sebagai perangkat desa untuk melakukan perubahan ini.']);
        exit;
    }
}