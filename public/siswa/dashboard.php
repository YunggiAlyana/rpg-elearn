<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('siswa');
?>

<!DOCTYPE html>
<html>
<head>  
    <title>Dashboard Siswa</title>
</head>
<body>
    <h1>Selamat datang, <?= $_SESSION['user']['username'] ?>!</h1>
    <p>Ini adalah halaman dashboard untuk siswa.</p>

    <ul>
        <li><a href="materi.php">📚 Lihat Materi Pembelajaran</a></li>
        <li><a href="quest.php">🎯 Lihat Quest</a></li>
        <li><a href="badge.php">🏆 Badges & Achievements</a></li>
        <!-- Tambahkan menu lainnya di sini -->
    </ul>

    <p><a href="<?= BASE_URL ?>/public/logout.php">Logout</a></p>
</body>
</html>