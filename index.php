<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/admin/includes/auth.php';

define('WA_ADMIN_NUMBER', '6281234567890');

define('TELEGRAM_BOT_TOKEN', '');
define('TELEGRAM_CHAT_ID', '');

$conn->query("CREATE TABLE IF NOT EXISTS site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    value_text TEXT DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_section_key (section, key_name)
) ENGINE=InnoDB");

function getSiteText($conn, string $section, string $key, string $default = ''): string
{
  $stmt = $conn->prepare('SELECT value_text FROM site_content WHERE section = ? AND key_name = ? LIMIT 1');
  $stmt->bind_param('ss', $section, $key);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  return $row['value_text'] ?? $default;
}

function saveSiteText($conn, string $section, string $key, string $value, int $userId): void
{
  $stmt = $conn->prepare('INSERT INTO site_content (section, key_name, value_text, updated_by) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value_text = VALUES(value_text), updated_by = VALUES(updated_by), updated_at = CURRENT_TIMESTAMP');
  $stmt->bind_param('sssi', $section, $key, $value, $userId);
  $stmt->execute();
  $stmt->close();
}

function getPrograms($conn, string $category): array
{
  $stmt = $conn->prepare('SELECT * FROM program_desa WHERE kategori = ? AND status = ? ORDER BY urutan ASC, id DESC');
  $status = 'tampil';
  $stmt->bind_param('ss', $category, $status);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function getProgramById($conn, int $id): ?array
{
  $stmt = $conn->prepare('SELECT * FROM program_desa WHERE id = ? LIMIT 1');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
  return $row ?: null;
}

function saveProgram($conn, array $data, int $userId): void
{
  if (!empty($data['id'])) {
    $stmt = $conn->prepare('UPDATE program_desa SET kategori = ?, judul = ?, deskripsi = ?, jadwal = ?, gambar = ?, urutan = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->bind_param('sssssii', $data['kategori'], $data['judul'], $data['deskripsi'], $data['jadwal'], $data['gambar'], $data['urutan'], $data['id']);
  } else {
    $stmt = $conn->prepare('INSERT INTO program_desa (kategori, judul, deskripsi, jadwal, gambar, urutan, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $status = 'tampil';
    $stmt->bind_param('sssssisi', $data['kategori'], $data['judul'], $data['deskripsi'], $data['jadwal'], $data['gambar'], $data['urutan'], $status, $userId);
  }
  $stmt->execute();
  $stmt->close();
}

function deleteProgram($conn, int $id): void
{
  $stmt = $conn->prepare('DELETE FROM program_desa WHERE id = ?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
}

function getActiveStatistic($conn): array
{
  $stmt = $conn->prepare('SELECT * FROM statistik_desa WHERE is_aktif = 1 ORDER BY tahun DESC, id DESC LIMIT 1');
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  return $row ?: [
    'tahun' => date('Y'),
    'jumlah_penduduk' => 3723,
    'jumlah_kk' => 1222,
    'jumlah_laki_laki' => 1872,
    'jumlah_perempuan' => 1851,
    'luas_wilayah_ha' => 244.95,
    'jumlah_dusun' => 5,
    'jumlah_rt' => 19,
    'jumlah_rw' => 9,
    'ketinggian_mdpl' => 650,
    'kode_pos' => '45461',
    'is_aktif' => 1,
  ];
}

function saveStatistic($conn, array $data, int $userId): void
{
  $stmt = $conn->prepare('SELECT id FROM statistik_desa WHERE is_aktif = 1 LIMIT 1');
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();

  if ($row) {
    $stmt = $conn->prepare('UPDATE statistik_desa SET tahun = ?, jumlah_penduduk = ?, jumlah_kk = ?, jumlah_laki_laki = ?, jumlah_perempuan = ?, luas_wilayah_ha = ?, jumlah_dusun = ?, jumlah_rt = ?, jumlah_rw = ?, ketinggian_mdpl = ?, kode_pos = ?, updated_by = ? WHERE id = ?');
    $stmt->bind_param('iiiiidiiiiisi', $data['tahun'], $data['jumlah_penduduk'], $data['jumlah_kk'], $data['jumlah_laki_laki'], $data['jumlah_perempuan'], $data['luas_wilayah_ha'], $data['jumlah_dusun'], $data['jumlah_rt'], $data['jumlah_rw'], $data['ketinggian_mdpl'], $data['kode_pos'], $userId, $row['id']);
  } else {
    $stmt = $conn->prepare('INSERT INTO statistik_desa (tahun, jumlah_penduduk, jumlah_kk, jumlah_laki_laki, jumlah_perempuan, luas_wilayah_ha, jumlah_dusun, jumlah_rt, jumlah_rw, ketinggian_mdpl, kode_pos, is_aktif, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)');
    $stmt->bind_param('iiiiidiiiiisi', $data['tahun'], $data['jumlah_penduduk'], $data['jumlah_kk'], $data['jumlah_laki_laki'], $data['jumlah_perempuan'], $data['luas_wilayah_ha'], $data['jumlah_dusun'], $data['jumlah_rt'], $data['jumlah_rw'], $data['ketinggian_mdpl'], $data['kode_pos'], $userId);
  }
  $stmt->execute();
  $stmt->close();
}

function getFinancialReports($conn): array
{
  $stmt = $conn->prepare('SELECT * FROM laporan_keuangan ORDER BY tahun_anggaran DESC, created_at DESC');
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function getReportById($conn, int $id): ?array
{
  $stmt = $conn->prepare('SELECT * FROM laporan_keuangan WHERE id = ? LIMIT 1');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
  return $row ?: null;
}

function saveFinancialReport($conn, array $data, int $userId): void
{
  if (!empty($data['id'])) {
    $stmt = $conn->prepare('UPDATE laporan_keuangan SET judul = ?, tahun_anggaran = ?, periode = ?, keterangan = ?, nama_file = ?, ukuran_file = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->bind_param('sisssii', $data['judul'], $data['tahun_anggaran'], $data['periode'], $data['keterangan'], $data['nama_file'], $data['ukuran_file'], $data['id']);
  } else {
    $stmt = $conn->prepare('INSERT INTO laporan_keuangan (judul, tahun_anggaran, periode, keterangan, nama_file, ukuran_file, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sisssii', $data['judul'], $data['tahun_anggaran'], $data['periode'], $data['keterangan'], $data['nama_file'], $data['ukuran_file'], $userId);
  }
  $stmt->execute();
  $stmt->close();
}

function deleteFinancialReport($conn, int $id): void
{
  $stmt = $conn->prepare('DELETE FROM laporan_keuangan WHERE id = ?');
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
}

function uploadInfografis($conn, int $userId): ?string
{
  if (empty($_FILES['infografis_file']['name'])) {
    return null;
  }

  $targetDir = __DIR__ . '/images/infografis/';
  if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
  }

  $fileName = time() . '-' . preg_replace('/[^A-Za-z0-9._-]/', '-', basename($_FILES['infografis_file']['name']));
  $targetFile = $targetDir . $fileName;

  if (!move_uploaded_file($_FILES['infografis_file']['tmp_name'], $targetFile)) {
    return null;
  }

  return 'images/infografis/' . $fileName;
}

function uploadProgramGambar(): ?string
{
  if (empty($_FILES['gambar_file']['name'])) {
    return null;
  }
  $targetDir = __DIR__ . '/images/program/';
  if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
  }
  $fileName = time() . '-' . preg_replace('/[^A-Za-z0-9._-]/', '-', basename($_FILES['gambar_file']['name']));
  $targetFile = $targetDir . $fileName;
  if (!move_uploaded_file($_FILES['gambar_file']['tmp_name'], $targetFile)) {
    return null;
  }
  return 'images/program/' . $fileName;
}

function savePengaduan($conn, array $data): int
{
  $stmt = $conn->prepare('INSERT INTO pengaduan (nama_pengadu, kontak, kategori, isi_pengaduan, status) VALUES (?, ?, ?, ?, "baru")');
  $stmt->bind_param('ssss', $data['nama_pengadu'], $data['kontak'], $data['kategori'], $data['isi_pengaduan']);
  $stmt->execute();
  $newId = $stmt->insert_id;
  $stmt->close();
  return $newId;
}

function getPengaduan($conn): array
{
  $stmt = $conn->prepare('SELECT * FROM pengaduan ORDER BY created_at DESC');
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function updatePengaduanStatus($conn, int $id, string $status, string $tanggapan, int $userId): void
{
  $stmt = $conn->prepare('UPDATE pengaduan SET status = ?, tanggapan = ?, ditanggapi_oleh = ? WHERE id = ?');
  $stmt->bind_param('ssii', $status, $tanggapan, $userId, $id);
  $stmt->execute();
  $stmt->close();
}

function sendTelegramNotification(string $message): bool
{
  if (TELEGRAM_BOT_TOKEN === '' || TELEGRAM_CHAT_ID === '') {
    return false;
  }
  $url = 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage';
  $payload = [
    'chat_id' => TELEGRAM_CHAT_ID,
    'text' => $message,
    'parse_mode' => 'HTML',
  ];
  try {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_exec($ch);
    $ok = curl_errno($ch) === 0;
    curl_close($ch);
    return $ok;
  } catch (\Throwable $e) {
    return false;
  }
}

function waLink(string $keperluan, string $catatan = ''): string
{
  $text = "Assalamualaikum, saya ingin mengurus layanan: {$keperluan}.";
  if ($catatan !== '') {
    $text .= " ({$catatan})";
  }
  return 'https://wa.me/08580649206602' . WA_ADMIN_NUMBER . '?text=' . rawurlencode($text);
}

$isAdmin = !empty($_SESSION['user_id']);
$successMessage = '';
$errorMessage = '';

if (isset($_GET['logout']) && $isAdmin) {
  session_unset();
  session_destroy();
  header('Location: index.php');
  exit;
}

if ($isAdmin && isset($_GET['hapus_program']) && csrfCheck($_GET['csrf'] ?? '')) {
  deleteProgram($conn, (int)$_GET['hapus_program']);
  header('Location: index.php');
  exit;
}
if ($isAdmin && isset($_GET['hapus_laporan']) && csrfCheck($_GET['csrf'] ?? '')) {
  deleteFinancialReport($conn, (int)$_GET['hapus_laporan']);
  header('Location: index.php');
  exit;
}

$editingProgramId = isset($_GET['edit_program_id']) ? (int)$_GET['edit_program_id'] : 0;
$editingReportId = isset($_GET['edit_report_id']) ? (int)$_GET['edit_report_id'] : 0;
$editingProgram = $editingProgramId ? getProgramById($conn, $editingProgramId) : null;
$editingReport = $editingReportId ? getReportById($conn, $editingReportId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
  if (!csrfCheck($_POST['csrf_token'] ?? '')) {
    $errorMessage = 'Token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.';
  } else {
    if (isset($_POST['save_home'])) {
      saveSiteText($conn, 'home', 'hero_title', trim($_POST['hero_title'] ?? ''), (int)$_SESSION['user_id']);
      saveSiteText($conn, 'home', 'hero_subtitle', trim($_POST['hero_subtitle'] ?? ''), (int)$_SESSION['user_id']);
      saveSiteText($conn, 'home', 'hero_desc', trim($_POST['hero_desc'] ?? ''), (int)$_SESSION['user_id']);
      $successMessage = 'Konten beranda berhasil diperbarui.';
    }

    if (isset($_POST['save_profile'])) {
      saveSiteText($conn, 'profile', 'intro', trim($_POST['intro'] ?? ''), (int)$_SESSION['user_id']);
      saveSiteText($conn, 'profile', 'history', trim($_POST['history'] ?? ''), (int)$_SESSION['user_id']);
      saveSiteText($conn, 'profile', 'vision', trim($_POST['vision'] ?? ''), (int)$_SESSION['user_id']);
      saveSiteText($conn, 'profile', 'mission', trim($_POST['mission'] ?? ''), (int)$_SESSION['user_id']);
      saveSiteText($conn, 'profile', 'boundary', trim($_POST['boundary'] ?? ''), (int)$_SESSION['user_id']);
      $desaFields = [
        'desa_kode_pum','desa_kecamatan','desa_kabupaten_provinsi','desa_koordinat','desa_tipologi_desa','desa_klasifikasi_desa','desa_kategori_desa',
        'desa_komoditas_luas_tanam','desa_komoditas_nilai_ekonomi','desa_jarak_ke_kecamatan','desa_jarak_ke_kabupaten','desa_jarak_ke_provinsi','desa_luas_tanah_kas',
        'desa_batas_utara','desa_batas_selatan','desa_batas_barat','desa_batas_timur','desa_luas_sawah','desa_luas_ladang','desa_luas_perkebunan','desa_luas_peternakan',
        'desa_luas_hutan','desa_luas_bengkok','desa_luas_titi_sara','desa_luas_lainnya','desa_kepala_name','desa_kepala_education','desa_kepala_quote',
        'desa_sekretaris_name','desa_sekretaris_education','desa_sekretaris_note','desa_ketua_bpd_name','desa_ketua_bpd_education',
        'desa_pd_usia_0_17','desa_pd_usia_18_56','desa_pd_usia_56_plus','desa_kk_lakilaki','desa_kk_perempuan','desa_kk_pra_sejahtera','desa_kk_sejahtera_i',
        'desa_kk_sejahtera_ii','desa_kk_sejahtera_iii','desa_kk_sejahtera_iii_plus','desa_pekerjaan_pns','desa_pekerjaan_tni_polri','desa_pekerjaan_swasta','desa_pekerjaan_wiraswasta',
        'desa_pekerjaan_petani','desa_pekerjaan_buruh_tani','desa_pekerjaan_peternak','desa_pekerjaan_jasa','desa_pekerjaan_pengrajin','desa_pekerjaan_seni','desa_pekerjaan_pensiunan',
        'desa_pekerjaan_tidak_bekerja','desa_pendidikan_tk','desa_pendidikan_sd','desa_pendidikan_smp','desa_pendidikan_sma','desa_pendidikan_akademi','desa_pendidikan_sarjana',
        'desa_pendidikan_s2','desa_pendidikan_pondok','desa_pendidikan_sklb','desa_pendidikan_tidak_sekolah','desa_kesehatan_dokter','desa_kesehatan_bidan','desa_kesehatan_perawat',
        'desa_sarana_puskesmas','desa_sarana_poskesdes','desa_sarana_posyandu','desa_sarana_perpus','desa_sarana_tk','desa_sarana_sd','desa_sarana_masjid','desa_sarana_mushola',
        'desa_sarana_olahraga','desa_sarana_kantor','desa_sarana_jalan_desa','desa_sarana_jalan_kabupaten','desa_sarana_jalan_provinsi','desa_sarana_sumur_gali','desa_sarana_sumur_pompa',
        'desa_sarana_saluran_drainase','desa_sarana_saluran_irigasi','desa_keamanan_linmas','desa_keamanan_pos_kamling','desa_keamanan_kasus','desa_lingkungan_wabah','desa_lingkungan_bencana'
      ];
      foreach ($desaFields as $field) {
        saveSiteText($conn, 'desa', $field, trim($_POST[$field] ?? ''), (int)$_SESSION['user_id']);
      }
      $successMessage = 'Data profil dan data desa berhasil diperbarui.';
    }

    if (isset($_POST['save_program'])) {
      $judul = trim($_POST['judul'] ?? '');
      $deskripsi = trim($_POST['deskripsi'] ?? '');
      $jadwal = trim($_POST['jadwal'] ?? '');
      $kategori = trim($_POST['kategori'] ?? 'prioritas');
      $urutan = (int)($_POST['urutan'] ?? 0);
      $id = (int)($_POST['program_id'] ?? 0);
      $uploadedGambar = uploadProgramGambar();
      $gambar = $uploadedGambar ?? trim($_POST['gambar_existing'] ?? '');

      if ($judul === '' || $deskripsi === '') {
        $errorMessage = 'Judul dan deskripsi wajib diisi.';
      } else {
        saveProgram($conn, ['id' => $id, 'kategori' => $kategori, 'judul' => $judul, 'deskripsi' => $deskripsi, 'jadwal' => $jadwal, 'gambar' => $gambar, 'urutan' => $urutan], (int)$_SESSION['user_id']);
        $successMessage = 'Data program berhasil disimpan.';
      }
    }

    if (isset($_POST['save_stat'])) {
      saveStatistic($conn, [
        'tahun' => (int)($_POST['tahun'] ?? date('Y')),
        'jumlah_penduduk' => (int)($_POST['jumlah_penduduk'] ?? 3723),
        'jumlah_kk' => (int)($_POST['jumlah_kk'] ?? 1222),
        'jumlah_laki_laki' => (int)($_POST['jumlah_laki_laki'] ?? 0),
        'jumlah_perempuan' => (int)($_POST['jumlah_perempuan'] ?? 0),
        'luas_wilayah_ha' => (float)($_POST['luas_wilayah_ha'] ?? 244.95),
        'jumlah_dusun' => (int)($_POST['jumlah_dusun'] ?? 5),
        'jumlah_rt' => (int)($_POST['jumlah_rt'] ?? 19),
        'jumlah_rw' => (int)($_POST['jumlah_rw'] ?? 9),
        'ketinggian_mdpl' => (int)($_POST['ketinggian_mdpl'] ?? 650),
        'kode_pos' => trim($_POST['kode_pos'] ?? '45461'),
      ], (int)$_SESSION['user_id']);
      $successMessage = 'Statistik desa berhasil diperbarui.';
    }

    if (isset($_POST['save_report'])) {
      $judul = trim($_POST['judul'] ?? '');
      if ($judul === '') {
        $errorMessage = 'Judul laporan wajib diisi.';
      } else {
        $uploadedPath = uploadInfografis($conn, (int)$_SESSION['user_id']);
        $namaFile = $uploadedPath ?? trim($_POST['nama_file_existing'] ?? '');
        saveFinancialReport($conn, [
          'id' => (int)($_POST['report_id'] ?? 0),
          'judul' => $judul,
          'tahun_anggaran' => (int)($_POST['tahun_anggaran'] ?? date('Y')),
          'periode' => trim($_POST['periode'] ?? ''),
          'keterangan' => trim($_POST['keterangan'] ?? ''),
          'nama_file' => $namaFile,
          'ukuran_file' => (int)($_POST['ukuran_file'] ?? 0),
        ], (int)$_SESSION['user_id']);
        $successMessage = 'Infografis laporan keuangan berhasil disimpan.';
      }
    }

    if (isset($_POST['save_pengaduan_status'])) {
      updatePengaduanStatus(
        $conn,
        (int)($_POST['pengaduan_id'] ?? 0),
        trim($_POST['status'] ?? 'baru'),
        trim($_POST['tanggapan'] ?? ''),
        (int)$_SESSION['user_id']
      );
      $successMessage = 'Status pengaduan berhasil diperbarui.';
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pengaduan'])) {
  if (!csrfCheck($_POST['csrf_token'] ?? '')) {
    $errorMessage = 'Token keamanan tidak valid. Silakan muat ulang halaman dan coba lagi.';
  } else {
    $nama = trim($_POST['nama_pengadu'] ?? '');
    $isi = trim($_POST['isi_pengaduan'] ?? '');
    if ($nama === '' || $isi === '') {
      $errorMessage = 'Nama dan isi pengaduan wajib diisi.';
    } else {
      $kontak = trim($_POST['kontak'] ?? '');
      $kategoriPengaduan = trim($_POST['kategori'] ?? 'lainnya');
      $newId = savePengaduan($conn, [
        'nama_pengadu' => $nama,
        'kontak' => $kontak,
        'kategori' => $kategoriPengaduan,
        'isi_pengaduan' => $isi,
      ]);

      $pesanTelegram = "<b>Pengaduan Baru Masuk</b>\n"
        . "No: #{$newId}\n"
        . "Nama: {$nama}\n"
        . "Kontak: " . ($kontak !== '' ? $kontak : '-') . "\n"
        . "Kategori: {$kategoriPengaduan}\n"
        . "Isi: {$isi}";
      sendTelegramNotification($pesanTelegram);

      $successMessage = 'Terima kasih, pengaduan Anda berhasil dikirim dan akan segera ditindaklanjuti oleh perangkat desa.';
    }
  }
}

$heroTitle = getSiteText($conn, 'home', 'hero_title', 'Selamat Datang di');
$heroSubtitle = getSiteText($conn, 'home', 'hero_subtitle', 'Desa Ujungberung');
$heroDesc = getSiteText($conn, 'home', 'hero_desc', 'Pusat informasi pelayanan publik, potensi desa, dan keterbukaan informasi untuk mewujudkan masyarakat sejahtera dan mandiri.');
$profileIntro = getSiteText($conn, 'profile', 'intro', 'Selamat datang di Desa Ujungberung, sebuah desa yang terletak di Kecamatan Sindangwangi, Kabupaten Majalengka, Provinsi Jawa Barat. Kami mengundang Anda untuk mengenal lebih dekat desa kami melalui sejarah yang kaya, visi dan misi pembangunan yang jelas, serta potensi alam dan budaya yang luar biasa.');
$profileHistory = getSiteText($conn, 'profile', 'history', 'Desa Ujungberung merupakan salah satu desa yang terletak di Kecamatan Sindangwangi, Kabupaten Majalengka, Provinsi Jawa Barat. Nama "Ujungberung" berasal dari bahasa Sunda: "Ujung" mengacu pada posisi geografisnya, sementara "Berung" menggambarkan karakteristik tanah yang subur untuk pertanian dan perkebunan.');
$profileVision = getSiteText($conn, 'profile', 'vision', 'Terwujudnya Desa Ujungberung yang Maju, Mandiri, Sejahtera, dan Berbudaya Berlandaskan Keimanan dan Ketakwaan.');
$profileMission = getSiteText($conn, 'profile', 'mission', 'Meningkatkan kualitas sumber daya manusia; Mengembangkan ekonomi berbasis potensi lokal; Meningkatkan infrastruktur dan pelayanan dasar; Melestarikan budaya dan lingkungan hidup.');
$profileBoundary = getSiteText($conn, 'profile', 'boundary', 'Utara: Desa Balagedog · Selatan: Desa Jerukleueut & Desa Sindangwangi · Barat: Desa Lengkong Kulon & Desa Cipanas (Kab. Cirebon) · Timur: Desa Leuwilaja & Desa Buah Kapas');

$desaDataDefaults = [
  'desa_kode_pum' => '32.10.212.007',
  'desa_kecamatan' => 'Sindangwangi',
  'desa_kabupaten_provinsi' => 'Majalengka / Jawa Barat',
  'desa_koordinat' => '108,374203 BT / -6,768923 LS',
  'desa_tipologi_desa' => 'Dataran Tinggi',
  'desa_klasifikasi_desa' => 'Berkembang',
  'desa_kategori_desa' => 'Swakarya',
  'desa_komoditas_luas_tanam' => 'Perkebunan',
  'desa_komoditas_nilai_ekonomi' => 'Pertanian',
  'desa_jarak_ke_kecamatan' => '2 Km',
  'desa_jarak_ke_kabupaten' => '25 Km',
  'desa_jarak_ke_provinsi' => '120 Km',
  'desa_luas_tanah_kas' => '13 Ha',
  'desa_batas_utara' => 'Desa Balagedog',
  'desa_batas_selatan' => 'Desa Jerukleueut & Sindangwangi',
  'desa_batas_barat' => 'Desa Lengkong Kulon & Cipanas (Kab. Cirebon)',
  'desa_batas_timur' => 'Desa Leuwilaja & Buah Kapas',
  'desa_luas_sawah' => '35 Ha',
  'desa_luas_ladang' => '15 Ha',
  'desa_luas_perkebunan' => '73 Ha',
  'desa_luas_peternakan' => '1 Ha',
  'desa_luas_hutan' => '47 Ha',
  'desa_luas_bengkok' => '7 Ha',
  'desa_luas_titi_sara' => '6 Ha',
  'desa_luas_lainnya' => '60,95 Ha',
  'desa_kepala_name' => 'Aris Susanto, A.Md',
  'desa_kepala_education' => 'Pendidikan: D III · Laki-laki',
  'desa_kepala_quote' => 'Saya berkomitmen mengemban amanah masyarakat dan melayani dengan sepenuh hati untuk membangun Desa Ujungberung yang lebih baik.',
  'desa_sekretaris_name' => 'Robet Hadi Somad, S.Ag',
  'desa_sekretaris_education' => 'Pendidikan: Sarjana · Laki-laki',
  'desa_sekretaris_note' => 'Pelatihan: Pengelolaan Keuangan, Pengisian Prodeskel, Penyusunan Perdes, Penyusunan RKP & RPJM Desa',
  'desa_ketua_bpd_name' => 'Waspudi, S.Pd',
  'desa_ketua_bpd_education' => 'Pendidikan: S1 · Laki-laki',
  'desa_pd_usia_0_17' => '796',
  'desa_pd_usia_18_56' => '2271',
  'desa_pd_usia_56_plus' => '656',
  'desa_kk_lakilaki' => '1050',
  'desa_kk_perempuan' => '172',
  'desa_kk_pra_sejahtera' => '427',
  'desa_kk_sejahtera_i' => '383',
  'desa_kk_sejahtera_ii' => '236',
  'desa_kk_sejahtera_iii' => '141',
  'desa_kk_sejahtera_iii_plus' => '35',
  'desa_pekerjaan_pns' => '21',
  'desa_pekerjaan_tni_polri' => '3',
  'desa_pekerjaan_swasta' => '435',
  'desa_pekerjaan_wiraswasta' => '1958',
  'desa_pekerjaan_petani' => '35',
  'desa_pekerjaan_buruh_tani' => '135',
  'desa_pekerjaan_peternak' => '20',
  'desa_pekerjaan_jasa' => '50',
  'desa_pekerjaan_pengrajin' => '30',
  'desa_pekerjaan_seni' => '25',
  'desa_pekerjaan_pensiunan' => '5',
  'desa_pekerjaan_tidak_bekerja' => '521',
  'desa_pendidikan_tk' => '76',
  'desa_pendidikan_sd' => '218',
  'desa_pendidikan_smp' => '56',
  'desa_pendidikan_sma' => '48',
  'desa_pendidikan_akademi' => '18',
  'desa_pendidikan_sarjana' => '46',
  'desa_pendidikan_s2' => '2',
  'desa_pendidikan_pondok' => '65',
  'desa_pendidikan_sklb' => '2',
  'desa_pendidikan_tidak_sekolah' => '35',
  'desa_kesehatan_dokter' => '1',
  'desa_kesehatan_bidan' => '1',
  'desa_kesehatan_perawat' => '10',
  'desa_sarana_puskesmas' => '1',
  'desa_sarana_poskesdes' => '1',
  'desa_sarana_posyandu' => '5',
  'desa_sarana_perpus' => '1',
  'desa_sarana_tk' => '2',
  'desa_sarana_sd' => '2',
  'desa_sarana_masjid' => '3',
  'desa_sarana_mushola' => '14',
  'desa_sarana_olahraga' => '3',
  'desa_sarana_kantor' => '1',
  'desa_sarana_jalan_desa' => '6',
  'desa_sarana_jalan_kabupaten' => '2',
  'desa_sarana_jalan_provinsi' => '1',
  'desa_sarana_sumur_gali' => '50',
  'desa_sarana_sumur_pompa' => '35',
  'desa_sarana_saluran_drainase' => '3000',
  'desa_sarana_saluran_irigasi' => '5000',
  'desa_keamanan_linmas' => '5',
  'desa_keamanan_pos_kamling' => '15',
  'desa_keamanan_kasus' => '2',
  'desa_lingkungan_wabah' => '1',
  'desa_lingkungan_bencana' => '0',
];
foreach ($desaDataDefaults as $key => $default) {
  ${$key} = getSiteText($conn, 'desa', $key, $default);
}

$programsPrioritas = getPrograms($conn, 'prioritas');
$programsPariwisata = getPrograms($conn, 'pariwisata');
$programsProduk = getPrograms($conn, 'produk_unggulan');
$programsRutin = getPrograms($conn, 'kegiatan_rutin');
$activeStatistic = getActiveStatistic($conn);
$financialReports = getFinancialReports($conn);
$pengaduanList = $isAdmin ? getPengaduan($conn) : [];

$agendaTetap = [
  ['icon' => 'fa-solid fa-flag', 'judul' => 'PHBN 17 Agustus', 'waktu' => '17 Agustus (Tahunan)', 'desk' => 'Peringatan Hari Besar Nasional Kemerdekaan RI: upacara bendera, lomba warga, dan pawai kemerdekaan.'],
  ['icon' => 'fa-solid fa-mosque', 'judul' => 'PHBI Maulid Nabi', 'waktu' => '12 Rabiul Awal (Tahunan)', 'desk' => 'Peringatan Hari Besar Islam Maulid Nabi Muhammad SAW dengan pengajian dan sholawatan bersama warga.'],
  ['icon' => 'fa-solid fa-moon', 'judul' => 'Syafaran', 'waktu' => 'Bulan Safar (Tahunan)', 'desk' => 'Tradisi syukuran dan doa bersama menyambut bulan Safar menurut kalender Hijriah.'],
  ['icon' => 'fa-solid fa-star-and-crescent', 'judul' => 'Syura', 'waktu' => '1 Muharram (Tahunan)', 'desk' => 'Peringatan tahun baru Islam (1 Muharram) yang diisi dengan doa bersama dan renungan warga.'],
  ['icon' => 'fa-solid fa-kaaba', 'judul' => "PHBI Isra Mi'raj", 'waktu' => '27 Rajab (Tahunan)', 'desk' => "Peringatan Hari Besar Islam Isra Mi'raj Nabi Muhammad SAW melalui pengajian akbar desa."],
  ['icon' => 'fa-solid fa-wheat-awn', 'judul' => 'Hajat Bumi (Bongkar Bumi)', 'waktu' => 'Pasca Panen (Tahunan)', 'desk' => 'Tradisi adat Sunda sebagai wujud syukur atas hasil bumi, diisi dengan kenduri dan kesenian rakyat.'],
  ['icon' => 'fa-solid fa-drum', 'judul' => 'Takbir Keliling Idul Fitri/Idul Adha', 'waktu' => 'Malam Idulfitri & Iduladha', 'desk' => 'Pawai takbir keliling warga menyambut Hari Raya Idul Fitri maupun Idul Adha.'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Desa Ujungberung — Kec. Sindangwangi, Kab. Majalengka, Jawa Barat</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    :root {
      --primary: #0d3d1a;
      --primary-container: #1B5E20;
      --secondary: #1B5E20;
      --secondary-container: #fbe04c;
      --secondary-fixed: #fbe04c;
      --surface: #fafaf8;
      --surface-container: #ede9e0;
      --surface-container-low: #f5f3f0;
      --surface-container-high: #e3dfdd;
      --on-surface: #1a1a1a;
      --on-surface-variant: #49453e;
      --outline-variant: #cac4bb;
      --tertiary-fixed: #ffddb3;
      --on-tertiary-fixed-variant: #6b5840;
    }

    * { box-sizing: border-box; }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--surface);
      color: var(--on-surface);
      padding-bottom: 78px;
    }

    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      vertical-align: middle;
    }

    .bg-forest { background-color: var(--primary) !important; }
    .bg-forest-container { background-color: var(--secondary-container) !important; }
    .bg-surface-container { background-color: var(--surface-container) !important; }
    .bg-surface-container-low { background-color: var(--surface-container-low) !important; }
    .text-forest { color: var(--primary) !important; }
    .text-forest-secondary { color: var(--secondary) !important; }
    .text-on-surface-variant { color: var(--on-surface-variant) !important; }
    .text-secondary-fixed { color: var(--secondary-fixed) !important; }
    .border-forest { border-color: var(--primary) !important; }
    .rounded-4 { border-radius: 1.25rem !important; }
    .rounded-5 { border-radius: 1.75rem !important; }

    a.card-link-block { text-decoration: none; color: inherit; display: block; }

    .section-eyebrow {
      color: var(--secondary);
      font-weight: 700;
      text-transform: uppercase;
      font-size: .78rem;
      letter-spacing: .1em;
    }

    .icon-chip {
      width: 56px;
      height: 56px;
      border-radius: .9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }

    .infografis-shell {
      background: linear-gradient(135deg, #f8fbf8 0%, #f1f7f1 100%);
      border: 1px solid rgba(13, 61, 26, 0.1);
      border-radius: 1.1rem;
      padding: .75rem;
    }

    .infografis-media {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 260px;
      background: #ffffff;
      border-radius: .85rem;
      overflow: hidden;
      box-shadow: inset 0 0 0 1px rgba(13, 61, 26, 0.06);
    }

    .infografis-image {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .3s ease;
    }

    .infografis-media:hover .infografis-image { transform: scale(1.04); }

    .btn-forest { background-color: var(--primary); color: #fff; border: none; }
    .btn-forest:hover { background-color: var(--secondary); color: #fff; }

    .btn-outline-forest {
      background-color: var(--secondary-fixed);
      color: var(--primary);
      border: 1px solid var(--outline-variant);
    }

    .btn-outline-forest:hover { background-color: #f5d534; color: var(--primary); }

    .btn-wa {
      background-color: #25D366;
      color: #fff;
      border: none;
    }
    .btn-wa:hover { background-color: #1ebe5a; color: #fff; }

    .hero {
      min-height: 92vh;
      background: url('images/cover_website.JPG') center/cover no-repeat;
      display: flex;
      align-items: center;
      position: relative;
      padding-top: 90px;
    }

    .hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(100deg, rgba(13, 61, 26, .88) 0%, rgba(13, 61, 26, .55) 45%, rgba(13, 61, 26, .25) 100%);
    }

    .hero .container { position: relative; z-index: 2; }

    .glass-card {
      background: rgba(255, 255, 255, .12);
      border: 1px solid rgba(255, 255, 255, .28);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-radius: 1.25rem;
    }

    .stat-num { font-weight: 700; font-size: 1.45rem; color: var(--secondary-fixed); }
    .stat-label { font-size: 11px; letter-spacing: .05em; text-transform: uppercase; color: var(--secondary-fixed); font-weight: 600; }

    .navbar-desa { background-color: rgba(250, 250, 248, .95); backdrop-filter: blur(8px); }
    .navbar-desa .nav-link { color: var(--on-surface-variant); font-weight: 500; font-size: .9rem; }
    .navbar-desa .nav-link:hover, .navbar-desa .nav-link.active { color: var(--primary); background-color: var(--secondary-container); border-radius: .5rem; }

    .card-hover { transition: box-shadow .2s ease, transform .2s ease; }
    .card-hover:hover { box-shadow: 0 10px 28px rgba(0, 0, 0, .1); transform: translateY(-3px); }

    .nav-pills .nav-link { border: 1px solid var(--outline-variant); color: var(--on-surface-variant); font-size: .8rem; font-weight: 600; }
    .nav-pills .nav-link.active { background-color: var(--primary); color: #fff; }

    .accordion-desa .accordion-button { font-weight: 600; color: var(--primary); background-color: var(--surface-container-low); }
    .accordion-desa .accordion-button:not(.collapsed) { background-color: var(--secondary-container); color: #0D3B0C; box-shadow: none; }
    .accordion-desa .accordion-button:focus { box-shadow: none; border-color: var(--outline-variant); }
    .accordion-desa .accordion-item { border-color: var(--outline-variant); border-radius: 1rem !important; overflow: hidden; margin-bottom: .75rem; }

    .data-row { display: flex; justify-content: space-between; gap: 1rem; padding: .4rem 0; border-bottom: 1px dashed var(--outline-variant); font-size: .85rem; }
    .data-row:last-child { border-bottom: none; }
    .data-row span:first-child { color: var(--on-surface-variant); }
    .data-row span:last-child { font-weight: 600; text-align: right; }

    .mini-card { background: #fff; border: 1px solid var(--outline-variant); border-radius: .9rem; padding: 1rem; height: 100%; }
    .mini-card-head { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
    .mini-card-head i { color: var(--secondary); font-size: 1rem; width: 20px; text-align: center; }

    .layanan-tile {
      display: flex;
      align-items: center;
      gap: .75rem;
      text-decoration: none;
      color: inherit;
      transition: transform .15s ease, box-shadow .15s ease;
    }
    .layanan-tile:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08); color: inherit; }

    .video-wrap { margin: 0 auto; }

    .video-side-card {
      background: rgba(255, 255, 255, .08);
      border: 1px solid rgba(255, 255, 255, .18);
      border-radius: 1.25rem;
      padding: 1.75rem;
      height: 100%;
    }
    .video-side-card ul { list-style: none; padding: 0; margin: 0; }
    .video-side-card li {
      display: flex;
      align-items: flex-start;
      gap: .6rem;
      padding: .55rem 0;
      border-bottom: 1px solid rgba(255,255,255,.1);
      color: rgba(255,255,255,.78);
      font-size: .9rem;
    }
    .video-side-card li:last-child { border-bottom: none; }
    .video-side-card li i { color: var(--secondary-fixed); margin-top: 3px; }

    .gallery-frame {
      border-radius: 1rem;
      overflow: hidden;
      aspect-ratio: 4/3;
      box-shadow: 0 4px 16px rgba(0,0,0,.08);
    }
    .gallery-frame img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .3s ease;
    }
    .gallery-frame:hover img { transform: scale(1.06); }

    .peta-cta { background: linear-gradient(120deg, var(--primary), var(--secondary)); }

    .bottom-nav {
      position: fixed; bottom: 0; left: 0; width: 100%; z-index: 1030;
      background: rgba(250, 250, 248, .97); border-top: 1px solid var(--outline-variant);
      display: flex; justify-content: space-around; padding: .5rem 0;
    }
    .bottom-nav a { color: var(--on-surface-variant); text-decoration: none; text-align: center; font-size: 10px; font-weight: 600; }
    .bottom-nav a.active { color: var(--primary); }
    .bottom-nav .material-symbols-outlined { display: block; font-size: 22px; }

    .fab-btn {
      position: fixed; bottom: 88px; right: 20px; z-index: 1030;
      width: 56px; height: 56px; border-radius: 50%;
      background-color: #25D366; color: #fff; border: none; box-shadow: 0 6px 16px rgba(0, 0, 0, .2);
    }

    .back-to-top {
      position: fixed; bottom: 100px; right: 20px; z-index: 1030;
      width: 44px; height: 44px; border-radius: 50%;
      background-color: var(--primary); color: #fff;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
    }

    @media (min-width: 768px) { .bottom-nav, .fab-btn { display: none; } }

    html { scroll-behavior: smooth; }
    section { scroll-margin-top: 80px; }

    @media (max-width: 991px) {
      .hero { min-height: auto; padding: 130px 0 60px; }
    }
  </style>
</head>

<body>
  <?php if (!empty($successMessage)): ?>
    <div class="alert alert-success mx-3 mx-md-5 mt-3 mb-0 shadow-sm" role="alert"><?= htmlspecialchars($successMessage) ?></div>
  <?php endif; ?>
  <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger mx-3 mx-md-5 mt-3 mb-0 shadow-sm" role="alert"><?= htmlspecialchars($errorMessage) ?></div>
  <?php endif; ?>

  <nav class="navbar navbar-expand-lg navbar-desa fixed-top shadow-sm py-2">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#beranda">
        <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEiIhA6W8mwxMDo6nFztwgZC69d5tt3c3ic-72lccDF0Fz0txCE382rXTR5G3FXtTk0IhZ8JUMVRHBAKAgPyJBRXmLorK-53heUtpuOiHFzR7pXX3QquguuimNbiL1sKTyT7iatiZifN6xp3/s1600/Majalengka.png" alt="Logo Kabupaten Majalengka" width="42" height="42" class="rounded-circle">
        <span class="d-flex flex-column lh-sm">
          <span class="fw-bold text-forest" style="font-size:16px;">Desa Ujungberung</span>
          <span class="text-on-surface-variant" style="font-size:11px;">Kec. Sindangwangi · Kab. Majalengka</span>
        </span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="material-symbols-outlined text-forest">menu</span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto gap-1 align-items-lg-center">
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#profil">Profil</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#program">Program</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#data-desa">Data</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#pariwisata">Pariwisata</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#laporan-keuangan">Keuangan</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#berita">Berita</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2" href="#layanan">Layanan</a></li>
          <li class="nav-item"><a class="nav-link px-3 py-2 d-flex align-items-center gap-1" href="map/map.html"><span class="material-symbols-outlined" style="font-size:18px;">map</span> Peta</a></li>
          <li class="nav-item">
            <?php if ($isAdmin): ?>
              <a class="btn btn-outline-forest px-3 py-2 ms-lg-2 d-flex align-items-center gap-2" href="?logout=1">
                <span class="material-symbols-outlined" style="font-size:18px;">logout</span> Logout
              </a>
            <?php else: ?>
              <a class="btn btn-forest px-3 py-2 ms-lg-2 d-flex align-items-center gap-2" href="admin/login.php">
                <span class="material-symbols-outlined" style="font-size:18px;">login</span> Login
              </a>
            <?php endif; ?>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero" id="beranda">
    <div class="container py-5">
      <div class="row align-items-center g-5">
        <div class="col-lg-7">
          <span class="badge bg-forest-container text-forest fw-semibold text-uppercase px-3 py-2 mb-3" style="letter-spacing:.05em;">Kec. Sindangwangi · Kab. Majalengka · Jawa Barat</span>
          <h1 class="fw-bold text-white display-5 mb-3">
            <?= htmlspecialchars($heroTitle) ?><br>
            <span class="text-secondary-fixed"><?= htmlspecialchars($heroSubtitle) ?></span>
          </h1>
          <p class="text-white-50 fs-5 mb-4"><?= htmlspecialchars($heroDesc) ?></p>

          <div class="row g-2 mb-4">
            <div class="col-sm-4">
              <a href="#layanan" class="btn btn-forest w-100 d-flex justify-content-between align-items-center py-3 px-3 rounded-3 shadow">
                <span><span class="material-symbols-outlined align-middle me-1">description</span> Layanan</span>
                <span class="material-symbols-outlined">arrow_forward</span>
              </a>
            </div>
            <div class="col-sm-4">
              <a href="#berita" class="btn btn-outline-forest w-100 d-flex justify-content-between align-items-center py-3 px-3 rounded-3 shadow">
                <span><span class="material-symbols-outlined align-middle me-1">newspaper</span> Berita Desa</span>
                <span class="material-symbols-outlined">arrow_forward</span>
              </a>
            </div>
            <div class="col-sm-4">
              <a href="map/map.html" class="btn btn-outline-forest w-100 d-flex justify-content-between align-items-center py-3 px-3 rounded-3 shadow">
                <span><span class="material-symbols-outlined align-middle me-1">map</span> Peta Desa</span>
                <span class="material-symbols-outlined">arrow_forward</span>
              </a>
            </div>
          </div>

          <div class="glass-card d-inline-flex flex-wrap gap-4 px-4 py-3">
            <div class="text-center">
              <div class="stat-num"><?= number_format((int)$activeStatistic['jumlah_penduduk'], 0, ',', '.') ?></div>
              <div class="stat-label">Penduduk (Jiwa)</div>
            </div>
            <div class="text-center">
              <div class="stat-num"><?= number_format((int)$activeStatistic['jumlah_kk'], 0, ',', '.') ?></div>
              <div class="stat-label">Kepala Keluarga</div>
            </div>
            <div class="text-center">
              <div class="stat-num"><?= (int)$activeStatistic['jumlah_rw'] ?> / <?= (int)$activeStatistic['jumlah_rt'] ?></div>
              <div class="stat-label">RW / RT</div>
            </div>
            <div class="text-center">
              <div class="stat-num"><?= number_format((float)$activeStatistic['luas_wilayah_ha'], 2, ',', '.') ?> Ha</div>
              <div class="stat-label">Luas Wilayah</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </header>

  <section class="py-5" id="profil">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Tentang Kami</span>
        <h2 class="fw-semibold text-forest mt-1 mb-3">Profil Desa Ujungberung</h2>
        <p class="text-on-surface-variant"><?= nl2br(htmlspecialchars($profileIntro)) ?></p>
        <p class="text-on-surface-variant small">Desa Ujungberung berada di kawasan dataran tinggi dengan kategori Swakarya dan klasifikasi desa Berkembang, sekitar 2 km dari pusat pemerintahan Kecamatan Sindangwangi dan 25 km dari pusat Kabupaten Majalengka.</p>
      </div>
      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
          <p class="fw-semibold text-forest small mb-2"><i class="fa-solid fa-pen-to-square me-1"></i> Panel Admin — Edit Profil Desa</p>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="save_profile" value="1">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Intro Profil</label>
              <textarea class="form-control" name="intro" rows="3"><?= htmlspecialchars($profileIntro) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Sejarah Singkat</label>
              <textarea class="form-control" name="history" rows="3"><?= htmlspecialchars($profileHistory) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Visi</label>
              <textarea class="form-control" name="vision" rows="2"><?= htmlspecialchars($profileVision) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Misi (pisahkan dengan titik koma ;)</label>
              <textarea class="form-control" name="mission" rows="2"><?= htmlspecialchars($profileMission) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Batas Wilayah</label>
              <textarea class="form-control" name="boundary" rows="2"><?= htmlspecialchars($profileBoundary) ?></textarea>
            </div>
            <div class="col-12">
              <button class="btn btn-forest">Simpan Profil</button>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="bg-forest text-white rounded-4 p-4 h-100 text-center">
            <div class="mx-auto mb-3 rounded-circle bg-white bg-opacity-10 border border-white border-opacity-25 d-flex align-items-center justify-content-center" style="width:96px;height:96px;">
              <span class="material-symbols-outlined" style="font-size:48px; opacity:.8;">
                <div></div>
              </span>
            </div>
            <span class="badge bg-forest-container text-forest fw-bold text-uppercase mb-2">Kepala Desa</span>
            <h5 class="fw-semibold mb-1">Aris Susanto, A.Md</h5>
            <p class="text-white-50 small mb-3">Pendidikan Terakhir: D III</p>
            <p class="text-white-50 fst-italic small">"Saya berkomitmen mengemban amanah masyarakat dan melayani dengan sepenuh hati untuk membangun Desa Ujungberung yang lebih baik."</p>

            <div class="border-top border-white border-opacity-25 pt-3 mt-3 text-start small">
              <div class="d-flex justify-content-between mb-2"><span class="text-white-50">Kecamatan</span><span class="fw-medium">Sindangwangi</span></div>
              <div class="d-flex justify-content-between mb-2"><span class="text-white-50">Kabupaten</span><span class="fw-medium">Majalengka</span></div>
              <div class="d-flex justify-content-between mb-2"><span class="text-white-50">Kode Desa (PUM)</span><span class="fw-medium">32.10.212.007</span></div>
              <div class="d-flex justify-content-between"><span class="text-white-50">Kategori Desa</span><span class="fw-medium">Swakarya</span></div>
            </div>

            <div class="border-top border-white border-opacity-25 pt-3 mt-3 text-start">
              <p class="text-white-50 text-uppercase fw-bold small mb-2">Perangkat Inti:</p>
              <div class="small text-white-50 mb-1"><i class="fa-solid fa-check me-1"></i> Sekretaris Desa: Robet Hadi Somad, S.Ag</div>
              <div class="small text-white-50"><i class="fa-solid fa-check me-1"></i> Ketua BPD: Waspudi, S.Pd</div>
            </div>

            <a href="" class="btn btn-sm btn-outline-forest w-100 mt-3">Lihat Struktur Organisasi <i class="fa-solid fa-arrow-right ms-1"></i></a>
          </div>
        </div>

        <div class="col-md-8">
          <div class="bg-surface-container rounded-4 p-4 mb-4">
            <div class="rounded-3 bg-forest-container d-inline-flex align-items-center justify-content-center mb-3" style="width:40px;height:40px;">
              <span class="material-symbols-outlined text-forest-secondary">history_edu</span>
            </div>
            <h5 class="fw-semibold text-forest mb-2">Sejarah Singkat</h5>
            <p class="text-on-surface-variant small"><?= nl2br(htmlspecialchars($profileHistory)) ?></p>
            <p class="text-on-surface-variant small mb-0">Hingga saat ini, Desa Ujungberung terus berusaha meningkatkan kualitas kehidupan masyarakatnya melalui berbagai program pembangunan yang berkelanjutan, dengan komoditas unggulan di sektor perkebunan dan pertanian.</p>
          </div>

          <div class="bg-surface-container rounded-4 p-4 mb-4">
            <div class="rounded-3 bg-forest-container d-inline-flex align-items-center justify-content-center mb-3" style="width:40px;height:40px;">
              <span class="material-symbols-outlined text-forest-secondary">explore</span>
            </div>
            <h5 class="fw-semibold text-forest mb-3">Batas & Koordinat Wilayah</h5>
            <div class="row g-2 small text-on-surface-variant">
              <div class="col-6"><strong class="text-forest d-block">Utara</strong>Desa Balagedog</div>
              <div class="col-6"><strong class="text-forest d-block">Selatan</strong>Desa Jerukleueut & Desa Sindangwangi</div>
              <div class="col-6"><strong class="text-forest d-block">Barat</strong>Desa Lengkong Kulon & Desa Cipanas (Kab. Cirebon)</div>
              <div class="col-6"><strong class="text-forest d-block">Timur</strong>Desa Leuwilaja & Desa Buah Kapas</div>
            </div>
            <p class="text-on-surface-variant small mb-0 mt-3"><i class="fa-solid fa-location-dot me-1"></i> Koordinat: 108,374203 BT / -6,768923 LS</p>
          </div>

          <div class="row g-4">
            <div class="col-md-6">
              <div class="rounded-4 p-4 h-100" style="background-color: var(--secondary-container);">
                <span class="material-symbols-outlined text-forest-secondary mb-2 d-block">stars</span>
                <h6 class="fw-semibold mb-2">Visi</h6>
                <p class="fst-italic small mb-0" style="color:#0D3B0C;">"<?= htmlspecialchars($profileVision) ?>"</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="bg-surface-container rounded-4 p-4 h-100">
                <span class="material-symbols-outlined text-forest-secondary mb-2 d-block">flag</span>
                <h6 class="fw-semibold text-forest mb-2">Misi</h6>
                <ul class="small text-on-surface-variant ps-3 mb-0">
                  <?php foreach (explode(';', $profileMission) as $missionItem): ?>
                    <?php $missionItem = trim($missionItem);
                    if ($missionItem !== ''): ?><li><?= htmlspecialchars($missionItem) ?></li><?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-white" id="program">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Komitmen Kepemimpinan</span>
        <h2 class="fw-semibold text-forest mt-1 mb-2">Program Prioritas Kepala Desa</h2>
        <p class="text-on-surface-variant">Inisiatif strategis untuk membangun Desa Ujungberung yang lebih maju</p>
      </div>
      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
          <p class="fw-semibold text-forest small mb-2"><i class="fa-solid <?= $editingProgram && $editingProgram['kategori'] === 'prioritas' ? 'fa-pen-to-square' : 'fa-circle-plus' ?> me-1"></i> <?= $editingProgram && $editingProgram['kategori'] === 'prioritas' ? 'Edit Program Prioritas #' . (int)$editingProgram['id'] : 'Tambah Program Prioritas' ?></p>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="save_program" value="1">
            <input type="hidden" name="kategori" value="prioritas">
            <input type="hidden" name="program_id" value="<?= ($editingProgram && $editingProgram['kategori'] === 'prioritas') ? (int)$editingProgram['id'] : 0 ?>">
            <input type="hidden" name="gambar_existing" value="<?= ($editingProgram && $editingProgram['kategori'] === 'prioritas') ? htmlspecialchars($editingProgram['gambar'] ?? '') : '' ?>">
            <div class="col-md-4"><label class="form-label small fw-semibold">Judul</label><input class="form-control" name="judul" required value="<?= ($editingProgram && $editingProgram['kategori'] === 'prioritas') ? htmlspecialchars($editingProgram['judul']) : '' ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Jadwal</label><input class="form-control" name="jadwal" value="<?= ($editingProgram && $editingProgram['kategori'] === 'prioritas') ? htmlspecialchars($editingProgram['jadwal'] ?? '') : '' ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Urutan</label><input class="form-control" type="number" name="urutan" value="<?= ($editingProgram && $editingProgram['kategori'] === 'prioritas') ? (int)$editingProgram['urutan'] : 0 ?>"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="2" required><?= ($editingProgram && $editingProgram['kategori'] === 'prioritas') ? htmlspecialchars($editingProgram['deskripsi']) : '' ?></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Gambar (opsional)</label><input class="form-control" type="file" name="gambar_file" accept="image/*"></div>
            <div class="col-12 d-flex gap-2">
              <button class="btn btn-forest">Simpan Program</button>
              <?php if ($editingProgram && $editingProgram['kategori'] === 'prioritas'): ?>
                <a href="index.php#program" class="btn btn-outline-secondary">Batal Edit</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      <?php endif; ?>
      <div class="row g-4">
        <?php if (empty($programsPrioritas)): ?>
          <p class="text-on-surface-variant small">Belum ada program prioritas yang ditambahkan.</p>
        <?php endif; ?>
        <?php foreach ($programsPrioritas as $program): ?>
          <div class="col-md-4">
            <div class="card h-100 border-0 border-start border-4 border-forest shadow-sm rounded-4 card-hover position-relative">
              <?php if ($isAdmin): ?>
                <div class="position-absolute top-0 end-0 m-3 d-flex gap-1">
                  <a href="?edit_program_id=<?= (int)$program['id'] ?>#program" class="btn btn-sm btn-outline-forest">Edit</a>
                  <a href="?hapus_program=<?= (int)$program['id'] ?>&csrf=<?= htmlspecialchars(csrfToken()) ?>#program" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus program ini?')">Hapus</a>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <div class="icon-chip bg-forest text-white mb-3"><i class="fa-solid fa-list-check"></i></div>
                <h5 class="text-forest fw-semibold"><?= htmlspecialchars($program['judul']) ?></h5>
                <p class="text-on-surface-variant small"><?= nl2br(htmlspecialchars($program['deskripsi'])) ?></p>
                <?php if (!empty($program['jadwal'])): ?><p class="small text-forest-secondary mb-2"><strong>Jadwal:</strong> <?= htmlspecialchars($program['jadwal']) ?></p><?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-5 bg-surface-container-low" id="pariwisata">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Gerbang Potensi Wisata Kabupaten Majalengka</span>
        <h2 class="fw-semibold text-forest mt-1 mb-2">Pariwisata & Potensi Unggulan</h2>
        <p class="text-on-surface-variant">Desa Ujungberung terletak di Kecamatan Sindangwangi, Kabupaten Majalengka, pada kawasan dataran tinggi dengan udara sejuk dan lingkungan asri. Wilayahnya didominasi lahan pertanian dan perkebunan dengan tanah subur, di mana durian menjadi komoditas unggulan sekaligus identitas desa — modal utama pengembangan desa wisata berbasis masyarakat (community-based tourism).</p>
        <p class="text-on-surface-variant mb-4">Sebagai desa yang terdaftar dalam Jaringan Desa Wisata Indonesia (JADESTA), Ujungberung berpeluang besar berkembang menjadi destinasi wisata unggulan di Kecamatan Sindangwangi, dengan agrowisata durian sebagai daya tarik utama saat musim panen.</p>

        <div class="d-flex flex-wrap gap-2">
          <a href="https://online.fliphtml5.com/ujungberung/uzbk/" class="btn btn-forest btn-sm d-flex align-items-center gap-2 px-3">
            <i class="fa-solid fa-book-open"></i> Booklet Pariwisata
          </a>
          <a href="agrowisata.html" class="btn btn-outline-forest btn-sm d-flex align-items-center gap-2 px-3">
            <i class="fa-solid fa-seedling"></i> Halaman Agro Desa
          </a>
        </div>
      </div>

      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
          <p class="fw-semibold text-forest small mb-2"><i class="fa-solid fa-circle-plus me-1"></i> Tambah / Edit Produk Unggulan Desa</p>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="save_program" value="1">
            <input type="hidden" name="program_id" value="<?= ($editingProgram && in_array($editingProgram['kategori'], ['pariwisata', 'produk_unggulan'])) ? (int)$editingProgram['id'] : 0 ?>">
            <input type="hidden" name="gambar_existing" value="<?= ($editingProgram && in_array($editingProgram['kategori'], ['pariwisata', 'produk_unggulan'])) ? htmlspecialchars($editingProgram['gambar'] ?? '') : '' ?>">
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Kategori</label>
              <select class="form-select" name="kategori">
                <option value="pariwisata" <?= ($editingProgram && $editingProgram['kategori'] === 'pariwisata') ? 'selected' : '' ?>>Destinasi Wisata</option>
                <option value="produk_unggulan" <?= ($editingProgram && $editingProgram['kategori'] === 'produk_unggulan') ? 'selected' : '' ?>>Produk Unggulan / UMKM</option>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Judul</label><input class="form-control" name="judul" required value="<?= ($editingProgram && in_array($editingProgram['kategori'], ['pariwisata', 'produk_unggulan'])) ? htmlspecialchars($editingProgram['judul']) : '' ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jadwal / Jam Buka</label><input class="form-control" name="jadwal" value="<?= ($editingProgram && in_array($editingProgram['kategori'], ['pariwisata', 'produk_unggulan'])) ? htmlspecialchars($editingProgram['jadwal'] ?? '') : '' ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Urutan</label><input class="form-control" type="number" name="urutan" value="<?= ($editingProgram && in_array($editingProgram['kategori'], ['pariwisata', 'produk_unggulan'])) ? (int)$editingProgram['urutan'] : 0 ?>"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Deskripsi</label><textarea class="form-control" name="deskripsi" rows="2" required><?= ($editingProgram && in_array($editingProgram['kategori'], ['pariwisata', 'produk_unggulan'])) ? htmlspecialchars($editingProgram['deskripsi']) : '' ?></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Gambar (opsional)</label><input class="form-control" type="file" name="gambar_file" accept="image/*"></div>
            <div class="col-12"><button class="btn btn-forest">Simpan Data</button></div>
          </form>
        </div>
      <?php endif; ?>

      <h5 class="fw-semibold text-forest mb-3">Produk Unggulan & UMKM</h5>
      <div class="row g-4">
        <?php if (empty($programsProduk)): ?>
          <p class="text-on-surface-variant small">Belum ada produk unggulan yang ditambahkan.</p>
        <?php endif; ?>
        <?php foreach ($programsProduk as $program): ?>
          <div class="col-md-6 col-lg-4">
            <div class="position-relative h-100">
              <a href="pariwisata.html" class="card-link-block h-100">
                <div class="card h-100 border-0 shadow-sm rounded-4 card-hover">
                  <div class="card-body">
                    <div class="icon-chip bg-forest text-white mb-3"><i class="fa-solid fa-basket-shopping"></i></div>
                    <h5 class="text-forest fw-semibold"><?= htmlspecialchars($program['judul']) ?></h5>
                    <p class="text-on-surface-variant small"><?= nl2br(htmlspecialchars($program['deskripsi'])) ?></p>
                    <span class="text-forest-secondary fw-semibold small d-inline-flex align-items-center gap-1 mt-2">Selengkapnya <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span></span>
                  </div>
                </div>
              </a>
              <?php if ($isAdmin): ?>
                <div class="position-absolute top-0 end-0 m-3 d-flex gap-1">
                  <a href="?edit_program_id=<?= (int)$program['id'] ?>#pariwisata" class="btn btn-sm btn-outline-forest">Edit</a>
                  <a href="?hapus_program=<?= (int)$program['id'] ?>&csrf=<?= htmlspecialchars(csrfToken()) ?>#pariwisata" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?')">Hapus</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-5 bg-forest" id="video">
    <div class="container">
      <div class="mb-4 text-center">
        <span class="text-secondary-fixed fw-bold text-uppercase small" style="letter-spacing:.1em;">Media</span>
        <h2 class="fw-semibold text-white mt-1 mb-1">Video Profil Desa</h2>
        <p class="text-white-50 mb-0">Saksikan sekilas Desa Ujungberung melalui video resmi kami</p>
      </div>
      <div class="row g-4 align-items-stretch justify-content-center">
        <div class="col-lg-7">
          <div class="video-wrap">
            <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
              <iframe src="https://www.youtube.com/embed/KMaapmJbkWQ?rel=0" title="Video Profil Desa" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
            <p class="text-white-50 small text-center mt-3 mb-0">Profil Desa Ujungberung </p>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="video-side-card">
            <h6 class="text-white fw-semibold mb-3">Yang Anda Lihat di Video Ini</h6>
            <ul>
              <li><i class="fa-solid fa-mountain-sun"></i> Panorama geografis dan lanskap Desa Ujungberung</li>
              <li><i class="fa-solid fa-wheat-awn"></i> Aktivitas pertanian, perkebunan, dan agrowisata durian</li>
              <li><i class="fa-solid fa-people-group"></i> Kehidupan sosial dan gotong royong warga</li>
              <li><i class="fa-solid fa-landmark"></i> Fasilitas dan pelayanan pemerintah desa</li>
            </ul>
            <a href="#" class="btn btn-outline-forest btn-sm w-100 mt-3 d-flex align-items-center justify-content-center gap-2">
              <i class="fa-brands fa-youtube"></i> Kunjungi Kanal YouTube Desa
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-surface-container-low" id="data-desa">
    <div class="container">
      <div class="mb-4 d-flex justify-content-between align-items-end flex-wrap gap-2">
        <div>
          <span class="section-eyebrow">Keterbukaan Informasi</span>
          <h2 class="fw-semibold text-forest mt-1 mb-2">Data Pokok Desa</h2>
          <p class="text-on-surface-variant mb-0">Data resmi Desa Ujungberung — Tahun <?= (int)$activeStatistic['tahun'] ?></p>
        </div>
        <span class="badge bg-forest-container text-forest px-3 py-2">Update: <?= htmlspecialchars($activeStatistic['tahun']) ?></span>
      </div>
      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
          <p class="fw-semibold text-forest small mb-2"><i class="fa-solid fa-pen-to-square me-1"></i> Panel Admin — Edit Data Pokok Desa</p>
          <form method="post" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="save_profile" value="1">

            <div class="col-12">
              <h6 class="fw-semibold text-forest mb-2">Identitas & Wilayah</h6>
            </div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Kode Desa (PUM)</label><input class="form-control" name="desa_kode_pum" value="<?= htmlspecialchars($desa_kode_pum) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Kecamatan</label><input class="form-control" name="desa_kecamatan" value="<?= htmlspecialchars($desa_kecamatan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Kabupaten / Provinsi</label><input class="form-control" name="desa_kabupaten_provinsi" value="<?= htmlspecialchars($desa_kabupaten_provinsi) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Koordinat</label><input class="form-control" name="desa_koordinat" value="<?= htmlspecialchars($desa_koordinat) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Tipologi</label><input class="form-control" name="desa_tipologi_desa" value="<?= htmlspecialchars($desa_tipologi_desa) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Klasifikasi</label><input class="form-control" name="desa_klasifikasi_desa" value="<?= htmlspecialchars($desa_klasifikasi_desa) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Kategori Desa</label><input class="form-control" name="desa_kategori_desa" value="<?= htmlspecialchars($desa_kategori_desa) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Luas Tanah Kas</label><input class="form-control" name="desa_luas_tanah_kas" value="<?= htmlspecialchars($desa_luas_tanah_kas) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jarak ke Kec.</label><input class="form-control" name="desa_jarak_ke_kecamatan" value="<?= htmlspecialchars($desa_jarak_ke_kecamatan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jarak ke Kab./Prov.</label><input class="form-control" name="desa_jarak_ke_kabupaten" value="<?= htmlspecialchars($desa_jarak_ke_kabupaten) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jarak ke Prov.</label><input class="form-control" name="desa_jarak_ke_provinsi" value="<?= htmlspecialchars($desa_jarak_ke_provinsi) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Utara</label><input class="form-control" name="desa_batas_utara" value="<?= htmlspecialchars($desa_batas_utara) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Selatan</label><input class="form-control" name="desa_batas_selatan" value="<?= htmlspecialchars($desa_batas_selatan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Barat</label><input class="form-control" name="desa_batas_barat" value="<?= htmlspecialchars($desa_batas_barat) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Timur</label><input class="form-control" name="desa_batas_timur" value="<?= htmlspecialchars($desa_batas_timur) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sawah</label><input class="form-control" name="desa_luas_sawah" value="<?= htmlspecialchars($desa_luas_sawah) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Ladang</label><input class="form-control" name="desa_luas_ladang" value="<?= htmlspecialchars($desa_luas_ladang) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Perkebunan</label><input class="form-control" name="desa_luas_perkebunan" value="<?= htmlspecialchars($desa_luas_perkebunan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Hutan</label><input class="form-control" name="desa_luas_hutan" value="<?= htmlspecialchars($desa_luas_hutan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Peternakan</label><input class="form-control" name="desa_luas_peternakan" value="<?= htmlspecialchars($desa_luas_peternakan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Bengkok</label><input class="form-control" name="desa_luas_bengkok" value="<?= htmlspecialchars($desa_luas_bengkok) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Titi Sara</label><input class="form-control" name="desa_luas_titi_sara" value="<?= htmlspecialchars($desa_luas_titi_sara) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Lainnya</label><input class="form-control" name="desa_luas_lainnya" value="<?= htmlspecialchars($desa_luas_lainnya) ?>"></div>

            <div class="col-12"><h6 class="fw-semibold text-forest mb-2">Personil Pemerintah Desa</h6></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Kepala Desa</label><input class="form-control" name="desa_kepala_name" value="<?= htmlspecialchars($desa_kepala_name) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Pendidikan Kepala Desa</label><input class="form-control" name="desa_kepala_education" value="<?= htmlspecialchars($desa_kepala_education) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Kutipan Kepala Desa</label><input class="form-control" name="desa_kepala_quote" value="<?= htmlspecialchars($desa_kepala_quote) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Sekretaris Desa</label><input class="form-control" name="desa_sekretaris_name" value="<?= htmlspecialchars($desa_sekretaris_name) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Pendidikan Sekretaris</label><input class="form-control" name="desa_sekretaris_education" value="<?= htmlspecialchars($desa_sekretaris_education) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Catatan Sekretaris</label><input class="form-control" name="desa_sekretaris_note" value="<?= htmlspecialchars($desa_sekretaris_note) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Ketua BPD</label><input class="form-control" name="desa_ketua_bpd_name" value="<?= htmlspecialchars($desa_ketua_bpd_name) ?>"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Pendidikan Ketua BPD</label><input class="form-control" name="desa_ketua_bpd_education" value="<?= htmlspecialchars($desa_ketua_bpd_education) ?>"></div>

            <div class="col-12"><h6 class="fw-semibold text-forest mb-2">Kependudukan</h6></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Usia 0–17</label><input class="form-control" name="desa_pd_usia_0_17" value="<?= htmlspecialchars($desa_pd_usia_0_17) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Usia 18–56</label><input class="form-control" name="desa_pd_usia_18_56" value="<?= htmlspecialchars($desa_pd_usia_18_56) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Usia 56+</label><input class="form-control" name="desa_pd_usia_56_plus" value="<?= htmlspecialchars($desa_pd_usia_56_plus) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">KK Laki-laki</label><input class="form-control" name="desa_kk_lakilaki" value="<?= htmlspecialchars($desa_kk_lakilaki) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">KK Perempuan</label><input class="form-control" name="desa_kk_perempuan" value="<?= htmlspecialchars($desa_kk_perempuan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Pra Sejahtera</label><input class="form-control" name="desa_kk_pra_sejahtera" value="<?= htmlspecialchars($desa_kk_pra_sejahtera) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sejahtera I</label><input class="form-control" name="desa_kk_sejahtera_i" value="<?= htmlspecialchars($desa_kk_sejahtera_i) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sejahtera II</label><input class="form-control" name="desa_kk_sejahtera_ii" value="<?= htmlspecialchars($desa_kk_sejahtera_ii) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sejahtera III</label><input class="form-control" name="desa_kk_sejahtera_iii" value="<?= htmlspecialchars($desa_kk_sejahtera_iii) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sejahtera III+</label><input class="form-control" name="desa_kk_sejahtera_iii_plus" value="<?= htmlspecialchars($desa_kk_sejahtera_iii_plus) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">PNS</label><input class="form-control" name="desa_pekerjaan_pns" value="<?= htmlspecialchars($desa_pekerjaan_pns) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">TNI/Polri</label><input class="form-control" name="desa_pekerjaan_tni_polri" value="<?= htmlspecialchars($desa_pekerjaan_tni_polri) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Swasta</label><input class="form-control" name="desa_pekerjaan_swasta" value="<?= htmlspecialchars($desa_pekerjaan_swasta) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Wiraswasta</label><input class="form-control" name="desa_pekerjaan_wiraswasta" value="<?= htmlspecialchars($desa_pekerjaan_wiraswasta) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Petani</label><input class="form-control" name="desa_pekerjaan_petani" value="<?= htmlspecialchars($desa_pekerjaan_petani) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Buruh Tani</label><input class="form-control" name="desa_pekerjaan_buruh_tani" value="<?= htmlspecialchars($desa_pekerjaan_buruh_tani) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Peternak</label><input class="form-control" name="desa_pekerjaan_peternak" value="<?= htmlspecialchars($desa_pekerjaan_peternak) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jasa</label><input class="form-control" name="desa_pekerjaan_jasa" value="<?= htmlspecialchars($desa_pekerjaan_jasa) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Pengrajin</label><input class="form-control" name="desa_pekerjaan_pengrajin" value="<?= htmlspecialchars($desa_pekerjaan_pengrajin) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Pekerja Seni</label><input class="form-control" name="desa_pekerjaan_seni" value="<?= htmlspecialchars($desa_pekerjaan_seni) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Tidak Bekerja</label><input class="form-control" name="desa_pekerjaan_tidak_bekerja" value="<?= htmlspecialchars($desa_pekerjaan_tidak_bekerja) ?>"></div>

            <div class="col-12"><h6 class="fw-semibold text-forest mb-2">Pendidikan & Kesehatan</h6></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">TK</label><input class="form-control" name="desa_pendidikan_tk" value="<?= htmlspecialchars($desa_pendidikan_tk) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">SD/Sederajat</label><input class="form-control" name="desa_pendidikan_sd" value="<?= htmlspecialchars($desa_pendidikan_sd) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">SMP/Sederajat</label><input class="form-control" name="desa_pendidikan_smp" value="<?= htmlspecialchars($desa_pendidikan_smp) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">SMA/Sederajat</label><input class="form-control" name="desa_pendidikan_sma" value="<?= htmlspecialchars($desa_pendidikan_sma) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Akademi</label><input class="form-control" name="desa_pendidikan_akademi" value="<?= htmlspecialchars($desa_pendidikan_akademi) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sarjana</label><input class="form-control" name="desa_pendidikan_sarjana" value="<?= htmlspecialchars($desa_pendidikan_sarjana) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">S2</label><input class="form-control" name="desa_pendidikan_s2" value="<?= htmlspecialchars($desa_pendidikan_s2) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Pondok Pesantren</label><input class="form-control" name="desa_pendidikan_pondok" value="<?= htmlspecialchars($desa_pendidikan_pondok) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">SLB</label><input class="form-control" name="desa_pendidikan_sklb" value="<?= htmlspecialchars($desa_pendidikan_sklb) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Tidak Bersekolah</label><input class="form-control" name="desa_pendidikan_tidak_sekolah" value="<?= htmlspecialchars($desa_pendidikan_tidak_sekolah) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Dokter Umum</label><input class="form-control" name="desa_kesehatan_dokter" value="<?= htmlspecialchars($desa_kesehatan_dokter) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Bidan / Dukun Bayi</label><input class="form-control" name="desa_kesehatan_bidan" value="<?= htmlspecialchars($desa_kesehatan_bidan) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Perawat</label><input class="form-control" name="desa_kesehatan_perawat" value="<?= htmlspecialchars($desa_kesehatan_perawat) ?>"></div>

            <div class="col-12"><h6 class="fw-semibold text-forest mb-2">Sarana & Prasarana</h6></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Puskesmas Pembantu</label><input class="form-control" name="desa_sarana_puskesmas" value="<?= htmlspecialchars($desa_sarana_puskesmas) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Poskesdes</label><input class="form-control" name="desa_sarana_poskesdes" value="<?= htmlspecialchars($desa_sarana_poskesdes) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Posyandu / Polindes</label><input class="form-control" name="desa_sarana_posyandu" value="<?= htmlspecialchars($desa_sarana_posyandu) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Perpustakaan</label><input class="form-control" name="desa_sarana_perpus" value="<?= htmlspecialchars($desa_sarana_perpus) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">TK</label><input class="form-control" name="desa_sarana_tk" value="<?= htmlspecialchars($desa_sarana_tk) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">SD</label><input class="form-control" name="desa_sarana_sd" value="<?= htmlspecialchars($desa_sarana_sd) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Masjid</label><input class="form-control" name="desa_sarana_masjid" value="<?= htmlspecialchars($desa_sarana_masjid) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Mushola</label><input class="form-control" name="desa_sarana_mushola" value="<?= htmlspecialchars($desa_sarana_mushola) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sarana Olahraga</label><input class="form-control" name="desa_sarana_olahraga" value="<?= htmlspecialchars($desa_sarana_olahraga) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Kantor Desa</label><input class="form-control" name="desa_sarana_kantor" value="<?= htmlspecialchars($desa_sarana_kantor) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jalan Desa</label><input class="form-control" name="desa_sarana_jalan_desa" value="<?= htmlspecialchars($desa_sarana_jalan_desa) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jalan Kab.</label><input class="form-control" name="desa_sarana_jalan_kabupaten" value="<?= htmlspecialchars($desa_sarana_jalan_kabupaten) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Jalan Prov.</label><input class="form-control" name="desa_sarana_jalan_provinsi" value="<?= htmlspecialchars($desa_sarana_jalan_provinsi) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sumur Gali</label><input class="form-control" name="desa_sarana_sumur_gali" value="<?= htmlspecialchars($desa_sarana_sumur_gali) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Sumur Pompa</label><input class="form-control" name="desa_sarana_sumur_pompa" value="<?= htmlspecialchars($desa_sarana_sumur_pompa) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Drainase (M)</label><input class="form-control" name="desa_sarana_saluran_drainase" value="<?= htmlspecialchars($desa_sarana_saluran_drainase) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Irigasi (M)</label><input class="form-control" name="desa_sarana_saluran_irigasi" value="<?= htmlspecialchars($desa_sarana_saluran_irigasi) ?>"></div>

            <div class="col-12"><h6 class="fw-semibold text-forest mb-2">Keamanan & Lingkungan Hidup</h6></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Anggota Linmas</label><input class="form-control" name="desa_keamanan_linmas" value="<?= htmlspecialchars($desa_keamanan_linmas) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Pos Kamling</label><input class="form-control" name="desa_keamanan_pos_kamling" value="<?= htmlspecialchars($desa_keamanan_pos_kamling) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Kasus Keamanan</label><input class="form-control" name="desa_keamanan_kasus" value="<?= htmlspecialchars($desa_keamanan_kasus) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Wabah</label><input class="form-control" name="desa_lingkungan_wabah" value="<?= htmlspecialchars($desa_lingkungan_wabah) ?>"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Bencana Alam</label><input class="form-control" name="desa_lingkungan_bencana" value="<?= htmlspecialchars($desa_lingkungan_bencana) ?>"></div>

            <div class="col-12"><button class="btn btn-forest">Simpan Semua Data Desa</button></div>
          </form>
        </div>
      <?php endif; ?>

      <div class="row g-3 mb-4">
        <?php
        $statistikRingkas = [
          ['nilai' => number_format((int)$activeStatistic['jumlah_penduduk'], 0, ',', '.'), 'label' => 'Jumlah Penduduk', 'ket' => 'Jiwa'],
          ['nilai' => number_format((int)$activeStatistic['jumlah_kk'], 0, ',', '.'), 'label' => 'Kepala Keluarga', 'ket' => 'KK'],
          ['nilai' => number_format((float)$activeStatistic['luas_wilayah_ha'], 2, ',', '.'), 'label' => 'Luas Wilayah', 'ket' => 'Hektar (Ha)'],
          ['nilai' => (int)$activeStatistic['jumlah_rw'], 'label' => 'Jumlah RW', 'ket' => 'Rukun Warga'],
          ['nilai' => (int)$activeStatistic['jumlah_rt'], 'label' => 'Jumlah RT', 'ket' => 'Rukun Tetangga'],
          ['nilai' => (int)$activeStatistic['jumlah_dusun'], 'label' => 'Jumlah Dusun', 'ket' => 'Wilayah'],
          ['nilai' => (int)$activeStatistic['ketinggian_mdpl'], 'label' => 'Ketinggian', 'ket' => 'MDPL'],
          ['nilai' => (int)$activeStatistic['tahun'], 'label' => 'Tahun Data', 'ket' => 'Terakhir Update'],
        ];
        foreach ($statistikRingkas as $s): ?>
          <div class="col-6 col-md-3">
            <div class="card border-2 shadow-sm rounded-4 text-center h-100 card-hover" style="border-color: rgba(27,94,32,.1) !important;">
              <div class="card-body">
                <div class="fs-3 fw-bold text-forest-secondary mb-1"><?= htmlspecialchars((string)$s['nilai']) ?></div>
                <p class="fw-semibold small mb-1"><?= htmlspecialchars($s['label']) ?></p>
                <p class="text-on-surface-variant" style="font-size:11px;"><?= htmlspecialchars($s['ket']) ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="accordion accordion-desa" id="accordionData">

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#acc1">
              <span class="material-symbols-outlined me-2">public</span> Identitas & Wilayah
            </button>
          </h2>
          <div id="acc1" class="accordion-collapse collapse show" data-bs-parent="#accordionData">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mini-card">
                    <div class="data-row"><span>Kode Desa (PUM)</span><span><?= htmlspecialchars($desa_kode_pum) ?></span></div>
                    <div class="data-row"><span>Kecamatan</span><span><?= htmlspecialchars($desa_kecamatan) ?></span></div>
                    <div class="data-row"><span>Kabupaten / Provinsi</span><span><?= htmlspecialchars($desa_kabupaten_provinsi) ?></span></div>
                    <div class="data-row"><span>Koordinat</span><span><?= htmlspecialchars($desa_koordinat) ?></span></div>
                    <div class="data-row"><span>Tipologi Desa</span><span><?= htmlspecialchars($desa_tipologi_desa) ?></span></div>
                    <div class="data-row"><span>Klasifikasi Desa</span><span><?= htmlspecialchars($desa_klasifikasi_desa) ?></span></div>
                    <div class="data-row"><span>Kategori Desa</span><span><?= htmlspecialchars($desa_kategori_desa) ?></span></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mini-card">
                    <div class="data-row"><span>Komoditas Unggulan (Luas Tanam)</span><span><?= htmlspecialchars($desa_komoditas_luas_tanam) ?></span></div>
                    <div class="data-row"><span>Komoditas Unggulan (Nilai Ekonomi)</span><span><?= htmlspecialchars($desa_komoditas_nilai_ekonomi) ?></span></div>
                    <div class="data-row"><span>Jarak ke Kec.</span><span><?= htmlspecialchars($desa_jarak_ke_kecamatan) ?></span></div>
                    <div class="data-row"><span>Jarak ke Kabupaten</span><span><?= htmlspecialchars($desa_jarak_ke_kabupaten) ?></span></div>
                    <div class="data-row"><span>Jarak ke Provinsi</span><span><?= htmlspecialchars($desa_jarak_ke_provinsi) ?></span></div>
                    <div class="data-row"><span>Luas Tanah Kas Desa</span><span><?= htmlspecialchars($desa_luas_tanah_kas) ?></span></div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Batas Wilayah</p>
                    <div class="row g-2 small text-on-surface-variant">
                      <div class="col-md-3"><strong class="text-forest d-block">Utara</strong><?= htmlspecialchars($desa_batas_utara) ?></div>
                      <div class="col-md-3"><strong class="text-forest d-block">Selatan</strong><?= htmlspecialchars($desa_batas_selatan) ?></div>
                      <div class="col-md-3"><strong class="text-forest d-block">Barat</strong><?= htmlspecialchars($desa_batas_barat) ?></div>
                      <div class="col-md-3"><strong class="text-forest d-block">Timur</strong><?= htmlspecialchars($desa_batas_timur) ?></div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Rincian Luas Wilayah — Total <?= htmlspecialchars($desa_luas_sawah) ?> + <?= htmlspecialchars($desa_luas_ladang) ?> + <?= htmlspecialchars($desa_luas_perkebunan) ?> + <?= htmlspecialchars($desa_luas_peternakan) ?> + <?= htmlspecialchars($desa_luas_hutan) ?> + <?= htmlspecialchars($desa_luas_bengkok) ?> + <?= htmlspecialchars($desa_luas_titi_sara) ?> + <?= htmlspecialchars($desa_luas_lainnya) ?></p>
                    <div class="row g-2 small">
                      <div class="col-6 col-md-3 data-row"><span>Lahan Sawah</span><span><?= htmlspecialchars($desa_luas_sawah) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Lahan Ladang</span><span><?= htmlspecialchars($desa_luas_ladang) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Perkebunan</span><span><?= htmlspecialchars($desa_luas_perkebunan) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Peternakan</span><span><?= htmlspecialchars($desa_luas_peternakan) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Hutan</span><span><?= htmlspecialchars($desa_luas_hutan) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Bengkok</span><span><?= htmlspecialchars($desa_luas_bengkok) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Titi Sara</span><span><?= htmlspecialchars($desa_luas_titi_sara) ?></span></div>
                      <div class="col-6 col-md-3 data-row"><span>Lainnya</span><span><?= htmlspecialchars($desa_luas_lainnya) ?></span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc2">
              <span class="material-symbols-outlined me-2">badge</span> Personil Pemerintah Desa
            </button>
          </h2>
          <div id="acc2" class="accordion-collapse collapse" data-bs-parent="#accordionData">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest mb-1">Kepala Desa</p>
                    <p class="small mb-1"><?= htmlspecialchars($desa_kepala_name) ?></p>
                    <p class="text-on-surface-variant" style="font-size:12px;"><?= htmlspecialchars($desa_kepala_education) ?></p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest mb-1">Sekretaris Desa</p>
                    <p class="small mb-1"><?= htmlspecialchars($desa_sekretaris_name) ?></p>
                    <p class="text-on-surface-variant" style="font-size:12px;"><?= htmlspecialchars($desa_sekretaris_education) ?></p>
                    <p class="text-on-surface-variant" style="font-size:11px;"><?= htmlspecialchars($desa_sekretaris_note) ?></p>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest mb-1">Ketua BPD</p>
                    <p class="small mb-1"><?= htmlspecialchars($desa_ketua_bpd_name) ?></p>
                    <p class="text-on-surface-variant" style="font-size:12px;"><?= htmlspecialchars($desa_ketua_bpd_education) ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc3">
              <span class="material-symbols-outlined me-2">groups</span> Kependudukan
            </button>
          </h2>
          <div id="acc3" class="accordion-collapse collapse" data-bs-parent="#accordionData">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Jumlah Penduduk — <?= number_format((int)$activeStatistic['jumlah_penduduk'], 0, ',', '.') ?> Jiwa</p>
                    <div class="data-row"><span>Laki-laki</span><span><?= number_format((int)$activeStatistic['jumlah_laki_laki'], 0, ',', '.') ?> Jiwa</span></div>
                    <div class="data-row"><span>Perempuan</span><span><?= number_format((int)$activeStatistic['jumlah_perempuan'], 0, ',', '.') ?> Jiwa</span></div>
                    <div class="data-row"><span>Usia 0–17 Tahun</span><span><?= htmlspecialchars($desa_pd_usia_0_17) ?> Jiwa</span></div>
                    <div class="data-row"><span>Usia 18–56 Tahun</span><span><?= htmlspecialchars($desa_pd_usia_18_56) ?> Jiwa</span></div>
                    <div class="data-row"><span>Usia 56 Tahun ke atas</span><span><?= htmlspecialchars($desa_pd_usia_56_plus) ?> Jiwa</span></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Kepala Keluarga — <?= number_format((int)$activeStatistic['jumlah_kk'], 0, ',', '.') ?> KK</p>
                    <div class="data-row"><span>KK Laki-laki</span><span><?= htmlspecialchars($desa_kk_lakilaki) ?> KK</span></div>
                    <div class="data-row"><span>KK Perempuan</span><span><?= htmlspecialchars($desa_kk_perempuan) ?> KK</span></div>
                    <div class="data-row"><span>Keluarga Pra Sejahtera</span><span><?= htmlspecialchars($desa_kk_pra_sejahtera) ?> KK</span></div>
                    <div class="data-row"><span>Keluarga Sejahtera I</span><span><?= htmlspecialchars($desa_kk_sejahtera_i) ?> KK</span></div>
                    <div class="data-row"><span>Keluarga Sejahtera II</span><span><?= htmlspecialchars($desa_kk_sejahtera_ii) ?> KK</span></div>
                    <div class="data-row"><span>Keluarga Sejahtera III</span><span><?= htmlspecialchars($desa_kk_sejahtera_iii) ?> KK</span></div>
                    <div class="data-row"><span>Keluarga Sejahtera III+</span><span><?= htmlspecialchars($desa_kk_sejahtera_iii_plus) ?> KK</span></div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Mata Pencaharian</p>
                    <div class="row g-2">
                      <div class="col-6 col-md-3 data-row"><span>PNS</span><span><?= htmlspecialchars($desa_pekerjaan_pns) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>TNI/Polri</span><span><?= htmlspecialchars($desa_pekerjaan_tni_polri) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Karyawan Swasta</span><span><?= htmlspecialchars($desa_pekerjaan_swasta) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Wiraswasta/Pedagang</span><span><?= htmlspecialchars($desa_pekerjaan_wiraswasta) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Petani</span><span><?= htmlspecialchars($desa_pekerjaan_petani) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Buruh Tani</span><span><?= htmlspecialchars($desa_pekerjaan_buruh_tani) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Peternak</span><span><?= htmlspecialchars($desa_pekerjaan_peternak) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Jasa</span><span><?= htmlspecialchars($desa_pekerjaan_jasa) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Pengrajin</span><span><?= htmlspecialchars($desa_pekerjaan_pengrajin) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Pekerja Seni</span><span><?= htmlspecialchars($desa_pekerjaan_seni) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Pensiunan</span><span><?= htmlspecialchars($desa_pekerjaan_pensiunan) ?> Orang</span></div>
                      <div class="col-6 col-md-3 data-row"><span>Lainnya</span><span><?= htmlspecialchars($desa_pekerjaan_tidak_bekerja) ?> Orang</span></div>
                    </div>
                    <div class="data-row mt-1"><span>Tidak Bekerja / Penganggur</span><span><?= htmlspecialchars($desa_pekerjaan_tidak_bekerja) ?> Orang</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc4">
              <span class="material-symbols-outlined me-2">school</span> Pendidikan & Kesehatan
            </button>
          </h2>
          <div id="acc4" class="accordion-collapse collapse" data-bs-parent="#accordionData">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Rasio Murid</p>
                    <div class="data-row"><span>TK</span><span><?= htmlspecialchars($desa_pendidikan_tk) ?> Orang</span></div>
                    <div class="data-row"><span>SD / Sederajat</span><span><?= htmlspecialchars($desa_pendidikan_sd) ?> Orang</span></div>
                    <div class="data-row"><span>SMP / Sederajat</span><span><?= htmlspecialchars($desa_pendidikan_smp) ?> Orang</span></div>
                    <div class="data-row"><span>SMA / Sederajat</span><span><?= htmlspecialchars($desa_pendidikan_sma) ?> Orang</span></div>
                    <div class="data-row"><span>Akademi</span><span><?= htmlspecialchars($desa_pendidikan_akademi) ?> Orang</span></div>
                    <div class="data-row"><span>Sarjana</span><span><?= htmlspecialchars($desa_pendidikan_sarjana) ?> Orang</span></div>
                    <div class="data-row"><span>Pasca Sarjana</span><span><?= htmlspecialchars($desa_pendidikan_s2) ?> Orang</span></div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Tenaga Kesehatan</p>
                    <div class="data-row"><span>Dokter Umum</span><span><?= htmlspecialchars($desa_kesehatan_dokter) ?></span></div>
                    <div class="data-row"><span>Bidan/Dukun Bayi Terlatih</span><span><?= htmlspecialchars($desa_kesehatan_bidan) ?></span></div>
                    <div class="data-row"><span>Perawat</span><span><?= htmlspecialchars($desa_kesehatan_perawat) ?></span></div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Tingkat Pendidikan Warga</p>
                    <div class="data-row"><span>SD/Sederajat</span><span><?= htmlspecialchars($desa_pendidikan_sd) ?> Orang</span></div>
                    <div class="data-row"><span>SMP/Sederajat</span><span><?= htmlspecialchars($desa_pendidikan_smp) ?> Orang</span></div>
                    <div class="data-row"><span>SMA/Sederajat</span><span><?= htmlspecialchars($desa_pendidikan_sma) ?> Orang</span></div>
                    <div class="data-row"><span>D1–D3</span><span><?= htmlspecialchars($desa_pendidikan_akademi) ?> Orang</span></div>
                    <div class="data-row"><span>Sarjana S1</span><span><?= htmlspecialchars($desa_pendidikan_sarjana) ?> Orang</span></div>
                    <div class="data-row"><span>Sarjana S2</span><span><?= htmlspecialchars($desa_pendidikan_s2) ?> Orang</span></div>
                    <div class="data-row"><span>Pondok Pesantren</span><span><?= htmlspecialchars($desa_pendidikan_pondok) ?> Orang</span></div>
                    <div class="data-row"><span>Sekolah Luar Biasa</span><span><?= htmlspecialchars($desa_pendidikan_sklb) ?> Orang</span></div>
                    <div class="data-row"><span>Tidak Bersekolah</span><span><?= htmlspecialchars($desa_pendidikan_tidak_sekolah) ?> Orang</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc5">
              <span class="material-symbols-outlined me-2">apartment</span> Sarana & Prasarana
            </button>
          </h2>
          <div id="acc5" class="accordion-collapse collapse" data-bs-parent="#accordionData">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-hospital"></i><p class="fw-semibold text-forest small mb-0">Kesehatan</p></div>
                    <div class="data-row"><span>Puskesmas Pembantu</span><span><?= htmlspecialchars($desa_sarana_puskesmas) ?></span></div>
                    <div class="data-row"><span>Poskesdes</span><span><?= htmlspecialchars($desa_sarana_poskesdes) ?></span></div>
                    <div class="data-row"><span>Posyandu & Polindes</span><span><?= htmlspecialchars($desa_sarana_posyandu) ?></span></div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-3">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-graduation-cap"></i><p class="fw-semibold text-forest small mb-0">Pendidikan</p></div>
                    <div class="data-row"><span>Perpustakaan Desa</span><span><?= htmlspecialchars($desa_sarana_perpus) ?></span></div>
                    <div class="data-row"><span>Gedung TK</span><span><?= htmlspecialchars($desa_sarana_tk) ?></span></div>
                    <div class="data-row"><span>Gedung SD</span><span><?= htmlspecialchars($desa_sarana_sd) ?></span></div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-3">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-mosque"></i><p class="fw-semibold text-forest small mb-0">Ibadah</p></div>
                    <div class="data-row"><span>Masjid</span><span><?= htmlspecialchars($desa_sarana_masjid) ?></span></div>
                    <div class="data-row"><span>Mushola</span><span><?= htmlspecialchars($desa_sarana_mushola) ?></span></div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-3">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-futbol"></i><p class="fw-semibold text-forest small mb-0">Umum</p></div>
                    <div class="data-row"><span>Sarana Olahraga</span><span><?= htmlspecialchars($desa_sarana_olahraga) ?></span></div>
                    <div class="data-row"><span>Kantor Desa</span><span><?= htmlspecialchars($desa_sarana_kantor) ?></span></div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-road"></i><p class="fw-semibold text-forest small mb-0">Transportasi</p></div>
                    <div class="data-row"><span>Jalan Desa (Aspal/Beton)</span><span><?= htmlspecialchars($desa_sarana_jalan_desa) ?> Km</span></div>
                    <div class="data-row"><span>Jalan Kabupaten (Aspal/Beton)</span><span><?= htmlspecialchars($desa_sarana_jalan_kabupaten) ?> Km</span></div>
                    <div class="data-row"><span>Jalan Provinsi (Aspal/Beton)</span><span><?= htmlspecialchars($desa_sarana_jalan_provinsi) ?> Km</span></div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-faucet-drip"></i><p class="fw-semibold text-forest small mb-0">Air Bersih</p></div>
                    <div class="data-row"><span>Sumur Gali</span><span><?= htmlspecialchars($desa_sarana_sumur_gali) ?> Buah</span></div>
                    <div class="data-row"><span>Sumur Pompa</span><span><?= htmlspecialchars($desa_sarana_sumur_pompa) ?> Buah</span></div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="mini-card">
                    <div class="mini-card-head"><i class="fa-solid fa-water"></i><p class="fw-semibold text-forest small mb-0">Sanitasi & Irigasi</p></div>
                    <div class="data-row"><span>Saluran Drainase</span><span><?= htmlspecialchars($desa_sarana_saluran_drainase) ?> M</span></div>
                    <div class="data-row"><span>Saluran Irigasi</span><span><?= htmlspecialchars($desa_sarana_saluran_irigasi) ?> M</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc7">
              <span class="material-symbols-outlined me-2">shield</span> Keamanan & Lingkungan Hidup
            </button>
          </h2>
          <div id="acc7" class="accordion-collapse collapse" data-bs-parent="#accordionData">
            <div class="accordion-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Keamanan & Ketertiban</p>
                    <div class="data-row"><span>Anggota Linmas</span><span><?= htmlspecialchars($desa_keamanan_linmas) ?> Orang</span></div>
                    <div class="data-row"><span>Pos Kamling</span><span><?= htmlspecialchars($desa_keamanan_pos_kamling) ?> Buah</span></div>
                    <div class="data-row"><span>Kasus Pencurian/Perampokan</span><span><?= htmlspecialchars($desa_keamanan_kasus) ?> Kasus</span></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mini-card">
                    <p class="fw-semibold text-forest small mb-2">Lingkungan Hidup</p>
                    <div class="data-row"><span>Wabah Penyakit Menular</span><span><?= htmlspecialchars($desa_lingkungan_wabah) ?> Kasus</span></div>
                    <div class="data-row"><span>Kejadian Bencana Alam</span><span><?= htmlspecialchars((int)$desa_lingkungan_bencana) === 0 ? 'Nihil' : htmlspecialchars($desa_lingkungan_bencana . ' Kasus') ?></span></div>
                  </div>
                </div>
              </div>
              <p class="text-on-surface-variant mt-2 mb-0" style="font-size:11px;">Sumber: Data Pokok Desa/Kelurahan, Bulan Juli Tahun 2026 — Pemerintah Desa Ujungberung.</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <section class="py-5 bg-white" id="laporan-keuangan">
    <div class="container">
      <div class="mb-4 d-flex justify-content-between align-items-end flex-wrap gap-2">
        <div>
          <span class="section-eyebrow">Transparansi Anggaran</span>
          <h2 class="fw-semibold text-forest mt-1 mb-2">Laporan Keuangan & Infografis APBDes</h2>
          <p class="text-on-surface-variant mb-0">Infografis realisasi anggaran desa yang diperbarui secara berkala oleh perangkat desa.</p>
        </div>
      </div>

      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
          <p class="fw-semibold text-forest small mb-2">
            <i class="fa-solid <?= $editingReport ? 'fa-pen-to-square' : 'fa-circle-plus' ?> me-1"></i>
            <?= $editingReport ? 'Edit Laporan #' . (int)$editingReport['id'] : 'Tambah Infografis Laporan Keuangan' ?>
          </p>
          <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="save_report" value="1">
            <input type="hidden" name="report_id" value="<?= $editingReport ? (int)$editingReport['id'] : 0 ?>">
            <input type="hidden" name="nama_file_existing" value="<?= $editingReport ? htmlspecialchars($editingReport['nama_file']) : '' ?>">
            <div class="col-md-3"><label class="form-label small fw-semibold">Judul</label><input class="form-control" name="judul" required value="<?= $editingReport ? htmlspecialchars($editingReport['judul']) : '' ?>"></div>
            <div class="col-md-2"><label class="form-label small fw-semibold">Tahun</label><input class="form-control" type="number" name="tahun_anggaran" value="<?= $editingReport ? (int)$editingReport['tahun_anggaran'] : date('Y') ?>"></div>
            <div class="col-md-2"><label class="form-label small fw-semibold">Periode</label><input class="form-control" name="periode" value="<?= $editingReport ? htmlspecialchars($editingReport['periode'] ?? '') : '' ?>" placeholder="Semester I"></div>
            <div class="col-md-3"><label class="form-label small fw-semibold">Upload Foto Infografis</label><input class="form-control" type="file" name="infografis_file" accept="image/*"></div>
            <div class="col-md-2"><label class="form-label small fw-semibold">Ukuran File (KB)</label><input class="form-control" type="number" name="ukuran_file" value="<?= $editingReport ? (int)$editingReport['ukuran_file'] : 0 ?>"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Keterangan</label><textarea class="form-control" name="keterangan" rows="2"><?= $editingReport ? htmlspecialchars($editingReport['keterangan'] ?? '') : '' ?></textarea></div>
            <div class="col-12 d-flex gap-2">
              <button class="btn btn-forest">Simpan Infografis</button>
              <?php if ($editingReport): ?><a href="index.php#laporan-keuangan" class="btn btn-outline-secondary">Batal Edit</a><?php endif; ?>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <?php if (empty($financialReports)): ?>
        <div class="text-center py-5 bg-surface-container-low rounded-4">
          <i class="fa-solid fa-file-invoice-dollar text-forest-secondary mb-2" style="font-size:36px;"></i>
          <p class="text-on-surface-variant mb-0">Belum ada infografis laporan keuangan yang diunggah.</p>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($financialReports as $report): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover">
                <div class="p-3 pb-0">
                  <div class="infografis-shell">
                    <div class="infografis-media">
                      <?php if (!empty($report['nama_file'])): ?>
                        <a href="<?= htmlspecialchars($report['nama_file']) ?>" target="_blank" rel="noopener" class="w-100 h-100 d-block">
                          <img src="<?= htmlspecialchars($report['nama_file']) ?>" class="infografis-image" alt="Infografis <?= htmlspecialchars($report['judul']) ?>">
                        </a>
                      <?php else: ?>
                        <span class="text-on-surface-variant small">Belum ada gambar infografis</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <span class="badge bg-forest-container text-forest fw-semibold">Tahun <?= (int)$report['tahun_anggaran'] ?></span>
                    <?php if (!empty($report['periode'])): ?><span class="badge bg-surface-container text-forest-secondary"><?= htmlspecialchars($report['periode']) ?></span><?php endif; ?>
                  </div>
                  <h6 class="fw-semibold text-forest mb-1"><?= htmlspecialchars($report['judul']) ?></h6>
                  <p class="small text-on-surface-variant mb-3"><?= htmlspecialchars($report['keterangan'] ?: 'Infografis laporan keuangan desa') ?></p>
                  <div class="d-flex gap-2">
                    <?php if (!empty($report['nama_file'])): ?>
                      <a href="<?= htmlspecialchars($report['nama_file']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-forest flex-fill">
                        <i class="fa-solid fa-eye me-1"></i> Lihat
                      </a>
                    <?php endif; ?>
                    <?php if ($isAdmin): ?>
                      <a href="?edit_report_id=<?= (int)$report['id'] ?>#laporan-keuangan" class="btn btn-sm btn-outline-secondary flex-fill">Edit</a>
                      <a href="?hapus_laporan=<?= (int)$report['id'] ?>&csrf=<?= htmlspecialchars(csrfToken()) ?>#laporan-keuangan" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus laporan ini?')">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="py-5 bg-surface-container-low" id="layanan">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Pelayanan Publik</span>
        <h2 class="fw-semibold text-forest mt-1 mb-1">Layanan Mandiri</h2>
        <p class="text-on-surface-variant">Klik salah satu layanan untuk terhubung langsung dengan admin desa melalui WhatsApp.</p>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <a href="<?= waLink('Cetak Surat Online (Domisili/Usaha/dll)') ?>" target="_blank" rel="noopener" class="layanan-tile rounded-4 p-4 h-100 d-block" style="background-color: var(--secondary-container);">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-forest-secondary flex-shrink-0" style="width:48px;height:48px;">
                <i class="fa-solid fa-id-card fs-5"></i>
              </div>
              <div>
                <h5 class="fw-semibold mb-1" style="color:#0D3B0C;">Cetak Surat Online</h5>
                <p class="small mb-0" style="color:#0D3B0C;opacity:.8;">Urus surat keterangan domisili, usaha, dan lainnya tanpa antri — via WhatsApp.</p>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-6">
          <div class="row g-3 h-100">
            <div class="col-6">
              <a href="<?= waLink('Pembayaran / Informasi Pajak PBB') ?>" target="_blank" rel="noopener" class="layanan-tile bg-white rounded-4 p-3 h-100 d-block border">
                <i class="fa-solid fa-coins text-forest fs-4 d-block mb-1"></i>
                <p class="small mb-0 fw-semibold">Pajak PBB</p><span class="text-on-surface-variant" style="font-size:11px;"><i class="fa-regular fa-clock"></i> 1–2 hari kerja</span>
              </a>
            </div>
            <div class="col-6">
              <a href="<?= waLink('Informasi Bantuan Sosial') ?>" target="_blank" rel="noopener" class="layanan-tile bg-white rounded-4 p-3 h-100 d-block border">
                <i class="fa-solid fa-hand-holding-heart text-forest fs-4 d-block mb-1"></i>
                <p class="small mb-0 fw-semibold">Bantuan Sosial</p><span class="text-on-surface-variant" style="font-size:11px;"><i class="fa-regular fa-clock"></i> Sesuai jadwal</span>
              </a>
            </div>
            <div class="col-6">
              <a href="<?= waLink('Forum Warga / Musyawarah') ?>" target="_blank" rel="noopener" class="layanan-tile bg-white rounded-4 p-3 h-100 d-block border">
                <i class="fa-solid fa-people-group text-forest fs-4 d-block mb-1"></i>
                <p class="small mb-0 fw-semibold">Forum Warga</p><span class="text-on-surface-variant" style="font-size:11px;"><i class="fa-regular fa-clock"></i> Senin–Jumat</span>
              </a>
            </div>
            <div class="col-6">
              <a href="#layanan" class="layanan-tile bg-white rounded-4 p-3 h-100 d-block border">
                <i class="fa-solid fa-bullhorn text-forest fs-4 d-block mb-1"></i>
                <p class="small mb-0 fw-semibold">Form Pengaduan</p><span class="text-on-surface-variant" style="font-size:11px;">Respon cepat ↓</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-6">
          <div class="bg-white rounded-4 p-4 h-100 shadow-sm">
            <h6 class="text-forest fw-semibold mb-1"><i class="fa-solid fa-triangle-exclamation text-forest-secondary me-1"></i> Form Pengaduan</h6>
            <p class="text-on-surface-variant" style="font-size:12px;">Pengaduan yang dikirim akan langsung diteruskan sebagai notifikasi ke perangkat desa.</p>
            <form method="post" class="row g-3">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
              <input type="hidden" name="save_pengaduan" value="1">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama</label>
                <input class="form-control" name="nama_pengadu" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Kontak</label>
                <input class="form-control" name="kontak" placeholder="No HP / Email">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Kategori</label>
                <select class="form-select" name="kategori">
                  <option value="infrastruktur">Infrastruktur</option>
                  <option value="pelayanan">Pelayanan</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Isi Pengaduan</label>
                <textarea class="form-control" name="isi_pengaduan" rows="4" required></textarea>
              </div>
              <div class="col-12">
                <button class="btn btn-forest">Kirim Pengaduan</button>
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-6">
          <div class="bg-white rounded-4 p-4 h-100 shadow-sm">
            <h6 class="text-forest fw-semibold mb-3"><i class="fa-solid fa-phone-volume text-forest-secondary me-1"></i> Hubungi Kami</h6>
            <a href="tel:+<?= htmlspecialchars(WA_ADMIN_NUMBER) ?>" class="d-flex align-items-center gap-2 bg-surface-container-low rounded-3 p-2 mb-2 text-decoration-none small fw-medium text-dark border"><i class="fa-solid fa-phone text-forest-secondary"></i> Telepon Kantor Desa</a>
            <a href="https://wa.me/08580649206602<?= htmlspecialchars(WA_ADMIN_NUMBER) ?>" target="_blank" rel="noopener" class="d-flex align-items-center gap-2 rounded-3 p-2 mb-2 text-decoration-none small fw-medium text-white btn-wa"><i class="fa-brands fa-whatsapp"></i> WhatsApp Desa</a>
            <a href="mailto:desa.ujungberung@majalengkakab.go.id" class="d-flex align-items-center gap-2 bg-surface-container-low rounded-3 p-2 mb-2 text-decoration-none small fw-medium text-dark border"><i class="fa-solid fa-envelope text-forest-secondary"></i> Email Resmi Desa</a>
            <a href="map/map.html" class="d-flex align-items-center gap-2 bg-forest rounded-3 p-2 text-decoration-none small fw-medium text-white"><i class="fa-solid fa-map-location-dot"></i> Lihat Peta Interaktif Desa</a>
          </div>
        </div>
      </div>

      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
          <h6 class="text-forest fw-semibold mb-3"><i class="fa-solid fa-table-list text-forest-secondary me-1"></i> Daftar Pengaduan Masuk</h6>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama</th>
                  <th>Kontak</th>
                  <th>Kategori</th>
                  <th>Isi Pengaduan</th>
                  <th>Status</th>
                  <th>Waktu</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pengaduanList as $item): ?>
                  <tr>
                    <td><?= (int)$item['id'] ?></td>
                    <td><?= htmlspecialchars($item['nama_pengadu']) ?></td>
                    <td><?= htmlspecialchars($item['kontak'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($item['kategori'] ?: '-') ?></td>
                    <td style="max-width:220px;"><?= htmlspecialchars($item['isi_pengaduan']) ?></td>
                    <td>
                      <span class="badge <?= $item['status'] === 'selesai' ? 'bg-success' : ($item['status'] === 'diproses' ? 'bg-warning text-dark' : 'bg-secondary') ?>"><?= htmlspecialchars($item['status']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($item['created_at']) ?></td>
                    <td>
                      <form method="post" class="d-flex gap-1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="save_pengaduan_status" value="1">
                        <input type="hidden" name="pengaduan_id" value="<?= (int)$item['id'] ?>">
                        <select name="status" class="form-select form-select-sm" style="width:110px;">
                          <option value="baru" <?= $item['status'] === 'baru' ? 'selected' : '' ?>>Baru</option>
                          <option value="diproses" <?= $item['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                          <option value="selesai" <?= $item['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                        <button class="btn btn-sm btn-outline-forest">Simpan</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($pengaduanList)): ?>
                  <tr><td colspan="8" class="text-center text-on-surface-variant">Belum ada pengaduan masuk.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="py-5 bg-white" id="berita">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <span class="section-eyebrow">Informasi Terkini</span>
          <h2 class="fw-semibold text-forest mt-1">Berita & Pengumuman</h2>
        </div>
      </div>

      <?php if ($isAdmin): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
          <p class="fw-semibold text-forest small mb-2"><i class="fa-solid fa-circle-plus me-1"></i> Tambah Berita / Pengumuman / Kegiatan</p>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="save_program" value="1">
            <input type="hidden" name="kategori" value="kegiatan_rutin">
            <div class="col-md-4"><label class="form-label small fw-semibold">Judul</label><input class="form-control" name="judul" required></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Tanggal / Jadwal</label><input class="form-control" name="jadwal" placeholder="20 Juli 2026"></div>
            <div class="col-md-4"><label class="form-label small fw-semibold">Urutan</label><input class="form-control" type="number" name="urutan" value="0"></div>
            <div class="col-12"><label class="form-label small fw-semibold">Isi</label><textarea class="form-control" name="deskripsi" rows="2" required></textarea></div>
            <div class="col-12"><label class="form-label small fw-semibold">Gambar (opsional)</label><input class="form-control" type="file" name="gambar_file" accept="image/*"></div>
            <div class="col-12"><button class="btn btn-forest">Tambah Berita</button></div>
          </form>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-column gap-3">
        <?php if (empty($programsRutin)): ?>
          <p class="text-on-surface-variant small">Belum ada berita atau pengumuman yang ditambahkan.</p>
        <?php endif; ?>
        <?php foreach ($programsRutin as $berita): ?>
          <div class="card border-0 shadow-sm rounded-4 position-relative">
            <div class="card-body d-flex align-items-center gap-3">
              <?php if (!empty($berita['gambar'])): ?>
                <img src="<?= htmlspecialchars($berita['gambar']) ?>" class="rounded-3 flex-shrink-0" style="width:96px;height:96px;object-fit:cover;" alt="<?= htmlspecialchars($berita['judul']) ?>">
              <?php else: ?>
                <div class="icon-chip bg-surface-container text-forest-secondary flex-shrink-0" style="width:96px;height:96px;"><i class="fa-solid fa-newspaper fs-3"></i></div>
              <?php endif; ?>
              <div>
                <h6 class="mb-1 fw-semibold text-forest"><?= htmlspecialchars($berita['judul']) ?></h6>
                <?php if (!empty($berita['jadwal'])): ?><p class="text-on-surface-variant mb-1" style="font-size:12px;"><?= htmlspecialchars($berita['jadwal']) ?></p><?php endif; ?>
                <p class="text-on-surface-variant small mb-0"><?= nl2br(htmlspecialchars($berita['deskripsi'])) ?></p>
              </div>
              <?php if ($isAdmin): ?>
                <div class="ms-auto d-flex gap-1">
                  <a href="?edit_program_id=<?= (int)$berita['id'] ?>#berita" class="btn btn-sm btn-outline-forest">Edit</a>
                  <a href="?hapus_program=<?= (int)$berita['id'] ?>&csrf=<?= htmlspecialchars(csrfToken()) ?>#berita" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus item ini?')">Hapus</a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-5" id="galeri">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Dokumentasi</span>
        <h2 class="fw-semibold text-forest mt-1 mb-2">Galeri Foto</h2>
        <p class="text-on-surface-variant">Momen dan keindahan Desa Ujungberung</p>
      </div>
      <?php
      $galeriFoto = ['DSC01502.jpg', 'DSC01511.jpg', 'DSC02626.JPG', 'DSC02662.JPG', 'DSC02884.JPG', 'DSC02888.JPG', 'DSC02908.JPG', 'DSC02919.JPG', 'DSC02930.JPG', 'DSC02931.JPG', 'DSC03497.JPG', 'DSC03533.JPG', 'DSC03542.JPG', 'DSC03555.JPG', 'DSCF5301.JPG', 'DSCF5484.JPG', 'DSCF5498.JPG', 'tebingkandaga.JPG', 'DSCF5680.JPG', 'DSCF5949.JPG'];
      ?>
      <div class="row g-3">
        <?php foreach ($galeriFoto as $foto): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <a href="images/<?= htmlspecialchars($foto) ?>" target="_blank" rel="noopener" class="gallery-frame d-block">
              <img src="images/<?= htmlspecialchars($foto) ?>" alt="Dokumentasi Desa Ujungberung" loading="lazy">
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-5 bg-white" id="agenda">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Jadwal Penting</span>
        <h2 class="fw-semibold text-forest mt-1 mb-2">Agenda Kegiatan Desa</h2>
        <p class="text-on-surface-variant">Jadwal rutin serta tradisi dan hari besar yang diperingati Desa Ujungberung setiap tahun</p>
      </div>
      <div class="row g-4 mb-4">
        <?php
        $agendaRutin = [
          ['icon' => 'fa-solid fa-users', 'judul' => 'Rapat Desa Bulanan', 'waktu' => 'Setiap Minggu Pertama, Pukul 19:00', 'desk' => 'Membahas kebijakan dan program pembangunan desa bersama perangkat dan ketua RT/RW.'],
          ['icon' => 'fa-solid fa-syringe', 'judul' => 'Posyandu & Vaksinasi', 'waktu' => 'Setiap Bulan (Jadwal Fleksibel)', 'desk' => 'Kegiatan kesehatan masyarakat di 5 titik Posyandu/Polindes untuk seluruh warga.'],
        ];
        foreach ($agendaRutin as $a): ?>
          <div class="col-md-6">
            <div class="rounded-4 p-4 h-100 card-hover" style="background:linear-gradient(135deg,#f0fdf4,#ecfdf5); border:2px solid rgba(27,94,32,.1);">
              <div class="d-flex gap-3">
                <div class="icon-chip bg-forest text-white"><i class="<?= $a['icon'] ?>"></i></div>
                <div>
                  <h6 class="text-forest fw-semibold mb-1"><?= htmlspecialchars($a['judul']) ?></h6>
                  <p class="text-on-surface-variant small mb-1">Waktu: <?= htmlspecialchars($a['waktu']) ?></p>
                  <p class="text-on-surface-variant" style="font-size:12px;"><?= htmlspecialchars($a['desk']) ?></p>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <h5 class="fw-semibold text-forest mb-3">Tradisi & Hari Besar Keagamaan</h5>
      <div class="row g-4">
        <?php foreach ($agendaTetap as $a): ?>
          <div class="col-md-6 col-lg-4">
            <div class="rounded-4 p-4 h-100 card-hover" style="background:linear-gradient(135deg,#fdfaf3,#fef7ec); border:2px solid rgba(107,88,64,.15);">
              <div class="d-flex gap-3">
                <div class="icon-chip" style="background-color: var(--tertiary-fixed); color: var(--on-tertiary-fixed-variant);"><i class="<?= $a['icon'] ?>"></i></div>
                <div>
                  <h6 class="fw-semibold mb-1" style="color: var(--on-tertiary-fixed-variant);"><?= htmlspecialchars($a['judul']) ?></h6>
                  <p class="text-on-surface-variant small mb-1">Waktu: <?= htmlspecialchars($a['waktu']) ?></p>
                  <p class="text-on-surface-variant" style="font-size:12px;"><?= htmlspecialchars($a['desk']) ?></p>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="text-on-surface-variant small mt-3 mb-0" style="font-size:11px;">* Tanggal peringatan hari besar Islam menyesuaikan kalender Hijriah dan pengumuman resmi pemerintah setiap tahunnya.</p>
    </div>
  </section>

  <section class="py-5 peta-cta text-white">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-md-8">
          <span class="text-secondary-fixed fw-bold text-uppercase small" style="letter-spacing:.1em;">Sistem Informasi Geospasial</span>
          <h2 class="fw-semibold mt-1 mb-2">Jelajahi Peta Interaktif Desa Ujungberung</h2>
          <p class="text-white-50 mb-0">Lihat batas dusun, sebaran potensi wisata, titik UMKM, hingga tingkat kerawanan bencana longsor melalui peta interaktif berbasis data spasial desa.</p>
        </div>
        <div class="col-md-4 text-md-end">
          <a href="map/map.html" class="btn btn-lg btn-outline-forest px-4 d-inline-flex align-items-center gap-2">
            <i class="fa-solid fa-map-location-dot"></i> Buka Peta Desa
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="py-5 bg-surface-container-low" id="kontak">
    <div class="container">
      <div class="mb-4">
        <span class="section-eyebrow">Hubungi Kami</span>
        <h2 class="fw-semibold text-forest mt-1 mb-2">Lokasi & Kontak</h2>
        <p class="text-on-surface-variant">Temukan kami di Kecamatan Sindangwangi, Kabupaten Majalengka</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex gap-3 mb-3">
                <span class="bg-forest-container rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 text-forest-secondary" style="width:40px;height:40px;"><i class="fa-solid fa-location-dot"></i></span>
                <div><strong class="small d-block">Alamat</strong>
                  <p class="text-on-surface-variant small mb-0">Kantor Desa Ujungberung<br>Kec. Sindangwangi, Kab. Majalengka<br>Jawa Barat</p>
                </div>
              </div>
              <div class="d-flex gap-3 mb-3">
                <span class="bg-forest-container rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 text-forest-secondary" style="width:40px;height:40px;"><i class="fa-solid fa-phone"></i></span>
                <div><strong class="small d-block">Telepon / WhatsApp</strong>
                  <p class="text-on-surface-variant small mb-0">+<?= htmlspecialchars(WA_ADMIN_NUMBER) ?></p>
                </div>
              </div>
              <div class="d-flex gap-3 mb-3">
                <span class="bg-forest-container rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 text-forest-secondary" style="width:40px;height:40px;"><i class="fa-solid fa-envelope"></i></span>
                <div><strong class="small d-block">Email</strong>
                  <p class="text-on-surface-variant small mb-0">desa.ujungberung@majalengkakab.go.id</p>
                </div>
              </div>
              <div class="d-flex gap-2">
                <a href="#" class="btn btn-sm bg-surface-container text-forest flex-fill"><i class="fa-brands fa-facebook"></i></a>
                <a href="#" class="btn btn-sm bg-surface-container text-forest flex-fill"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="btn btn-sm bg-surface-container text-forest flex-fill"><i class="fa-brands fa-youtube"></i></a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="rounded-4 overflow-hidden shadow-sm h-100" style="min-height:320px;">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15851.5!2d108.374203!3d-6.768923!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sUjungberung%2C%20Sindangwangi%2C%20Majalengka%2C%20West%20Java!5e0!3m2!1sen!2sid!4v1700000000000" width="100%" height="100%" style="border:0;min-height:320px;" allowfullscreen loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="bg-white border-top">
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-md-3">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="bg-forest-container rounded-circle d-flex align-items-center justify-content-center text-forest-secondary" style="width:40px;height:40px;"><i class="fa-solid fa-landmark"></i></span>
            <span class="fw-bold text-forest">Desa Ujungberung</span>
          </div>
          <p class="text-on-surface-variant small">Kecamatan Sindangwangi, Kabupaten Majalengka, Jawa Barat.</p>
          <p class="text-forest fw-semibold small">desa.ujungberung@majalengkakab.go.id</p>
        </div>
        <div class="col-md-3">
          <h6 class="fw-bold text-uppercase small mb-3">Menu Utama</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="#beranda" class="text-on-surface-variant text-decoration-none">Beranda</a></li>
            <li class="mb-2"><a href="#profil" class="text-on-surface-variant text-decoration-none">Profil Desa</a></li>
            <li class="mb-2"><a href="#data-desa" class="text-on-surface-variant text-decoration-none">Data Desa</a></li>
            <li><a href="#layanan" class="text-on-surface-variant text-decoration-none">Layanan</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h6 class="fw-bold text-uppercase small mb-3">Informasi</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="#laporan-keuangan" class="text-on-surface-variant text-decoration-none">Laporan Keuangan</a></li>
            <li class="mb-2"><a href="#berita" class="text-on-surface-variant text-decoration-none">Berita Desa</a></li>
            <li class="mb-2"><a href="#galeri" class="text-on-surface-variant text-decoration-none">Galeri</a></li>
            <li><a href="#kontak" class="text-on-surface-variant text-decoration-none">Kontak</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h6 class="fw-bold text-uppercase small mb-3">Link Terkait</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="map/map.html" class="text-on-surface-variant text-decoration-none">Peta Desa</a></li>
            <li class="mb-2"><a href="https://majalengkakab.go.id" target="_blank" class="text-on-surface-variant text-decoration-none">Kab. Majalengka</a></li>
            <li class="mb-2"><a href="https://jabarprov.go.id" target="_blank" class="text-on-surface-variant text-decoration-none">Prov. Jawa Barat</a></li>
            <li><a href="https://kemendesa.go.id" target="_blank" class="text-on-surface-variant text-decoration-none">Kemendes PDTT</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="border-top">
      <div class="container py-3 d-flex flex-column flex-md-row justify-content-between gap-2 small text-on-surface-variant">
        <p class="mb-0">© <?= date('Y') ?> Pemerintah Desa Ujungberung · Kec. Sindangwangi · Kab. Majalengka · Jawa Barat</p>
        <p class="mb-0"> ~ Tim KKN PPM UGM Simfoni Sindangwangi P2 2026</p>
      </div>
    </div>
  </footer>

  <div class="bottom-nav">
    <a href="#beranda" class="active"><span class="material-symbols-outlined">home</span>Beranda</a>
    <a href="#data-desa"><span class="material-symbols-outlined">query_stats</span>Data Desa</a>
    <a href="#layanan"><span class="material-symbols-outlined">grid_view</span>Layanan</a>
    <a href="map/map.html"><span class="material-symbols-outlined">map</span>Peta</a>
    <a href="#kontak"><span class="material-symbols-outlined">location_on</span>Kontak</a>
  </div>

  <a href="#beranda" class="back-to-top d-none d-md-flex"><span class="material-symbols-outlined">keyboard_arrow_up</span></a>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>