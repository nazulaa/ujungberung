<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    if (APP_DEBUG) {
        die("Koneksi gagal: " . $conn->connect_error);
    } else {
        die("Terjadi kesalahan pada server. Silakan coba lagi nanti.");
    }
}

// Pastikan koneksi pakai utf8mb4 (biar teks bahasa Indonesia & emoji aman tersimpan)
$conn->set_charset("utf8mb4");