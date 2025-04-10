<?php
require_once '../../includes/db.php';
require_once '../../includes/session.php';
require_once '../../includes/middleware.php';

requireRole('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (empty($username) || empty($_POST['password']) || empty($role)) {
        $errors[] = "Semua field wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Username sudah ada.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $role]);
            echo "User berhasil didaftarkan.";
        }
    }
}
?>

<form method="post">
    <h2>Tambah User Baru</h2>
    <?php foreach ($errors as $e) echo "<p style='color:red;'>$e</p>"; ?>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <select name="role" required>
        <option value="">Pilih Role</option>
        <option value="admin">Admin</option>
        <option value="dosen">Dosen</option>
        <option value="siswa">Siswa</option>
    </select><br>
    <button type="submit">Tambah</button>
</form>
