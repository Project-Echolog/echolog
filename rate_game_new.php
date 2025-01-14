<?php
include "dbconnection.php";
session_start();

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user data from the session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Ensure a valid game_id is provided
if (!isset($_GET['game_id'])) {
    die("Error: Game ID not provided.");
}

$game_id = intval($_GET['game_id']);

// Fetch game details
$stmt = $conn->prepare("SELECT game_title, cover_image FROM Games WHERE game_id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $game = $result->fetch_assoc();
    $game_title = htmlspecialchars($game['game_title']);
} else {
    echo "<p>No game found.</p>";
    exit;
}

$stmt->close();

// Check if the user has already reviewed or rated this game
$stmt = $conn->prepare("SELECT r.review_text, rat.rating 
                            FROM Reviews r 
                            LEFT JOIN Ratings rat ON r.game_id = rat.game_id AND r.user_id = rat.user_id 
                            WHERE r.user_id = ? AND r.game_id = ?");
$stmt->bind_param("ii", $user_id, $game_id);
$stmt->execute();
$result = $stmt->get_result();

$user_review = null;
$user_rating = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_review = htmlspecialchars($row['review_text']);
    $user_rating = (int)$row['rating'];
}

$stmt->close();

// Process the review submission if the user hasn't already reviewed this game
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$user_review) {
    // Sanitize and validate inputs
    $review = filter_input(INPUT_POST, "review", FILTER_SANITIZE_SPECIAL_CHARS);
    $rating = filter_input(INPUT_POST, "rating", FILTER_VALIDATE_INT);
    $game_id = filter_input(INPUT_POST, "game_id", FILTER_VALIDATE_INT);

    if (empty($review) || !$rating || !$game_id) {
        echo "<p style='color: red;'>Invalid input. Please try again.</p>";
    } else {
        // Start a transaction to ensure both review and rating are inserted atomically
        $conn->begin_transaction();

        try {
            // Insert the review into the Reviews table
            $stmt = $conn->prepare("INSERT INTO Reviews (user_id, game_id, review_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $game_id, $review);

            if (!$stmt->execute()) {
                throw new Exception("Error inserting review: " . $stmt->error);
            }

            $stmt->close();

            // Insert the rating into the Ratings table
            $stmt = $conn->prepare("INSERT INTO Ratings (user_id, game_id, rating) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $game_id, $rating);

            if (!$stmt->execute()) {
                throw new Exception("Error inserting rating: " . $stmt->error);
            }

            $stmt->close();
            $conn->commit();

            // Redirect to the game page to avoid resubmission of the form
            header("Location: gamepage.php?game_id=" . $game_id);
            exit;
        } catch (Exception $e) {
            // Rollback in case of an error
            $conn->rollback();
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible=IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h3 {
            margin-top: 0;
        }

        .submissionfield {
            width: 100%;
            height: 150px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 4px;
            resize: vertical;
        }

        .slider-label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        .slider {
            width: 100%;
            margin: 10px 0;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            background-color: #0078ff;
            color: #fff;
            cursor: pointer;
            text-align: center;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #005fcc;
        }

        .message {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3>Rate the game: <?= htmlspecialchars($game_title) ?></h3>
        <?php if ($user_review): ?>
            <div class="message">
                <p>You have already submitted a review and rating for this game:</p>
                <p><strong>Review:</strong> <?= htmlspecialchars($user_review) ?></p>
                <p><strong>Rating:</strong> <?= htmlspecialchars($user_rating) ?>/5</p>
            </div>
        <?php else: ?>
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"] . "?game_id=" . $game_id); ?>" method="POST">
                <label for="review">Your review:</label><br>
                <textarea name="review" class="submissionfield" required></textarea><br>
                <label for="rating" class="slider-label">Rate the game (1 to 5):</label><br>
                <input type="range" name="rating" class="slider" min="1" max="5" step="1" required><br>
                <input type="hidden" name="game_id" value="<?= htmlspecialchars($game_id) ?>">
                <button type="submit" class="btn">Submit Review</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>