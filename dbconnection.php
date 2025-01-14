
<?php
// Web Stranding LLC © 2025. All rights reserved. Unauthorized changes, misuse, or copying of this PHP shitcode 
// will result in consequences so severe that even your cat will feel the existential dread. 
// Proceed with caution—or don't. Your call.
$servername = "localhost";
$username = "root";
$password = "";
$databasename = "EchoLog";
$conn = "";

try {
    $conn = mysqli_connect(
        $servername,
        $username,
        $password,
        $databasename
    );
} catch (mysqli_sql_exception) {
    echo "Abort Neo, connection is impossible";
}

if ($conn) {
    //  echo "You're IN, NEO";
} else {
    // echo "Abort Neo, connection is impossible";
}
?>