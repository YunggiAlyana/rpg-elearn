<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('admin');
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Dashboard Admin</title>
</head>
<body>
    <h1>Selamat datang, <?= $_SESSION['user']['username'] ?>!</h1>
    <p>Ini adalah halaman dashboard khusus admin.</p>

    <ul>
        <li><a href="register_user.php">➕ Tambah User Baru</a></li>
        <li><a href="manage_user.php">👥 Kelola User</a></li>
        <li><a href="setting.php">⚙️ Pengaturan Sistem</a></li>
        <!-- Tambahkan menu lainnya di sini -->
    </ul>

    <p><a href="<?= BASE_URL ?>/public/logout.php">Logout</a></p>
</body>
</html>