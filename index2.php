<?php 
    include "dbconnection.php";
    include "navbar.php";
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Get user data from the session
    $user_id = $_SESSION['user_id'];
    $username = htmlspecialchars($_SESSION['username']); // Use htmlspecialchars for security

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

    // Close the statement to free resources
    $stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Recommended Games</title>
</head>
<body>
    <h1>Welcome, <?= $username ?></h1>
    <h3>Must Have Games</h3>

    <?php if (!empty($games)): ?>
        <ul>
            <?php foreach ($games as $game): ?>
                <li>
                    <a href="gamepage.php?game_id=<?= urlencode($game['game_id']) ?>">
                        <h4><?= htmlspecialchars($game['game_title']) ?></h4>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No recommended games found.</p>
    <?php endif; ?>

    <h3>Popular Games</h3>
    <?php if (!empty($populargames)): ?>
        <ul>
            <?php foreach ($populargames as $pgame): ?>
                <li>
                    <a href="gamepage.php?game_id=<?= urlencode($pgame['game_id']) ?>">
                        <h4><?= htmlspecialchars($pgame['game_title']) ?></h4>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No popular games to display right now.</p>
    <?php endif; ?>

    <h3>Newest Games</h3>
    <?php if (!empty($newgames)): ?>
        <ul>
            <?php foreach ($newgames as $ngame): ?>
                <li>
                    <a href="gamepage.php?game_id=<?= urlencode($ngame['game_id']) ?>">
                        <h4><?= htmlspecialchars($ngame['game_title']) ?></h4>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No new games to display at the moment.</p>
    <?php endif; ?>

    <h3>Popular Reviews</h3>
    <?php if (!empty($popularreviews)): ?>
        <ul>
            <?php foreach ($popularreviews as $preview): ?>
                <li>
                    <a href="gamepage.php?game_id=<?= urlencode($preview['game_id']) ?>">
                        <h4>Title: <?= htmlspecialchars($preview['game_title']) ?></h4>
                        <p>By: <?= htmlspecialchars($preview['user_nickname']) ?></p>
                        <p>Rating: <?= htmlspecialchars($preview['user_rating']) ?></p>
                        <p>Review: <?= htmlspecialchars($preview['review_text']) ?></p>
                        <p>Likes: <?= htmlspecialchars($preview['like_count']) ?></p>
                    </a>
                    <form method="POST" class="like-form">
                        <input type="hidden" name="like" value="1">
                        <input type="hidden" name="review_id" value="<?= $preview['review_id']; ?>">
                        <button type="submit">Like/Unlike (<span class="like-count"><?= $preview['like_count']; ?></span>)</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No popular reviews available.</p>
    <?php endif; ?>

</body>
</html>
