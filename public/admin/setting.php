<?php
// File: public/admin/setting.php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Sistem - Admin</title>
     <style>
        .setting-section { margin-bottom: 20px; padding: 15px; border: 1px solid #eee; border-radius: 5px; }
        .setting-section h2 { margin-top: 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold;}
        input[type="text"], input[type="number"], select { width: 300px; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 3px; }
        button { padding: 10px 15px; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 3px;}
        button:hover { background-color: #0056b3; }
        #message { margin-top: 15px; padding: 10px; border-radius: 5px; display: none; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Pengaturan Sistem</h1>

    <div id="message"></div>

    <form id="settings-form" onsubmit="saveSettings(event)">
        <div class="setting-section">
            <h2>Pengaturan Umum</h2>
            <label for="app_name">Nama Aplikasi:</label>
            <input type="text" id="app_name" name="app_name">

            <label for="default_xp_quest">Default XP untuk Quest Baru:</label>
            <input type="number" id="default_xp_quest" name="default_xp_quest" min="0">

            </div>

         <div class="setting-section">
            <h2>Pengaturan Email (Contoh)</h2>
             <p><i>Fitur ini hanya contoh, perlu implementasi backend untuk pengiriman email.</i></p>
            <label for="smtp_host">SMTP Host:</label>
            <input type="text" id="smtp_host" name="smtp_host" placeholder="contoh: smtp.example.com">

             <label for="smtp_port">SMTP Port:</label>
            <input type="number" id="smtp_port" name="smtp_port" placeholder="contoh: 587">

             <label for="smtp_user">SMTP Username:</label>
            <input type="text" id="smtp_user" name="smtp_user">

             <label for="smtp_pass">SMTP Password:</label>
            <input type="password" id="smtp_pass" name="smtp_pass">
        </div>


        <button type="submit">Simpan Pengaturan</button>
    </form>


    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>

     <script>
         const form = document.getElementById('settings-form');
         const messageDiv = document.getElementById('message');
         const apiBaseUrl = '<?= BASE_URL ?>/api';

         async function loadSettings() {
             messageDiv.style.display = 'none';
             try {
                 // --- API Call Assumption ---
                 // Assumes an API /api/settings/get_settings.php exists
                 // Returns { success: true, data: { setting_key: 'value', ... } }
                 const response = await fetch(`${apiBaseUrl}/settings/get_settings.php`); // Adjust endpoint
                 const result = await response.json();

                 if (result.success && result.data) {
                     // Populate form fields
                     for (const key in result.data) {
                         const input = document.getElementById(key);
                         if (input) {
                             input.value = result.data[key];
                         }
                     }
                 } else {
                     showMessage(`Gagal memuat pengaturan: ${result.message || 'Error'}`, 'error');
                 }
             } catch (error) {
                 console.error("Error loading settings:", error);
                 showMessage('Terjadi kesalahan saat menghubungi server.', 'error');
             }
         }

         async function saveSettings(event) {
             event.preventDefault();
             messageDiv.style.display = 'none';
             showMessage('Menyimpan...', 'info'); // Use an 'info' class or similar

             const formData = new FormData(form);
             const settingsData = {};
             formData.forEach((value, key) => { settingsData[key] = value });

             try {
                 // --- API Call Assumption ---
                 // Assumes an API /api/settings/save_settings.php exists
                 // Accepts POST with JSON body containing settings data
                 // Returns { success: true/false, message: '...' }
                 const response = await fetch(`${apiBaseUrl}/settings/save_settings.php`, { // Adjust endpoint
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json' },
                     body: JSON.stringify(settingsData)
                 });
                 const result = await response.json();

                  if (result.success) {
                     showMessage('Pengaturan berhasil disimpan!', 'success');
                 } else {
                     showMessage(`Gagal menyimpan: ${result.message || 'Error'}`, 'error');
                 }

             } catch (error) {
                 console.error("Error saving settings:", error);
                 showMessage('Terjadi kesalahan saat menghubungi server.', 'error');
             }
         }

         function showMessage(msg, type = 'info') {
             messageDiv.textContent = msg;
             messageDiv.className = type; // 'success', 'error', 'info'
             messageDiv.style.display = 'block';
         }

         // Initial Load
         loadSettings();
    </script>
</body>
</html>