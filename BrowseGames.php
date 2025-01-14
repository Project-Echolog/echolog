<?php
include "dbconnection.php";

session_start();

// TODO: FIX PUBLISHER ERROR(DONE)

// Query all genres from the database
$stmt = $conn->prepare("SELECT genre FROM games");
$stmt->execute();
$result = $stmt->get_result();
$all_genres = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $genres = explode(",", $row['genre']);
        $genres = array_map('trim', $genres);
        $all_genres = array_merge($all_genres, $genres);
    }
    $unique_genres = array_unique($all_genres);
    sort($unique_genres);
}

// Build query conditions based on filters
$conditions = [];
$params = [];
$param_types = "";

// Handle genre filter
if (isset($_GET['genre']) && !empty($_GET['genre'])) {
    $genre_conditions = [];
    foreach ($_GET['genre'] as $genre) {
        $genre_conditions[] = "genre LIKE ?";
        $params[] = "%" . $genre . "%";
        $param_types .= "s";
    }
    if (!empty($genre_conditions)) {
        $conditions[] = "(" . implode(" AND ", $genre_conditions) . ")";
    }
}

// Handle developer filter
if (isset($_GET['Publisher']) && !empty($_GET['Publisher'])) {
    $conditions[] = "Publisher = ?";
    $params[] = $_GET['Publisher'];
    $param_types .= "s";
}

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $conditions[] = "game_title LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $param_types .= "s";
}

$query = "SELECT * FROM games";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$games = [];

if ($stmt->execute()) {
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-weight: 400;
            color: #ffffff;
            font-size: 25px;
            line-height: 29.83px;
        }

        h3,
        h1 {
            margin: 0;
        }

        .browse-title {
            font-size: 66px;
            font-weight: 500;
            line-height: 78.76px;
            margin-left: 18px;
            margin-top: 57px;
        }

        .search-bar {
            margin-top: 50px;
            margin-left: 23px;
            margin-bottom: 33px;
        }

        .search-bar>input {
            width: 360px;
            height: 52px;
            border-radius: 3px;
            background-color: #555555;
            font-size: 20px;
            line-height: 23.87px;
            border: 3px solid #555555;
        }

        .search-bar ::placeholder {
            color: #d9d9d9;
        }

        .filters h3 {
            margin-top: 20px;
            margin-left: 10px;
            margin-bottom: 7px;
            font-size: 26px;
            font-weight: 500;
            line-height: 31.03px;
        }

        .filters {
            padding-left: 11px;
            border-radius: 8px;
            color: #fff;
            width: 291px;
        }

        .filter-list {
            list-style: none;
            padding: 8px 28px 8px 18px;
            margin: 0;
        }

        .filter-list li {
            margin-bottom: 24px;
        }

        .filter-list label {
            display: flex;
            justify-content: space-between;
            cursor: pointer;
            font-size: 20px;
        }

        .filter-list input[type='checkbox'] {
            width: 24px;
            height: 24px;
            cursor: pointer;
            background-color: white;
            border: 2px solid #000;
            appearance: none;
            position: relative;
        }

        .filter-list input[type='checkbox']:checked::before {
            content: '✔';
            color: black;
            font-size: 18px;
            position: absolute;
            left: 4px;
        }

        .carousel-two-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            /* Her satırda otomatik olarak sığacak kadar öğe */
            gap: 16px;
            /* Grid öğeleri arasındaki boşluk */
            padding: 5rem;
        }

        .card-popular {
            /* Kart stilini burada tanımlayın */
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
        }

        .card-popular:hover {
            border: 2px solid #00c965;
        }

        .card-popular img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }


        .browse-games {
            display: flex;
            flex-direction: row;
        }

        .all-game-list {
            display: flex;
            flex-direction: column;
        }

        .next-button {
            width: 152px;
            height: 41px;
            border-radius: 3px;
            border: 1px solid #66788a;
            background-color: #66788a;
            color: #ffffff;
            margin-left: 975px;
            font-size: 21px;
            font-weight: 500;
            line-height: 25.06px;
        }

        .btn {
            width: 152px;
            height: 41px;
            border-radius: 3px;
            border: 1px solid #66788a;
            background-color: #66788a;
            color: #ffffff;
            font-size: 21px;
            font-weight: 500;
            line-height: 25.06px;
            margin-left: 23px;
        }
    </style>
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/echolog/">
            <img src="/echolog/assets/svgs/logo.svg" alt="My Website Logo" />
        </a>
        <nav class="header__nav">
            <a class='header__nav-link active' href='/echolog/BrowseGames.php'>Games</a>
            <a class='header__nav-link' href='/echolog/BrowsePlaylist.php'>Collections</a>
            <a class='header__nav-link' href='/echolog/profile.php'>Profile</a>
        </nav>
        <a class="header__login" href="/echolog/login.php">Login</a>
    </header>
    <main>
        <div class="container">
            <h1 class="browse-title">Browse Games</h1>
            <section class="browse-games">
                <form method="GET" action="">
                    <div class="flex flex-column justify-start align-start">
                        <section class="search-bar">
                            <input type="text" placeholder="Search Game" class="search-input" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        </section>
                        <div class="filters">
                            <h3>Filters</h3>
                            <ul class="filter-list">
                                <?php foreach ($unique_genres as $genre) : ?>
                                    <li>
                                        <label>
                                            <?= htmlspecialchars($genre) ?>
                                            <input type="checkbox" name="genre[]" value="<?= htmlspecialchars($genre) ?>" <?= (isset($_GET['genre']) && in_array($genre, $_GET['genre'])) ? 'checked' : '' ?>>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <button type="submit" class="btn">Apply Filters</button>
                    </div>
                </form>
                <div class="all-game-list">
                    <!-- <section class="search-bar">
                        <input type="text" placeholder="Search Game" class="search-input">
                    </section> -->
                    <section class="game-images">
                        <div class="carousel-two">
                            <div class="carousel-two-items">
                                <?php foreach ($games as $game) : ?>
                                    <a class="card-popular" href="gamepage.php?game_id=<?= urlencode($game['game_id']) ?>"
                                        title="<?= htmlspecialchars($game['game_title']) ?>">
                                        <img src="<?= htmlspecialchars($game['cover_image']) ?>" alt="<?= htmlspecialchars($game['game_title']) ?>">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>

</html>