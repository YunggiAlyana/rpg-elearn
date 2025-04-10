<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: " . BASE_URL . "/public/index.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user']['role'] !== $role) {
        echo "Akses ditolak: role tidak sesuai.";
        exit;
    }
}
