<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];

try {
    // Check if file was uploaded
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != 0) {
        echo json_encode(['success' => false, 'message' => 'File avatar tidak ditemukan atau error.']);
        exit;
    }
    
    $file = $_FILES['avatar'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.']);
        exit;
    }
    
    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
        exit;
    }
    
    // Create avatars directory if it doesn't exist
    $target_dir = "../../uploads/avatars/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename
    $filename = $user_id . '_' . time() . '_' . basename($file['name']);
    $target_file = $target_dir . $filename;
    $file_path = 'uploads/avatars/' . $filename;
    
    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengunggah avatar.']);
        exit;
    }
    
    // Update user avatar in database
    $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    
    if ($stmt->execute([$file_path, $user_id])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Avatar berhasil diperbarui.',
            'avatar_url' => BASE_URL . '/' . $file_path
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui avatar di database.']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}