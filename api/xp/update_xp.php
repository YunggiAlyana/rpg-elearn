<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

// Only users with appropriate roles can update XP
$allowed_roles = ['admin', 'dosen'];
if (!in_array($_SESSION['user']['role'], $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengubah XP.']);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan. Gunakan POST.']);
    exit;
}

// Get parameters
$target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
$source = isset($_POST['source']) ? $_POST['source'] : 'manual';
$source_id = isset($_POST['source_id']) ? intval($_POST['source_id']) : 0;
$reason = isset($_POST['reason']) ? $_POST['reason'] : null;

try {
    // Validate user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$target_user_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
        exit;
    }
    
    // Validate amount
    if ($amount == 0) {
        echo json_encode(['success' => false, 'message' => 'Jumlah XP harus tidak nol.']);
        exit;
    }
    
    // Insert XP transaction
    $stmt = $db->prepare("
        INSERT INTO user_xp (user_id, amount, source, source_id, reason, earned_at, awarded_by) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    
    if ($stmt->execute([
        $target_user_id, 
        $amount, 
        $source, 
        $source_id, 
        $reason, 
        $_SESSION['user']['id']
    ])) {
        // Get updated XP total
        $stmt = $db->prepare("
            SELECT SUM(amount) AS total_xp 
            FROM user_xp 
            WHERE user_id = ?
        ");
        $stmt->execute([$target_user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_xp = $result['total_xp'];
        
        // Calculate new level
        $new_level = floor(sqrt($total_xp / 100)) + 1;
        
        echo json_encode([
            'success' => true, 
            'message' => 'XP berhasil diperbarui.',
            'amount' => $amount,
            'total_xp' => $total_xp,
            'new_level' => $new_level
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui XP.']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}