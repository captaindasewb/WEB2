<?php
// Prefer TCP loopback to avoid named-pipe/socket issues with 'localhost'
mysqli_report(MYSQLI_REPORT_OFF);

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'plantShop';
$db_port = 3306;

$con = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$con) {
    error_log('Database connect error: ' . mysqli_connect_error());
    die('Database connection error. Please ensure MySQL is running and connection settings are correct.');
}

// QUAN TRỌNG: set charset để hiển thị tiếng Việt đúng
mysqli_set_charset($con, "utf8mb4");