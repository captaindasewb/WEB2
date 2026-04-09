<?php
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