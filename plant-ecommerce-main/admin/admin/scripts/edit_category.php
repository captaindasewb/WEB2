<?php
require "../includes/conn.php";
session_start();
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['id']) && !empty($_POST['title'])){
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $query = "UPDATE categories SET title='$title' WHERE id=$id";
    if(mysqli_query($conn, $query)){
        header("Location: ../categories.php?success=Cập nhật danh mục thành công");
    } else {
        header("Location: ../categories.php?error=Lỗi khi cập nhật danh mục");
    }
} else {
    header("Location: ../categories.php");
}
?>
