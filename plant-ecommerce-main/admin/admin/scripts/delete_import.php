<?php
require "../includes/conn.php";
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $receipt_id = intval($_GET['id']);
    
    // Only allow deleting draft receipts
    $check = mysqli_query($conn, "SELECT * FROM import_receipts WHERE id = $receipt_id AND status = 'draft'");
    if(mysqli_num_rows($check) == 0){
        header("Location: ../imports.php?error=Không thể xóa phiếu nhập đã hoàn thành");
        exit();
    }
    
    // Delete detail items first
    mysqli_query($conn, "DELETE FROM import_receipt_details WHERE receipt_id = $receipt_id");
    // Delete receipt
    mysqli_query($conn, "DELETE FROM import_receipts WHERE id = $receipt_id");
    
    header("Location: ../imports.php?success=Đã xóa phiếu nhập");
} else {
    header("Location: ../imports.php");
}
?>
