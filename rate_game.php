// TODO:
// Fix and sanitize code, user can only create one comment, if comment exist redirect them to edt password_get_info
// Creat game detailed page
// OLD NOT USE IT

<?php 
    include "dbconnection.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // Get user data
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
?>

<?php 
    $game_id = 10; // Assuming this is dynamic or passed via GET/POST

    // Prepare and execute query to fetch game details
    $stmt = $conn->prepare("SELECT game_title, cover_image FROM Games WHERE game_id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<h3>" . "Rate the: " . htmlspecialchars($row['game_title']) . "</h3>";
        }
    } else {
        echo "<p>No game found.</p>";
    }

    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review</title>
    <style>
        .submissionfield { width: 450px; height: 150px; border: 1px solid #999999; padding: 5px; }
    </style>
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        Your review:<br>
        <textarea name="Review" class="submissionfield" required></textarea><br>
        <input type="submit" name="submit" value="Submit Review">
    </form>
</body>
</html>

<?php 
    // Process the review submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize input to prevent XSS
        $review = filter_input(INPUT_POST, "Review", FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($review)) {
            echo "Please enter a review.";
        } else {
            // Insert the review into the database
            $stmt = $conn->prepare("INSERT INTO REVIEWS (user_id, game_id, review_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $game_id, $review);

            if ($stmt->execute()) {
                echo "<p style='color: green'>Review submitted successfully!</p>";
            } else {
                echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
            }

            $stmt->close();
        }
    }

    mysqli_close($conn);
?>
