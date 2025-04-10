<?php
// File: public/admin/manage_user.php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola User - Admin</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions button, .actions select { margin-right: 5px; padding: 3px 6px; cursor: pointer; }
        #edit-form { margin-top: 20px; padding: 15px; border: 1px dashed #ccc; display: none; }
         #edit-form label, #edit-form input, #edit-form select { display: block; margin-bottom: 10px;}
    </style>
</head>
<body>
    <h1>Kelola User</h1>

    <p><a href="register_user.php">Tambah User Baru</a></p>

    <div id="user-list">
        <p>Memuat daftar user...</p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                </tbody>
        </table>
    </div>

    <div id="edit-form">
        <h2>Edit User: <span id="edit-username"></span></h2>
        <input type="hidden" id="edit-user-id">
        <label for="edit-role">Role:</label>
        <select id="edit-role" name="role">
            <option value="siswa">Siswa</option>
            <option value="dosen">Dosen</option>
            <option value="admin">Admin</option>
        </select>
         <label for="edit-password">Password Baru (kosongkan jika tidak ingin diubah):</label>
         <input type="password" id="edit-password" name="password">
        <button type="button" onclick="submitUserUpdate()">Simpan Perubahan</button>
        <button type="button" onclick="hideEditForm()">Batal</button>
        <p id="edit-message" style="margin-top: 10px;"></p>
    </div>

    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>

    <script>
        const userTableBody = document.getElementById('user-table-body');
        const userListDiv = document.getElementById('user-list').querySelector('p'); // Loading message
        const editFormDiv = document.getElementById('edit-form');
        const editUserIdInput = document.getElementById('edit-user-id');
        const editUsernameSpan = document.getElementById('edit-username');
        const editRoleSelect = document.getElementById('edit-role');
        const editPasswordInput = document.getElementById('edit-password');
        const editMessageP = document.getElementById('edit-message');

        const apiBaseUrl = '<?= BASE_URL ?>/api';

        async function fetchUsers() {
            userListDiv.style.display = 'block'; // Show loading message
            userTableBody.innerHTML = ''; // Clear table
            hideEditForm(); // Hide edit form if open

            try {
                // --- API Call Assumption ---
                // Assumes an API endpoint /api/user/manage_users.php (or similar) exists
                // Accepts GET request (maybe with filters later)
                // Returns { success: true, data: [ {id: 1, username: 'admin', role: 'admin', created_at: '...'}, ... ] }
                const response = await fetch(`${apiBaseUrl}/user/manage_users.php`); // Adjust endpoint if needed
                const result = await response.json();

                userListDiv.style.display = 'none'; // Hide loading message

                if (result.success && result.data) {
                    if (result.data.length > 0) {
                         result.data.forEach(user => {
                            const row = userTableBody.insertRow();
                            row.innerHTML = `
                                <td>${user.id}</td>
                                <td>${escapeHTML(user.username)}</td>
                                <td>${escapeHTML(user.role)}</td>
                                <td>${formatDate(user.created_at)}</td>
                                <td class="actions">
                                    <button onclick="showEditForm(${user.id}, '${escapeHTML(user.username)}', '${escapeHTML(user.role)}')">Edit</button>
                                    <button onclick="deleteUser(${user.id}, '${escapeHTML(user.username)}')">Hapus</button>
                                    </td>
                            `;
                        });
                    } else {
                         userTableBody.innerHTML = '<tr><td colspan="5">Tidak ada user ditemukan.</td></tr>';
                    }
                } else {
                     userTableBody.innerHTML = `<tr><td colspan="5">Gagal memuat user: ${result.message || 'Error'}</td></tr>`;
                }

            } catch (error) {
                console.error('Error fetching users:', error);
                 userListDiv.style.display = 'none';
                userTableBody.innerHTML = '<tr><td colspan="5">Terjadi kesalahan saat menghubungi server.</td></tr>';
            }
        }

        function showEditForm(userId, username, currentRole) {
            editUserIdInput.value = userId;
            editUsernameSpan.innerText = username;
            editRoleSelect.value = currentRole;
             editPasswordInput.value = ''; // Clear password field
             editMessageP.textContent = ''; // Clear message
            editFormDiv.style.display = 'block';
        }

        function hideEditForm() {
            editFormDiv.style.display = 'none';
        }

        async function submitUserUpdate() {
             const userId = editUserIdInput.value;
             const newRole = editRoleSelect.value;
             const newPassword = editPasswordInput.value;
             editMessageP.textContent = 'Menyimpan...';

             let updateData = {
                 user_id: userId,
                 role: newRole
             };

             if (newPassword && newPassword.trim() !== '') {
                 updateData.password = newPassword;
             }

             try {
                 // --- API Call Assumption ---
                 // Assumes an API endpoint /api/user/manage_users.php (or similar)
                 // Accepts POST/PUT request with user_id, role, (optional) password
                 // Returns { success: true/false, message: '...' }
                 const response = await fetch(`${apiBaseUrl}/user/manage_users.php`, { // Adjust endpoint if needed
                     method: 'POST', // Or PUT
                     headers: { 'Content-Type': 'application/json' },
                     body: JSON.stringify(updateData)
                 });
                 const result = await response.json();

                 if (result.success) {
                     editMessageP.textContent = 'Update berhasil!';
                     editMessageP.style.color = 'green';
                     fetchUsers(); // Refresh list
                     setTimeout(hideEditForm, 1500); // Hide form after success
                 } else {
                     editMessageP.textContent = `Gagal: ${result.message || 'Error'}`;
                      editMessageP.style.color = 'red';
                 }

             } catch (error) {
                 console.error('Error updating user:', error);
                 editMessageP.textContent = 'Error server.';
                  editMessageP.style.color = 'red';
             }
        }

        async function deleteUser(userId, username) {
            if (!confirm(`Apakah Anda yakin ingin menghapus user "${username}" (ID: ${userId})? Tindakan ini tidak bisa dibatalkan.`)) {
                return;
            }

             // Optionally disable buttons while deleting
             // ...

            try {
                 // --- API Call Assumption ---
                 // Assumes an API endpoint /api/user/manage_users.php (or similar)
                 // Accepts DELETE request (or POST with an 'action=delete' parameter)
                 // Needs user_id to delete
                 // Returns { success: true/false, message: '...' }
                 const response = await fetch(`${apiBaseUrl}/user/manage_users.php`, { // Adjust endpoint if needed
                     method: 'DELETE', // Or POST
                     headers: { 'Content-Type': 'application/json' },
                     body: JSON.stringify({ user_id: userId })
                     // Or for POST: body: JSON.stringify({ action: 'delete', user_id: userId })
                 });
                 const result = await response.json();

                 if (result.success) {
                     alert(`User "${username}" berhasil dihapus.`);
                     fetchUsers(); // Refresh list
                 } else {
                     alert(`Gagal menghapus user: ${result.message || 'Error'}`);
                 }

            } catch (error) {
                 console.error('Error deleting user:', error);
                 alert('Terjadi kesalahan saat menghubungi server.');
            } finally {
                 // Re-enable buttons if disabled
                 // ...
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
        fetchUsers();

    </script>

</body>
</html>