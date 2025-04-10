<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('dosen');
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Dashboard Dosen</title>
</head>
<body>
    <h1>Selamat datang, <?= $_SESSION['user']['username'] ?>!</h1>
    <p>Ini adalah halaman dashboard khusus dosen.</p>

    <ul>
        <li><a href="upload_materi.php">📚 Upload Materi Pembelajaran</a></li>
        <li><a href="buat_quest.php">🎯 Buat Quest Baru</a></li>
        <!-- Tambahkan menu lainnya di sini -->
    </ul>

    <p><a href="<?= BASE_URL ?>/public/logout.php">Logout</a></p>
</body>
</html>