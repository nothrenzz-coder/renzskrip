<?php
require_once '../../config/database.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmp = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

// Validasi tipe file
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
$fileType = mime_content_type($fileTmp);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Hanya file JPG, PNG, WEBP, dan GIF yang diperbolehkan']);
    exit;
}

// Validasi ukuran file (max 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($fileSize > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'Ukuran file maksimal 5MB']);
    exit;
}

// Buat nama file unik
$extension = pathinfo($fileName, PATHINFO_EXTENSION);
$newFileName = uniqid() . '_' . time() . '.' . $extension;
$uploadPath = IMG_DIR . $newFileName;

// Kompres gambar jika terlalu besar
if ($fileSize > 1024 * 1024) { // Jika > 1MB
    compressImage($fileTmp, $uploadPath, 80);
} else {
    // Upload biasa
    if (!move_uploaded_file($fileTmp, $uploadPath)) {
        echo json_encode(['success' => false, 'error' => 'Gagal upload file']);
        exit;
    }
}

// Optimasi gambar
optimizeImage($uploadPath);

echo json_encode([
    'success' => true,
    'filename' => $newFileName,
    'path' => 'img/' . $newFileName,
    'message' => 'Gambar berhasil diupload'
]);

// Fungsi kompres gambar
function compressImage($source, $destination, $quality) {
    $info = getimagesize($source);
    
    if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg') {
        $image = imagecreatefromjpeg($source);
        imagejpeg($image, $destination, $quality);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
        // PNG quality: 0 (best compression) - 9 (least compression)
        $pngQuality = 9 - round(($quality / 100) * 9);
        imagepng($image, $destination, $pngQuality);
    } elseif ($info['mime'] == 'image/webp') {
        $image = imagecreatefromwebp($source);
        imagewebp($image, $destination, $quality);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($source);
        imagegif($image, $destination);
    }
    
    imagedestroy($image);
}

// Fungsi optimasi gambar
function optimizeImage($path) {
    $info = getimagesize($path);
    
    // Resize jika terlalu besar (max width/height 800px)
    $maxDimension = 800;
    list($width, $height) = $info;
    
    if ($width > $maxDimension || $height > $maxDimension) {
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = round($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = round($width * ($maxDimension / $height));
        }
        
        $srcImage = null;
        if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg') {
            $srcImage = imagecreatefromjpeg($path);
        } elseif ($info['mime'] == 'image/png') {
            $srcImage = imagecreatefrompng($path);
        } elseif ($info['mime'] == 'image/webp') {
            $srcImage = imagecreatefromwebp($path);
        } elseif ($info['mime'] == 'image/gif') {
            $srcImage = imagecreatefromgif($path);
        }
        
        if ($srcImage) {
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Maintain transparency for PNG
            if ($info['mime'] == 'image/png') {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // Save resized image
            if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg') {
                imagejpeg($dstImage, $path, 85);
            } elseif ($info['mime'] == 'image/png') {
                imagepng($dstImage, $path, 6);
            } elseif ($info['mime'] == 'image/webp') {
                imagewebp($dstImage, $path, 85);
            } elseif ($info['mime'] == 'image/gif') {
                imagegif($dstImage, $path);
            }
            
            imagedestroy($srcImage);
            imagedestroy($dstImage);
        }
    }
}
?>