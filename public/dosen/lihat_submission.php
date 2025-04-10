<?php
// File: public/dosen/lihat_submission.php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('dosen');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lihat Submission Quest - Dosen</title>
    <style>
        body { font-family: sans-serif; }
        .submission-list, .quest-filter { margin-bottom: 20px; }
        .submission-item { border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 5px; background-color: #fff; }
        .submission-item h3 { margin-top: 0; margin-bottom: 10px; }
        .submission-meta { font-size: 0.9em; color: #666; margin-bottom: 10px; }
        .submission-answer { margin-bottom: 10px; background-color: #f8f8f8; padding: 10px; border-radius: 3px; }
        .submission-actions label, .submission-actions input, .submission-actions button { margin-right: 10px; }
        .submission-actions button { padding: 5px 10px; cursor: pointer; }
        .status-pending { color: orange; }
        .status-graded { color: green; }
        .status-rejected { color: red; } /* Example */
        #loading, #no-submissions { text-align: center; padding: 20px; color: #888; }
        select { padding: 5px; }
    </style>
</head>
<body>
    <h1>Lihat Submission Quest Siswa</h1>

    <div class="quest-filter">
        <label for="quest-selector">Filter berdasarkan Quest:</label>
        <select id="quest-selector" onchange="fetchSubmissions()">
            <option value="all">Semua Quest</option>
            </select>
        <label for="status-selector" style="margin-left: 15px;">Filter Status:</label>
        <select id="status-selector" onchange="fetchSubmissions()">
            <option value="all">Semua Status</option>
            <option value="pending" selected>Pending</option>
            <option value="graded">Graded</option>
             <option value="rejected">Rejected</option> </select>
    </div>

    <div id="loading">Memuat submissions...</div>
    <div id="no-submissions" style="display: none;">Tidak ada submission yang cocok dengan filter.</div>
    <div id="submission-list" class="submission-list">
        </div>

    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>

    <script>
        const submissionListDiv = document.getElementById('submission-list');
        const questSelector = document.getElementById('quest-selector');
        const statusSelector = document.getElementById('status-selector');
        const loadingDiv = document.getElementById('loading');
        const noSubmissionsDiv = document.getElementById('no-submissions');
        const apiBaseUrl = '<?= BASE_URL ?>/api';
        const baseContentUrl = '<?= BASE_URL ?>/';

        async function loadQuestOptions() {
            try {
                // --- API Call Assumption ---
                // Assumes an API endpoint exists to get quests relevant for the current Dosen
                // e.g., /api/quest/get_dosen_quests.php or similar
                const response = await fetch(`${apiBaseUrl}/quest/get_dosen_quests.php`); // Adjust if needed
                const result = await response.json();

                if (result.success && result.data) {
                    result.data.forEach(quest => {
                        const option = document.createElement('option');
                        option.value = quest.id;
                        option.textContent = escapeHTML(quest.title);
                        questSelector.appendChild(option);
                    });
                } else {
                    console.error("Gagal memuat daftar quest:", result.message);
                }
            } catch (error) {
                console.error('Error loading quest options:', error);
            }
        }


        async function fetchSubmissions() {
            const selectedQuestId = questSelector.value;
            const selectedStatus = statusSelector.value;
            loadingDiv.style.display = 'block';
            noSubmissionsDiv.style.display = 'none';
            submissionListDiv.innerHTML = ''; // Clear previous list

            try {
                // --- API Call Assumption ---
                // Assumes an API endpoint /api/quest/get_submissions.php (or similar) exists
                // Accepts GET parameters like 'quest_id' and 'status'
                // Returns { success: true, data: [ {id: 1, quest_title: 'Q1', username: 'siswaA', user_id: 5, submitted_at: '...', answer_text: '...', answer_file: 'path/file.pdf', status: 'pending', grade: null, feedback: null }, ... ] }
                let apiUrl = `${apiBaseUrl}/quest/get_submissions.php?`;
                if (selectedQuestId !== 'all') {
                    apiUrl += `quest_id=${selectedQuestId}&`;
                }
                 if (selectedStatus !== 'all') {
                    apiUrl += `status=${selectedStatus}&`;
                }
                // Remove trailing '&' or '?'
                apiUrl = apiUrl.replace(/[&?]$/, '');

                const response = await fetch(apiUrl);
                const result = await response.json();

                loadingDiv.style.display = 'none';

                if (result.success && result.data.length > 0) {
                    result.data.forEach(sub => {
                        submissionListDiv.appendChild(createSubmissionElement(sub));
                    });
                } else if (result.success && result.data.length === 0) {
                     noSubmissionsDiv.style.display = 'block';
                } else {
                    submissionListDiv.innerHTML = `<p style="color: red;">Gagal memuat submissions: ${result.message || 'Error tidak diketahui'}</p>`;
                }

            } catch (error) {
                console.error('Error fetching submissions:', error);
                loadingDiv.style.display = 'none';
                submissionListDiv.innerHTML = '<p style="color: red;">Terjadi kesalahan saat menghubungi server.</p>';
            }
        }

        function createSubmissionElement(submission) {
            const div = document.createElement('div');
            div.className = 'submission-item';
            div.id = `submission-${submission.id}`;

            const fileLink = submission.answer_file
                ? `<a href="${baseContentUrl}${escapeHTML(submission.answer_file)}" target="_blank">Lihat File Jawaban</a>`
                : '<i>Tidak ada file</i>';

            div.innerHTML = `
                <h3>${escapeHTML(submission.quest_title || 'Quest Tidak Diketahui')}</h3>
                <div class="submission-meta">
                    Oleh: ${escapeHTML(submission.username || 'Siswa Tidak Diketahui')} (ID: ${submission.user_id}) <br>
                    Dikirim: ${formatDate(submission.submitted_at)} <br>
                    Status: <span class="status-${submission.status}">${escapeHTML(submission.status)}</span>
                    ${submission.status === 'graded' ? ` | Nilai: ${submission.grade ?? 'N/A'} | Feedback: ${escapeHTML(submission.feedback ?? 'N/A')}` : ''}
                </div>
                <div class="submission-answer">
                    <strong>Jawaban Teks:</strong><br>
                    <pre>${escapeHTML(submission.answer_text || 'Tidak ada jawaban teks.')}</pre>
                </div>
                <div><strong>File Jawaban:</strong> ${fileLink}</div>
                ${submission.status === 'pending' ? `
                <div class="submission-actions" style="margin-top: 15px;">
                    <label for="grade-${submission.id}">Nilai:</label>
                    <input type="number" id="grade-${submission.id}" min="0" max="100" step="1" style="width: 60px;">
                    <label for="feedback-${submission.id}">Feedback:</label>
                    <input type="text" id="feedback-${submission.id}" size="30">
                    <button onclick="gradeSubmission(${submission.id}, 'graded')">Setujui</button>
                    <button onclick="gradeSubmission(${submission.id}, 'rejected')">Tolak</button> <span id="action-message-${submission.id}" style="margin-left: 10px;"></span>
                </div>` : ''}
            `;
            return div;
        }

        async function gradeSubmission(submissionId, newStatus) {
            const gradeInput = document.getElementById(`grade-${submissionId}`);
            const feedbackInput = document.getElementById(`feedback-${submissionId}`);
            const messageSpan = document.getElementById(`action-message-${submissionId}`);

            const grade = gradeInput ? gradeInput.value : null;
            const feedback = feedbackInput ? feedbackInput.value : null;

            messageSpan.textContent = 'Memproses...';

            // Basic validation for grading
            if (newStatus === 'graded' && (grade === null || grade === '')) {
                 messageSpan.textContent = 'Nilai harus diisi untuk menyetujui.';
                 return;
             }


            try {
                 // --- API Call Assumption ---
                 // Assumes an API endpoint /api/quest/review_submission.php (or similar) exists
                 // Accepts POST request with submission_id, status, grade, feedback
                 // Returns { success: true/false, message: '...' }
                const response = await fetch(`${apiBaseUrl}/quest/review_submission.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        submission_id: submissionId,
                        status: newStatus,
                        grade: grade,
                        feedback: feedback
                    })
                });
                const result = await response.json();

                if (result.success) {
                    messageSpan.textContent = 'Berhasil!';
                    messageSpan.style.color = 'green';
                    // Refresh the list or update the specific item visually
                    fetchSubmissions();
                } else {
                    messageSpan.textContent = `Gagal: ${result.message || 'Error'}`;
                    messageSpan.style.color = 'red';
                }

            } catch (error) {
                console.error('Error grading submission:', error);
                 messageSpan.textContent = 'Error server.';
                 messageSpan.style.color = 'red';
            }
        }


        function formatDate(dateString) {
             if (!dateString) return 'N/A';
             try {
                 const date = new Date(dateString);
                 return date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
             } catch (e) {
                 return dateString;
             }
         }

        function escapeHTML(str) {
            if (str === null || typeof str === 'undefined') return '';
            var p = document.createElement("p");
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
         }

        // Initial Load
        loadQuestOptions().then(() => {
            fetchSubmissions(); // Fetch submissions after quests are loaded
        });

    </script>
</body>
</html>