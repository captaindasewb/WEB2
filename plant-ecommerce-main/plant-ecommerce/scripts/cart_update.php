<?php
require("../includes/conn.php");
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['qty']) && is_numeric($_GET['qty'])) {
    $cart_id = $_GET['id'];
    $qty = $_GET['qty'];
    $user_id = $_SESSION['user_id'];

    // Kiểm tra tồn kho
    $stock_query = "SELECT p.qty as stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = '$cart_id' AND c.user_id = '$user_id'";
    $stock_res = mysqli_query($con, $stock_query);
    if($stock_row = mysqli_fetch_assoc($stock_res)) {
        if ($qty > $stock_row['stock']) {
            echo "INSUFFICIENT_STOCK:".$stock_row['stock'];
            exit();
        }
    }

    if ($qty <= 0) {
        $query = "DELETE FROM `cart` WHERE id='$cart_id' AND user_id='$user_id'";
    } else {
        $query = "UPDATE `cart` SET `qty` = '$qty' WHERE id='$cart_id' AND user_id='$user_id'";
    }
    
    mysqli_query($con, $query) or die(mysqli_error($con));
    echo "Success";
} else {
    echo "Invalid Request";
}
?>
