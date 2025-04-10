<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];

// Get the student's progress for a specific or all courses
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

try {
    if ($course_id > 0) {
        // Fetch progress for a specific course
        $stmt = $db->prepare("
            SELECT 
                m.id AS materi_id,
                m.judul AS materi_judul,
                CASE WHEN mp.id IS NOT NULL THEN 1 ELSE 0 END AS completed,
                mp.completed_at
            FROM 
                materi m
            LEFT JOIN 
                materi_progress mp ON m.id = mp.materi_id AND mp.user_id = ?
            WHERE 
                m.course_id = ?
            ORDER BY 
                m.created_at ASC
        ");
        $stmt->execute([$user_id, $course_id]);
    } else {
        // Fetch overall progress across all courses
        $stmt = $db->prepare("
            SELECT 
                COUNT(DISTINCT m.id) AS total_materi,
                COUNT(DISTINCT mp.materi_id) AS completed_materi,
                ROUND((COUNT(DISTINCT mp.materi_id) / COUNT(DISTINCT m.id)) * 100, 1) AS completion_percentage
            FROM 
                materi m
            LEFT JOIN 
                materi_progress mp ON m.id = mp.materi_id AND mp.user_id = ?
        ");
        $stmt->execute([$user_id]);
    }
    
    $progress = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $progress]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil data progres: ' . $e->getMessage()]);
}