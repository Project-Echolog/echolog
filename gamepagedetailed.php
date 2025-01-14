<?php
// Connect to database
// TODO: add like/unlike button
include "dbconnection.php";

session_start();


// Get game ID from URL (example: reviews.php?game_id=1)
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
$user_id = $_SESSION['user_id'] ?? null;

// Check if game_id is valid
if ($game_id <= 0) {
    die("Please provide a valid game ID");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details for Game | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            padding-top: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .game-info {
            background-color: transparent;
            /* padding: 20px; */
            margin-bottom: 20px;
            border-radius: 8px;
            /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); */
        }

        .game-info img {
            max-width: 200px;
            border-radius: 8px;
        }

        .review {
            background-color: #2a3b4d;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .user-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .rating {
            color: gold;
            font-weight: bold;
            font-size: 1.2em;
        }

        .date {
            color: #999;
            font-size: 0.9em;
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
        <div class="container">
            <?php
            // First, get game information
            $game_sql = "SELECT game_title, cover_image FROM Games WHERE game_id = ?";
            $game_stmt = $conn->prepare($game_sql);
            $game_stmt->bind_param("i", $game_id);
            $game_stmt->execute();
            $game = $game_stmt->get_result()->fetch_assoc();

            // Display game information
            if ($game) {
                echo "<div class='game-info'>";
                echo "<h1>" . htmlspecialchars($game['game_title']) . "</h1>";
                if ($game['cover_image']) {
                    echo "<img src='" . htmlspecialchars($game['cover_image']) . "' alt='Game Cover'>";
                }
                echo "</div>";
            }

            // Get all reviews for this game
            $review_sql = "
        SELECT 
            r.review_text,
            r.created_at,
            r.like_count,
            u.user_nickname,
            u.profile_image,
            rt.rating
        FROM Reviews r
        JOIN Users u ON r.user_id = u.user_id
        LEFT JOIN Ratings rt ON (r.game_id = rt.game_id AND r.user_id = rt.user_id)
        WHERE r.game_id = ?
        ORDER BY r.created_at DESC
    ";

            $review_stmt = $conn->prepare($review_sql);
            $review_stmt->bind_param("i", $game_id);
            $review_stmt->execute();
            $reviews = $review_stmt->get_result();


            $liked_user = "
        SELECT 
            l.user_id,
            u.user_nickname,
            u.profile_image
        FROM Likes_Games l
        JOIN Users u ON l.user_id = u.user_id
        Where l.game_id = ?
    ";

            $liked_stmt = $conn->prepare($liked_user);
            $liked_stmt->bind_param("i", $game_id);
            $liked_stmt->execute();
            $likes = $liked_stmt->get_result();


            $wishlisted_user = "
        SELECT 
            w.user_id,
            u.user_nickname,
            u.profile_image
        FROM WISHLIST w
        JOIN Users u ON w.user_id = u.user_id
        Where w.game_id = ?
    ";

            $wish_stmt = $conn->prepare($wishlisted_user);
            $wish_stmt->bind_param("i", $game_id);
            $wish_stmt->execute();
            $wishes = $wish_stmt->get_result();


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

            // Display reviews

            if ($reviews->num_rows > 0) {
                while ($review = $reviews->fetch_assoc()) {
                    // echo "<div class='review'>";

                    // // User info section
                    // echo "<div class='user-info'>";
                    // if ($review['profile_image']) {
                    //     echo "<img src='" . htmlspecialchars($review['profile_image']) . "' alt='Profile'>";
                    // }
                    // echo "<div>";
                    // echo "<h3>" . htmlspecialchars($review['user_nickname']) . "</h3>";
                    // echo "<span class='date'>" . date('F j, Y', strtotime($review['created_at'])) . "</span>";
                    // echo "<p>" . (int)htmlspecialchars($review['like_count']) . "</p>";
                    // echo "</div>";
                    // echo "</div>";

                    // // Review content
                    // echo "<p>" . nl2br(htmlspecialchars($review['review_text'])) . "</p>";

                    // // Show rating if exists
                    // if ($review['rating']) {
                    //     echo "<div class='rating'>Rating: " . htmlspecialchars($review['rating']) . "/5 ⭐</div>";
                    // }

                    echo "<div class='review-card mb-3'>";
                    echo "<div class='review-card__left'>";
                    echo "<img class='review-card__game-image alt' src='" . $review['profile_image'] . "' alt='" . $review['user_nickname'] . "' />";
                    echo "<p class='m-0 text-center fs-1-2'>" . $review['user_nickname'] . "</p>";
                    echo "</div>";
                    echo "<div class='review-card__right'>";
                    if ($review['rating']) {
                        echo "<p class='review-card__rating m-0'><span>" . $review['rating'] . "</span>/5</p>";
                    }
                    echo "<p class='review-card__text m-0'>" . $review['review_text'] . "</p>";
                    echo "<div class='review-card__like-info'>";
                    echo "<a class='review-card__like-button'>";
                    echo "<img class='review-card__like-icon' src='/echolog/assets/svgs/heart-outline.svg' alt='Like Icon' />";
                    echo "<span class='review-card__like-count'>" . $review['like_count'] . "</span>";
                    echo "</a>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='review'>No reviews yet for this game.</div>";
            }


            echo "<h2>People who Liked</h2>";
            if ($likes->num_rows > 0) {
                while ($like = $likes->fetch_assoc()) {
                    echo "<div class='review'>";

                    // User info section
                    echo "<div class='user-info'>";
                    if ($like['profile_image']) {
                        echo "<img src='" . htmlspecialchars($like['profile_image']) . "' alt='Profile'>";
                    }
                    echo "<div>";
                    echo "<h3>" . htmlspecialchars($like['user_nickname']) . "</h3>";
                    // echo "<span class='date'>" . date('F j, Y', strtotime($review['created_at'])) . "</span>";
                    // echo "<p>" . (int)htmlspecialchars($review['like_count']) ."</p>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='review'>No likes for this game.</div>";
            }

            echo "<h2>People who Wishlisted</h2>";
            if ($wishes->num_rows > 0) {
                while ($wish = $wishes->fetch_assoc()) {
                    echo "<div class='review'>";

                    // User info section
                    echo "<div class='user-info'>";
                    if ($wish['profile_image']) {
                        echo "<img src='" . htmlspecialchars($wish['profile_image']) . "' alt='Profile'>";
                    }
                    echo "<div>";
                    echo "<h3>" . htmlspecialchars($wish['user_nickname']) . "</h3>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='review'>No wishes for this game :( </div>";
            }

            echo "<h2>Playlist that contains this game</h2>";

            if ($result->num_rows > 0) {
                echo "<div class='collections'>";
                while ($row = $result->fetch_assoc()) {
                    // Fetch and sanitize data
                    $playlist_id = $row['playlist_id'];
                    $playlist_name = htmlspecialchars($row['playlist_name']);
                    $playlist_description = htmlspecialchars($row['playlist_description']);
                    $creator_name = htmlspecialchars($row['creator_name']);
                    $like_count = (int) $row['like_count'];
                    $cover_image = htmlspecialchars($row['game_cover_image']);

                    // // Display each playlist
                    // echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;'>";
                    // echo "<h3>$playlist_name</h3>";
                    // echo "<p><strong>Created by:</strong> $creator_name</p>";
                    // echo "<p><strong>Description:</strong> $playlist_description</p>";
                    // echo "<p><strong>Likes:</strong> $like_count</p>";
                    // if ($cover_image) {
                    //     echo "<img src='$cover_image' alt='Game Cover' style='width: 100px; height: 100px; object-fit: cover;'>";
                    // }
                    // echo "</div>";
                    // echo "<a href='playlist_view.php?playlist_id=$playlist_id'>View</a>";

                    echo "<a href='playlist_view.php?playlist_id=$playlist_id' class='collection-card alt'>";
                    echo "<div class='collection-card__image-frame alt'>";
                    echo "<div class='collection-card__overlay collection-card__image--1'>";
                    echo "<img class='collection-card__image collection-card__image--1' src='/echolog/assets/images/list-1.png' alt='$playlist_name' />";
                    echo "</div>";
                    echo "<div class='collection-card__overlay collection-card__image--2'>";
                    echo "<img class='collection-card__image collection-card__image--2' src='/echolog/assets/images/list-2.png' alt='$playlist_name' />";
                    echo "</div>";
                    echo "<div class='collection-card__overlay collection-card__image--3'>";
                    echo "<img class='collection-card__image collection-card__image--3' src='/echolog/assets/images/list-3.png' alt='$playlist_name' />";
                    echo "</div>";
                    echo "<div class='collection-card__overlay collection-card__image--4'>";
                    echo "<img class='collection-card__image collection-card__image--4' src='/echolog/assets/images/list-4.png' alt='$playlist_name' />";
                    echo "</div>";
                    echo "<div class='collection-card__overlay collection-card__image--5'>";
                    echo "<img class='collection-card__image collection-card__image--5' src='/echolog/assets/images/list-5.png' alt='$playlist_name' />";
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='pl-1'>";
                    echo "<p class='collection-card__title fs-2 m-0'>$playlist_name</p>";
                    echo "<p class='collection-card__info m-0'>";
                    echo "<span class='collection-card__username'>$creator_name</span>";
                    echo "<span> | </span>";
                    echo "<span>$like_count likes</span>";
                    echo "</p>";
                    echo "<p class='gray m-0'>$playlist_description</p>";
                    echo "</div>";
                    echo "</a>";
                }
                echo "</div>";
            } else {
                echo "<p>No playlists found containing this game.</p>";
            }


            // Close database connections
            $game_stmt->close();
            $review_stmt->close();
            $liked_stmt->close();
            $wish_stmt->close();
            $stmt->close();
            ?>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>

</html>