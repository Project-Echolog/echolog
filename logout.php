<?php 
    session_start();
    session_unset();
    session_destroy();
    header("Website/login.php");
    exit;
?>