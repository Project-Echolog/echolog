<?php
include 'dbconnection.php';

if (isset($_GET['game'])) {
    $searchTerm = $_GET['game'];
} else {
    $searchTerm = '';
}

// Fetch games
$stmtGames = $conn->prepare("SELECT game_id, game_title FROM Games WHERE game_title LIKE CONCAT('%', ?, '%')");
$stmtGames->bind_param("s", $searchTerm);
$stmtGames->execute();
$resultGames = $stmtGames->get_result();

$results = [];

if ($resultGames->num_rows > 0) {
    while ($row = $resultGames->fetch_assoc()) {
        $results[] = [
            'type' => 'game',
            'id' => $row['game_id'],
            'title' => htmlspecialchars($row['game_title'])
        ];
    }
}
$stmtGames->close();

// Fetch playlists
$stmtPlaylists = $conn->prepare("SELECT playlist_id, playlist_name FROM Playlist WHERE playlist_name LIKE CONCAT('%', ?, '%')");
$stmtPlaylists->bind_param("s", $searchTerm);
$stmtPlaylists->execute();
$resultPlaylists = $stmtPlaylists->get_result();

if ($resultPlaylists->num_rows > 0) {
    while ($row = $resultPlaylists->fetch_assoc()) {
        $results[] = [
            'type' => 'playlist',
            'id' => $row['playlist_id'],
            'title' => htmlspecialchars($row['playlist_name'])
        ];
    }
}
$stmtPlaylists->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($results);
?>
