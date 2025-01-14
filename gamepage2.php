<?php
include "dbconnection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 1;

// Fetch game details
$stmt = $conn->prepare("SELECT game_title, genre, like_count, platform, publisher, released_date FROM Games WHERE game_id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$game_result = $stmt->get_result();
$game = $game_result->fetch_assoc();
$stmt->close();

// Fetch reviews for the game
$review_stmt = $conn->prepare("SELECT reviews.review_id, reviews.review_text, reviews.like_count, users.username FROM reviews JOIN users ON reviews.user_id = users.user_id WHERE reviews.game_id = ?");
$review_stmt->bind_param("i", $game_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();
$reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
$review_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['game_title']); ?></title>
    <style>
        .like-btn {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        .liked {
            font-weight: bold;
            color: red;
        }

        .review {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
    <script>
        async function toggleLike(reviewId) {
            try {
                const response = await fetch('like_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ review_id: reviewId }),
                });

                const data = await response.json();

                if (data.success) {
                    const likeBtn = document.getElementById(`like-btn-${reviewId}`);
                    const likeCount = document.getElementById(`like-count-${reviewId}`);

                    likeBtn.classList.toggle('liked', data.liked);
                    likeCount.textContent = data.like_count;
                } else {
                    alert(data.message || 'An error occurred.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while liking the review.');
            }
        }
    </script>
</head>
<body>
    <h1><?php echo htmlspecialchars($game['game_title']); ?></h1>
    <p>Genre: <?php echo htmlspecialchars($game['genre']); ?></p>
    <p>Platform: <?php echo htmlspecialchars($game['platform']); ?></p>
    <p>Publisher: <?php echo htmlspecialchars($game['publisher']); ?></p>
    <p>Release Date: <?php echo htmlspecialchars($game['released_date']); ?></p>

    <h2>Reviews</h2>
    <?php if (!empty($reviews)) { ?>
        <?php foreach ($reviews as $review) { ?>
            <div class="review">
                <p><strong><?php echo htmlspecialchars($review['username']); ?>:</strong></p>
                <p><?php echo htmlspecialchars($review['review_text']); ?></p>
                <p>
                    <span id="like-count-<?php echo $review['review_id']; ?>">
                        <?php echo $review['like_count']; ?>
                    </span>
                    <span id="like-btn-<?php echo $review['review_id']; ?>" class="like-btn <?php echo in_array($review['review_id'], $_SESSION['liked_reviews'] ?? []) ? 'liked' : ''; ?>" onclick="toggleLike(<?php echo $review['review_id']; ?>)">
                        Like
                    </span>
                </p>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p>No reviews available.</p>
    <?php } ?>
</body>
</html>
