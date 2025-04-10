<?php
require_once(__DIR__ . '/../../includes/middleware.php');
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
</body>
</html>
