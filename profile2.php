<?php
//DONT USE IT, ITS FOR TESTING NOT WORKING
include "dbconnection.php";
include "navbar.php";
session_start();

// Regenerate session ID to prevent session fixation
//session_regenerate_id(true);

$logged_in_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $logged_in_user_id;
$is_own_profile = $logged_in_user_id === $profile_user_id;

// Ensure that the profile_user_id exists in the database
$stmt = $conn->prepare("SELECT user_nickname FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("User not found."); // Prevent further execution if the user doesn't exist
}

$row = $result->fetch_assoc();
$username = htmlspecialchars($row['user_nickname'], ENT_QUOTES, 'UTF-8');
echo "<h1>" . $username . "</h1>";

// Show the "Edit Profile" button if it's the logged-in user's profile
if ($is_own_profile) {
    echo "<a href='edit_profile.php?user_id=$logged_in_user_id' style='padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Edit Profile</a>";
}

// Fetch Wishlisted Games
try {
    $stmt = $conn->prepare("
        SELECT g.game_title
        FROM WISHLIST ws
        JOIN Games g ON ws.game_id = g.game_id
        WHERE ws.user_id = ?
    ");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h3>Games you wishlisted:</h3><ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['game_title'], ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>You haven't wishlisted any games yet.</p>";
    }
    $stmt->close();
} catch (Exception $e) {
    // Log error to file and show a generic message
    error_log($e->getMessage());
    echo "<p>Error fetching wishlisted games. Please try again later.</p>";
}

// Fetch Liked Games
try {
    $stmt = $conn->prepare("
        SELECT g.game_title
        FROM Likes_Games lg
        JOIN Games g ON lg.game_id = g.game_id
        WHERE lg.user_id = ?
    ");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h3>Games you liked:</h3><ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['game_title'], ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>You haven't liked any games yet.</p>";
    }
    $stmt->close();
} catch (Exception $e) {
    // Log error to file and show a generic message
    error_log($e->getMessage());
    echo "<p>Error fetching liked games. Please try again later.</p>";
}

// Fetch User's Playlists
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

    $playlists = [];
    while ($row = $result->fetch_assoc()) {
        $playlist_id = $row['playlist_id'];
        if (!isset($playlists[$playlist_id])) {
            $playlists[$playlist_id] = [
                'name' => htmlspecialchars($row['playlist_name'], ENT_QUOTES, 'UTF-8'),
                'description' => htmlspecialchars($row['playlist_description'], ENT_QUOTES, 'UTF-8'),
                'creator_id' => $row['creator_id'],
                'games' => [],
            ];
        }

        if (count($playlists[$playlist_id]['games']) < 4 && $row['cover_image']) {
            // Sanitize cover image URL
            $cover_image = htmlspecialchars($row['cover_image'], ENT_QUOTES, 'UTF-8');
            $playlists[$playlist_id]['games'][] = $cover_image;
        }
    }
    $stmt->close();

    if (!empty($playlists)) {
        echo "<h2>Your Playlists</h2>";
        foreach ($playlists as $playlist_id => $playlist) {
            echo "<div style='margin-bottom: 20px;'>";
            echo "<h3>" . $playlist['name'] . "</h3>";
            echo "<p>" . $playlist['description'] . "</p>";

            if (!empty($playlist['games'])) {
                echo "<div style='display: flex; gap: 10px;'>";
                foreach ($playlist['games'] as $cover_image) {
                    echo "<img src='" . $cover_image . "' alt='Game Cover' style='width: 100px; height: 100px; object-fit: cover;'>";
                }
                echo "</div>";
            } else {
                echo "<p>No games associated with this playlist.</p>";
            }

            // Display the "Edit" button if the logged-in user is the creator of the playlist
            if ($playlist['creator_id'] == $logged_in_user_id) {
                echo "<a href='playlist_edit.php?playlist_id=" . urlencode($playlist_id) . "'>Edit</a> | ";
            }

            echo "<a href='playlist_view.php?playlist_id=" . urlencode($playlist_id) . "'>View</a>";
            echo "</div>";
        }
    } else {
        echo "<p>You haven't created or liked any playlists yet.</p>";
    }
} catch (Exception $e) {
    // Log error to file and show a generic message
    error_log($e->getMessage());
    echo "<p>Error fetching playlists. Please try again later.</p>";
}

// Fetch User's Reviews
try {
    $stmt = $conn->prepare("
        SELECT r.game_id, g.game_title, r.review_text, r.user_id
        FROM REVIEWS r
        JOIN Games g ON r.game_id = g.game_id
        WHERE r.user_id = ?
    ");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h3>Your Reviews:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<h4>" . htmlspecialchars($row['game_title'], ENT_QUOTES, 'UTF-8') . "</h4>";
            echo "<p>" . htmlspecialchars($row['review_text'], ENT_QUOTES, 'UTF-8') . "</p>";
            if ($row['user_id'] == $logged_in_user_id) {
                echo "<a href='edit_rate.php?game_id=" . urlencode($row['game_id']) . "'>Edit Review</a>";
            }
        }
    } else {
        echo "<p>You haven't reviewed any games yet.</p>";
    }
    $stmt->close();
} catch (Exception $e) {
    // Log error to file and show a generic message
    error_log($e->getMessage());
    echo "<p>Error fetching reviews. Please try again later.</p>";
}
?>
