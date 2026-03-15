<?php
// Redirect ke dashboard jika sudah login, atau ke login jika belum
require_once '../config/database.php';

if (isAuthenticated()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
?>