<?php
session_start();
session_destroy();
header('Location: ../index.html?logout=1');
exit;
?>