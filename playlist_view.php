<?php
include "dbconnection.php";

session_start();

if (!isset($_GET['playlist_id'])) {
    die("Error: Playlist ID not provided.");
}

$playlist_id = intval($_GET['playlist_id']);
$user_id = $_SESSION['user_id'] ?? null; // Assuming user is logged in, get user_id from session

$playlist = [];
$games = [];
$isEditable = false;

// Handle the like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    if (!$user_id) {
        die("Error: You need to be logged in to like or unlike a playlist.");
    }

    try {
        // Check if the user has already liked this playlist
        $stmt = $conn->prepare("
            SELECT * FROM Likes_Playlists 
            WHERE user_id = ? AND playlist_id = ?
        ");
        $stmt->bind_param("ii", $user_id, $playlist_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User has already liked, so remove the like
            $stmt = $conn->prepare("
                DELETE FROM Likes_Playlists 
                WHERE user_id = ? AND playlist_id = ?
            ");
            $stmt->bind_param("ii", $user_id, $playlist_id);
            $stmt->execute();

            // echo "<p>You have unliked this playlist.</p>";
        } else {
            // User has not liked, so add the like
            $stmt = $conn->prepare("
                INSERT INTO Likes_Playlists (user_id, playlist_id) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $user_id, $playlist_id);
            $stmt->execute();

            // echo "<p>You've liked this playlist!</p>";
        }

        // Redirect to avoid resubmitting the form on page refresh
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch the playlist and games
try {
    // Fetch the username of the playlist creator using playlist_id
    $stmt = $conn->prepare("
        SELECT u.user_nickname, u.profile_image, p.playlist_name, p.playlist_description, p.like_count, p.user_id AS creator_id
        FROM Playlist p
        JOIN Users u ON p.user_id = u.user_id 
        WHERE p.playlist_id = ?
    ");
    $stmt->bind_param("i", $playlist_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $playlist = $result->fetch_assoc();
        $usernickname = htmlspecialchars($playlist['user_nickname']);
        $profile_image = htmlspecialchars($playlist['profile_image']);
        $playlist_name = htmlspecialchars($playlist['playlist_name']);
        $playlist_desc = htmlspecialchars($playlist['playlist_description']);
        $like_count = (int)$playlist['like_count']; // Ensure it's an integer
        $creator_id = $playlist['creator_id']; // ID of the playlist creator
    } else {
        die("Error: Playlist not found.");
    }
    $stmt->close();

    // Check if the logged-in user is the creator of the playlist
    $is_creator = ($user_id == $creator_id);

    // Fetch the games associated with the playlist
    $stmt = $conn->prepare("
        SELECT g.game_id, g.game_title, g.cover_image
        FROM Playlist_Games pg 
        JOIN Games g ON pg.game_id = g.game_id 
        WHERE pg.playlist_id = ?
    ");
    $stmt->bind_param("i", $playlist_id);
    $stmt->execute();
    $games_result = $stmt->get_result();

    // set $playlist variable
    $playlist = [
        'playlist_name' => $playlist_name,
        'playlist_desc' => $playlist_desc,
        'like_count' => $like_count,
        'usernickname' => $usernickname,
        'profile_image' => $profile_image,
        'is_creator' => $is_creator,
        'games' => []
    ];

    // // Display playlist details and games
    // echo "<h1>$playlist_name</h1>";
    // echo "<h2>$playlist_desc</h2>";
    // echo "<p>Created by: " . $usernickname . "</p>";
    // echo "<p>Likes: <span id='like-count'>$like_count</span></p>";

    // Check if the user has already liked this playlist
    $liked = false;
    if ($user_id) {
        $stmt = $conn->prepare("
            SELECT * FROM Likes_Playlists 
            WHERE user_id = ? AND playlist_id = ?
        ");
        $stmt->bind_param("ii", $user_id, $playlist_id);
        $stmt->execute();
        $like_result = $stmt->get_result();
        if ($like_result->num_rows > 0) {
            $liked = true;
        }
    }

    // // Display the like button
    // if ($user_id) {
    //     echo "<form method='POST'>
    //             <button type='submit' name='like' id='like-button'>" . ($liked ? 'Unlike' : 'Like') . "</button>
    //           </form>";
    // } else {
    //     echo "<p>Please log in to like or unlike this playlist.</p>";
    // }

    // If the user is the creator, show the edit button
    if ($is_creator) {
        $isEditable = true;
        // echo "<a href='playlist_edit.php?playlist_id=$playlist_id'>
        //         <button>Edit Playlist</button>
        //       </a>";
    }

    // Display the games in the playlist
    if ($games_result->num_rows > 0) {
        while ($row = $games_result->fetch_assoc()) {
            $playlist['games'][] = $row;
        }
        // echo "<h4>Games in this playlist:</h4><ul>";
        // while ($row = $games_result->fetch_assoc()) {
        //     echo "<li>";
        //     echo "<img src='" . htmlspecialchars($row['cover_image']) . "' alt='Game Cover' style='width: 150px; height: 150px; object-fit: cover;'>";
        //     echo "</li>";
        // }
        // echo "</ul>";
    } else {
        // echo "<p>No games in this playlist yet.</p>";
    }
    $stmt->close();
} catch (Exception $e) {
    echo "<p>Error fetching playlist details: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlist Details | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css" />
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 40px;
        }

        h1.collection__name {
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .collection__edit-button {
            cursor: pointer;
        }

        img.collection__edit-button-icon {
            width: 2.5rem;
            height: 2.5rem;
        }

        h2.collection__description {
            font-size: 2.5rem;
            font-weight: 400;
            margin-bottom: 20px;
        }

        .collection__by {
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        img.collection__avatar {
            width: 5rem;
            height: 5rem;
            border-radius: 1000px;
            margin-right: 1.5rem;
        }

        .collection__username {
            font-size: 2rem;
            font-weight: 400;
        }

        button.collection__like {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            color: #fff;
            border-radius: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
            cursor: pointer;
            background-color: transparent;
            border: none;
        }

        img.collection__like-icon {
            width: 3rem;
            height: 3rem;
        }

        select.collection__sort {
            padding: 1rem;
            font-size: 1.8rem;
            font-weight: 700;
            border-radius: 0.5rem;
            cursor: pointer;
            background-color: transparent;
            color: #fff;
            border: none;
        }

        option.collection__sort-option {
            font-size: 1.8rem;
            font-weight: 700;
            color: initial;
        }

        .collection__search-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background-color: #555;
        }

        img.collection__search-icon {
            width: 2.5rem;
            height: 2.5rem;
        }

        input.collection__search {
            padding: 1rem;
            font-size: 1.8rem;
            font-weight: 700;
            border-radius: 0.5rem;
            border: none;
            background-color: transparent;
            color: #d9d9d9;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 20px;
            margin-top: 40px;
            row-gap: 40px;
            margin-bottom: 160px;
        }

        .other-collections {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        a.image-card {
            display: block;
            width: 100%;
            height: 100%;
            text-decoration: none;
            color: inherit;
            border-radius: 1.5rem;
            transition: transform 0.2s;
            overflow: hidden;
        }

        a.image-card:hover,
        a.image-card:focus {
            transform: translateY(-0.5rem);
        }

        a.image-card--hidden {
            display: none;
        }

        img.image-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
        <div class="collection container">
            <h1 class="collection__name">
                <?php echo $playlist['playlist_name']; ?>
                <?php if ($isEditable) : ?>
                    <a
                        class="collection__edit-button"
                        href="/echolog/playlist_edit.php?playlist_id=<?= urlencode($playlist_id) ?>"
                        id="collection-edit-button">
                        <img class="collection__edit-button-icon" src="/echolog/assets/svgs/pencil-outline.svg" alt="Edit icon" />
                    </a>
                <?php endif; ?>
            </h1>
            <h2 class="collection__description gray">
                <?php echo $playlist['playlist_desc']; ?>
            </h2>
            <div class="collection__stats flex flex-row justify-between align-center">
                <div class="collection__by">
                    <img
                        src="<?php echo $playlist['profile_image']; ?>"
                        alt="Avatar of the collection creator"
                        class="collection__avatar" />
                    <h3 class="collection__username">Collection by <span>
                            <?php echo $playlist['usernickname']; ?>
                        </span></h3>
                </div>
                <form method="POST">
                    <button type="submit" name="like" class="collection__like" id="collection-like">
                        <img
                            src="/echolog/assets/svgs/<?= $liked ? 'heart-solid.svg' : 'heart-outline.svg' ?>"
                            alt="Heart icon"
                            class="collection__like-icon">
                        <p class="collection__like-count m-0">
                            <?php echo $playlist['like_count']; ?>
                        </p>
                    </button>
                </form>
            </div>
            <hr color="#8a8a8a" />
            <!-- <div class="flex flex-row justify-between align-center">
                <select class="collection__sort" id="collection-sort">
                    <option class="collection__sort-option" value="popular">Popular</option>
                    <option class="collection__sort-option" value="newest">Newest</option>
                </select>
                <div class="collection__search-bar">
                    <img src="/echolog/assets/svgs/magnifying-glass-outline.svg" alt="Search icon" class="collection__search-icon" />
                    <input type="text" class="collection__search" placeholder="Search in list" id="collection-search" />
                </div>
            </div> -->
            <section class="gallery">
                <?php foreach ($playlist['games'] as $game) : ?>
                    <a href="/echolog/gamepage.php?game_id=<?= urlencode($game['game_id']) ?>" class="image-card">
                        <img class="image-card__image" src="<?= htmlspecialchars($game['cover_image']) ?>" alt="Game Cover" />
                    </a>
                <?php endforeach; ?>
            </section>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>