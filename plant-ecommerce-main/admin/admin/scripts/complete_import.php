<?php
require "../includes/conn.php";
if(!isset($_SESSION['admin_email'])){ header("Location: ../login.php"); exit(); }

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $receipt_id = intval($_GET['id']);
    
    // Verify receipt exists and is draft
    $check = mysqli_query($conn, "SELECT * FROM import_receipts WHERE id = $receipt_id AND status = 'draft'");
    if(mysqli_num_rows($check) == 0){
        header("Location: ../imports.php?error=Phiếu nhập không tồn tại hoặc đã hoàn thành");
        exit();
    }
    
    // Get all detail items
    $details = mysqli_query($conn, "SELECT * FROM import_receipt_details WHERE receipt_id = $receipt_id");
    
    if(mysqli_num_rows($details) == 0){
        header("Location: ../imports.php?error=Phiếu nhập không có sản phẩm nào");
        exit();
    }
    
    // Process each item: update stock + weighted average cost
    while($item = mysqli_fetch_assoc($details)){
        $product_id = $item['product_id'];
        $import_qty = $item['quantity'];
        $import_price = $item['import_price'];
        
        // Get current product data
        $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT qty, cost_price, profit_margin FROM products WHERE id = $product_id"));
        
        if($prod){
            $current_qty = $prod['qty'];
            $current_cost = $prod['cost_price'];
            
            // Calculate weighted average cost price
            // new_cost = (current_qty * current_cost + import_qty * import_price) / (current_qty + import_qty)
            $total_qty = $current_qty + $import_qty;
            if($total_qty > 0){
                $new_cost = ($current_qty * $current_cost + $import_qty * $import_price) / $total_qty;
            } else {
                $new_cost = $import_price;
            }
            $new_cost = round($new_cost, 2);
            
            // Calculate new selling price based on profit margin
            $new_price = round($new_cost * (1 + $prod['profit_margin'] / 100));
            
            // Update product
            $update = "UPDATE products SET 
                qty = $total_qty, 
                cost_price = $new_cost,
                price = $new_price
                WHERE id = $product_id";
            mysqli_query($conn, $update);
        }
    }
    
    // Mark receipt as completed
    mysqli_query($conn, "UPDATE import_receipts SET status = 'completed' WHERE id = $receipt_id");
    
    header("Location: ../imports.php?success=Hoàn thành phiếu nhập! Tồn kho và giá vốn đã được cập nhật.");
} else {
    header("Location: ../imports.php");
}
?>
