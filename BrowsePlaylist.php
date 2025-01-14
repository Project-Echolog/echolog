<?php
include "dbconnection.php";

$conditions = [];
$params = [];
$param_types = "";

// Check if search parameter is set
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $conditions[] = "p.playlist_name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $param_types .= "s";
}

// Query for playlists
$query = "SELECT p.*, COUNT(pg.game_id) as game_count, u.user_nickname
          FROM Playlist p
          LEFT JOIN Playlist_games pg ON p.playlist_id = pg.playlist_id
          JOIN Users u ON p.user_id = u.user_id";

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " GROUP BY p.playlist_id ORDER BY p.playlist_id";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$playlists = [];
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $playlists[] = [
            'playlist_id' => $row['playlist_id'],
            'playlist_name' => htmlspecialchars($row['playlist_name']),
            'playlist_description' => htmlspecialchars($row['playlist_description']),
            'like_count' => (int) $row['like_count'],
            'game_count' => (int) $row['game_count'],
            'username' => htmlspecialchars($row['user_nickname'])
        ];
    }
} else {
    $message = "An error occurred while fetching playlists.";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlists | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background-color: #555;
            width: fit-content;
        }

        img.search-icon {
            width: 2.5rem;
            height: 2.5rem;
        }

        input.search {
            padding: 1rem;
            font-size: 1.8rem;
            font-weight: 700;
            border-radius: 0.5rem;
            border: none;
            background-color: transparent;
            color: #d9d9d9;
        }

        .collection-card {
            display: flex;
            flex-direction: column;
            /* gap: 1rem; */
            width: 100%;
            max-width: 400px;
            height: 100%;
            /* border-radius: 1.5rem; */
            overflow: hidden;
            transition: transform 0.2s;
        }

        .collection-card.alt {
            flex-direction: row;
            max-width: 100%;
        }

        .collection-card:hover,
        .collection-card:focus {
            transform: translateY(-0.5rem);
        }

        a.collection-card--hidden {
            display: none;
        }

        div.collection-card__image-frame {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            aspect-ratio: 3 / 2;
            margin-bottom: 0.4rem;
        }

        div.collection-card__image-frame.alt {
            max-width: 300px;
        }

        div.collection-card__overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            transition: opacity 0.2s;
        }

        img.collection-card__image {
            position: absolute;
            top: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        div.collection-card__image--1 {
            max-width: 200px;
            left: 0;
            z-index: 500;
        }

        div.collection-card__image--2 {
            max-width: 200px;
            left: 25px;
            z-index: 400;
        }

        div.collection-card__image--3 {
            max-width: 200px;
            left: 50px;
            z-index: 300;
        }

        div.collection-card__image--4 {
            max-width: 200px;
            left: 75px;
            z-index: 200;
        }

        div.collection-card__image--5 {
            max-width: 200px;
            left: 100px;
            z-index: 100;
        }

        p.collection-card__title {
            line-height: 1.2;
            font-weight: 700;
            color: #fff;
        }

        p.collection-card__info {
            font-size: 1.4rem;
            color: #c1c1c1;
        }

        span.collection-card__username {
            font-weight: 700;
        }
    </style>
</head>

<body>
    <header class="header">
        <!-- Header Section -->
        <a class="header__logo" href="/">
            <img src="/echolog/assets/svgs/logo.svg" alt="Echolog Logo">
        </a>
        <nav class="header__nav">
            <a class="header__nav-link" href="/echolog/BrowseGames.php">Games</a>
            <a class="header__nav-link active" href="/echolog/BrowsePlaylist.php">Collections</a>
            <a class="header__nav-link" href="/echolog/profile.php">Profile</a>
        </nav>
        <a class="header__login" href="/echolog/login.php">Login</a>
    </header>

    <main>
        <div class="container">
            <h1>Browse Collections</h1>
            <!-- Search Bar -->
            <div class="search-bar mt-3">
                <img src="/echolog/assets/svgs/magnifying-glass-outline.svg" alt="Search icon" class="search-icon" />
                <form method="GET" action="BrowsePlaylist.php">
                    <input
                        type="text"
                        class="search"
                        placeholder="Search in list"
                        name="search"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
                </form>
            </div>

            <!-- Collections Section -->
            <section class="collections mt-3">
                <?php if (empty($playlists)): ?>
                    <p id="no-results">No results found</p>
                <?php else: ?>
                    <?php foreach ($playlists as $playlist): ?>
                        <a href="playlist_view.php?playlist_id=<?= urlencode($playlist['playlist_id']) ?>"
                            class="collection-card alt mb-2"
                            title="<?= htmlspecialchars($playlist['playlist_name']) ?>">
                            <div class="collection-card__image-frame alt">
                                <div class="collection-card__overlay collection-card__image--1">
                                    <img
                                        class="collection-card__image collection-card__image--1"
                                        src="/echolog/assets/images/list-1.png"
                                        alt="<?= htmlspecialchars($playlist['playlist_name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--2">
                                    <img
                                        class="collection-card__image collection-card__image--2"
                                        src="/echolog/assets/images/list-2.png"
                                        alt="<?= htmlspecialchars($playlist['playlist_name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--3">
                                    <img
                                        class="collection-card__image collection-card__image--3"
                                        src="/echolog/assets/images/list-3.png"
                                        alt="<?= htmlspecialchars($playlist['playlist_name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--4">
                                    <img
                                        class="collection-card__image collection-card__image--4"
                                        src="/echolog/assets/images/list-4.png"
                                        alt="<?= htmlspecialchars($playlist['playlist_name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--5">
                                    <img
                                        class="collection-card__image collection-card__image--5"
                                        src="/echolog/assets/images/list-5.png"
                                        alt="<?= htmlspecialchars($playlist['playlist_name']) ?>" />
                                </div>
                            </div>
                            <div class="pl-1">
                                <p class="collection-card__title fs-2 m-0">
                                    <?= $playlist['playlist_name'] ?>
                                </p>
                                <p class="collection-card__info m-0">
                                    <span class="collection-card__username">
                                        <?= htmlspecialchars($playlist['username']) ?>
                                    </span>
                                    <span>|</span>
                                    <span><?= $playlist['game_count'] ?> games</span>
                                </p>
                                <p class="gray m-0">
                                    <?= $playlist['playlist_description'] ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>

</html>