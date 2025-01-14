<?php
include "dbconnection.php";
// include "navbar.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Playlist | Echolog</title>
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
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 5px 0;
            background: #f9f9f9;
            color: #111;
            padding: 10px;
            border: 1px solid #ddd;
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
    </script>
</head>

<body>
    <h1>Create a Playlist</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return submitPlaylist();">
        <label for="playlist-name">Playlist Name</label>
        <input type="text" id="playlist-name" name="playlist_name" required>

        <label for="playlist-description">Description</label>
        <textarea id="playlist-description" name="playlist_desc" rows="4" required></textarea>

        <label for="game-search">Search Games</label>
        <input type="text" id="game-search" oninput="searchGames()" autocomplete="off">
        <div id="game-dropdown" class="game-list"></div>

        <h3>Selected Games:</h3>
        <ul id="game-list"></ul>

        <input type="hidden" id="game-ids" name="games">
        <button type="submit">Create Playlist</button>
    </form>

    <?php
    // Thinking that facebook was written on php, i am amazed what they could build out of this
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_id = $_SESSION['user_id'];
        $playlist_name = $_POST['playlist_name'];  // Don't use htmlspecialchars for input variables.
        $playlist_description = $_POST['playlist_desc']; // Avoid htmlspecialchars here as well

        $game_ids = json_decode($_POST['games'], true);

        if (!$playlist_name || !$playlist_description || empty($game_ids)) {
            die('Error: Missing required fields.');
        }


        $stmt = $conn->prepare("INSERT INTO Playlist (playlist_name, playlist_description, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $playlist_name, $playlist_description, $user_id);

        // Tonight the music seem so loud
        // I wish we could lose this crowd
        // Maybeeee its better this way
        // 
        if ($stmt->execute()) {
            $playlist_id = $conn->insert_id;

            $stmt_games = $conn->prepare("INSERT INTO Playlist_Games (playlist_id, game_id) VALUES (?, ?)");
            foreach ($game_ids as $game_id) {
                $stmt_games->bind_param("ii", $playlist_id, $game_id);
                if (!$stmt_games->execute()) {
                    echo "Error inserting game ID $game_id: " . $stmt_games->error . "<br>";
                }
            }
            echo "<p>Playlist and games added successfully!</p>";
            header("Location: playlist_new.php?success=1");
            exit;
        } else {
            echo "<p>Error inserting playlist: " . $stmt->error . "</p>";
        }

        $stmt->close();
        $conn->close();
    }
    ?>

    <?php
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        echo "<p style='color: green;'>Playlist and games added successfully!</p>";
        header("Location: playlist_new.php");
        exit;
    }
    ?>

</body>

</html>