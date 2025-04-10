<?php
// File: public/siswa/badge.php
require_once(__DIR__ . '/../../includes/middleware.php');
require_once(__DIR__ . '/../../includes/config.php');
requireRole('siswa');
// require_once(__DIR__ . '/../../includes/db.php'); // Data likely comes from profile API
?>

<!DOCTYPE html>
<html>
<head>
    <title>Badges & Achievements - Siswa</title>
    <style>
        .badge-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .badge-item { border: 1px solid #ccc; padding: 15px; border-radius: 8px; text-align: center; width: 150px; background-color: #f9f9f9; }
        .badge-item.locked { filter: grayscale(1); opacity: 0.6; }
        .badge-icon { width: 60px; height: 60px; margin-bottom: 10px; object-fit: contain; }
        .badge-name { font-weight: bold; }
        .badge-desc { font-size: 0.9em; color: #555; margin-top: 5px; }
        .badge-earned { font-size: 0.8em; color: green; margin-top: 8px; }
    </style>
</head>
<body>
    <h1>Badges & Achievements</h1>

    <h2>Badges yang Telah Didapatkan</h2>
    <div id="earned-badges" class="badge-grid">
        <p>Memuat badges...</p>
    </div>

    <h2>Badges yang Belum Didapatkan</h2>
    <div id="locked-badges" class="badge-grid">
        <p>Memuat badges...</p>
    </div>

    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>

    <script>
        const earnedBadgesDiv = document.getElementById('earned-badges');
        const lockedBadgesDiv = document.getElementById('locked-badges');
        const apiBaseUrl = '<?= BASE_URL ?>/api';
        const baseContentUrl = '<?= BASE_URL ?>/'; // For image paths

        async function fetchBadges() {
            try {
                // --- API Call Assumption ---
                // Option 1: Use existing profile API if it includes all badges (earned and maybe locked)
                 const profileResponse = await fetch(`${apiBaseUrl}/user/get_profile.php`); // Fetches current user profile
                 const profileResult = await profileResponse.json();

                 // --- OR ---

                 // Option 2: Assume dedicated badge APIs
                 // const earnedResponse = await fetch(`${apiBaseUrl}/badge/get_user_badges.php`); // Needs user ID implicitly
                 // const allBadgesResponse = await fetch(`${apiBaseUrl}/badge/get_all_badges.php`);
                 // const earnedResult = await earnedResponse.json();
                 // const allBadgesResult = await allBadgesResponse.json();

                earnedBadgesDiv.innerHTML = ''; // Clear loading
                lockedBadgesDiv.innerHTML = '';

                // --- Logic based on Option 1 (Profile API) ---
                if (profileResult.success && profileResult.data && profileResult.data.badges) {
                     if (profileResult.data.badges.length > 0) {
                         profileResult.data.badges.forEach(badge => {
                            earnedBadgesDiv.appendChild(createBadgeElement(badge, true));
                         });
                     } else {
                         earnedBadgesDiv.innerHTML = '<p>Anda belum mendapatkan badge.</p>';
                     }
                     // To show locked badges, you'd need the profile API (or another API)
                     // to also return a list of *all* available badges.
                     // Then you'd compare the 'all badges' list with the 'earned badges' list.
                     // This example assumes profile API only returns earned badges.
                     // For a complete implementation, a dedicated 'get_all_badges' API might be better.
                     lockedBadgesDiv.innerHTML = '<p>Fitur menampilkan badge terkunci belum diimplementasikan (membutuhkan data semua badge).</p>';

                }
                // --- Add Logic for Option 2 (Dedicated APIs) if chosen ---
                // else if (earnedResult.success && allBadgesResult.success) { ... compare lists ... }

                else {
                    const errorMsg = profileResult.message || 'Gagal memuat data badges.';
                    earnedBadgesDiv.innerHTML = `<p>${errorMsg}</p>`;
                    lockedBadgesDiv.innerHTML = `<p>${errorMsg}</p>`;
                }

            } catch (error) {
                console.error('Error fetching badges:', error);
                earnedBadgesDiv.innerHTML = '<p>Terjadi kesalahan saat menghubungi server.</p>';
                lockedBadgesDiv.innerHTML = '<p>Terjadi kesalahan saat menghubungi server.</p>';
            }
        }

        function createBadgeElement(badge, earned = false) {
            const badgeDiv = document.createElement('div');
            badgeDiv.className = 'badge-item' + (earned ? '' : ' locked');

            // Ensure icon path is relative to base URL if stored like 'uploads/badges/icon.png'
            const iconUrl = (badge.icon_path && !badge.icon_path.startsWith('http'))
                           ? baseContentUrl + badge.icon_path
                           : (badge.icon_path || 'path/to/default/badge_icon.png'); // Default icon

            badgeDiv.innerHTML = `
                <img src="${escapeHTML(iconUrl)}" alt="${escapeHTML(badge.name)}" class="badge-icon">
                <div class="badge-name">${escapeHTML(badge.name)}</div>
                <div class="badge-desc">${escapeHTML(badge.description || '')}</div>
                ${earned && badge.earned_at ? `<div class="badge-earned">Didapatkan: ${formatDate(badge.earned_at)}</div>` : ''}
            `;
            return badgeDiv;
        }

        function formatDate(dateString) {
             if (!dateString) return '';
             try {
                 const date = new Date(dateString);
                 return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
             } catch (e) {
                 return dateString; // Return original if parsing fails
             }
         }

         function escapeHTML(str) {
            var p = document.createElement("p");
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
         }

        // Initial load
        fetchBadges();
    </script>
</body>
</html>