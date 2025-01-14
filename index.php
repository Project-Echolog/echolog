<?php
include "dbconnection.php";

session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php"); // Redirect to profile page if user is already logged in
    exit;
}

try {
    // Get user data from the session
    // $user_id = $_SESSION['user_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "Guest";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Handle Like/Unlike actions
if (isset($_POST['like']) && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    $like = $_POST['like'] == '1' ? true : false;

    // Check if the user has already liked the review
    $stmt = $conn->prepare("SELECT * FROM Review_Likes WHERE review_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the user has already liked, unlike it
        if ($like) {
            $stmt = $conn->prepare("DELETE FROM Review_Likes WHERE review_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $review_id, $user_id);
            $stmt->execute();
            // Update like count in reviews
            $stmt = $conn->prepare("UPDATE REVIEWS SET like_count = like_count - 1 WHERE review_id = ?");
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
        }
    } else {
        // If the user has not liked, like the review
        if ($like) {
            $stmt = $conn->prepare("INSERT INTO Review_Likes (review_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $review_id, $user_id);
            $stmt->execute();
            // Update like count in reviews
            $stmt = $conn->prepare("UPDATE REVIEWS SET like_count = like_count + 1 WHERE review_id = ?");
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
        }
    }
    $stmt->close();
}

// Fetch recommended games
$stmt = $conn->prepare("SELECT game_title, cover_image, game_id FROM Games WHERE Admin_recommend = 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If multiple games are recommended, we fetch them all
    $games = [];
    while ($row = $result->fetch_assoc()) {
        $games[] = [
            'game_title' => htmlspecialchars($row['game_title']),
            'game_id' => htmlspecialchars($row['game_id']),
            'cover_image' => htmlspecialchars($row['cover_image'])
        ];
    }
} else {
    $games = []; // Initialize as empty if no games found
}

$stmt->close();

// Fetch popular games
$stmt = $conn->prepare("
    SELECT game_id, game_title, cover_image, 
    (Like_count * 1 + Wishlist_count * 1) AS popularity 
    FROM Games 
    WHERE Like_count >= 50 OR Wishlist_count >= 50
    ORDER BY popularity DESC 
    LIMIT 10;
    ");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If multiple popular games are found, fetch them
    $populargames = [];
    while ($row = $result->fetch_assoc()) {
        $populargames[] = [
            'game_title' => htmlspecialchars($row['game_title']),
            'game_id' => htmlspecialchars($row['game_id']),
            'cover_image' => htmlspecialchars($row['cover_image'])
        ];
    }
} else {
    $populargames = []; // Initialize as empty if no games found
}

$stmt->close();

// Fetch new games (last year or current year)
$stmt = $conn->prepare("
    SELECT game_title, game_id, cover_image FROM Games 
    WHERE YEAR(release_date) IN (YEAR(CURDATE()), YEAR(CURDATE()) - 1) 
    ORDER BY release_date DESC;
    ");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If multiple new games are found, fetch them
    $newgames = [];
    while ($row = $result->fetch_assoc()) {
        $newgames[] = [
            'game_title' => htmlspecialchars($row['game_title']),
            'game_id' => htmlspecialchars($row['game_id']),
        ];
    }
} else {
    $newgames = []; // Initialize as empty if no games found
}

$stmt->close();

// Fetch popular reviews
$stmt = $conn->prepare("
    SELECT 
    r.review_text, 
    r.review_id,
    r.like_count, 
    r.user_id, 
    r.game_id, 
    rg.rating AS user_rating, 
    u.user_nickname, 
    u.profile_image,
    g.cover_image,
    g.game_title
FROM 
    REVIEWS r
LEFT JOIN 
    Ratings rg ON r.game_id = rg.game_id AND r.user_id = rg.user_id
JOIN 
    Users u ON r.user_id = u.user_id
JOIN
    Games g ON r.game_id = g.game_id
WHERE 
    r.like_count > 0
ORDER BY 
    r.like_count DESC;
    ");

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Check if the query returns any rows
if ($result->num_rows > 0) {
    // Initialize an array to store the reviews
    $popularreviews = [];

    // Fetch each row and sanitize data
    while ($row = $result->fetch_assoc()) {
        $popularreviews[] = [
            'review_text' => htmlspecialchars($row['review_text']), // Sanitize review text
            'review_id' => htmlspecialchars($row['review_id']),
            'game_title' => htmlspecialchars($row['game_title']),
            'game_id' => (int) htmlspecialchars($row['game_id']), // Convert to int for security
            'game_cover' => htmlspecialchars($row['cover_image']), // Sanitize cover image
            'user_nickname' => htmlspecialchars($row['user_nickname']), // Sanitize user nickname
            'user_rating' => (int) htmlspecialchars($row['user_rating']), // Convert to int for security
            'like_count' => (int) htmlspecialchars($row['like_count']) // Convert to int for security
        ];
    }
} else {
    $popularreviews = []; // Initialize as empty array if no reviews are found
}

$playlists = [];

try {
    $stmt = $conn->prepare("
        SELECT 
            p.playlist_id, p.playlist_name, p.playlist_description, p.like_count, u.user_nickname
        FROM 
            Playlist p
        JOIN 
            Users u ON p.user_id = u.user_id
        ORDER BY 
            p.like_count DESC
        LIMIT 3;
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $playlists[] = [
                'playlist_id' => $row['playlist_id'],
                'name' => $row['playlist_name'],
                'description' => $row['playlist_description'],
                'user_nickname' => $row['user_nickname'],
                'like_count' => $row['like_count']
            ];
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Close the statement to free resources
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css" />
    <style>
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        section.carousel {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 75dvh;
        }

        .carousel__item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: #14181c;
            color: #fff;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .carousel__item.active {
            opacity: 1;
        }

        .carousel__item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: blur(2px) brightness(0.6);
        }

        div.carousel__front-text {
            position: absolute;
            z-index: 1;
            left: 0;
            bottom: 0;
            width: 40dvw;
            padding: 0 0 8rem 3rem;
        }

        h1.carousel__title {
            font-size: 5.5rem;
            font-weight: 700;
            margin-bottom: 5rem;
            line-height: 1.3;
        }

        a.carousel__cta {
            color: #fff;
            background-color: var(--color-button-primary);
            border: 2px solid var(--color-button-primary);
            border-radius: 0.5rem;
            padding: 1rem 3rem;
            text-decoration: none;
            transition: background-color 0.3s;
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
        <section class="carousel">
            <div class="carousel__item active">
                <img src="/echolog/assets/images/carousel-1.png" alt="Carousel Image 1" />
            </div>
            <div class="carousel__item">
                <img src="/echolog/assets/images/carousel-2.jpg" alt="Carousel Image 2" />
            </div>
            <div class="carousel__item">
                <img src="/echolog/assets/images/carousel-3.jpg" alt="Carousel Image 3" />
            </div>
            <div class="carousel__item">
                <img src="/echolog/assets/images/carousel-4.jpg" alt="Carousel Image 4" />
            </div>
            <div class="carousel__front-text">
                <h1 class="carousel__title">Track your games, share your thoughts, build your collection</h1>
                <a class="carousel__cta" href="/echolog/registration.php">Get Started - It's lit</a>
            </div>
        </section>
        <div class="container">
            <section class="popular-games mt-5">
                <p class="fs-2">Popular Games to Track</p>
                <?php if (empty($games)) : ?>
                    <p>No recommended games found.</p>
                <?php else : ?>
                    <div class="gallery">
                        <?php foreach ($populargames as $game) : ?>
                            <a href="/echolog/gamepage.php?game_id=<?= $game['game_id']; ?>" class="image-card" title="<?= $game['game_title']; ?>">
                                <img class="image-card__image" src="<?= $game['cover_image']; ?>" alt="<?= $game['game_title']; ?>" title="<?= $game['game_title']; ?>" />
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <section class="popular-reviews mt-5">
                <p class="fs-2">Most Popular Reviews</p>
                <?php if (empty($popularreviews)) : ?>
                    <p>No popular reviews available.</p>
                <?php else : ?>
                    <?php foreach ($popularreviews as $review) : ?>
                        <div class="review-card mb-3">
                            <div class="review-card__left">
                                <img class="review-card__game-image" src="<?= $review['game_cover'] ?>" alt="<?= $review['game_title'] ?>" />
                            </div>
                            <div class="review-card__right">
                                <p class="review-card__game-title fs-2">
                                    <?= $review['game_title'] ?>
                                </p>
                                <p class="review-card__adjoining-info">
                                    <span class="review-card__rating">
                                        Rating: <?= $review['user_rating'] ? $review['user_rating'] . '/5' : 'No rating' ?>
                                    </span>
                                </p>
                                <p class="review-card__text">
                                    <?= $review['review_text'] ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <section class="popular-collections">
                <p class="fs-2">Most Rated Collections</p>
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
                            <p class="collection-card__title fs-2 m-0">
                                <!--  -->
                                <?= $playlist['name'] ?>
                            </p>
                            <p class="collection-card__info m-0">
                                <span class="collection-card__username">
                                    <?= $playlist['user_nickname'] ?>
                                </span>
                                <span>|</span>
                                <span>
                                    <?= $playlist['like_count'] ?> likes
                                </span>
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <hr class="mt-5" />
            <p class="fs-3 text-center">...aaaand so much more</p>
            <p class="button px-3 text-center">
                <a href="/echolog/registration.php" class="button px-3 text-center">
                    Sign Up
                </a>
            </p>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
    <script>
        const carousel = document.querySelector('.carousel');
        const carouselItems = document.querySelectorAll('.carousel__item');

        let carouselItemIndex = 0;

        setInterval(() => {
            carouselItems[carouselItemIndex].classList.remove('active');
            carouselItemIndex = (carouselItemIndex + 1) % carouselItems.length;
            carouselItems[carouselItemIndex].classList.add('active');
        }, 5000);

        // document
        //     .querySelectorAll('.review-card__like-button')
        //     .forEach((likeButton, index) => {
        //         likeButton.addEventListener('click', (event) => {
        //             event.preventDefault();
        //             const likeCounts = document.querySelectorAll('.review-card__like-count');
        //             const likeIcon = likeButton.children[0];
        //             const likeCount = parseInt(likeCounts[index].textContent);
        //             if (likeIcon.classList.contains('liked')) {
        //                 likeIcon.classList.remove('liked');
        //                 likeIcon.src = '/echolog/assets/svgs/heart-outline.svg';
        //                 likeCounts[index].textContent = likeCount - 1;
        //                 return;
        //             }
        //             likeIcon.classList.add('liked');
        //             likeIcon.src = '/echolog/assets/svgs/heart-solid.svg';
        //             likeCounts[index].textContent = likeCount + 1;
        //         });
        //     });
    </script>
</body>

</html>