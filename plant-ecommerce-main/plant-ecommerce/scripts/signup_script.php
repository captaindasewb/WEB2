<?php
require "../includes/conn.php";
session_start();

$email = $_POST['email'];
$email = mysqli_real_escape_string($con, $email);

$pass = $_POST['password'];
$pass = mysqli_real_escape_string($con, $pass);
$pass = md5($pass);

$first = $_POST['fname'];
$first = mysqli_real_escape_string($con, $first);

$last = $_POST['lname'];
$last = mysqli_real_escape_string($con, $last);

$mobile = $_POST['mobile'];
$mobile = mysqli_real_escape_string($con, $mobile);

$street   = mysqli_real_escape_string($con, trim($_POST['street'] ?? ''));
$ward     = mysqli_real_escape_string($con, trim($_POST['ward'] ?? ''));
$district = mysqli_real_escape_string($con, trim($_POST['district'] ?? ''));
$address  = $street . ', ' . $ward . ', ' . $district . ', TP. Hồ Chí Minh';

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    $m = "Số điện thoại phải đúng 10 chữ số";
    header('location: ../register.php?error=' . urlencode($m));
    exit();
}

$query = "SELECT * from users where email='$email'";
$result = mysqli_query($con, $query);
$num = mysqli_num_rows($result);
if ($num != 0) {
    $m = "Email này đã được đăng ký. Vui lòng dùng email khác hoặc đăng nhập!";
    header('location: ../register.php?error=' . urlencode($m));
    exit();
} else {
    $quer = "INSERT INTO `users`(`first_name`, `last_name`, `mobile`, `email`, `password`, `address`) VALUES ('$first','$last','$mobile','$email','$pass','$address')";
    mysqli_query($con, $quer);

    echo "New record has id: " . mysqli_insert_id($con);
    $user_id = mysqli_insert_id($con);
    $_SESSION['email'] = $email;
    $_SESSION['user_id'] = $user_id;
    header('location: ../index.php');
}
?>