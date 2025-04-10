<?php
// File: public/dosen/buat_quest.php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('dosen');
// require_once(__DIR__ . '/../../includes/db.php'); // Actions handled by API
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Quest Baru - Dosen</title>
    <style>
        #quest-form label, #quest-form input, #quest-form textarea, #quest-form select {
            display: block;
            margin-bottom: 10px;
            width: 90%; /* Adjust as needed */
            max-width: 500px;
        }
        #quest-form button { padding: 10px 15px; cursor: pointer; }
        #form-message { margin-top: 15px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Buat Quest Baru</h1>

    <form id="quest-form" onsubmit="submitNewQuest(event)">
        <div>
            <label for="title">Judul Quest:</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div>
            <label for="description">Deskripsi:</label>
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>

        <div>
            <label for="xp_reward">XP Reward:</label>
            <input type="number" id="xp_reward" name="xp_reward" min="0" step="1" value="10" required>
        </div>

        <button type="submit">Buat Quest</button>
    </form>

    <div id="form-message" style="display: none;"></div>

    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    <p><a href="lihat_submission.php">Lihat Submission Siswa</a></p>


    <script>
        const form = document.getElementById('quest-form');
        const messageDiv = document.getElementById('form-message');
        const apiBaseUrl = '<?= BASE_URL ?>/api';

        async function submitNewQuest(event) {
            event.preventDefault(); // Prevent default form submission
            messageDiv.style.display = 'none'; // Hide previous messages
            messageDiv.className = ''; // Clear classes

            const formData = new FormData(form);
            // Convert FormData to JSON object if your API expects JSON
            // let jsonObject = {};
            // formData.forEach((value, key) => {jsonObject[key] = value});

            try {
                messageDiv.innerText = 'Membuat quest...';
                messageDiv.style.display = 'block';

                // --- API Call Assumption ---
                // Assumes an API endpoint /api/quest/create_quest.php exists
                // that accepts POST requests (either FormData or JSON)
                // with fields: title, description, xp_reward, (optional: type, etc.)
                // and returns { success: true/false, message: '...' }
                const response = await fetch(`${apiBaseUrl}/quest/create_quest.php`, { // Adjust endpoint if needed
                    method: 'POST',
                     body: formData // Send as FormData
                    // OR:
                    // headers: { 'Content-Type': 'application/json' },
                    // body: JSON.stringify(jsonObject) // Send as JSON
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.innerText = `Sukses: ${result.message || 'Quest berhasil dibuat!'}`;
                    messageDiv.className = 'success';
                    form.reset(); // Clear the form
                } else {
                    messageDiv.innerText = `Gagal: ${result.message || 'Tidak dapat membuat quest.'}`;
                    messageDiv.className = 'error';
                }
            } catch (error) {
                console.error('Error creating quest:', error);
                messageDiv.innerText = 'Terjadi kesalahan saat menghubungi server.';
                messageDiv.className = 'error';
            } finally {
                 messageDiv.style.display = 'block';
            }
        }
    </script>
</body>
</html>