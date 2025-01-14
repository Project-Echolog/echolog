<?php
include "dbconnection.php";

session_start();

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$logged_in_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $logged_in_user_id;
$is_own_profile = $logged_in_user_id === $profile_user_id;

// Ensure that the profile_user_id exists in the database
$stmt = $conn->prepare("SELECT user_nickname, profile_image FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("User not found."); // Prevent further execution if the user doesn't exist
}

$row = $result->fetch_assoc();
$username = htmlspecialchars($row['user_nickname'], ENT_QUOTES, 'UTF-8');
$profile_image = htmlspecialchars($row['profile_image'], ENT_QUOTES, 'UTF-8');

$liked_games = [];
$playlists = [];
$wishlisted_games = [];
$reviews = [];
$reviews_with_ratings = [];

try {
    $stmt = $conn->prepare("
    SELECT g.game_title, g.cover_image, g.game_id
    FROM Likes_Games lg
    JOIN Games g ON lg.game_id = g.game_id
    WHERE lg.user_id = ?
");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $liked_games[] = $row;
    }

    $stmt->close();
} catch (Exception $e) {
    die("Error fetching user data.");
}

try {
    // Query to get playlists created or liked by the user
    $stmt = $conn->prepare("
        SELECT 
            p.playlist_id, 
            p.playlist_name, 
            p.playlist_description, 
            g.cover_image,
            p.user_id AS creator_id
        FROM 
            Playlist p
        LEFT JOIN 
            Playlist_Games pg ON p.playlist_id = pg.playlist_id
        LEFT JOIN 
            Games g ON pg.game_id = g.game_id
        LEFT JOIN
            Likes_Playlists lp ON p.playlist_id = lp.playlist_id
        WHERE 
            p.user_id = ?
        OR 
            lp.user_id = ? 
        ORDER BY 
            p.playlist_id, pg.playlist_games_id
    ");
    $stmt->bind_param("ii", $profile_user_id, $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $playlist_id = $row['playlist_id'];
        $playlist_name = $row['playlist_name'];
        $playlist_description = $row['playlist_description'];
        $cover_image = $row['cover_image'];
        $creator_id = $row['creator_id'];

        if (!isset($playlists[$playlist_id])) {
            $playlists[$playlist_id] = [
                'playlist_id' => $playlist_id,
                'name' => $playlist_name,
                'description' => $playlist_description,
                'games' => [],
                'creator_id' => $creator_id
            ];
        }

        if ($cover_image) {
            $playlists[$playlist_id]['games'][] = $cover_image;
        }
    }

    $stmt->close();
} catch (Exception $e) {
    die("Error fetching user data.");
}

try {
    $stmt = $conn->prepare("
    SELECT g.game_id, g.game_title, g.cover_image
    FROM WISHLIST ws
    JOIN Games g ON ws.game_id = g.game_id
    WHERE ws.user_id = ?
");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $wishlisted_games[] = $row;
    }

    $stmt->close();
} catch (Exception $e) {
    die("Error fetching user data.");
}

try {
    $stmt = $conn->prepare("
        SELECT r.game_id, g.game_title, g.cover_image, r.review_text, r.user_id, r.like_count, rat.rating
        FROM REVIEWS r
        JOIN Games g ON r.game_id = g.game_id
        LEFT JOIN Ratings rat ON r.game_id = rat.game_id AND r.user_id = rat.user_id
        WHERE r.user_id = ?
    ");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    $stmt->close();
} catch (Exception $e) {
    die("Error fetching user data.");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css" />
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            margin-top: 50px;
        }

        section.user-panel {
            display: flex;
            flex-direction: column;
        }

        img.user-panel__avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
            object-fit: cover;
        }

        p.user-panel__username {
            position: relative;
            display: inline-block;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 10px;
            width: min-content;
        }

        span.user-panel__edit-button {
            font-size: 1.6rem;
            background-color: #909090;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: -70px;
        }

        p.user-panel__stats {
            font-size: 1.6rem;
            color: #909090;
        }

        hr.divider {
            border: 0;
            border-top: 1px solid #909090;
            margin-bottom: 20px;
        }

        .section-header {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 20px;
        }

        .collections {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .create-new-list {
            display: flex;
            flex-direction: column;
            /* gap: 1rem; */
            width: 100%;
            max-width: 400px;
            height: 100%;
            /* border-radius: 1.5rem; */
            overflow: hidden;
            transition: transform 0.2s;
            background-color: transparent;
            border: none;
            color: #fff;
            padding: 0;
            cursor: pointer;
        }

        .create-new-list:hover,
        .create-new-list:focus {
            transform: translateY(-0.5rem);
        }

        div.create-new-list__frame {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 1.5rem;
            aspect-ratio: 3 / 2;
            margin-bottom: 0.4rem;
            background-color: #4c4c4c;
        }

        p.create-new-list__title {
            padding: 0.5rem 0 0 1rem;
        }

        /* IMAGE CARD */
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

        .collections {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(3, 1fr);
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

        .hide {
            display: none;
        }

        .review-card {
            display: flex;
            flex-direction: row;
            gap: 1rem;
            padding: 1.5rem;
            border-radius: 1.5rem;
            background-color: #3b3b48;
            box-shadow: 0 0 1rem rgba(0, 0, 0, 0.5);
            width: 50%;
        }

        .review-card__left {
            width: 100px;
        }

        .review-card__game-image {
            width: 100%;
            height: 100%;
            border-radius: 1.5rem;
            object-fit: cover;
        }

        .review-card__right {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            width: 100%;
        }

        .review-card__right>* {
            margin: 0;
        }

        p.review-card__game-title {
            font-size: 2.5rem;
            line-height: 1.2;
        }

        p.review-card__adjoining-info {
            font-size: 1.25rem;
            color: #c1c1c1;
        }

        span.review-card__rating {
            color: var(--color-review);
        }

        p.review-card__text {
            font-size: 1.5rem;
            line-height: 1.5;
            margin-top: 1rem;
            color: #c1c1c1;
        }

        .review-card__like-info {
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
            flex: 1;
            justify-content: flex-end;
            align-items: flex-end;
            width: 100%;
            height: 100%;
        }

        a.review-card__like-button {
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
            align-items: center;
            color: #c1c1c1;
            text-decoration: none;
        }

        img.review-card__like-icon {
            width: 2rem;
            height: 2rem;
            transition: transform 0.2s;
        }

        img.review-card__like-icon:hover {
            transform: scale(1.1);
        }

        span.review-card__like-count {
            font-size: 1.5rem;
            color: #c1c1c1;
            user-select: none;
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
            <a class='header__nav-link active' href='/echolog/profile.php'>Profile</a>
        </nav>
        <a class="header__login" href="/echolog/login.php">Login</a>
    </header>
    <main>
        <div class="container">
            <section class="user-panel">
                <img class="user-panel__avatar" src="<?= $profile_image ?>" alt="User Avatar" />
                <p class="user-panel__username m-0">
                    <?= $username ?>
                    <?php if ($is_own_profile) : ?>
                        <span class="user-panel__edit-button">
                            <a class="white" href="/echolog/edit_profile.php">Edit</a>
                        </span>
                    <?php endif; ?>
                </p>
                <p class="user-panel__stats m-0">
                    <span class="user-panel__stats__item">
                        <?= count($liked_games) ?> games</span>
                    <span>|</span>
                    <span class="user-panel__stats__item">
                        <?= count($reviews) ?> reviews</span>
                    <span>|</span>
                    <span class="user-panel__stats__item">
                        <?= count($playlists) ?> lists</span>
                </p>
            </section>
            <hr class="divider" />
            <section class="popular-games mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">Favorite Games</p>
                    <a class="fs-1-5" href="/echolog/BrowseGames.php">See all</a>
                </div>
                <?php if (empty($liked_games)) : ?>
                    <p>No favorite games found.</p>
                <?php endif; ?>
                <div class="gallery">
                    <?php foreach ($liked_games as $game) : ?>
                        <a href="/echolog/gamepage.php?game_id=<?= $game['game_id'] ?>" class="image-card" title="<?= $game['game_title'] ?>">
                            <img class="image-card__image" src="<?= $game['cover_image'] ?>" alt="<?= $game['game_title'] ?>" title="<?= $game['game_title'] ?>" />
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="user-collections mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">Collections</p>
                    <a class="fs-1-5" href="/echolog/playlist_new.php">Create new collection</a>
                </div>
                <div class="collections">
                    <?php foreach ($playlists as $playlist) : ?>
                        <a
                            href="/echolog/playlist_view.php?playlist_id=<?= $playlist['playlist_id'] ?>"
                            class="collection-card"
                            title="<?= $playlist['name'] ?>">
                            <div class="collection-card__image-frame">
                                <div class="collection-card__overlay collection-card__image--1">
                                    <img
                                        class="collection-card__image collection-card__image--1"
                                        src="/echolog/assets/images/list-1.png"
                                        alt="<?= $playlist['name'] ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--2">
                                    <img
                                        class="collection-card__image collection-card__image--2"
                                        src="/echolog/assets/images/list-2.png"
                                        alt="<?= $playlist['name'] ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--3">
                                    <img
                                        class="collection-card__image collection-card__image--3"
                                        src="/echolog/assets/images/list-3.png"
                                        alt="<?= $playlist['name'] ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--4">
                                    <img
                                        class="collection-card__image collection-card__image--4"
                                        src="/echolog/assets/images/list-4.png"
                                        alt="<?= $playlist['name'] ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--5">
                                    <img
                                        class="collection-card__image collection-card__image--5"
                                        src="/echolog/assets/images/list-5.png"
                                        alt="<?= $playlist['name'] ?>" />
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($is_own_profile && empty($playlists)) : ?>
                        <a class="create-new-list" id="create-new-list" href="/echolog/playlist_new.php">
                            <div class="create-new-list__frame"></div>
                            <p class="create-new-list__title fs-2 m-0">
                                Create new list
                            </p>
                        </a>
                    <?php endif; ?>
                </div>
            </section>
            <section class="popular-games mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">Wishlisted Games</p>
                    <a class="fs-1-5" href="/echolog/BrowseGames.php">See all</a>
                </div>
                <?php if (empty($wishlisted_games)) : ?>
                    <p>No wishlisted games found.</p>
                <?php endif; ?>
                <div class="gallery">
                    <?php foreach ($wishlisted_games as $game) : ?>
                        <a href="/echolog/gamepage.php?game_id=<?= $game['game_id'] ?>" class="image-card" title="<?= $game['game_title'] ?>">
                            <img class="image-card__image" src="<?= $game['cover_image'] ?>" alt="<?= $game['game_title'] ?>" title="<?= $game['game_title'] ?>" />
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="reviews mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">Reviews</p>
                </div>
                <?php if (empty($reviews)) : ?>
                    <p>No reviews found.</p>
                <?php endif; ?>
                <?php foreach ($reviews as $review) : ?>
                    <div class="review-card mb-3">
                        <div class="review-card__left">
                            <img
                                class="review-card__game-image"
                                src="<?= $review['cover_image'] ?>"
                                alt="<?= $review['game_title'] ?>" />
                        </div>
                        <div class="review-card__right">
                            <p class="review-card__game-title fs-2">
                                <?= $review['game_title'] ?>
                            </p>
                            <p class="review-card__adjoining-info">
                                <span class="review-card__rating">
                                    Rating: <?= $review['rating'] ? $review['rating'] . '/5' : 'No rating' ?>
                                </span>
                            </p>
                            <p class="review-card__text">
                                <?= $review['review_text'] ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>