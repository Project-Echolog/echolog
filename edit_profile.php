<?php
// include "dbconnection.php";

// session_start();

// // Redirect to login if the user is not logged in
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

// $user_id = $_SESSION['user_id'];
// $username = htmlspecialchars($_SESSION['username']);

// // Fetch current user data (nickname and profile image URL)
// $stmt = $conn->prepare("SELECT user_nickname, profile_image FROM Users WHERE user_id = ?");
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $stmt->store_result();
// $stmt->bind_result($user_nickname, $profile_image);
// $stmt->fetch();

// // Handle form submission to update profile
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     if (isset($_POST['submit'])) {
//         $new_nickname = htmlspecialchars($_POST['nickname']);
//         $new_profile_image = htmlspecialchars($_POST['profile_image']);

//         // Check if the new nickname is already taken (excluding the current user)
//         $stmtCheck = $conn->prepare("SELECT user_id FROM Users WHERE user_nickname = ? AND user_id != ?");
//         $stmtCheck->bind_param("si", $new_nickname, $user_id);
//         $stmtCheck->execute();
//         $stmtCheck->store_result();

//         if ($stmtCheck->num_rows > 0) {
//             // Nickname is already taken
//             echo "<p style='color: red;'>Error: The nickname is already taken. Please choose another one.</p>";
//         } else {
//             // Proceed with updating the profile
//             try {
//                 $stmtUpdate = $conn->prepare("UPDATE Users SET user_nickname = ?, profile_image = ? WHERE user_id = ?");
//                 $stmtUpdate->bind_param("ssi", $new_nickname, $new_profile_image, $user_id);
//                 if (!$stmtUpdate->execute()) {
//                     throw new Exception("Error updating profile: " . $stmtUpdate->error);
//                 }

//                 echo "<p style='color: green;'>Profile updated successfully</p>";
//                 header("Location: profile.php?user_id=$user_id&success=1");
//                 exit;
//             } catch (Exception $e) {
//                 echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
//             }
//         }
//         $stmtCheck->close();
//     }
// }
?>

<!-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        .submissionfield {
            width: 450px;
            padding: 5px;
        }

        .slider-label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h1>Edit Profile: <?= $username ?></h1>
    <form action="" method="POST">
        <label for="nickname">Nickname:</label><br>
        <input type="text" name="nickname" value="<?= $user_nickname ?>" required class="submissionfield"><br><br>

        <label for="profile_image">Profile Image URL:</label><br>
        <input type="text" name="profile_image" value="<?= $profile_image ?>" class="submissionfield"><br><br>

        <input type="submit" name="submit" value="Update Profile">
    </form>

    <br><br>

    <a href="profile.php?user_id=<?= $user_id ?>">Go back to profile</a>
</body>

</html> -->

<?php
include "dbconnection.php";

session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Fetch current user data (nickname and profile image URL)
$stmt = $conn->prepare("SELECT user_nickname, profile_image FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($user_nickname, $profile_image);
$stmt->fetch();

// Handle form submission to update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit'])) {
        $new_nickname = htmlspecialchars($_POST['nickname']);
        $new_profile_image = htmlspecialchars($_POST['profile_image']);

        // Check if the new nickname is already taken (excluding the current user)
        $stmtCheck = $conn->prepare("SELECT user_id FROM Users WHERE user_nickname = ? AND user_id != ?");
        $stmtCheck->bind_param("si", $new_nickname, $user_id);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            // Nickname is already taken
            $error_message = "The nickname is already taken. Please choose another one.";
        } else {
            // Proceed with updating the profile
            try {
                $stmtUpdate = $conn->prepare("UPDATE Users SET user_nickname = ?, profile_image = ? WHERE user_id = ?");
                $stmtUpdate->bind_param("ssi", $new_nickname, $new_profile_image, $user_id);
                if (!$stmtUpdate->execute()) {
                    throw new Exception("Error updating profile: " . $stmtUpdate->error);
                }

                // Redirect to profile page with success message
                header("Location: profile.php?user_id=$user_id&success=1");
                exit;
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
        }
        $stmtCheck->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #2a3b4d;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #fff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            display: inline-block;
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }

        .btn:hover {
            background: #0056b3;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Edit Profile: <?= $username ?></h1>

        <?php if (isset($error_message)) : ?>
            <p class="error-message">Error: <?= $error_message ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="nickname">Nickname:</label>
                <input type="text" name="nickname" value="<?= $user_nickname ?>" required>
            </div>

            <div class="form-group">
                <label for="profile_image">Profile Image URL:</label>
                <input type="text" name="profile_image" value="<?= $profile_image ?>">
            </div>

            <button type="submit" name="submit" class="btn">Update Profile</button>
        </form>

        <br>
        <a href="profile.php?user_id=<?= $user_id ?>">Go back to profile</a>
    </div>
</body>

</html>