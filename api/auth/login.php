<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        // Redirect sesuai role
        switch ($user['role']) {
            case 'admin':
                header('Location: ' . BASE_URL . '/public/admin/dashboard.php');
                break;
            case 'dosen':
                header('Location: ' . BASE_URL . '/public/dosen/dashboard.php');
                break;
            case 'siswa':
                header('Location: ' . BASE_URL . '/public/siswa/dashboard.php');
                break;
            default:
                echo "Role tidak dikenal.";
                exit;
        }
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login - RPG Elearn</title></head>
<body>
    <h2>Login</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required/><br/>
        <input type="password" name="password" placeholder="Password" required/><br/>
        <button type="submit">Login</button>
    </form>
</body>
</html>
