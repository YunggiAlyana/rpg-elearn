<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireLogin();
require_once(__DIR__ . '/../../includes/db.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$quest_id = isset($_POST['quest_id']) ? intval($_POST['quest_id']) : 0;
$answer = isset($_POST['answer']) ? $_POST['answer'] : '';
$file_answer = isset($_FILES['file_answer']) ? $_FILES['file_answer'] : null;

try {
    // Check if quest exists
    $stmt = $db->prepare("SELECT * FROM quests WHERE id = ?");
    $stmt->execute([$quest_id]);
    $quest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quest) {
        echo json_encode(['success' => false, 'message' => 'Quest tidak ditemukan.']);
        exit;
    }
    
    // Check if quest was already submitted
    $stmt = $db->prepare("SELECT * FROM quest_submissions WHERE user_id = ? AND quest_id = ?");
    $stmt->execute([$user_id, $quest_id]);
    $existing_submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_submission) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah mengirimkan jawaban untuk quest ini.']);
        exit;
    }
    
    // Handle file upload if exists
    $file_path = null;
    if ($file_answer && $file_answer['error'] == 0) {
        $target_dir = "../../uploads/quest_answers/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($file_answer['name']);
        $target_file = $target_dir . $file_name;
        $file_path = 'uploads/quest_answers/' . $file_name;
        
        if (!move_uploaded_file($file_answer['tmp_name'], $target_file)) {
            echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file jawaban.']);
            exit;
        }
    }
    
    // Save the submission
    $stmt = $db->prepare("
        INSERT INTO quest_submissions (user_id, quest_id, answer_text, answer_file, submitted_at, status) 
        VALUES (?, ?, ?, ?, NOW(), 'pending')
    ");
    
    if ($stmt->execute([$user_id, $quest_id, $answer, $file_path])) {
        // Award XP for submission
        $xp_amount = $quest['xp_reward'] ?? 10; // Default 10 XP if not set
        
        $stmt = $db->prepare("
            INSERT INTO user_xp (user_id, amount, source, source_id, earned_at) 
            VALUES (?, ?, 'quest_submission', ?, NOW())
        ");
        $stmt->execute([$user_id, $xp_amount, $quest_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Quest berhasil diserahkan! Anda mendapatkan ' . $xp_amount . ' XP.',
            'xp_earned' => $xp_amount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan jawaban quest.']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}