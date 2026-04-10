<?php
require "../includes/conn.php";
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
    $import_date = mysqli_real_escape_string($conn, $_POST['import_date']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $items = $_POST['items'] ?? [];
    
    if(empty($items)){
        header("Location: ../import_create.php?error=Vui lòng thêm ít nhất một sản phẩm");
        exit();
    }
    
    // Create receipt
    $query = "INSERT INTO import_receipts (supplier_name, import_date, notes, status, created_at) 
              VALUES ('$supplier_name', '$import_date', '$notes', 'draft', NOW())";
    
    if(mysqli_query($conn, $query)){
        $receipt_id = mysqli_insert_id($conn);
        
        // Insert detail items
        foreach($items as $item){
            $product_id = intval($item['product_id']);
            $quantity = intval($item['quantity']);
            $unit_price = floatval($item['unit_price']);
            
            if($product_id > 0 && $quantity > 0){
                $detail_query = "INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) 
                                 VALUES ($receipt_id, $product_id, $quantity, $unit_price)";
                mysqli_query($conn, $detail_query);
            }
        }
        
        header("Location: ../imports.php?success=Tạo phiếu nhập thành công (trạng thái: Nháp)");
    } else {
        header("Location: ../import_create.php?error=Lỗi: " . mysqli_error($conn));
    }
} else {
    header("Location: ../imports.php");
}
?>
