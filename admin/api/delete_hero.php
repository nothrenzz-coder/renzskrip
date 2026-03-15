<?php
require_once '../../config/database.php';
requireLogin();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$heroes = getHeroes();

if (isset($input['action'])) {
    if ($input['action'] === 'deleteSeries') {
        $heroId = (int)$input['heroId'];
        $seriesIndex = (int)$input['seriesIndex'];
        
        if (isset($heroes[$heroId]['series'][$seriesIndex])) {
            $seriesName = $heroes[$heroId]['series'][$seriesIndex]['name'];
            array_splice($heroes[$heroId]['series'], $seriesIndex, 1);
            
            if (saveHeroes($heroes)) {
                logActivity('DELETE_SERIES', "Series: $seriesName dari Hero ID: $heroId");
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menyimpan']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Series tidak ditemukan']);
        }
        exit;
    }
    
    if ($input['action'] === 'deleteVariant') {
        $heroId = (int)$input['heroId'];
        $seriesIndex = (int)$input['seriesIndex'];
        $variantIndex = (int)$input['variantIndex'];
        
        if (isset($heroes[$heroId]['series'][$seriesIndex]['items'][$variantIndex])) {
            $variantName = $heroes[$heroId]['series'][$seriesIndex]['items'][$variantIndex]['name'];
            array_splice($heroes[$heroId]['series'][$seriesIndex]['items'], $variantIndex, 1);
            
            if (saveHeroes($heroes)) {
                logActivity('DELETE_VARIANT', "Variant: $variantName");
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menyimpan']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Variant tidak ditemukan']);
        }
        exit;
    }
}

// Delete hero
if (isset($input['id'])) {
    $id = (int)$input['id'];
    
    if (!isset($heroes[$id])) {
        echo json_encode(['success' => false, 'error' => 'Hero tidak ditemukan']);
        exit;
    }
    
    $heroName = $heroes[$id]['name'];
    
    // Hapus file gambar
    if (isset($heroes[$id]['image'])) {
        $imageFilename = basename($heroes[$id]['image']);
        $imagePath = IMG_DIR . $imageFilename;
        
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    array_splice($heroes, $id, 1);
    
    if (saveHeroes($heroes)) {
        logActivity('DELETE_HERO', "Hero: $heroName");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>