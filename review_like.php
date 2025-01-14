<?php
include "dbconnection.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['review_id'])) {
    $review_id = $input['review_id'];

    // Check if user already liked the review
    $check_stmt = $conn->prepare("SELECT * FROM review_likes WHERE user_id = ? AND review_id = ?");
    $check_stmt->bind_param("ii", $user_id, $review_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // User already liked, remove the like
        $delete_stmt = $conn->prepare("DELETE FROM review_likes WHERE user_id = ? AND review_id = ?");
        $delete_stmt->bind_param("ii", $user_id, $review_id);
        $delete_stmt->execute();

        // Decrease the like count
        $update_stmt = $conn->prepare("UPDATE Reviews SET like_count = like_count - 1 WHERE review_id = ?");
        $update_stmt->bind_param("i", $review_id);
        $update_stmt->execute();

        $update_stmt->close();
        $delete_stmt->close();

        // Fetch new like count
        $count_stmt = $conn->prepare("SELECT like_count FROM Reviews WHERE review_id = ?");
        $count_stmt->bind_param("i", $review_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $new_like_count = $count_result->fetch_assoc()['like_count'];

        echo json_encode(['success' => true, 'new_like_count' => $new_like_count]);
    } else {
        // User hasn't liked, add the like
        $insert_stmt = $conn->prepare("INSERT INTO review_likes (user_id, review_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $user_id, $review_id);
        $insert_stmt->execute();

        // Increase the like count
        $update_stmt = $conn->prepare("UPDATE Reviews SET like_count = like_count + 1 WHERE review_id = ?");
        $update_stmt->bind_param("i", $review_id);
        $update_stmt->execute();

        $update_stmt->close();
        $insert_stmt->close();

        // Fetch new like count
        $count_stmt = $conn->prepare("SELECT like_count FROM Reviews WHERE review_id = ?");
        $count_stmt->bind_param("i", $review_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $new_like_count = $count_result->fetch_assoc()['like_count'];

        echo json_encode(['success' => true, 'new_like_count' => $new_like_count]);
    }

    $count_stmt->close();
    $check_stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
$conn->close();
