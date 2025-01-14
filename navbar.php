<?php
// Include database connection and session management
include "dbconnection.php";
session_start();

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!-- Navbar HTML -->
<div class="navbar">
    <a href="index.php" class="logo">GameZone</a>
    <div class="search-container">
        <input 
            type="text" 
            id="game-search" 
            placeholder="Search Games or Playlists" 
            oninput="searchGames()" 
        />
        <div id="game-dropdown" class="dropdown"></div>
    </div>
    <?php if (!$is_logged_in): ?>
        <a href="login.php" class="login-button">Login</a>
    <?php endif; ?>
</div>

<!-- Inline CSS -->
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
    }

    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #333;
        padding: 10px 20px;
        color: white;
    }

    .navbar .logo {
        text-decoration: none;
        color: white;
        font-size: 20px;
        font-weight: bold;
    }

    .navbar .login-button, .navbar .profile-button {
        color: white;
        text-decoration: none;
        background-color: #007BFF;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 16px;
    }

    .navbar .login-button:hover, .navbar .profile-button:hover {
        background-color: #0056b3;
    }

    .search-container {
        position: relative;
    }

    #game-search {
        padding: 5px 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 250px;
    }

    .dropdown {
        position: absolute;
        top: 40px;
        left: 0;
        background: white;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .dropdown .game-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        color: red;
    }

    .dropdown .game-item:hover {
        background-color: #f0f0f0;
    }
</style>

<!-- Inline JavaScript for Search -->
<script>
    async function searchGames() {
        const query = document.getElementById('game-search').value.trim();
        const dropdown = document.getElementById('game-dropdown');
        dropdown.innerHTML = ''; // Clear existing options

        if (!query) {
            dropdown.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`search_general.php?game=${encodeURIComponent(query)}`);
            const results = await response.json();

            if (results.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'game-item';
                noResults.textContent = 'No results found';
                dropdown.appendChild(noResults);
            } else {
                results.forEach(item => {
                    const option = document.createElement('div');
                    option.className = 'game-item';
                    option.textContent = `${item.title} (${item.type === 'game' ? 'Game' : 'Playlist'})`;
                    option.onclick = () => {
                        if (item.type === 'game') {
                            window.location.href = `gamepage.php?game_id=${item.id}`;
                        } else if (item.type === 'playlist') {
                            window.location.href = `playlist_view.php?playlist_id=${item.id}`;
                        }
                    };
                    dropdown.appendChild(option);
                });
            }

            dropdown.style.display = 'block';
        } catch (error) {
            console.error('Error fetching results:', error);
        }
    }
</script>
