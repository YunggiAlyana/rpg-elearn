<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$target_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;

try {
    // Get total XP
    $stmt = $db->prepare("
        SELECT 
            IFNULL(SUM(amount), 0) AS total_xp
        FROM 
            user_xp
        WHERE 
            user_id = ?
    ");
    $stmt->execute([$target_user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_xp = $result['total_xp'];
    
    // Calculate level based on XP
    $level = floor(sqrt($total_xp / 100)) + 1; // Simple level formula
    $current_level_xp = pow($level - 1, 2) * 100;
    $next_level_xp = pow($level, 2) * 100;
    $xp_in_level = $total_xp - $current_level_xp;
    $xp_needed_for_level = $next_level_xp - $current_level_xp;
    $level_progress = ($xp_in_level / $xp_needed_for_level) * 100;
    
    // Get XP history
    $stmt = $db->prepare("
        SELECT 
            amount,
            source,
            source_id,
            earned_at
        FROM 
            user_xp
        WHERE 
            user_id = ?
        ORDER BY 
            earned_at DESC
        LIMIT 10
    ");
    $stmt->execute([$target_user_id]);
    $xp_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get XP breakdown by source
    $stmt = $db->prepare("
        SELECT 
            source,
            SUM(amount) AS total
        FROM 
            user_xp
        WHERE 
            user_id = ?
        GROUP BY 
            source
        ORDER BY 
            total DESC
    ");
    $stmt->execute([$target_user_id]);
    $xp_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build response
    $xp_data = [
        'total_xp' => $total_xp,
        'level' => $level,
        'level_progress' => round($level_progress, 1),
        'xp_in_level' => $xp_in_level,
        'xp_needed_for_level' => $xp_needed_for_level,
        'xp_history' => $xp_history,
        'xp_breakdown' => $xp_breakdown
    ];
    
    echo json_encode(['success' => true, 'data' => $xp_data]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}