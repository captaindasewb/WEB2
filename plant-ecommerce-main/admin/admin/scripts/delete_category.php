<?php
require "../includes/conn.php";
session_start();
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $id = intval($_GET['id']);
    
    // Check if category has products
    $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products WHERE category = $id");
    $count = mysqli_fetch_assoc($check)['cnt'];
    
    if($count > 0){
        header("Location: ../categories.php?error=Không thể xóa danh mục đang có $count sản phẩm");
    } else {
        $query = "DELETE FROM categories WHERE id=$id";
        if(mysqli_query($conn, $query)){
            header("Location: ../categories.php?success=Xóa danh mục thành công");
        } else {
            header("Location: ../categories.php?error=Lỗi khi xóa danh mục");
        }
    }
} else {
    header("Location: ../categories.php");
}
?>
