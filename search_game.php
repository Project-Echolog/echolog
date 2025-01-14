<?php
    include "dbconnection.php";

    if (isset($_GET['game'])) {
        $gamename = $_GET['game'];  
    } else {
        $gamename = '';
    }

    $stmt = $conn->prepare("SELECT game_title,game_id, description FROM Games WHERE game_title LIKE CONCAT('%', ?, '%')");
    $stmt->bind_param("s", $gamename);
    $stmt->execute();
    $result = $stmt->get_result();

    $games = [];

    $games = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $games[] = [
                'game_id' => $row['game_id'], // Ensure game_id is included
                'game_title' => htmlspecialchars($row['game_title'])
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($games);

    $stmt->close();
?>
