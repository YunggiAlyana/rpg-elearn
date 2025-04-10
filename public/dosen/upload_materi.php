<?php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('dosen');
require_once(__DIR__ . '/../../includes/db.php');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $user_id = $_SESSION['user']['id'];
    
    // Handle file upload
    $target_dir = "../../uploads/materi/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($_FILES["file_materi"]["name"]);
    $target_file = $target_dir . time() . '_' . $file_name;
    $file_path = 'uploads/materi/' . time() . '_' . $file_name;
    
    if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
        // Insert into database
        $stmt = $db->prepare("INSERT INTO materi (judul, deskripsi, file_path, created_by) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$judul, $deskripsi, $file_path, $user_id])) {
            $message = "Materi berhasil diunggah!";
        } else {
            $error = "Gagal menyimpan data materi.";
        }
    } else {
        $error = "Gagal mengunggah file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Materi - Dosen</title>
</head>
<body>
    <h1>Upload Materi Pembelajaran</h1>
    
    <?php if ($message): ?><p style="color:green;"><?= $message ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
        <div>
            <label for="judul">Judul Materi:</label><br>
            <input type="text" id="judul" name="judul" required>
        </div>
        
        <div>
            <label for="deskripsi">Deskripsi:</label><br>
            <textarea id="deskripsi" name="deskripsi" rows="4" cols="50"></textarea>
        </div>
        
        <div>
            <label for="file_materi">File Materi (PDF, PPT, dll):</label><br>
            <input type="file" id="file_materi" name="file_materi" required>
        </div>
        
        <div>
            <button type="submit">Upload Materi</button>
        </div>
    </form>
    
    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
</body>
</html>