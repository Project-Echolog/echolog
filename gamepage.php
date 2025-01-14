<?php
// FINIHSED THIS PAGE IS FINISHED!!!!!
// FIX ADD REVIEW BUTTON
// LETS GOOOOOOOOOOOOOOO
include "dbconnection.php";

session_start();

// Ensure the user is logged in
$user_id = $_SESSION['user_id'] ?? null;
/*if (!isset($user_id)) {
    die("<p>Error: You need to be logged in to like or wishlist a game.</p>");
} */

// Retrieve game_id dynamically from the GET or POST request
$game_id = $_GET['game_id'] ?? $_POST['game_id'] ?? null;

if (!$game_id) {
    die("<p>Error: No game selected.</p>");
}

$liked = false;
$wish = false;
$message = '';

// Handle POST requests for likes and wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle Like functionality
        if (isset($_POST['like'])) {
            $stmt = $conn->prepare("SELECT * FROM Likes_Games WHERE user_id = ? AND game_id = ?");
            $stmt->bind_param("ii", $user_id, $game_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Unlike the game
                $stmt = $conn->prepare("DELETE FROM Likes_Games WHERE user_id = ? AND game_id = ?");
                $stmt->bind_param("ii", $user_id, $game_id);
                $stmt->execute();
                $liked = false;
                $message = "You have unliked this game.";
            } else {
                // Like the game
                $stmt = $conn->prepare("INSERT INTO Likes_Games (user_id, game_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $game_id);
                $stmt->execute();
                $liked = true;
                $message = "You have liked this game.";
            }
        }

        // Handle Wishlist functionality
        if (isset($_POST['wishlist'])) {
            $stmt = $conn->prepare("SELECT * FROM Wishlist_Games WHERE user_id = ? AND game_id = ?");
            $stmt->bind_param("ii", $user_id, $game_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Remove from wishlist
                $stmt = $conn->prepare("DELETE FROM Wishlist_Games WHERE user_id = ? AND game_id = ?");
                $stmt->bind_param("ii", $user_id, $game_id);
                $stmt->execute();
                $wish = false;
                $message = "You have removed this game from your wishlist.";
            } else {
                // Add to wishlist
                $stmt = $conn->prepare("INSERT INTO Wishlist_Games (user_id, game_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $game_id);
                $stmt->execute();
                $wish = true;
                $message = "You have added this game to your wishlist.";
            }
        }
    } catch (Exception $e) {
        $message = "An error occurred: " . $e->getMessage();
    }
}

// // Fetch like status
$stmt = $conn->prepare("SELECT * FROM Likes_Games WHERE user_id = ? AND game_id = ?");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$like_result = $stmt->get_result();
if ($like_result->num_rows > 0) {
    $liked = true;
}

// // Fetch wishlist status
$stmt = $conn->prepare("SELECT * FROM WISHLIST WHERE user_id = ? AND game_id = ?");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$wishlist_result = $stmt->get_result();
if ($wishlist_result->num_rows > 0) {
    $wish = true;
}

// // Display the like and wishlist buttons
// 
?>

<?php
// Code to display how many damn users like and wishlised game
$game_id = $_GET['game_id'] ?? $_POST['game_id'] ?? null;

try {
    // Get the total number of likes for the game
    $stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM Likes_Games WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $like_result = $stmt->get_result();
    $like_data = $like_result->fetch_assoc();
    $game_like_count = $like_data['like_count'];

    // Get the total number of wishlists for the game
    $stmt = $conn->prepare("SELECT COUNT(*) AS wishlist_count FROM WISHLIST WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $wishlist_result = $stmt->get_result();
    $wishlist_data = $wishlist_result->fetch_assoc();
    $wishlist_count = $wishlist_data['wishlist_count'];
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

?>

<?php
// Prepare the SQL statement to fetch the user's review and rating for the specified game
$game_id = $_GET['game_id'] ?? $_POST['game_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$stmt = $conn->prepare("SELECT r.review_text, rat.rating 
                        FROM Reviews r 
                        LEFT JOIN Ratings rat ON r.game_id = rat.game_id AND r.user_id = rat.user_id 
                        WHERE r.user_id = ? AND r.game_id = ?");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$result = $stmt->get_result();

$user_review = null;
$user_rating = null;

// Check if the user has reviewed this game
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_review = htmlspecialchars($row['review_text']);
    $user_rating = (int)$row['rating'];
}

$stmt->close();
?>

<?php

// Fetch game details
try {
    $stmt = $conn->prepare("SELECT game_title, genre, Like_count, platform, Publisher, release_date, cover_image, banner_image, description FROM Games WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Exception $e) {
    echo "<p>Error fetching game details: " . $e->getMessage() . "</p>";
}

$genres_array = [];

if ($result->num_rows > 0) {
    $game = $result->fetch_assoc();
    $game_title = htmlspecialchars($game['game_title']);
    $game_desc = htmlspecialchars($game['description']); // Fetch and sanitize description
    $game_pub = htmlspecialchars($game['Publisher']);
    $game_release = (int)htmlspecialchars($game['release_date']);
    $game_genre = htmlspecialchars($game['genre']);
    $game_likes = (int)$game['Like_count'];
    $game_platform = htmlspecialchars($game['platform']);
    $game_cover = htmlspecialchars($game['cover_image']);
    $game_banner = htmlspecialchars($game['banner_image']);

    // split the genres into an array
    $genres_array = explode(",", $game_genre);
}

$stmt->close();

?>

<?php

$game_id = $_GET['game_id'] ?? $_POST['game_id'] ?? null;

$popular_reviews = [];
$recent_reviews = [];

try {
    // Query 1: Popular Reviews
    $stmt = $conn->prepare("
        SELECT 
            r.review_text, 
            r.review_id,
            r.like_count, 
            r.user_id, 
            r.game_id, 
            rg.rating AS user_rating, 
            u.user_nickname, 
            u.profile_image
        FROM 
            Reviews r
        LEFT JOIN 
            Ratings rg ON r.game_id = rg.game_id AND r.user_id = rg.user_id
        JOIN 
            Users u ON r.user_id = u.user_id
        WHERE 
            r.game_id = ? AND r.like_count = (
                SELECT MAX(like_count) 
                FROM Reviews 
                WHERE game_id = ?
            )
    ");
    $stmt->bind_param("ii", $game_id, $game_id); // Bind two parameters
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $review_text = htmlspecialchars($row['review_text']);
            $review_id = htmlspecialchars($row['review_id']);
            $like_count = (int) $row['like_count'];
            $user_nickname = htmlspecialchars($row['user_nickname']);
            $profile_picture = htmlspecialchars($row['profile_image']);
            $user_rating = $row['user_rating'] ? (float) $row['user_rating'] : 'No Rating';

            $popular_reviews[] = [
                'review_text' => $review_text,
                'review_id' => $review_id,
                'like_count' => $like_count,
                'user_nickname' => $user_nickname,
                'profile_picture' => $profile_picture,
                'user_rating' => $user_rating
            ];
        }
    }
    $stmt->close(); // Close first statement

    // Query 2: Recent Reviews
    $stmt = $conn->prepare("
        SELECT 
            r.review_text, 
            r.review_id,
            r.like_count, 
            r.user_id, 
            r.game_id, 
            rg.rating AS user_rating, 
            u.user_nickname, 
            u.profile_image
        FROM 
            Reviews r
        LEFT JOIN 
            Ratings rg ON r.game_id = rg.game_id AND r.user_id = rg.user_id
        JOIN 
            Users u ON r.user_id = u.user_id
        WHERE 
            r.game_id = ?
        ORDER BY
            r.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // echo "<h2>Recent Reviews</h2>";
        while ($row = $result->fetch_assoc()) {
            $review_text = htmlspecialchars($row['review_text']);
            $like_count = (int) $row['like_count'];
            $user_nickname = htmlspecialchars($row['user_nickname']);
            $profile_picture = htmlspecialchars($row['profile_image']);
            $user_rating = $row['user_rating'] ? (float) $row['user_rating'] : 'No Rating';

            $recent_reviews[] = [
                'review_text' => $review_text,
                'like_count' => $like_count,
                'user_nickname' => $user_nickname,
                'profile_picture' => $profile_picture,
                'user_rating' => $user_rating
            ];
        }
    }
    $stmt->close();
} catch (Exception $e) {
    echo "<p>Error fetching reviews: " . $e->getMessage() . "</p>";
}

?>

<?php

$playlists = [];

try {
    // Prepare and execute the query to fetch playlists containing the selected game
    $stmt = $conn->prepare("
            SELECT 
                p.playlist_id, 
                p.playlist_name, 
                p.playlist_description, 
                p.user_id AS creator_id,
                u.user_nickname AS creator_name,
                COUNT(lp.user_id) AS like_count,
                g.cover_image AS game_cover_image
            FROM 
                Playlist p
            LEFT JOIN 
                Playlist_Games pg ON p.playlist_id = pg.playlist_id
            LEFT JOIN 
                Games g ON pg.game_id = g.game_id
            LEFT JOIN 
                Likes_Playlists lp ON p.playlist_id = lp.playlist_id
            LEFT JOIN 
                Users u ON p.user_id = u.user_id
            WHERE 
                pg.game_id = ?
            GROUP BY 
                p.playlist_id
            ORDER BY 
                like_count DESC, p.playlist_name ASC
        ");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Fetch and sanitize data
            $playlist_id = $row['playlist_id'];
            $playlist_name = htmlspecialchars($row['playlist_name']);
            $playlist_description = htmlspecialchars($row['playlist_description']);
            $creator_name = htmlspecialchars($row['creator_name']);
            $like_count = (int) $row['like_count'];
            $cover_image = htmlspecialchars($row['game_cover_image']);

            $playlists[] = [
                'id' => $playlist_id,
                'name' => $playlist_name,
                'description' => $playlist_description,
                'creator' => $creator_name,
                'likes' => $like_count,
                'cover_image' => $cover_image
            ];
        }
    }

    $stmt->close();
} catch (Exception $e) {
    echo "<p>Error fetching playlists: " . $e->getMessage() . "</p>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Page | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css" />
    <style>
        .banner-frame {
            height: 70dvh;
        }

        img.banner {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: blur(2px) brightness(0.6);
        }

        .container {
            padding-top: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        section.introduction {
            display: flex;
            flex-direction: row;
            width: 100%;
        }

        .introduction__left {
            display: flex;
            flex-direction: column;
            flex: 2;
            padding: 0 2rem;
            transform: translateY(-6rem);
        }

        .cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .introduction__tools {
            display: flex;
            flex-direction: row;
            justify-content: center;
            gap: 0.4rem;
            margin-top: 1rem;
        }

        a.introduction__tool {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            color: #fff;
            border-radius: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
            cursor: pointer;
        }

        button.introduction__tool-button {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0 1rem;
            color: #fff;
            border-radius: 0.5rem;
            font-size: 1.8rem;
            font-weight: 700;
            cursor: pointer;
            background-color: transparent;
            border: none;
            flex-direction: row;
            justify-content: center;
        }

        img.introduction__tool-icon {
            width: 3rem;
            height: 3rem;
        }

        .introduction__right {
            display: flex;
            flex-direction: column;
            flex: 5;
            padding: 0 2rem;
        }

        .introduction__title {
            font-size: 5rem;
            font-weight: 700;
            margin: 0;
        }

        .introduction__developer {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            width: fit-content;
            border-bottom: 1px solid #fff;
            line-height: 1.2;
        }

        .introduction__description {
            font-size: 1.5rem;
            font-weight: 400;
            margin: 2rem 0;
            line-height: 1.5;
            color: #909090;
        }

        aside.genres {
            margin: 2rem;
            display: flex;
            flex-direction: row;
            gap: 8rem;
            justify-content: flex-start;
            align-items: center;
        }

        h2.genres__title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .genres__list {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8rem;
        }

        a.genres__item {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            cursor: pointer;
            border-bottom: 2px solid var(--color-review-lighter);
        }

        aside.info-line {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            gap: 2rem;
            margin: 2rem;
        }

        p.info {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        section.personal-review {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin: 2rem;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .personal-review__button {
            margin: 0;
            background-color: var(--color-review-lighter) !important;
            border-radius: 1rem;
            padding: 0.5rem 4rem !important;
            font-size: 2.5rem !important;
        }

        .section-header {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        a.button {
            display: inline-block;
            padding: 1rem 2rem;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            color: #fff;
            background-color: var(--color-button-primary);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s;
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

        .review-card__game-image.alt {
            border-radius: 50px;
            height: auto;
            aspect-ratio: 1/1;
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

        .review-card__rating-alt:not(.alt) {
            display: none;
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
            cursor: pointer;
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

        .collection-card {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 400px;
            height: 100%;
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

        .collections {
            display: flex;
            flex-direction: row;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
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
        <div class="banner-frame">
            <img
                src="<?= $game_banner ?>"
                alt="<?= $game_title ?>"
                class="banner">
        </div>
        <div class="container">
            <section class="introduction">
                <div class="introduction__left">
                    <div class="cover-frame">
                        <img
                            src="<?= $game_cover ?>"
                            alt="<?= $game_title ?>"
                            title="<?= $game_title ?>"
                            class="cover">
                    </div>
                    <div class="introduction__tools">
                        <form method="POST">
                            <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($game_id); ?>">
                            <button type="submit" name="wishlist" class="introduction__tool-button" id="wishlist-button">
                                <?php if ($wish) : ?>
                                    <img src="/echolog/assets/svgs/bookmark-solid.svg" alt="Wishlist button" class="introduction__tool-icon">
                                <?php else : ?>
                                    <img src="/echolog/assets/svgs/bookmark-outline.svg" alt="Wishlist button" class="introduction__tool-icon">
                                <?php endif; ?>
                                <p><?= $wishlist_count ?></p>
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($game_id); ?>">
                            <button type="submit" name="like" class="introduction__tool-button" id="like-button">
                                <?php if ($liked) : ?>
                                    <img src="/echolog/assets/svgs/heart-solid.svg" alt="Like button" class="introduction__tool-icon">
                                <?php else : ?>
                                    <img src="/echolog/assets/svgs/heart-outline.svg" alt="Like button" class="introduction__tool-icon">
                                <?php endif; ?>
                                <p><?= $game_like_count ?></p>
                            </button>
                        </form>
                        <a
                            class="introduction__tool"
                            href="/echolog/addtoplay.php?game_id=<?= urlencode($game_id) ?>"
                            id="game-add">
                            <img src="/echolog/assets/svgs/plus-circle-outline.svg" alt="Add game button" class="introduction__tool-icon">
                        </a>
                    </div>
                </div>
                <div class="introduction__right">
                    <h1 class="introduction__title">
                        <?= $game_title ?>
                    </h1>
                    <h2 class="introduction__developer">
                        <a class="white" href="/echolog/BrowseGames.php?Publisher=<?= urlencode($game_pub) ?>">
                            <?= $game_pub ?>
                        </a>
                    </h2>
                    <p class="introduction__description">
                        <?= $game_desc ?>
                    </p>
                </div>
            </section>
            <hr color="#8a8a8a" />
            <aside class="genres">
                <h2 class="genres__title">Genres</h2>
                <div class="genres__list">
                    <?php foreach ($genres_array as $genre) : ?>
                        <a class="genres__item" href="/echolog/BrowseGames.php?genre[]=<?= urlencode($genre) ?>">
                            <?= $genre ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            <hr color="#8a8a8a" />
            <aside class="info-line">
                <!-- <p class="info">Rating: <span>4.5</span>/5</p> -->
                <p class="info">Developer: <a class="white" href="/echolog/BrowseGames.php?Publisher=<?= urlencode($game_pub) ?>"><span><?= $game_pub ?></span></a></p>
                <p class="info">Release Date: <span><?= $game_release ?></span></p>
                <p class="info">Platforms: <span><?= $game_platform ?></span></p>
            </aside>
            <hr color="#8a8a8a" />
            <section class="personal-review">
                <h3 class="personal-review__text m-0 fs-3">What do you think?</h3>
                <p class="button personal-review__button" id="review-button">
                    <a
                        href="/echolog/rate_game_new.php?game_id=<?= urlencode($game_id) ?>"
                        class="button personal-review__button">
                        Review it
                    </a>
                </p>
            </section>
            <hr color="#8a8a8a" />
            <section class="popular-reviews mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">Popular Reviews</p>
                    <a class="fs-1-5" href="/echolog/gamepagedetailed.php?game_id=<?= urlencode($game_id) ?>">
                        See all
                    </a>
                </div>
                <?php foreach ($popular_reviews as $review) : ?>
                    <div class="review-card mb-3">
                        <div class="review-card__left">
                            <img
                                class="review-card__game-image alt"
                                src="<?= $review['profile_picture'] ?>"
                                alt="<?= $review['user_nickname'] ?>" />
                            <p class="m-0 text-center fs-1-2">
                                <?= $review['user_nickname'] ?>
                            </p>
                        </div>
                        <div class="review-card__right">
                            <p class="review-card__game-title fs-2 hide">
                                <?= $game_title ?>
                            </p>
                            <p class="review-card__rating-alt m-0 alt"><span><?= $review['user_rating'] ?></span>/5</p>
                            <p class="review-card__text m-0">
                                <?= $review['review_text'] ?>
                            </p>
                            <div class="review-card__like-info">
                                <a class="review-card__like-button">
                                    <img
                                        class="review-card__like-icon"
                                        src="/echolog/assets/svgs/heart-outline.svg"
                                        alt="Like Icon" />
                                    <span class="review-card__like-count">
                                        <?= $review['like_count'] ?>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
            <hr color="#8a8a8a" />
            <section class="recent-reviews mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">
                        Recent Reviews
                    </p>
                    <a class="fs-1-5" href="/echolog/gamepagedetailed.php?game_id=<?= urlencode($game_id) ?>">
                        See all
                    </a>
                </div>
                <?php foreach ($recent_reviews as $review) : ?>
                    <div class="review-card mb-3">
                        <div class="review-card__left">
                            <img
                                class="review-card__game-image alt"
                                src="<?= $review['profile_picture'] ?>"
                                alt="<?= $review['user_nickname'] ?>" />
                            <p class="m-0 text-center fs-1-2">
                                <?= $review['user_nickname'] ?>
                            </p>
                        </div>
                        <div class="review-card__right">
                            <p class="review-card__game-title fs-2 hide">
                                <?= $game_title ?>
                            </p>
                            <p class="review-card__rating-alt m-0 alt">
                                <span><?= $review['user_rating'] ?></span>/5
                            </p>
                            <p class="review-card__text m-0">
                                <?= $review['review_text'] ?>
                            </p>
                            <div class="review-card__like-info">
                                <a class="review-card__like-button">
                                    <img
                                        class="review-card__like-icon"
                                        src="/echolog/assets/svgs/heart-outline.svg"
                                        alt="Like Icon" />
                                    <span class="review-card__like-count">
                                        <?= $review['like_count'] ?>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
            <hr color="#8a8a8a" />
            <section class="mentioned-collections mt-3">
                <div class="section-header mb-2">
                    <p class="fs-2 m-0">Mentioned Collections</p>
                    <a class="fs-1-5" href="/echolog/gamepagedetailed.php?game_id=<?= urlencode($game_id) ?>">
                        See all
                    </a>
                </div>
                <div class="collections">
                    <?php foreach ($playlists as $playlist) : ?>
                        <a href="/echolog/playlist_view.php?playlist_id=<?= urlencode($playlist['id']) ?>"
                            class="collection-card alt mb-2"
                            title="<?= htmlspecialchars($playlist['name']) ?>">
                            <div class="collection-card__image-frame alt">
                                <div class="collection-card__overlay collection-card__image--1">
                                    <img
                                        class="collection-card__image collection-card__image--1"
                                        src="/echolog/assets/images/list-1.png"
                                        alt="<?= htmlspecialchars($playlist['name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--2">
                                    <img
                                        class="collection-card__image collection-card__image--2"
                                        src="/echolog/assets/images/list-2.png"
                                        alt="<?= htmlspecialchars($playlist['name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--3">
                                    <img
                                        class="collection-card__image collection-card__image--3"
                                        src="/echolog/assets/images/list-3.png"
                                        alt="<?= htmlspecialchars($playlist['name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--4">
                                    <img
                                        class="collection-card__image collection-card__image--4"
                                        src="/echolog/assets/images/list-4.png"
                                        alt="<?= htmlspecialchars($playlist['name']) ?>" />
                                </div>
                                <div class="collection-card__overlay collection-card__image--5">
                                    <img
                                        class="collection-card__image collection-card__image--5"
                                        src="/echolog/assets/images/list-5.png"
                                        alt="<?= htmlspecialchars($playlist['name']) ?>" />
                                </div>
                            </div>
                            <div class="pl-1">
                                <p class="collection-card__title fs-2 m-0">
                                    <?= $playlist['name'] ?>
                                </p>
                                <p class="collection-card__info m-0">
                                    <span class="collection-card__username">
                                        <?= htmlspecialchars($playlist['creator']) ?>
                                    </span>
                                    <span>|</span>
                                    <span><?= $playlist['likes'] ?> likes</span>
                                </p>
                                <p class="gray m-0">
                                    <?= $playlist['description'] ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>