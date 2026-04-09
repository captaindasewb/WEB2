<?php
require "../includes/conn.php";
session_start();
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])){
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = intval($_POST['category']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $cost_price = floatval($_POST['cost_price']);
    $profit_margin = floatval($_POST['profit_margin']);
    $visibility = intval($_POST['visibility']);
    $old_image = $_POST['old_image'];
    
    // Calculate selling price
    $price = round($cost_price * (1 + $profit_margin / 100));
    
    $image = $old_image;
    
    // Handle remove image
    if(isset($_POST['remove_image']) && $_POST['remove_image'] == '1'){
        // Delete old image file
        if(!empty($old_image) && file_exists("../../../plant-ecommerce/img/product/" . $old_image)){
            unlink("../../../plant-ecommerce/img/product/" . $old_image);
        }
        $image = '';
    }
    
    // Handle new image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "../../../plant-ecommerce/img/product/";
        if(!is_dir($target_dir)){
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid('prod_') . '.' . $ext;
        $target_file = $target_dir . $image;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
            // Delete old image if new one uploaded successfully
            if(!empty($old_image) && file_exists($target_dir . $old_image)){
                unlink($target_dir . $old_image);
            }
        } else {
            header("Location: ../edit_product.php?id=$id&error=Lỗi upload hình");
            exit();
        }
    }

    $query = "UPDATE products SET 
        title = '$title',
        category = '$category',
        unit = '$unit',
        `desc` = '$desc',
        cost_price = '$cost_price',
        profit_margin = '$profit_margin',
        price = '$price',
        image = '$image',
        visibility = '$visibility'
        WHERE id = $id";
    
    if(mysqli_query($conn, $query)){
        header("Location: ../products.php?success=Cập nhật sản phẩm thành công");
    } else {
        header("Location: ../edit_product.php?id=$id&error=Lỗi: " . mysqli_error($conn));
    }
} else {
    header("Location: ../products.php");
}
?>
