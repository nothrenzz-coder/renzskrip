<?php
require_once '../../config/database.php';
requireLogin();

$heroes = getHeroes();

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="nothrenzz_backup_' . date('Y-m-d') . '.json"');

echo json_encode($heroes, JSON_PRETTY_PRINT);
?>