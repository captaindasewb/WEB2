<?php
session_start();

// Sửa đường dẫn cho chắc chắn
require_once __DIR__ . '/../includes/conn.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Kiểm tra input rỗng
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Vui lòng nhập đầy đủ email và mật khẩu.";
    header("Location: ../login.php#login-form");
    exit();
}

// BƯỚC 1: Đã thêm cột 'status' vào câu truy vấn SELECT
$stmt = $con->prepare("SELECT id, email, password, status FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['login_error'] = "Email hoặc mật khẩu không đúng.";
    $_SESSION['old_email'] = $email;
    header("Location: ../login.php#login-form");
    exit();
}

$user = $result->fetch_assoc();

// BƯỚC 2: Kiểm tra xem tài khoản có bị khóa không (status = 0)
if (isset($user['status']) && $user['status'] == 0) {
    $_SESSION['login_error'] = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
    $_SESSION['old_email'] = $email;
    header("Location: ../login.php#login-form");
    exit();
}

// Kiểm tra mật khẩu (MD5)
if (md5($password) === $user['password']) {
    // Đăng nhập thành công
    $_SESSION['email']    = $user['email'];
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['login_success'] = true;

    header("Location: ../index.php");  // Hoặc /ecommerce
    exit();
} else {
    $_SESSION['login_error'] = "Email hoặc mật khẩu không đúng.";
    $_SESSION['old_email'] = $email;
    header("Location: ../login.php#login-form");
    exit();
}

$stmt->close();
$con->close();
?>