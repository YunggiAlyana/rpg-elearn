<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body>
    <h2>Selamat datang, <?= $_SESSION['user']['username'] ?> (<?= $_SESSION['user']['role'] ?>)</h2>
    <a href="logout.php">Logout</a>
</body>
</html>
