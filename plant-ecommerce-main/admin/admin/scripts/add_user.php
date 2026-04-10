<?php
require "../includes/conn.php";

if(!isset($_SESSION['admin_email'])){
    header("Location: ../../login.php");
    exit();
}

if(isset($_POST['add_user'])){
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Hash the password with MD5
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashed_password = md5($password);
    
    $mobile = isset($_POST['mobile']) ? mysqli_real_escape_string($conn, $_POST['mobile']) : '';
    
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        header("Location: ../users.php?error=" . urlencode("Số điện thoại phải có đúng 10 chữ số."));
        exit();
    }
    
    $street = isset($_POST['street']) ? trim(mysqli_real_escape_string($conn, $_POST['street'])) : '';
    $ward = isset($_POST['ward']) ? trim(mysqli_real_escape_string($conn, $_POST['ward'])) : '';
    $district = isset($_POST['district']) ? trim(mysqli_real_escape_string($conn, $_POST['district'])) : '';
    $address = $street . ', ' . $ward . ', ' . $district . ', TP. Hồ Chí Minh';

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $res = mysqli_query($conn, $check_email);
    if(mysqli_num_rows($res) > 0){
        header("Location: ../users.php?error=Email đã tồn tại");
        exit();
    }

    $query = "INSERT INTO users (first_name, last_name, email, password, mobile, address, status) 
              VALUES ('$first_name', '$last_name', '$email', '$hashed_password', '$mobile', '$address', 1)";
    
    if(mysqli_query($conn, $query)){
        header("Location: ../users.php?success=Thêm tài khoản thành công");
    } else {
        header("Location: ../users.php?error=Lỗi khi thêm tài khoản");
    }
} else {
    header("Location: ../users.php");
}
?>
