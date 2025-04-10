<?php
require_once(__DIR__ . '/../../includes/middleware.php');
requireRole('admin');
require_once(__DIR__ . '/../../includes/db.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'siswa';

    // Cek apakah username sudah dipakai
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $error = 'Username sudah digunakan.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $role]);
        $success = 'User berhasil ditambahkan.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah User - Admin</title>
</head>
<body>
    <h2>Tambah User Baru (Admin Only)</h2>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <label>Role:</label>
        <select name="role" required>
            <option value="siswa">Siswa</option>
            <option value="dosen">Dosen</option>
            <option value="admin">Admin</option>
        </select><br><br>
        <button type="submit">Register User</button>
    </form>
</body>
</html>
