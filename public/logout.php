<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

// Hapus semua session
$_SESSION = [];
session_destroy();

// Redirect ke halaman login
header("Location: " . BASE_URL . "/public/index.php");
exit;
?>