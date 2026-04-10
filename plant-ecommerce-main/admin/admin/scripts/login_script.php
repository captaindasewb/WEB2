<?php

// Require kết nối DB (lên 1 cấp từ thư mục scripts/)
require_once '../includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Kiểm tra input rỗng
if (empty($email) || empty($password)) {
    header("Location: ../login.php?error=" . urlencode("Vui lòng nhập đầy đủ email và mật khẩu"));
    exit();
}

// Bảo vệ SQL Injection bằng prepared statement (rất quan trọng!)
$stmt = $conn->prepare("SELECT id, email, password FROM admin WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Lưu email cũ để điền lại form (tùy chọn)
    $_SESSION['old_admin_email'] = $email;
    header("Location: ../login.php?error=" . urlencode("Email hoặc mật khẩu không đúng"));
    exit();
}

$admin = $result->fetch_assoc();

// Kiểm tra mật khẩu (vẫn dùng MD5 như code cũ của bạn - nên nâng cấp sau)
if (md5($password) === $admin['password']) {
    // Đăng nhập thành công
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_id']    = $admin['id'];

    header("Location: ../index.php");  // Chuyển về admin/index.php
    exit();
} else {
    // Sai mật khẩu
    $_SESSION['old_admin_email'] = $email;
    header("Location: ../login.php?error=" . urlencode("Email hoặc mật khẩu không đúng"));
    exit();
}

$stmt->close();
$conn->close();
?>