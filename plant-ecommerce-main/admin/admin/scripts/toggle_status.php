<?php
require '../includes/conn.php';
session_start();

// Kiểm tra quyền admin
if(!isset($_SESSION['admin_email'])){
    header('Location: ../login.php');
    exit();
}

if(isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Nếu action là lock thì set status = 0, nếu unlock thì set status = 1
    $new_status = ($action == 'lock') ? 0 : 1;

    // Cập nhật vào cơ sở dữ liệu
    $update_query = "UPDATE `users` SET `status` = '$new_status' WHERE `id` = '$user_id'";
    $result = mysqli_query($conn, $update_query);

    if($result) {
        // Chuyển hướng về lại trang danh sách người dùng
        header('Location: ../users.php?msg=status_updated');
    } else {
        echo "Lỗi khi cập nhật trạng thái: " . mysqli_error($conn);
    }
} else {
    header('Location: ../users.php');
}
?>