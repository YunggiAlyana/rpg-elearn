<?php
// File: public/siswa/quest.php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('siswa');
// require_once(__DIR__ . '/../../includes/db.php'); // Likely not needed directly, data comes from API
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Quest - Siswa</title>
    <style>
        /* Basic styling */
        .quest-item { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
        .quest-title { font-weight: bold; font-size: 1.1em; }
        .quest-desc { margin-top: 5px; color: #555; }
        .quest-reward { margin-top: 5px; font-style: italic; color: green; }
        .quest-status { margin-top: 5px; font-weight: bold; }
        .quest-actions button { margin-top: 10px; padding: 5px 10px; cursor: pointer; }
        #submission-form { display: none; margin-top: 15px; padding: 15px; border: 1px dashed #aaa; }
        #submission-form label, #submission-form textarea, #submission-form input { display: block; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Daftar Quest</h1>

    <div id="quest-list">
        <p>Memuat quest...</p>
    </div>

    <div id="submission-form">
        <h3>Submit Jawaban Quest: <span id="submission-quest-title"></span></h3>
        <input type="hidden" id="submission-quest-id">
        <label for="answer_text">Jawaban Teks:</label>
        <textarea id="answer_text" name="answer_text" rows="4" cols="50"></textarea>

        <label for="file_answer">Upload File (jika perlu):</label>
        <input type="file" id="file_answer" name="file_answer">

        <button type="button" onclick="submitQuestAnswer()">Submit Jawaban</button>
        <button type="button" onclick="hideSubmissionForm()">Batal</button>
        <p id="submission-message" style="margin-top: 10px;"></p>
    </div>

    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>

    <script>
        const questListDiv = document.getElementById('quest-list');
        const submissionForm = document.getElementById('submission-form');
        const submissionQuestIdInput = document.getElementById('submission-quest-id');
        const submissionQuestTitleSpan = document.getElementById('submission-quest-title');
        const submissionMessage = document.getElementById('submission-message');
        const apiBaseUrl = '<?= BASE_URL ?>/api'; // Adjust if your API path is different

        async function fetchQuests() {
            try {
                // --- API Call Assumption ---
                // Assumes an API endpoint exists at /api/quest/get_quests.php (or similar)
                // that returns a JSON like: { success: true, data: [ {id: 1, title: 'Quest 1', description: '...', xp_reward: 50, status: 'available' | 'submitted' | 'completed'}, ... ] }
                // The 'status' indicates the current user's status for that quest.
                const response = await fetch(`${apiBaseUrl}/quest/get_quests.php`); // Adjust endpoint if needed
                const result = await response.json();

                questListDiv.innerHTML = ''; // Clear loading message

                if (result.success && result.data.length > 0) {
                    result.data.forEach(quest => {
                        const questDiv = document.createElement('div');
                        questDiv.className = 'quest-item';
                        questDiv.innerHTML = `
                            <div class="quest-title">${escapeHTML(quest.title)}</div>
                            <div class="quest-desc">${escapeHTML(quest.description)}</div>
                            <div class="quest-reward">Reward: ${quest.xp_reward || 0} XP</div>
                            <div class="quest-status">Status: ${getQuestStatusText(quest.status)}</div>
                            <div class="quest-actions">
                                ${quest.status === 'available' ? `<button onclick="showSubmissionForm(${quest.id}, '${escapeHTML(quest.title)}')">Kerjakan</button>` : ''}
                                ${quest.status === 'submitted' ? `<p><i>Menunggu penilaian...</i></p>` : ''}
                                ${quest.status === 'completed' ? `<p><i>Selesai</i></p>` : ''}
                                </div>
                        `;
                        questListDiv.appendChild(questDiv);
                    });
                } else if (result.success && result.data.length === 0) {
                    questListDiv.innerHTML = '<p>Belum ada quest yang tersedia saat ini.</p>';
                } else {
                    questListDiv.innerHTML = `<p>Gagal memuat quest: ${result.message || 'Error tidak diketahui'}</p>`;
                }
            } catch (error) {
                console.error('Error fetching quests:', error);
                questListDiv.innerHTML = '<p>Terjadi kesalahan saat menghubungi server.</p>';
            }
        }

        function showSubmissionForm(questId, questTitle) {
            submissionQuestIdInput.value = questId;
            submissionQuestTitleSpan.innerText = questTitle;
            submissionForm.style.display = 'block';
            submissionMessage.innerText = ''; // Clear previous messages
            document.getElementById('answer_text').value = ''; // Clear fields
            document.getElementById('file_answer').value = null;
        }

        function hideSubmissionForm() {
            submissionForm.style.display = 'none';
        }

        async function submitQuestAnswer() {
            const questId = submissionQuestIdInput.value;
            const answerText = document.getElementById('answer_text').value;
            const fileInput = document.getElementById('file_answer');
            const file = fileInput.files[0];

            const formData = new FormData();
            formData.append('quest_id', questId);
            formData.append('answer', answerText);
            if (file) {
                formData.append('file_answer', file);
            }

            submissionMessage.innerText = 'Mengirim jawaban...';

            try {
                // --- API Call Assumption ---
                // Uses the existing /api/quest/submit_quest.php endpoint
                const response = await fetch(`${apiBaseUrl}/quest/submit_quest.php`, {
                    method: 'POST',
                    body: formData // FormData handles multipart/form-data automatically
                });
                const result = await response.json();

                if (result.success) {
                    submissionMessage.innerText = `Sukses: ${result.message || 'Jawaban berhasil dikirim.'}`;
                    hideSubmissionForm();
                    fetchQuests(); // Refresh the quest list
                } else {
                    submissionMessage.innerText = `Gagal: ${result.message || 'Error tidak diketahui.'}`;
                }
            } catch (error) {
                console.error('Error submitting quest:', error);
                submissionMessage.innerText = 'Terjadi kesalahan saat menghubungi server.';
            }
        }


        function getQuestStatusText(status) {
            switch (status) {
                case 'available': return 'Tersedia';
                case 'submitted': return 'Terkirim (Menunggu Penilaian)';
                case 'completed': return 'Selesai';
                case 'graded': return 'Sudah Dinilai'; // Example if you add grading
                default: return 'Tidak Diketahui';
            }
        }

        function escapeHTML(str) {
            var p = document.createElement("p");
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
         }

        // Initial load
        fetchQuests();
    </script>
</body>
</html>