<?php
require_once '../../config/database.php';
requireLogin();

header('Content-Type: application/json');

// Validasi CSRF token untuk form POST
if ($_POST && (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token']))) {
    logActivity('CSRF_ATTEMPT', 'Invalid CSRF token');
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (saveHeroes($input)) {
        logActivity('DATA_SAVED', 'Bulk data saved');
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan']);
    }
    exit;
}

if ($_POST) {
    $heroes = getHeroes();
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // Sanitasi input
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    $heroData = [
        'name' => $name,
        'image' => '',
        'description' => $description,
        'series' => isset($_POST['series']) ? json_decode($_POST['series'], true) : []
    ];

    // Proses upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = IMG_DIR;
        
        // Validasi tipe file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'error' => 'Tipe file tidak diizinkan']);
            exit;
        }
        
        // Validasi ukuran
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'Ukuran file maksimal 5MB']);
            exit;
        }
        
        // Buat nama file aman
        $cleanName = strtolower(trim($name));
        $cleanName = preg_replace('/[^a-z0-9]+/', '_', $cleanName);
        $cleanName = trim($cleanName, '_');
        
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = $cleanName . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        // Hapus gambar lama
        if ($id !== null && isset($heroes[$id]['image'])) {
            $oldImagePath = $uploadDir . basename($heroes[$id]['image']);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        // Hapus file dengan nama sama (kalo ada)
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        
        // Upload file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $heroData['image'] = 'img/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Gagal menyimpan file']);
            exit;
        }
        
    } else if ($id !== null && isset($heroes[$id]['image'])) {
        $heroData['image'] = $heroes[$id]['image'];
    } else {
        echo json_encode(['success' => false, 'error' => 'Gambar wajib diupload']);
        exit;
    }

    // Update array
    if ($id !== null) {
        $heroes[$id] = $heroData;
        $action = 'UPDATE';
    } else {
        $heroes[] = $heroData;
        $action = 'CREATE';
    }

    // Sorting A-Z
    usort($heroes, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    // Simpan
    if (saveHeroes($heroes)) {
        logActivity($action, "Hero: $name");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan ke file']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>