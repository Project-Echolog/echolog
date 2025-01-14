<?php
include "dbconnection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    // Error handling array
    $errors = [];

    // Check if username is empty
    if (empty($username)) {
        $errors[] = "Please enter a username.";
    }

    // Check if email is empty and validate format
    if (empty($email)) {
        $errors[] = "Enter email, we won't spam it.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Check if password is empty and meets criteria
    if (empty($password)) {
        $errors[] = "Please enter a password. It must be a strong one.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/[A-Za-z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[\W_]/", $password)) {
        $errors[] = "Password must contain at least one letter, one number, and one special character.";
    }

    // If there are errors, display them
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    } else {
        // Check if username or email already exists in the database
        $check_stmt = $conn->prepare("SELECT user_id FROM Users WHERE email = ? OR user_nickname = ?");
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<p style='color: red;'>Username or email already exists. Please try another.</p>";
        } else {
            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user data into the database
            $stmt = $conn->prepare("INSERT INTO Users (user_nickname, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Registration successful! Welcome to the EchoLog!</p>";
                // Redirect the user to the login page after successful registration
                header("Location: login.php");
                exit;
            } else {
                echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
            }

            $stmt->close();
        }
        $check_stmt->close();
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - EchoLog</title>
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
                <h1>REGISTER</h1>
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Register</button>
                <hr class="divider">
                <a href="/echolog/login.php">Already have an account? Login here</a>
            </form>
        </div>
    </main>
    <footer class="footer mt-5">
        <p class="footer__text">&copy; 2025 Echolog. All rights reserved.</p>
    </footer>
</body>

</html>