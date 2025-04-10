<?php
require_once '../includes/db.php';
require_once '../includes/session.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'siswa'; // Default role

    if (empty($username) || empty($_POST['password'])) {
        $errors[] = "Username dan password wajib diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $errors[] = "Username sudah terdaftar.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $role]);
            header("Location: index.php");
            exit;
        }
    }
}
?>

<form method="post">
    <h2>Register</h2>
    <?php foreach ($errors as $e) echo "<p style='color:red;'>$e</p>"; ?>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Daftar</button>
</form>
