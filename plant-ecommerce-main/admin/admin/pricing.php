<?php
require 'includes/conn.php';
session_start();

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}

// Handle update profit margin
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pricing'])){
    $product_id = intval($_POST['product_id']);
    $new_margin = floatval($_POST['profit_margin']);
    
    // Get current cost price
    $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT cost_price FROM products WHERE id = $product_id"));
    if($prod){
        $new_price = round($prod['cost_price'] * (1 + $new_margin / 100));
        mysqli_query($conn, "UPDATE products SET profit_margin = $new_margin, price = $new_price WHERE id = $product_id");
        header("Location: pricing.php?success=Cập nhật giá bán thành công");
        exit();
    }
}

require "includes/header.php";
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Quản Lý Giá Bán</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Công thức:</strong> Giá bán = Giá vốn × (1 + Tỉ lệ lợi nhuận / 100)
            </div>

            <div class="mb-3">
                <form class="d-flex" method="GET" style="max-width:400px;">
                    <input type="text" name="search" class="form-control me-2" placeholder="Tìm theo tên SP..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <!-- Product Pricing Table -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-tags me-2"></i>Bảng giá sản phẩm</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên SP</th>
                                    <th>Giá vốn</th>
                                    <th>Tỉ lệ LN (%)</th>
                                    <th>Giá bán hiện tại</th>
                                    <th>Cập nhật</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Phân trang sản phẩm
                                $p_items_per_page = 10;
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $where = "WHERE (visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL))";
                                if(!empty($search)){
                                    $where .= " AND (title LIKE '%$search%')";
                                }
                                
                                $p_count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products $where");
                                $p_total_items = mysqli_fetch_assoc($p_count_result)['total'];
                                $p_total_pages = ceil($p_total_items / $p_items_per_page);
                                $p_current_page = isset($_GET['p_page']) ? max(1, intval($_GET['p_page'])) : 1;
                                if ($p_current_page > $p_total_pages && $p_total_pages > 0) $p_current_page = $p_total_pages;
                                $p_offset = ($p_current_page - 1) * $p_items_per_page;
                                
                                $result = mysqli_query($conn, "SELECT * FROM products $where ORDER BY title LIMIT $p_items_per_page OFFSET $p_offset");
                                while($row = mysqli_fetch_assoc($result)):
                                    $selling = round($row['cost_price'] * (1 + $row['profit_margin'] / 100));
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= number_format($row['cost_price'], 0, ',', '.') ?> VNĐ</td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center" style="gap:5px;">
                                            <input type="hidden" name="update_pricing" value="1">
                                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                            <input type="number" step="0.01" name="profit_margin" value="<?= $row['profit_margin'] ?>" class="form-control form-control-sm" style="width:80px;" min="0">
                                            <span>%</span>
                                    </td>
                                    <td><strong><?= number_format($selling, 0, ',', '.') ?> VNĐ</strong></td>
                                    <td>
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (isset($p_total_pages) && $p_total_pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-center border-top-0">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $p_query_string = "";
                                if(!empty($_GET['search'])) $p_query_string .= "&search=" . urlencode($_GET['search']);
                                if(!empty($_GET['b_page'])) $p_query_string .= "&b_page=" . urlencode($_GET['b_page']);
                                
                                // Nút Trước
                                if ($p_current_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?p_page=' . ($p_current_page - 1) . $p_query_string . '">&laquo; Trước</a></li>';
                                } else {
                                    echo '<li class="page-item disabled"><span class="page-link">&laquo; Trước</span></li>';
                                }
                                
                                // Số trang
                                $start = max(1, $p_current_page - 2);
                                $end = min($p_total_pages, $p_current_page + 2);
                                
                                if($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?p_page=1' . $p_query_string . '">1</a></li>';
                                    if($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                
                                for ($i = $start; $i <= $end; $i++) {
                                    $active = ($i == $p_current_page) ? "active" : "";
                                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?p_page=' . $i . $p_query_string . '">' . $i . '</a></li>';
                                }
                                
                                if($end < $p_total_pages){
                                    if($end < $p_total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    echo '<li class="page-item"><a class="page-link" href="?p_page=' . $p_total_pages . $p_query_string . '">' . $p_total_pages . '</a></li>';
                                }
                                
                                // Nút Sau
                                if ($p_current_page < $p_total_pages) {
                                    echo '<li class="page-item"><a class="page-link" href="?p_page=' . ($p_current_page + 1) . $p_query_string . '">Sau &raquo;</a></li>';
                                } else {
                                    echo '<li class="page-item disabled"><span class="page-link">Sau &raquo;</span></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Import Batch Pricing History -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Tra cứu giá vốn, % lợi nhuận, giá bán theo lô hàng đã nhập</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày nhập</th>
                                    <th>Tên SP</th>
                                    <th>SL nhập</th>
                                    <th>Giá nhập (lô)</th>
                                    <th>Giá vốn BQ hiện tại</th>
                                    <th>% Lợi nhuận</th>
                                    <th>Giá bán hiện tại</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Phân trang lô hàng
                                $b_items_per_page = 10;
                                $search_batch = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $batch_where = " WHERE ir.status = 'completed'";
                                if(!empty($search_batch)){
                                    $batch_where .= " AND (p.title LIKE '%$search_batch%')";
                                }
                                
                                $b_count_query = "SELECT COUNT(*) as total 
                                    FROM import_receipt_details ird
                                    JOIN import_receipts ir ON ird.receipt_id = ir.id
                                    JOIN products p ON ird.product_id = p.id
                                    $batch_where";
                                $b_count_result = mysqli_query($conn, $b_count_query);
                                $b_total_items = mysqli_fetch_assoc($b_count_result)['total'];
                                $b_total_pages = ceil($b_total_items / $b_items_per_page);
                                $b_current_page = isset($_GET['b_page']) ? max(1, intval($_GET['b_page'])) : 1;
                                if ($b_current_page > $b_total_pages && $b_total_pages > 0) $b_current_page = $b_total_pages;
                                $b_offset = ($b_current_page - 1) * $b_items_per_page;
                                
                                $batch_query = "SELECT ird.*, ir.id as receipt_id, ir.import_date, ir.supplier_name,
                                    p.title, p.cost_price as current_cost, p.profit_margin, p.price as current_sell_price
                                    FROM import_receipt_details ird
                                    JOIN import_receipts ir ON ird.receipt_id = ir.id
                                    JOIN products p ON ird.product_id = p.id
                                    $batch_where
                                    ORDER BY ir.import_date DESC, ir.id DESC
                                    LIMIT $b_items_per_page OFFSET $b_offset";
                                $batch_result = mysqli_query($conn, $batch_query);
                                
                                if(mysqli_num_rows($batch_result) == 0){
                                    echo "<tr><td colspan='8' class='text-center py-4 text-muted'>Chưa có lô hàng nào đã nhập</td></tr>";
                                }
                                
                                while($b = mysqli_fetch_assoc($batch_result)):
                                    $batch_sell = round($b['current_cost'] * (1 + $b['profit_margin'] / 100));
                                ?>
                                <tr>
                                    <td><strong>PN-<?= str_pad($b['receipt_id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($b['import_date'])) ?></td>
                                    <td><?= htmlspecialchars($b['title']) ?></td>
                                    <td><?= $b['quantity'] ?></td>
                                    <td><?= number_format($b['import_price'], 0, ',', '.') ?> VNĐ</td>
                                    <td class="text-primary fw-bold"><?= number_format($b['current_cost'], 0, ',', '.') ?> VNĐ</td>
                                    <td><?= $b['profit_margin'] ?>%</td>
                                    <td class="fw-bold"><?= number_format($batch_sell, 0, ',', '.') ?> VNĐ</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (isset($b_total_pages) && $b_total_pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-center border-top-0">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $b_query_string = "";
                                if(!empty($_GET['search'])) $b_query_string .= "&search=" . urlencode($_GET['search']);
                                if(!empty($_GET['p_page'])) $b_query_string .= "&p_page=" . urlencode($_GET['p_page']);
                                
                                // Nút Trước
                                if ($b_current_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?b_page=' . ($b_current_page - 1) . $b_query_string . '">&laquo; Trước</a></li>';
                                } else {
                                    echo '<li class="page-item disabled"><span class="page-link">&laquo; Trước</span></li>';
                                }
                                
                                // Số trang
                                $start = max(1, $b_current_page - 2);
                                $end = min($b_total_pages, $b_current_page + 2);
                                
                                if($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?b_page=1' . $b_query_string . '">1</a></li>';
                                    if($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                
                                for ($i = $start; $i <= $end; $i++) {
                                    $active = ($i == $b_current_page) ? "active" : "";
                                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?b_page=' . $i . $b_query_string . '">' . $i . '</a></li>';
                                }
                                
                                if($end < $b_total_pages){
                                    if($end < $b_total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    echo '<li class="page-item"><a class="page-link" href="?b_page=' . $b_total_pages . $b_query_string . '">' . $b_total_pages . '</a></li>';
                                }
                                
                                // Nút Sau
                                if ($b_current_page < $b_total_pages) {
                                    echo '<li class="page-item"><a class="page-link" href="?b_page=' . ($b_current_page + 1) . $b_query_string . '">Sau &raquo;</a></li>';
                                } else {
                                    echo '<li class="page-item disabled"><span class="page-link">Sau &raquo;</span></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
