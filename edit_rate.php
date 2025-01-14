<?php 
    // Delete for Review implemented soxsun bu muellime gozune
    include "dbconnection.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Get user data
    $user_id = $_SESSION['user_id'];

    if (!isset($_GET['game_id'])) {
        die("Error: Game ID is not provided.");
    }
    
    $game_id = $_GET['game_id'];
    
    // Ensure game_id is a valid number
    if (!is_numeric($game_id)) {
        die("Error: Invalid Game ID.");
    }

    // Fetch game details
    $stmt = $conn->prepare("SELECT game_title, cover_image FROM Games WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $game = $result->fetch_assoc();
        $game_title = htmlspecialchars($game['game_title']);
        echo "<h3>Rate the game: $game_title</h3>";
    } else {
        echo "<p>No game found.</p>";
        exit;
    }

    $stmt->close();

    // Check if user has already reviewed this game
    $stmt = $conn->prepare("
        SELECT r.review_text, rat.rating 
        FROM Reviews r 
        LEFT JOIN Ratings rat ON r.game_id = rat.game_id AND r.user_id = rat.user_id 
        WHERE r.user_id = ? AND r.game_id = ?
    ");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review</title>
    <style>
        .submissionfield { width: 450px; height: 150px; border: 1px solid #999999; padding: 5px; }
        .slider-label { font-weight: bold; }
    </style>
</head>
<body>
    <?php if ($user_review): ?>
        <form action="" method="POST">
            <label for="review">Edit your review:</label><br>
            <textarea name="review" class="submissionfield" required><?= $user_review ?></textarea><br>
            <label for="rating" class="slider-label">Update your rating (1 to 5):</label><br>
            <input type="range" name="rating" min="1" max="5" step="1" value="<?= $user_rating ?>" required><br>
            <input type="hidden" name="game_id" value="<?= $game_id ?>">
            <input type="submit" name="submit" value="Update Review">
        </form>

        <form action="" method="POST">
           <input type="hidden" name="game_id" value="<?= $game_id ?>">
           <input type="submit" name="delete" value="Delete Review" style="color: red;">
        </form>
    <?php else: ?>
        <p>You haven't submitted a review for this game yet.</p>
    <?php endif; ?>
</body>
</html>

<?php 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $game_id = filter_input(INPUT_POST, "game_id", FILTER_VALIDATE_INT);

        if (isset($_POST['submit'])) {
            $review = filter_input(INPUT_POST, "review", FILTER_SANITIZE_SPECIAL_CHARS);
            $rating = filter_input(INPUT_POST, "rating", FILTER_VALIDATE_INT);

            if (empty($review) || !$rating || !$game_id) {
                echo "<p style='color: red;'>Invalid input. Please try again.</p>";
            } else {
                // Start a transaction
                $conn->begin_transaction();

                try {
                    $stmt = $conn->prepare("UPDATE Reviews SET review_text = ? WHERE user_id = ? AND game_id = ?");
                    $stmt->bind_param("sii", $review, $user_id, $game_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error updating review: " . $stmt->error);
                    }

                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE Ratings SET rating = ? WHERE user_id = ? AND game_id = ?");
                    $stmt->bind_param("iii", $rating, $user_id, $game_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error updating rating: " . $stmt->error);
                    }

                    $conn->commit();
                    echo "<p style='color: green'>Review and rating updated successfully!</p>";
                } catch (Exception $e) {
                    $conn->rollback();
                    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
                }
            }
        } elseif (isset($_POST['delete'])) {
            try {
                $conn->begin_transaction();

                $stmt = $conn->prepare("
                    DELETE rv, rt 
                    FROM Reviews rv
                    LEFT JOIN Ratings rt ON rv.game_id = rt.game_id AND rv.user_id = rt.user_id
                    WHERE rv.user_id = ? AND rv.game_id = ?
                ");
                $stmt->bind_param("ii", $user_id, $game_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error deleting review: " . $stmt->error);
                }

                $conn->commit();
                echo "<p style='color: green;'>Review and rating deleted successfully!</p>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            }
        }
    }

    $conn->close();
?>
