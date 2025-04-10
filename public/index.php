<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');

if (isset($_SESSION['user'])) {
    // Redirect ke dashboard sesuai role
    $role = $_SESSION['user']['role'];
    header("Location: " . BASE_URL . "/public/{$role}/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - RPG Elearn</title>
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="<?= BASE_URL ?>/api/auth/login.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</body>
</html>