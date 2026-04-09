<?php
require("../includes/conn.php");
session_start();
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cart_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $query = "DELETE FROM `cart` WHERE id='$cart_id' AND user_id='$user_id'";
    mysqli_query($con, $query) or die(mysqli_error($con));
    header('location: ../cart.php#cart');
}
?>