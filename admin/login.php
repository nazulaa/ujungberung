<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Kalau sudah login, langsung lempar ke halaman depan
if (!empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

// Rate limiting sederhana: max 5 percobaan gagal, kunci 5 menit
const MAX_ATTEMPTS = 5;
const LOCKOUT_SECONDS = 300;

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_locked_until'] = 0;
}

$isLocked = $_SESSION['login_locked_until'] > time();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {

    $token    = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, password, nama_lengkap, jabatan, role, status
             FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && $user['status'] === 'aktif' && password_verify($password, $user['password'])) {
            // Login berhasil
            session_regenerate_id(true);
            $_SESSION['user_id']        = $user['id'];
            $_SESSION['username']       = $user['username'];
            $_SESSION['nama_lengkap']   = $user['nama_lengkap'];
            $_SESSION['jabatan']        = $user['jabatan'];
            $_SESSION['role']           = $user['role'];
            $_SESSION['login_attempts'] = 0;

            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= MAX_ATTEMPTS) {
                $_SESSION['login_locked_until'] = time() + LOCKOUT_SECONDS;
                $_SESSION['login_attempts'] = 0;
            }
            $error = ($user && $user['status'] !== 'aktif')
                ? 'Akun ini sudah dinonaktifkan. Hubungi admin lain.'
                : 'Username atau password salah.';
        }
    }
}

$isLocked = $_SESSION['login_locked_until'] > time();
if ($isLocked) {
    $sisaMenit = ceil(($_SESSION['login_locked_until'] - time()) / 60);
    $error = "Terlalu banyak percobaan gagal. Coba lagi dalam {$sisaMenit} menit.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login Perangkat Desa — Desa Ujungberung</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
  body { font-family: 'Poppins', sans-serif; }
</style>
</head>
<body class="bg-[#fafaf8] min-h-screen flex items-center justify-center px-4">

  <div class="w-full max-w-sm">
    <div class="text-center mb-6">
      <div class="w-14 h-14 rounded-full bg-[#0d3d1a] flex items-center justify-center mx-auto mb-3">
        <span class="text-white text-2xl">🏡</span>
      </div>
      <h1 class="font-bold text-[#0d3d1a] text-lg">Login Perangkat Desa</h1>
      <p class="text-gray-500 text-sm">Desa Ujungberung · Kab. Majalengka</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
      <?php if ($error): ?>
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="login.php" class="space-y-4" <?= $isLocked ? 'aria-disabled="true"' : '' ?>>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input
            type="text"
            name="username"
            required
            autofocus
            <?= $isLocked ? 'disabled' : '' ?>
            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none disabled:bg-gray-100"
            placeholder="Masukkan username"
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input
            type="password"
            name="password"
            required
            <?= $isLocked ? 'disabled' : '' ?>
            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#0d3d1a] focus:outline-none disabled:bg-gray-100"
            placeholder="Masukkan password"
          >
        </div>

        <button
          type="submit"
          <?= $isLocked ? 'disabled' : '' ?>
          class="w-full py-2.5 rounded-xl bg-[#0d3d1a] text-white font-semibold hover:bg-[#1B5E20] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
        >
          Masuk
        </button>
      </form>
    </div>

    <p class="text-center text-gray-400 text-xs mt-6">
      Halaman ini hanya untuk perangkat desa.<br>
      Akun tidak dapat didaftarkan sendiri — hubungi admin desa.
    </p>
    <p class="text-center mt-4">
      <a href="index.php" class="text-[#1B5E20] text-sm font-medium hover:underline">← Kembali ke Beranda</a>
    </p>
  </div>

</body>
</html>