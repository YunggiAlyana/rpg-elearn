<?php
session_start();
require_once '../includes/db.php'; // pastikan koneksi DB
require_once '../includes/config.php';

$isAdmin = isset($_GET['admin']) && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'] ?? 'siswa'; // default siswa kalau nggak ada

    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);

        if ($isAdmin) {
            header("Location: " . BASE_URL . "/public/admin/dashboard.php?success=1");
        } else {
            header("Location: " . BASE_URL . "/public/index.php?registered=1");
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

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <?php if ($isAdmin): ?>
        <label for="role">Role:</label>
        <select name="role">
            <option value="siswa">Siswa</option>
            <option value="dosen">Dosen</option>
            <option value="admin">Admin</option>
        </select><br>
        <?php endif; ?>

        <button type="submit">Register</button>
    </form>
    
    <p><a href="<?= BASE_URL ?>/public/index.php">Kembali ke Login</a></p>
</body>
</html>