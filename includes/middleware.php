<?php
// Mulai session kalau belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi cek login
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: /public/index.php");
        exit;
    }
}

// Fungsi cek role
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user']['role'] !== $role) {
        echo "Akses ditolak: role tidak sesuai.";
        exit;
    }
}
