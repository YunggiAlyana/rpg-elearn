<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('siswa');
require_once(__DIR__ . '/../../includes/db.php');

// Fetch all available learning materials
$stmt = $db->prepare("
    SELECT m.*, u.username as dosen_name 
    FROM materi m 
    JOIN users u ON m.created_by = u.id 
    ORDER BY m.created_at DESC
");
$stmt->execute();
$materi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Materi Pembelajaran - Siswa</title>
    <style>
        .materi-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .materi-title {
            font-weight: bold;
            font-size: 18px;
        }
        .materi-meta {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Materi Pembelajaran</h1>
    
    <?php if (empty($materi_list)): ?>
        <p>Belum ada materi pembelajaran yang tersedia.</p>
    <?php else: ?>
        <?php foreach ($materi_list as $materi): ?>
            <div class="materi-item">
                <div class="materi-title"><?= htmlspecialchars($materi['judul']) ?></div>
                <div class="materi-meta">
                    Diunggah oleh: <?= htmlspecialchars($materi['dosen_name']) ?> | 
                    Tanggal: <?= date('d M Y', strtotime($materi['created_at'])) ?>
                </div>
                <p><?= nl2br(htmlspecialchars($materi['deskripsi'])) ?></p>
                <a href="<?= BASE_URL ?>/<?= $materi['file_path'] ?>" target="_blank">Download Materi</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
</body>
</html>