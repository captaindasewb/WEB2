<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: imports.php");
    exit();
}

$receipt_id = intval($_GET['id']);
$receipt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM import_receipts WHERE id = $receipt_id"));
if(!$receipt){
    header("Location: imports.php?error=Phiếu nhập không tồn tại");
    exit();
}

$is_draft = ($receipt['status'] == 'draft');

// Handle update receipt info (only draft)
if($_SERVER['REQUEST_METHOD'] == 'POST' && $is_draft){
    
    // Update header info
    if(isset($_POST['update_header'])){
        $supplier = mysqli_real_escape_string($conn, $_POST['supplier_name']);
        $import_date = mysqli_real_escape_string($conn, $_POST['import_date']);
        $notes = mysqli_real_escape_string($conn, $_POST['notes']);
        mysqli_query($conn, "UPDATE import_receipts SET supplier_name='$supplier', import_date='$import_date', notes='$notes' WHERE id = $receipt_id");
        header("Location: import_edit.php?id=$receipt_id&success=Cập nhật thông tin phiếu thành công");
        exit();
    }
    
    // Add product to receipt
    if(isset($_POST['add_item'])){
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        $unit_price = floatval($_POST['unit_price']);
        
        // Check if product already in receipt
        $exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM import_receipt_details WHERE receipt_id = $receipt_id AND product_id = $product_id"));
        if($exists){
            // Update existing
            mysqli_query($conn, "UPDATE import_receipt_details SET quantity = $quantity, import_price = $unit_price WHERE id = {$exists['id']}");
        } else {
            mysqli_query($conn, "INSERT INTO import_receipt_details (receipt_id, product_id, quantity, import_price) VALUES ($receipt_id, $product_id, $quantity, $unit_price)");
        }
        header("Location: import_edit.php?id=$receipt_id&success=Thêm/cập nhật sản phẩm thành công");
        exit();
    }
    
    // Remove item from receipt
    if(isset($_POST['remove_item'])){
        $detail_id = intval($_POST['detail_id']);
        mysqli_query($conn, "DELETE FROM import_receipt_details WHERE id = $detail_id AND receipt_id = $receipt_id");
        header("Location: import_edit.php?id=$receipt_id&success=Đã xóa sản phẩm khỏi phiếu");
        exit();
    }
}

// Reload receipt data
$receipt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM import_receipts WHERE id = $receipt_id"));

// Get receipt details
$details = mysqli_query($conn, "SELECT ird.*, p.title, p.unit 
    FROM import_receipt_details ird 
    JOIN products p ON ird.product_id = p.id 
    WHERE ird.receipt_id = $receipt_id");

// Get products for dropdown (only for draft)
if($is_draft){
    $products_list = mysqli_query($conn, "SELECT id, title, cost_price, unit FROM products WHERE visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL) ORDER BY title");
}

require "includes/header.php";
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4"><?= $is_draft ? 'Sửa' : 'Chi Tiết' ?> Phiếu Nhập PN-<?= str_pad($receipt['id'], 4, '0', STR_PAD_LEFT) ?></h1>
                <?php if(!$is_draft): ?>
                    <span class="badge bg-success fs-6">Đã hoàn thành</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark fs-6">Nháp - Có thể sửa</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="container col-md-10 my-4">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <!-- Receipt Header Info -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Thông tin phiếu nhập</h5></div>
                <div class="card-body p-4">
                    <?php if($is_draft): ?>
                    <form method="POST">
                        <input type="hidden" name="update_header" value="1">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><strong>Nhà cung cấp</strong></label>
                                <input type="text" name="supplier_name" class="form-control" value="<?= htmlspecialchars($receipt['supplier_name']) ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><strong>Ngày nhập</strong></label>
                                <input type="date" name="import_date" class="form-control" value="<?= $receipt['import_date'] ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><strong>Ghi chú</strong></label>
                                <input type="text" name="notes" class="form-control" value="<?= htmlspecialchars($receipt['notes']) ?>">
                            </div>
                            <div class="col-md-1 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nhà cung cấp:</strong> <?= htmlspecialchars($receipt['supplier_name']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Ngày nhập:</strong> <?= date('d/m/Y', strtotime($receipt['import_date'])) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Ghi chú:</strong> <?= htmlspecialchars($receipt['notes']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($is_draft): ?>
            <!-- Add Product Form (only draft) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm sản phẩm vào phiếu</h5></div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="add_item" value="1">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><strong>Sản phẩm</strong></label>
                                <select name="product_id" id="product_select" class="form-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    <?php 
                                    if(isset($products_list)){
                                        mysqli_data_seek($products_list, 0);
                                        while($p = mysqli_fetch_assoc($products_list)): 
                                    ?>
                                        <option value="<?= $p['id'] ?>" data-cost="<?= $p['cost_price'] ?>">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </option>
                                    <?php endwhile; } ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label"><strong>Số lượng</strong></label>
                                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><strong>Đơn giá nhập</strong></label>
                                <input type="number" name="unit_price" id="unit_price" class="form-control" min="0" step="0.01" required>
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus me-1"></i>Thêm SP</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Receipt Details Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách sản phẩm trong phiếu</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên SP</th>
                                    <th>ĐVT</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá nhập</th>
                                    <th>Thành tiền</th>
                                    <?php if($is_draft): ?><th>Xóa</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grand_total = 0;
                                while($d = mysqli_fetch_assoc($details)):
                                    $line_total = $d['quantity'] * $d['import_price'];
                                    $grand_total += $line_total;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['title']) ?></td>
                                    <td><?= htmlspecialchars($d['unit']) ?></td>
                                    <td><?= $d['quantity'] ?></td>
                                    <td><?= number_format($d['import_price'], 0, ',', '.') ?></td>
                                    <td><?= number_format($line_total, 0, ',', '.') ?></td>
                                    <?php if($is_draft): ?>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="remove_item" value="1">
                                            <input type="hidden" name="detail_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa SP này khỏi phiếu?');"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="4" class="text-end">TỔNG CỘNG:</td>
                                    <td><?= number_format($grand_total, 0, ',', '.') ?> VNĐ</td>
                                    <?php if($is_draft): ?><td></td><?php endif; ?>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <?php if($is_draft): ?>
                    <a href="scripts/complete_import.php?id=<?= $receipt['id'] ?>" class="btn btn-success btn-lg" onclick="return confirm('Hoàn thành phiếu nhập? Tồn kho và giá vốn sẽ được cập nhật. Sau khi hoàn thành không thể sửa.');">
                        <i class="fas fa-check me-2"></i>Hoàn Thành Phiếu Nhập
                    </a>
                <?php endif; ?>
                <a href="imports.php" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill cost price when selecting product
document.getElementById('product_select')?.addEventListener('change', function(){
    var selected = this.options[this.selectedIndex];
    var cost = selected.getAttribute('data-cost');
    if(cost) document.getElementById('unit_price').value = cost;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
