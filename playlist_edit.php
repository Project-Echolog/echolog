<?php
//Delete Playlist implemented, soxsun gozune muellime
include "dbconnection.php";

session_start();

// Ensuring that user is loggined 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ensure playlist_id is set in GET
if (!isset($_GET['playlist_id'])) {
    die("Error: Playlist ID not provided.");
}

$playlist_id = $_GET['playlist_id'];

// To make sure that every user only can change thier own Playlist
$stmt = $conn->prepare("SELECT playlist_name, playlist_description FROM Playlist WHERE playlist_id = ? AND user_id = ?");
$stmt->bind_param("ii", $playlist_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$playlist = $result->fetch_assoc();

if (!$playlist) {
    die("Error: Playlist not found or you do not have permission to edit it.");
}

$playlist_name = $playlist['playlist_name'];
$playlist_description = $playlist['playlist_description'];

// BABY BYE BYE BYE
// IT AINT NO LIE
// DONT WANNA BE FOOL FOR YOU
// Deadpool Dance hits 
$stmt_games = $conn->prepare("
    SELECT g.game_id, g.game_title 
    FROM Playlist_Games pg 
    JOIN Games g ON pg.game_id = g.game_id 
    WHERE pg.playlist_id = ?
");
$stmt_games->bind_param("i", $playlist_id);
$stmt_games->execute();
$result_games = $stmt_games->get_result();

$games = [];
while ($game = $result_games->fetch_assoc()) {
    $games[] = $game;
}

// echo "<h1>";
// print_r($games);
// echo "</h1>";

$stmt->close();
$stmt_games->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Playlist | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            margin: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input,
        textarea,
        button {
            width: 100%;
            margin-top: 5px;
            padding: 10px;
            border: 1px solid #2a3b4d;
            border-radius: 5px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 5px 0;
            background: #2a3b4d;
            padding: 10px;
            border: 1px solid #2a3b4d;
            border-radius: 5px;
        }

        .game-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .game-item {
            cursor: pointer;
            padding: 5px;
            background: #2a3b4d;
            margin: 5px 0;
            border-radius: 3px;
        }

        .game-item:hover {
            background: #3a4b5d;
        }

        input,
        textarea {
            background-color: #555555;
            color: #eeeeee;
        }
    </style>
    <script>
        async function searchGames() {
            const query = document.getElementById('game-search').value.trim();
            if (!query) return;

            const response = await fetch(`search_game.php?game=${encodeURIComponent(query)}`);
            const games = await response.json();

            const dropdown = document.getElementById('game-dropdown');
            dropdown.innerHTML = '';
            games.forEach(game => {
                const option = document.createElement('div');
                option.className = 'game-item';
                option.textContent = game.game_title;
                option.onclick = () => addGameToList(game);
                dropdown.appendChild(option);
            });
        }

        function addGameToList(game) {
            const list = document.getElementById('game-list');
            if ([...list.children].some(item => item.dataset.gameId == game.game_id)) {
                alert("Game already added!");
                return;
            }

            const item = document.createElement('li');
            item.textContent = `${game.game_title} (ID: ${game.game_id})`;
            item.dataset.gameId = game.game_id;
            item.onclick = () => list.removeChild(item);
            list.appendChild(item);
        }

        function submitPlaylist() {
            const name = document.getElementById('playlist-name').value.trim();
            const description = document.getElementById('playlist-description').value.trim();
            const games = Array.from(document.getElementById('game-list').children).map(item => item.dataset.gameId);

            if (!name || !description || games.length === 0) {
                alert('Please fill out all fields and add at least one game.');
                return false;
            }

            document.getElementById('game-ids').value = JSON.stringify(games);
            return true;
        }

        // YOU MAY HATE ME 
        // BUT IT AINT NO LIE
        window.onload = function() {
            const games = <?php echo json_encode($games); ?>;
            const gameList = document.getElementById('game-list');

            // I just wanna tell you i had enough
            gameList.innerHTML = '';

            // Add each game to the list
            games.forEach(game => {
                const item = document.createElement('li');
                item.textContent = `Game ID: ${game.game_id}, Game Name: ${game.game_title}`;
                item.dataset.gameId = game.game_id;
                item.onclick = function() {
                    gameList.removeChild(item);
                };
                gameList.appendChild(item);
            });
        }
    </script>
</head>

<body>
    <h1>Edit Playlist</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?playlist_id=<?php echo $playlist_id; ?>" method="POST" onsubmit="return submitPlaylist();">
        <label for="playlist-name">Playlist Name</label>
        <input type="text" id="playlist-name" name="playlist_name" value="<?php echo htmlspecialchars($playlist_name); ?>" required>

        <label for="playlist-description">Description</label>
        <textarea id="playlist-description" name="playlist_desc" rows="4" required><?php echo htmlspecialchars($playlist_description); ?></textarea>

        <label for="game-search">Search Games</label>
        <input type="text" id="game-search" oninput="searchGames()" autocomplete="off">
        <div id="game-dropdown" class="game-list"></div>

        <h3>Selected Games:</h3>
        <ul id="game-list"></ul>

        <input type="hidden" id="game-ids" name="games">
        <button type="submit">Update Playlist</button>
    </form>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?playlist_id=<?php echo $playlist_id; ?>" method="POST" onsubmit="return submitPlaylist();">
        <input type="hidden" name="playlist_id" value="<?= $playlist_id ?>">
        <input type="submit" name="delete" value="Delete Playlist" style="color: red;">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['delete'])) {
            // Deleting the playlist
            try {
                $conn->begin_transaction();

                // Delete from Playlist_Games first to maintain referential integrity
                $stmt_clear = $conn->prepare("DELETE FROM Playlist_Games WHERE playlist_id = ?");
                $stmt_clear->bind_param("i", $playlist_id);
                $stmt_clear->execute();
                $stmt_clear->close();

                // Delete the playlist itself
                $stmt_delete = $conn->prepare("DELETE FROM Playlist WHERE playlist_id = ? AND user_id = ?");
                $stmt_delete->bind_param("ii", $playlist_id, $user_id);
                $stmt_delete->execute();

                if ($stmt_delete->affected_rows > 0) {
                    $conn->commit();
                    echo "<p style='color: green;'>Playlist deleted successfully!</p>";
                    header("Location: profile.php");
                    exit;
                } else {
                    throw new Exception("Playlist not found or you don't have permission to delete it.");
                }
            } catch (Exception $e) {
                $conn->rollback();
                echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            }
        } else {
            // Editing the playlist
            $playlist_name = $_POST['playlist_name'];  // Don't use htmlspecialchars for input variables.
            $playlist_description = $_POST['playlist_desc']; // Avoid htmlspecialchars here as well

            $game_ids = json_decode($_POST['games'], true);

            if (!$playlist_name || !$playlist_description || empty($game_ids)) {
                die('<p style="color: red;">Error: Missing required fields.</p>');
            }

            try {
                $conn->begin_transaction();

                // Update playlist details
                $stmt = $conn->prepare("UPDATE Playlist SET playlist_name = ?, playlist_description = ? WHERE playlist_id = ? AND user_id = ?");
                $stmt->bind_param("ssii", $playlist_name, $playlist_description, $playlist_id, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating playlist: " . $stmt->error);
                }

                // Clear existing game associations
                $stmt_clear = $conn->prepare("DELETE FROM Playlist_Games WHERE playlist_id = ?");
                $stmt_clear->bind_param("i", $playlist_id);
                $stmt_clear->execute();
                $stmt_clear->close();

                // Insert new game associations
                $stmt_games = $conn->prepare("INSERT INTO Playlist_Games (playlist_id, game_id) VALUES (?, ?)");
                foreach ($game_ids as $game_id) {
                    $stmt_games->bind_param("ii", $playlist_id, $game_id);
                    if (!$stmt_games->execute()) {
                        throw new Exception("Error inserting game ID $game_id: " . $stmt_games->error);
                    }
                }
                $stmt_games->close();

                $conn->commit();
                echo "<p style='color: green;'>Playlist updated successfully!</p>";
                // header("Location: playlist_edit.php?playlist_id=$playlist_id&success=1");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
            }
        }
    }

    // Success message on redirect
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo "<p style='color: green;'>Playlist updated successfully!</p>";
    }
    ?>

</body>

</html>