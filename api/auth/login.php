<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role']
        ];

        // Redirect berdasarkan role
        switch ($user['role']) {
            case 'admin':
                header('Location: /public/admin/dashboard.php');
                break;
            case 'dosen':
                header('Location: /public/dosen/dashboard.php');
                break;
            case 'siswa':
                header('Location: /public/siswa/dashboard.php');
                break;
            default:
                header('Location: /public/index.php');
                break;
        }
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - RPG Elearn</title>
</head>
<body>
    <h2>Login</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required/><br/>
        <input type="password" name="password" placeholder="Password" required/><br/>
        <button type="submit">Login</button>
    </form>
</body>
</html>
