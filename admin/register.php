<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    $status = $_POST['status'] ?? 'aktif';

    if ($username === '' || $password === '' || $confirmPassword === '' || $namaLengkap === '') {
        $message = 'Semua field wajib diisi.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password minimal 6 karakter.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Konfirmasi password tidak cocok.';
        $messageType = 'error';
    } elseif (!in_array($role, ['admin', 'superadmin'], true)) {
        $message = 'Role tidak valid.';
        $messageType = 'error';
    } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
        $message = 'Status tidak valid.';
        $messageType = 'error';
    } else {
        $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $checkStmt->bind_param('s', $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $existingUser = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ($existingUser) {
            $message = 'Username sudah digunakan, pilih yang lain.';
            $messageType = 'error';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $conn->prepare(
                'INSERT INTO users (username, password, nama_lengkap, jabatan, role, status) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $insertStmt->bind_param('ssssss', $username, $hashedPassword, $namaLengkap, $jabatan, $role, $status);

            if ($insertStmt->execute()) {
                $message = 'Akun berhasil dibuat.';
                $messageType = 'success';
            } else {
                $message = 'Gagal membuat akun: ' . $insertStmt->error;
                $messageType = 'error';
            }

            $insertStmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Register Akun Admin — Desa Ujungberung</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
  body { font-family: 'Poppins', sans-serif; }
</style>
</head>
<body class="bg-[#fafaf8] min-h-screen flex items-center justify-center px-4 py-8">
  <div class="w-full max-w-xl">
    <div class="text-center mb-6">
      <div class="w-14 h-14 rounded-full bg-[#0d3d1a] flex items-center justify-center mx-auto mb-3">
        <span class="text-white text-2xl">👤</span>
      </div>
      <h1 class="font-bold text-[#0d3d1a] text-lg">Buat Akun Admin Baru</h1>
      <p class="text-gray-500 text-sm">Halaman ini khusus dipakai untuk menambah akun perangkat desa.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
      <?php if ($message): ?>
        <div class="mb-4 px-4 py-3 rounded-xl border text-sm <?= $messageType === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none" placeholder="Masukkan username" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none" placeholder="Nama lengkap" />
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
            <input type="text" name="jabatan" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none" placeholder="Contoh: Sekretaris Desa" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select name="role" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none">
              <option value="admin">Admin</option>
              <option value="superadmin">Superadmin</option>
            </select>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none" placeholder="Minimal 6 karakter" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <input type="password" name="confirm_password" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none" placeholder="Ulangi password" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none">
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl bg-[#0d3d1a] text-white font-semibold hover:bg-[#1B5E20] transition-colors">
          Buat Akun
        </button>
      </form>
    </div>

    <p class="text-center mt-4">
      <a href="login.php" class="text-[#1B5E20] text-sm font-medium hover:underline">← Kembali ke Login</a>
    </p>
  </div>
</body>
</html>
