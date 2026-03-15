<?php
require_once '../../config/database.php';
requireLogin();

header('Content-Type: application/json');

$heroes = getHeroes();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (isset($heroes[$id])) {
        echo json_encode($heroes[$id]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Hero not found']);
    }
} else {
    echo json_encode($heroes);
}
?>