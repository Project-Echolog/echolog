<?php
include "dbconnection.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user data
$user_id = $_SESSION['user_id'];

if (!isset($_GET['game_id'])) {
    die("Error: Game ID not provided.");
}

$game_id = intval($_GET['game_id']);

$playlists = [];
$message = "";

// Fetch user's playlists
try {
    $stmt = $conn->prepare("SELECT playlist_name, playlist_id FROM Playlist WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($playlist = $result->fetch_assoc()) {
        $playlists[] = [
            'id' => intval($playlist['playlist_id']),
            'name' => htmlspecialchars($playlist['playlist_name'])
        ];
    }

    if (empty($playlists)) {
        $message = "You don't have any playlists. Create one first!";
    }
} catch (Exception $e) {
    $message = "Error fetching playlists: " . $e->getMessage();
}

// Handle adding game to the selected playlist
if (isset($_GET['playlist_id']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $playlist_id = intval($_GET['playlist_id']);

    try {
        // Check if the game is already in the playlist
        $stmt = $conn->prepare("SELECT * FROM Playlist_Games WHERE playlist_id = ? AND game_id = ?");
        $stmt->bind_param("ii", $playlist_id, $game_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This game is already in the selected playlist.";
        } else {
            // Add the game to the playlist
            $stmt = $conn->prepare("INSERT INTO Playlist_Games (playlist_id, game_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $playlist_id, $game_id);
            $stmt->execute();

            $message = "The game has been added to the playlist!";
        }
    } catch (Exception $e) {
        $message = "Error adding game to playlist: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create new playlist | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css">
    <style>
        .playlist-section {
            margin: 2rem auto;
            max-width: 600px;
            padding: 1.5rem;
            background-color: #2a3b4d;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        .playlist-section__new {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background-color: var(--color-review);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .playlist-section__new:hover {
            background-color: var(--color-review-lighter);
        }

        .playlist-section__title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            text-align: center;
            color: #ffffff;
        }

        .playlist-section__message {
            font-size: 1.4rem;
            color: #d9534f;
            text-align: center;
        }

        .playlist-section__list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .playlist-section__item {
            margin-bottom: 1rem;
        }

        .playlist-section__link {
            display: block;
            padding: 0.8rem 1rem;
            background-color: var(--color-review);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .playlist-section__link:hover {
            background-color: var(--color-review-lighter);
        }
    </style>
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/echolog/">
            <img src="/echolog/assets/svgs/logo.svg" alt="My Website Logo" />
        </a>
        <nav class="header__nav">
            <a class='header__nav-link' href='/echolog/BrowseGames.php'>Games</a>
            <a class='header__nav-link' href='/echolog/BrowsePlaylist.php'>Collections</a>
            <a class='header__nav-link' href='/echolog/profile.php'>Profile</a>
        </nav>
        <a class="header__login" href="/echolog/login.php">Login</a>
    </header>
    <main>
        <section class="playlist-section">
            <p>
                <a href="playlist_new.php" class="playlist-section__new">+ Create New Playlist</a>
            </p>
            <h2 class="playlist-section__title">Select a Playlist</h2>

            <?php if (!empty($message)): ?>
                <p class="playlist-section__message"><?php echo $message; ?></p>
            <?php endif; ?>

            <?php if (!empty($playlists)): ?>
                <ul class="playlist-section__list">
                    <?php foreach ($playlists as $playlist): ?>
                        <li class="playlist-section__item">
                            <a href="?game_id=<?php echo $game_id; ?>&playlist_id=<?php echo $playlist['id']; ?>"
                                class="playlist-section__link">
                                <?php echo $playlist['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>