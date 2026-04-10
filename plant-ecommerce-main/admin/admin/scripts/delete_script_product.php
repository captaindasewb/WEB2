<?php
require "../includes/conn.php";
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    // Check if product has been imported (exists in import_receipt_details)
    $check_import = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM import_receipt_details WHERE product_id = $product_id");
    $import_count = mysqli_fetch_assoc($check_import)['cnt'];
    
    // Also check if product has been ordered
    $check_order = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE product_id = $product_id");
    $order_count = mysqli_fetch_assoc($check_order)['cnt'];
    
    // Always hide from admin and website instead of permanently deleting
    $query = "UPDATE products SET is_deleted_admin = 1, visibility = 0 WHERE id = $product_id";
    if(mysqli_query($conn, $query)){
        header("Location: ../products.php?success=Sản phẩm đã được xóa (ẩn) khỏi hệ thống");
    } else {
        header("Location: ../products.php?error=Lỗi khi xóa sản phẩm");
    }
} else {
    header("Location: ../products.php");
}
?>
