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

// Fungsi cek role (bisa untuk satu atau banyak role)
function requireRole($roles) {
    requireLogin();

    $userRole = $_SESSION['user']['role'];

    // Jika $roles bukan array, ubah jadi array
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    // Cek apakah role user cocok
    if (!in_array($userRole, $roles)) {
        echo "Akses ditolak: role tidak sesuai.";
        exit;
    }
}
