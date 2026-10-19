<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

requireLogin();

$nama = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Admin - Desa Ujungberung</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fafaf8] min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-green-700 font-semibold">Selamat datang</p>
                    <h1 class="text-2xl font-bold text-[#0d3d1a]">Halo, <?= htmlspecialchars($nama) ?></h1>
                </div>
                <a href="logout.php" class="px-4 py-2 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700">Keluar</a>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Status Login</h2>
                    <p class="text-sm text-gray-600">Anda berhasil masuk sebagai perangkat desa.</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-5">
                    <h2 class="font-semibold text-gray-800 mb-2">Aksi</h2>
                    <p class="text-sm text-gray-600">Halaman ini bisa segera dikembangkan untuk mengelola data desa.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
