<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$target_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $user_id;

try {
    // Get basic user information
    $stmt = $db->prepare("
        SELECT 
            u.id, 
            u.username, 
            u.role,
            u.avatar,
            u.created_at,
            IFNULL(SUM(ux.amount), 0) AS total_xp
        FROM 
            users u
        LEFT JOIN 
            user_xp ux ON u.id = ux.user_id
        WHERE 
            u.id = ?
        GROUP BY 
            u.id
    ");
    $stmt->execute([$target_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
        exit;
    }
    
    // Get user badges
    $stmt = $db->prepare("
        SELECT 
            b.id,
            b.name,
            b.description,
            b.icon_path,
            ub.earned_at
        FROM 
            badges b
        JOIN 
            user_badges ub ON b.id = ub.badge_id
        WHERE 
            ub.user_id = ?
        ORDER BY 
            ub.earned_at DESC
    ");
    $stmt->execute([$target_user_id]);
    $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get user level based on XP
    $total_xp = $user['total_xp'];
    $level = floor(sqrt($total_xp / 100)) + 1; // Simple level formula
    $xp_for_next_level = pow($level, 2) * 100;
    $xp_needed = $xp_for_next_level - $total_xp;
    
    // Get recent activities
    $stmt = $db->prepare("
        (SELECT 
            'quest_submission' AS activity_type,
            q.title AS activity_name,
            qs.submitted_at AS activity_date
        FROM 
            quest_submissions qs
        JOIN 
            quests q ON qs.quest_id = q.id
        WHERE 
            qs.user_id = ?
        ORDER BY 
            qs.submitted_at DESC
        LIMIT 5)
        
        UNION
        
        (SELECT 
            'badge_earned' AS activity_type,
            b.name AS activity_name,
            ub.earned_at AS activity_date
        FROM 
            user_badges ub
        JOIN 
            badges b ON ub.badge_id = b.id
        WHERE 
            ub.user_id = ?
        ORDER BY 
            ub.earned_at DESC
        LIMIT 5)
        
        ORDER BY activity_date DESC
        LIMIT 10
    ");
    $stmt->execute([$target_user_id, $target_user_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build response
    $profile = [
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? 'default.png',
            'join_date' => $user['created_at'],
        ],
        'progress' => [
            'level' => $level,
            'total_xp' => $total_xp,
            'xp_for_next_level' => $xp_needed,
            'level_progress' => ($xp_for_next_level - $xp_needed) / $xp_for_next_level * 100
        ],
        'badges' => $badges,
        'recent_activities' => $activities
    ];
    
    echo json_encode(['success' => true, 'data' => $profile]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}