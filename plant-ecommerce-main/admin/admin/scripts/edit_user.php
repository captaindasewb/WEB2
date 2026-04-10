<?php
require "../includes/conn.php";

if(!isset($_SESSION['admin_email'])){
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    
    $user_id = intval($_POST['user_id']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $street = isset($_POST['street']) ? trim(mysqli_real_escape_string($conn, $_POST['street'])) : '';
    $ward = isset($_POST['ward']) ? trim(mysqli_real_escape_string($conn, $_POST['ward'])) : '';
    $district = isset($_POST['district']) ? trim(mysqli_real_escape_string($conn, $_POST['district'])) : '';
    $address = $street . ', ' . $ward . ', ' . $district . ', TP. Hồ Chí Minh';

    // Validate phone length
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        header("Location: ../users.php?error=" . urlencode("Số điện thoại khách hàng phải có đúng 10 chữ số."));
        exit();
    }

    $query = "UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email', mobile='$mobile', address='$address' WHERE id=$user_id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: ../users.php?success=" . urlencode("Cập nhật thông tin khách hàng thành công."));
    } else {
        header("Location: ../users.php?error=" . urlencode("Đã có lỗi xảy ra. Không thể cập nhật thông tin."));
    }
}
?>
