<?php
session_start();

// Xóa toàn bộ dữ liệu session
$_SESSION = [];

// Nếu dùng cookie session, xóa luôn cookie (tăng độ an toàn)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hủy session hoàn toàn
session_destroy();

// Redirect về trang đăng nhập admin
header("Location: ../login.php");
exit();
?>