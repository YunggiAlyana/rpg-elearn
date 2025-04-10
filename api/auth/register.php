<?php
session_start();
require_once(__DIR__ . '/../../includes/config.php');
require_once(__DIR__ . '/../../includes/db.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple validation
    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = 'Username sudah digunakan.';
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user with default role 'siswa'
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'siswa')");
            
            if ($stmt->execute([$username, $hashed_password])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal mendaftarkan user baru.';
            }
        }
    }
}

// If API is called directly
if (php_sapi_name() !== 'cli') {
    if (!empty($success)) {
        echo json_encode(['success' => true, 'message' => $success]);
    } else if (!empty($error)) {
        echo json_encode(['success' => false, 'message' => $error]);
    }
}
?>