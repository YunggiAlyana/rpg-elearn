<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$badge_id = isset($_GET['badge_id']) ? intval($_GET['badge_id']) : 0;

// Function to check if the badge exists
function badgeExists($db, $badge_id) {
    $stmt = $db->prepare("SELECT id FROM badges WHERE id = ?");
    $stmt->execute([$badge_id]);
    return $stmt->fetch() ? true : false;
}

// Function to check if the user already has the badge
function userHasBadge($db, $user_id, $badge_id) {
    $stmt = $db->prepare("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?");
    $stmt->execute([$user_id, $badge_id]);
    return $stmt->fetch() ? true : false;
}

// Function to unlock the badge
function unlockBadge($db, $user_id, $badge_id) {
    $stmt = $db->prepare("INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (?, ?, NOW())");
    return $stmt->execute([$user_id, $badge_id]);
}

try {
    // Validations
    if ($badge_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID badge tidak valid.']);
        exit;
    }
    
    if (!badgeExists($db, $badge_id)) {
        echo json_encode(['success' => false, 'message' => 'Badge tidak ditemukan.']);
        exit;
    }
    
    if (userHasBadge($db, $user_id, $badge_id)) {
        echo json_encode(['success' => false, 'message' => 'Badge sudah dimiliki.']);
        exit;
    }
    
    // Try to unlock the badge
    if (unlockBadge($db, $user_id, $badge_id)) {
        // Get badge details for response
        $stmt = $db->prepare("SELECT * FROM badges WHERE id = ?");
        $stmt->execute([$badge_id]);
        $badge = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Badge berhasil dibuka!',
            'data' => $badge
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membuka badge.']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}