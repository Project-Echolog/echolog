<?php
include "dbconnection.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
}

$stmt = $conn->prepare("SELECT user_id, password FROM Users WHERE user_nickname = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {

        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        echo "<p>Login successful! Welcome, $username.</p>";
        header("Location: profile.php");
    } else {
        echo "<p style='color:red;'>Incorrect username or password. Please try again.</p>";
    }
}
// else {
//     echo "<p style='color:red;'>No user found with that username. Please register first.</p>";
// }
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Echolog</title>
    <meta name="description" content="Track your games, share your thoughts, build your collection">
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 90dvh;
        }

        .popup {
            background-image: linear-gradient(180deg, #232526, #232526, #343434);
            border-radius: 10px;
            border: 2px solid #303033;
            padding: 80px 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            color: #ffffff;
            margin: 40px 0;
        }

        .popup h1 {
            margin: 0;
            margin-bottom: 20px;
            font-size: 32px;
        }

        .popup input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background-color: #353539;
            color: #ffffff;
            font-size: 14px;
        }

        .popup button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #0078ff;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
        }

        .popup button:hover {
            background-color: #005fcc;
        }

        .popup a {
            color: #0078ff;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin-top: 15px;
        }

        .popup a:hover {
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background-color: #444444;
            margin: 20px 0;
            border: none;
        }

        /*! patch for login and register pages */
        footer.footer {
            margin-top: 0 !important;
        }
    </style>
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/echolog/">
            <img src="/echolog/assets/svgs/logo.svg" alt="My Website Logo" />
        </a>
        <nav class="header__nav">
            <a class='header__nav-link' href='/echolog/BrowseGames.php'>Games</a>
            <a class='header__nav-link' href='/echolog/BrowsePlaylist.php'>Collections</a>
            <a class='header__nav-link' href='/echolog/profile.php'>Profile</a>
        </nav>
        <a class="header__login" href="/echolog/login.php">Login</a>
    </header>
    <main>
        <div class="container">
            <form class="popup" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <h1>SIGN IN</h1>
                <input type="username" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Continue</button>
                <hr class="divider">
                <a href="/echolog/registration.php">Create Account</a>
            </form>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>