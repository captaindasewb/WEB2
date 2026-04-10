<?php
require("../includes/conn.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!'); window.location.href='../login.php';</script>";
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['qty']) && is_numeric($_GET['qty'])) {
    $item_id = $_GET['id'];
    $qty = $_GET['qty'];
    $user_id = $_SESSION['user_id'];

    // Lấy số lượng tồn kho của sản phẩm
    $stock_query = "SELECT qty FROM products WHERE id = '$item_id'";
    $stock_result = mysqli_query($con, $stock_query);
    $stock_row = mysqli_fetch_assoc($stock_result);
    $available_stock = $stock_row['qty'];

    // Check if the item is already in the cart for this user
    $check_query = "SELECT id, qty FROM cart WHERE product_id = '$item_id' AND user_id = '$user_id'";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Item exists, update quantity
        $row = mysqli_fetch_assoc($check_result);
        $new_qty = $row['qty'] + $qty;
        
        if ($new_qty > $available_stock) {
            echo "<script>alert('Sản phẩm không đủ trong kho! (Còn lại $available_stock sản phẩm).'); window.history.back();</script>";
            exit();
        }
        
        $cart_id = $row['id'];
        $query = "UPDATE `cart` SET `qty` = '$new_qty' WHERE `id` = '$cart_id'";
    } else {
        if ($qty > $available_stock) {
            echo "<script>alert('Sản phẩm không đủ trong kho! (Còn lại $available_stock sản phẩm).'); window.history.back();</script>";
            exit();
        }
        
        // Item does not exist, insert new row
        $query = "INSERT INTO `cart`(`product_id`, `user_id`, `qty`) VALUES ('$item_id','$user_id','$qty')";
    }

    mysqli_query($con, $query) or die(mysqli_error($con));
    header('location: ../cart.php');
}
?>