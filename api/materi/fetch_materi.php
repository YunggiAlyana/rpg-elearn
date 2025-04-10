<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$materi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($materi_id > 0) {
    // Fetch specific material
    $stmt = $db->prepare("
        SELECT m.*, u.username as dosen_name 
        FROM materi m 
        JOIN users u ON m.created_by = u.id 
        WHERE m.id = ?
    ");
    $stmt->execute([$materi_id]);
    $materi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($materi) {
        echo json_encode(['success' => true, 'data' => $materi]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Materi tidak ditemukan']);
    }
} else {
    // Fetch all materials
    $stmt = $db->prepare("
        SELECT m.*, u.username as dosen_name 
        FROM materi m 
        JOIN users u ON m.created_by = u.id 
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
    $materi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $materi_list]);
}