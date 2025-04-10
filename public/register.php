<?php
session_start();
require_once '../includes/db.php'; // pastikan koneksi DB
require_once '../includes/middleware.php';

$isAdmin = isset($_GET['admin']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'] ?? 'siswa'; // default siswa kalau nggak ada

    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);

        if ($isAdmin) {
            header("Location: admin/dashboard.php?success=1");
        } else {
            header("Location: login.php?registered=1");
        }
        exit();
    } catch (PDOException $e) {
        $error = "Username sudah digunakan atau error lainnya.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2><?= $isAdmin ? 'Tambah Pengguna oleh Admin' : 'Register Akun Siswa' ?></h2>
    
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</
