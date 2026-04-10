<?php require "../includes/conn.php" ?>
<?php
if(!isset($_SESSION['admin_email'])){
    header("Location: ../login.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = intval($_POST['category']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $qty = intval($_POST['qty']);
    $cost_price = floatval($_POST['cost_price']);
    $profit_margin = floatval($_POST['profit_margin']);
    $visibility = intval($_POST['visibility']);
    
    // Calculate selling price
    $price = round($cost_price * (1 + $profit_margin / 100));
    
    // Handle image upload
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "../../../plant-ecommerce/img/product/";
        if(!is_dir($target_dir)){
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid('prod_') . '.' . $ext;
        $target_file = $target_dir . $image;
        
        if(!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){
            header("Location: ../product.php?error=Lỗi khi upload hình ảnh");
            exit();
        }
    }

    $query = "INSERT INTO `products`(`category`, `title`, `price`, `qty`, `desc`, `image`, `unit`, `cost_price`, `profit_margin`, `visibility`)
    VALUES ('$category', '$title', '$price', '$qty', '$desc', '$image', '$unit', '$cost_price', '$profit_margin', '$visibility')";

    if(mysqli_query($conn, $query)){
        header("Location: ../products.php?success=Thêm sản phẩm thành công");
    } else {
        header("Location: ../product.php?error=Lỗi: " . mysqli_error($conn));
    }
} else {
    header("Location: ../product.php");
}
?>