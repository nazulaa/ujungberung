<?php
require_once __DIR__ . '/../admin/includes/auth.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function send_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'status') {
    $authenticated = !empty($_SESSION['user_id']);
    send_json([
        'success' => true,
        'authenticated' => $authenticated,
        'csrf_token' => $authenticated ? csrfToken() : null,
        'username' => $authenticated ? ($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin') : null,
    ]);
}

if ($action === 'load-points') {
    $type = trim($_GET['type'] ?? '');
    if ($type !== 'umkm' && $type !== 'pariwisata') {
        send_json(['success' => false, 'error' => 'Tipe titik tidak valid.'], 400);
    }

    $table = $type === 'umkm' ? 'umkm' : 'pariwisata';
    $stmt = $conn->prepare("SELECT id, nama, longitude, latitude, jenis, foto FROM {$table} ORDER BY id ASC");
    if (!$stmt) {
        send_json(['success' => false, 'error' => 'Gagal menyiapkan query database.'], 500);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        send_json(['success' => false, 'error' => 'Gagal membaca data dari database.'], 500);
    }

    $result = $stmt->get_result();
    $features = [];
    while ($row = $result->fetch_assoc()) {
        $feature = [
            'type' => 'Feature',
            'properties' => [
                '_ID' => (int)$row['id'],
                '_LATITUDE' => (float)$row['latitude'],
                '_LONGITUDE' => (float)$row['longitude'],
                'gambar' => $row['foto'],
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float)$row['longitude'], (float)$row['latitude'], 0.0],
            ],
        ];

        if ($type === 'umkm') {
            $feature['properties']['Nama_UMKM'] = $row['nama'];
            $feature['properties']['Kategori_1'] = '';
            $feature['properties']['Produk_y_1'] = '';
            $feature['properties']['Jenis_Us_1'] = $row['jenis'];
            $feature['properties']['Nama_Pem_1'] = '';
        } else {
            $feature['properties']['_NAME'] = $row['nama'];
    if ($row['jenis'] !== null) {
        $feature['properties']['keterangan'] = $row['jenis'];
    }
}

$features[] = $feature;
}

$stmt->close();
send_json(['type' => 'FeatureCollection', 'features' => $features]);
}

if ($action === 'save-point' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['user_id'])) {
        send_json(['success' => false, 'error' => 'Anda harus login sebagai admin untuk menambahkan atau mengedit titik.'], 401);
    }

    if (!csrfCheck($_POST['csrf_token'] ?? '')) {
        send_json(['success' => false, 'error' => 'Token keamanan tidak valid.'], 403);
    }

    $type = trim($_POST['type'] ?? '');
    $originalType = trim($_POST['original_type'] ?? $type);
    if ($originalType !== 'umkm' && $originalType !== 'pariwisata') {
        send_json(['success' => false, 'error' => 'Tipe titik tidak valid.']);
    }

    $id = isset($_POST['id']) ? max(0, (int)$_POST['id']) : 0;
    $lat = filter_var($_POST['latitude'] ?? '', FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_POST['longitude'] ?? '', FILTER_VALIDATE_FLOAT);
    $name = mb_substr(trim($_POST['name'] ?? ''), 0, 150);

    if ($name === '' || $lat === false || $lng === false) {
        send_json(['success' => false, 'error' => 'Nama dan koordinat wajib diisi.']);
    }

    $table = $originalType === 'umkm' ? 'umkm' : 'pariwisata';
    $jenisValue = $originalType === 'umkm'
        ? mb_substr(trim($_POST['jenis'] ?? ''), 0, 100)
        : mb_substr(trim($_POST['description'] ?? ''), 0, 100);

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $imageFolder = __DIR__ . '/../images/map/' . ($originalType === 'umkm' ? 'umkm' : 'pariwisata') . '/';
        if (!is_dir($imageFolder) && !mkdir($imageFolder, 0777, true)) {
            send_json(['success' => false, 'error' => 'Gagal membuat folder penyimpanan gambar.'], 500);
        }

        $imageFile = basename($_FILES['image']['name']);
        $imageFile = time() . '-' . preg_replace('/[^A-Za-z0-9._-]/', '-', $imageFile);
        $targetFile = $imageFolder . $imageFile;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            send_json(['success' => false, 'error' => 'Gagal mengunggah file gambar.'], 500);
        }
        $imagePath = 'images/map/' . ($originalType === 'umkm' ? 'umkm/' : 'pariwisata/') . $imageFile;
    }

    $isEdit = $id > 0;
    $existingDbRow = null;
    if ($isEdit) {
        $stmt = $conn->prepare("SELECT foto FROM {$table} WHERE id = ?");
        if (!$stmt) {
            send_json(['success' => false, 'error' => 'Gagal menyiapkan query database.'], 500);
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingDbRow = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($existingDbRow === null) {
            send_json(['success' => false, 'error' => 'Titik tidak ditemukan di database.'], 404);
        }

        if ($imagePath === null) {
            $imagePath = $existingDbRow['foto'];
        }
    }

    if ($isEdit) {
        $stmt = $conn->prepare("UPDATE {$table} SET nama = ?, longitude = ?, latitude = ?, jenis = ?, foto = ? WHERE id = ?");
        if (!$stmt) {
            send_json(['success' => false, 'error' => 'Gagal menyiapkan query database.'], 500);
        }
        $stmt->bind_param('sddssi', $name, $lng, $lat, $jenisValue, $imagePath, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            send_json(['success' => false, 'error' => 'Gagal memperbarui data di database.'], 500);
        }
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO {$table} (nama, longitude, latitude, jenis, foto) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            send_json(['success' => false, 'error' => 'Gagal menyiapkan query database.'], 500);
        }
        $stmt->bind_param('sddss', $name, $lng, $lat, $jenisValue, $imagePath);
        if (!$stmt->execute()) {
            $stmt->close();
            send_json(['success' => false, 'error' => 'Gagal menyimpan data di database.'], 500);
        }
        $id = $stmt->insert_id;
        $stmt->close();
    }

    $featureFile = __DIR__ . '/../geojson/' . ($originalType === 'umkm' ? 'UMKM.geojson' : 'sebaranwisata.geojson');
    $data = ['type' => 'FeatureCollection', 'features' => []];
    if (file_exists($featureFile)) {
        $json = file_get_contents($featureFile);
        $decoded = json_decode($json, true);
        if (is_array($decoded) && isset($decoded['features']) && is_array($decoded['features'])) {
            $data = $decoded;
        }
    }

    $featureIndex = null;
    if ($isEdit) {
        foreach ($data['features'] as $idx => $existingFeature) {
            if (isset($existingFeature['properties']['_ID']) && (int)$existingFeature['properties']['_ID'] === $id) {
                $featureIndex = $idx;
                break;
            }
        }
    }

    $timestamp = gmdate('m/d/Y H:i:s') . ' GMT+07:00';
    $feature = [
        'type' => 'Feature',
        'properties' => [
            '_ID' => $id,
            '_NAME' => $originalType === 'pariwisata' ? $name : null,
            '_REMARKS' => trim($_POST['description'] ?? ''),
            '_REC_TIME' => $timestamp,
            '_LATITUDE' => $lat,
            '_LONGITUDE' => $lng,
            '_ELEVATION' => 0.0,
            '_ORTHO_HT' => 0.0,
        ],
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [$lng, $lat, 0.0],
        ],
    ];

    if ($originalType === 'umkm') {
        $feature['properties']['Nama_UMKM'] = $name;
        $feature['properties']['Kategori_1'] = trim($_POST['kategori'] ?? '');
        $feature['properties']['Produk_y_1'] = trim($_POST['product'] ?? '');
        $feature['properties']['Jenis_Us_1'] = trim($_POST['jenis'] ?? '');
        $feature['properties']['Nama_Pem_1'] = trim($_POST['owner'] ?? '');
    } else {
        if (trim($_POST['description'] ?? '') !== '') {
            $feature['properties']['keterangan'] = trim($_POST['description'] ?? '');
        }
    }

    if ($imagePath !== null) {
        $feature['properties']['gambar'] = $imagePath;
    } elseif ($featureIndex !== null && isset($data['features'][$featureIndex]['properties']['gambar'])) {
        $feature['properties']['gambar'] = $data['features'][$featureIndex]['properties']['gambar'];
    }

    if ($featureIndex !== null) {
        $data['features'][$featureIndex] = $feature;
    } else {
        $data['features'][] = $feature;
    }

    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false || file_put_contents($featureFile, $encoded, LOCK_EX) === false) {
        send_json(['success' => false, 'error' => 'Gagal menyimpan data GeoJSON.'], 500);
    }

    send_json([
        'success' => true,
        'message' => $isEdit ? 'Titik berhasil diperbarui.' : 'Titik berhasil ditambahkan.',
        'feature' => $feature,
    ]);
}

send_json(['success' => false, 'error' => 'Aksi tidak dikenal.'], 400);
