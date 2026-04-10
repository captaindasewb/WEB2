<?php
    // Use a dedicated session name for the admin panel
    // This prevents session conflicts with the user-facing website
    if (session_status() === PHP_SESSION_NONE) {
        session_name('plantshop_admin');
        session_start();
    }

    $server = "127.0.0.1";
    $username = "root";
    $password = "";
    $db = "plantshop";

    $conn = mysqli_connect($server, $username, $password, $db);

    if(mysqli_connect_error()){
        die("Could Not Connect to database");
    }

    // Set charset for Vietnamese support
    mysqli_set_charset($conn, "utf8mb4");
?>