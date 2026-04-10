<?php
require "../includes/conn.php";

if(!isset($_SESSION['admin_email'])){
    header("Location: ../../login.php");
    exit();
}

if(isset($_GET['id'])){
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Mật khẩu mặc định là 123456
    $new_password = md5('123456');

    $query = "UPDATE users SET password='$new_password' WHERE id='$user_id'";
    
    if(mysqli_query($conn, $query)){
        header("Location: ../users.php?success=" . urlencode("Khởi tạo lại mật khẩu thành công (Mật khẩu mới: 123456)"));
        exit();
    } else {
        header("Location: ../users.php?error=" . urlencode("Lỗi khi khởi tạo lại mật khẩu"));
        exit();
    }
} else {
    header("Location: ../users.php");
    exit();
}
?>
