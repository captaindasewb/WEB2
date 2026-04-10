<?php
require "../includes/conn.php";
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['title'])){
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $query = "INSERT INTO categories (title) VALUES ('$title')";
    if(mysqli_query($conn, $query)){
        header("Location: ../categories.php?success=Thêm danh mục thành công");
    } else {
        header("Location: ../categories.php?error=Lỗi khi thêm danh mục");
    }
} else {
    header("Location: ../categories.php");
}
?>
