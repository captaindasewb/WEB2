<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}
require "includes/header.php";

// Default low stock threshold
$low_threshold = isset($_GET['threshold']) ? intval($_GET['threshold']) : 5;
if($low_threshold < 1) $low_threshold = 5;
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Quản Lý Tồn Kho</h1>
            </div>
        </div>

        <div class="container">
            <!-- Low stock warning with configurable threshold -->
            <?php
            $low_stock = mysqli_query($conn, "SELECT title, qty, unit FROM products WHERE qty <= $low_threshold AND visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL) ORDER BY qty ASC");
            if(mysqli_num_rows($low_stock) > 0):
            ?>
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Cảnh báo sản phẩm sắp hết hàng (≤ <?= $low_threshold ?>):</h6>
                <ul class="mb-0">
                    <?php while($ls = mysqli_fetch_assoc($low_stock)): ?>
                        <li><strong><?= htmlspecialchars($ls['title']) ?></strong>: 
                            <span class="text-danger fw-bold"><?= $ls['qty'] ?> <?= htmlspecialchars($ls['unit']) ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form class="row g-2 align-items-end" method="GET">
                        <div class="col-md-3">
                            <label class="form-label">Tìm sản phẩm</label>
                            <input type="text" name="search" class="form-control" placeholder="Tìm theo tên SP..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Loại sản phẩm</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả loại</option>
                                <?php
                                $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY title");
                                while($cat = mysqli_fetch_assoc($cats)):
                                ?>
                                <option value="<?= $cat['id'] ?>" <?= ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Lọc tồn kho</label>
                            <select name="stock_filter" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="low" <?= ($_GET['stock_filter'] ?? '') == 'low' ? 'selected' : '' ?>>Sắp hết</option>
                                <option value="out" <?= ($_GET['stock_filter'] ?? '') == 'out' ? 'selected' : '' ?>>Hết hàng (0)</option>
                                <option value="available" <?= ($_GET['stock_filter'] ?? '') == 'available' ? 'selected' : '' ?>>Còn hàng</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ngưỡng cảnh báo</label>
                            <input type="number" name="threshold" class="form-control" min="1" value="<?= $low_threshold ?>" title="Số lượng được coi là sắp hết hàng">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Thời điểm</label>
                            <input type="date" name="at_date" class="form-control" value="<?= htmlspecialchars($_GET['at_date'] ?? '') ?>" title="Tra cứu tồn kho tại thời điểm">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Tra cứu</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            // Check if querying stock at a specific date
            $at_date = $_GET['at_date'] ?? '';
            $is_historical = !empty($at_date);
            ?>

            <?php if($is_historical): ?>
            <div class="alert alert-info">
                <i class="fas fa-clock me-2"></i>
                <strong>Tra cứu tồn kho tại thời điểm:</strong> <?= date('d/m/Y', strtotime($at_date)) ?>
                <br><small class="text-muted">Tồn kho = Tồn hiện tại - Nhập sau ngày + Xuất (đơn giao) sau ngày</small>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên SP</th>
                                    <th>Loại</th>
                                    <th>ĐVT</th>
                                    <th>Tồn kho<?= $is_historical ? ' (tại '.date('d/m', strtotime($at_date)).')' : '' ?></th>
                                    <th>Giá vốn</th>
                                    <th>Giá trị tồn</th>
                                    <th>Hiện trạng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $where_clauses = [];
                                if(!empty($search)){
                                    $where_clauses[] = "(p.title LIKE '%$search%')";
                                }
                                if(!empty($_GET['category'])){
                                    $cat_id = intval($_GET['category']);
                                    $where_clauses[] = "p.category = $cat_id";
                                }
                                
                                $stock_filter = $_GET['stock_filter'] ?? '';
                                if($stock_filter == 'low') $where_clauses[] = "p.qty > 0 AND p.qty <= $low_threshold";
                                elseif($stock_filter == 'out') $where_clauses[] = "p.qty = 0";
                                elseif($stock_filter == 'available') $where_clauses[] = "p.qty > $low_threshold";
                                
                                $where_clauses[] = "(p.is_deleted_admin = 0 OR p.is_deleted_admin IS NULL)";
                                $where = '';
                                if(!empty($where_clauses)){
                                    $where = ' WHERE ' . implode(' AND ', $where_clauses);
                                }
                                
                                // Phân trang
                                $items_per_page = 10;
                                $count_query = "SELECT COUNT(*) as total FROM products p $where";
                                $count_result = mysqli_query($conn, $count_query);
                                $total_items = mysqli_fetch_assoc($count_result)['total'];
                                $total_pages = ceil($total_items / $items_per_page);
                                $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                                if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
                                $offset = ($current_page - 1) * $items_per_page;
                                
                                $query = "SELECT p.*, c.title as category_name FROM products p LEFT JOIN categories c ON p.category = c.id $where ORDER BY p.qty ASC LIMIT $items_per_page OFFSET $offset";
                                $result = mysqli_query($conn, $query);
                                
                                $total_value = 0;
                                while ($row = mysqli_fetch_array($result)) {
                                    $display_qty = $row['qty'];
                                    
                                    // Calculate historical stock at specific date
                                    if($is_historical){
                                        $pid = $row['id'];
                                        $at_date_esc = mysqli_real_escape_string($conn, $at_date);
                                        
                                        // Imports after the date (completed receipts)
                                        $import_after = mysqli_fetch_assoc(mysqli_query($conn, 
                                            "SELECT COALESCE(SUM(ird.quantity),0) as total_imported 
                                             FROM import_receipt_details ird 
                                             JOIN import_receipts ir ON ird.receipt_id = ir.id 
                                             WHERE ird.product_id = $pid AND ir.status='completed' AND ir.import_date > '$at_date_esc'"
                                        ));
                                        
                                        // Orders delivered after the date
                                        $sold_after = mysqli_fetch_assoc(mysqli_query($conn, 
                                            "SELECT COALESCE(SUM(product_qty),0) as total_sold 
                                             FROM orders 
                                             WHERE product_id = $pid AND status='delivered' AND order_date > '$at_date_esc 23:59:59'"
                                        ));
                                        
                                        // stock_at_date = current_stock - imports_after + sold_after
                                        $display_qty = $row['qty'] - ($import_after['total_imported'] ?? 0) + ($sold_after['total_sold'] ?? 0);
                                        if($display_qty < 0) $display_qty = 0;
                                    }
                                    
                                    $stock_value = $display_qty * $row['cost_price'];
                                    $total_value += $stock_value;
                                    
                                    $qtyClass = '';
                                    if($display_qty == 0) $qtyClass = 'text-danger fw-bold';
                                    elseif($display_qty <= $low_threshold) $qtyClass = 'text-warning fw-bold';
                                    
                                    if ($row['visibility'] == 1) {
                                        $visBadge = ($display_qty > 0) ? "<span class='badge bg-success'>Đang bán</span>" : "<span class='badge bg-danger'>Hết hàng</span>";
                                    } else {
                                        $visBadge = "<span class='badge bg-secondary'>Ẩn</span>";
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['category_name'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                                    echo "<td class='$qtyClass'>" . $display_qty . "</td>";
                                    echo "<td>" . number_format($row['cost_price'], 0, ',', '.') . "</td>";
                                    echo "<td>" . number_format($stock_value, 0, ',', '.') . "</td>";
                                    echo "<td>$visBadge</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="5" class="text-end">TỔNG GIÁ TRỊ (TRANG HIỆN TẠI):</td>
                                    <td colspan="2"><?= number_format($total_value, 0, ',', '.') ?> VNĐ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-center">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $query_string = "";
                                if(!empty($_GET['search'])) $query_string .= "&search=" . urlencode($_GET['search']);
                                if(!empty($_GET['category'])) $query_string .= "&category=" . urlencode($_GET['category']);
                                if(!empty($_GET['stock_filter'])) $query_string .= "&stock_filter=" . urlencode($_GET['stock_filter']);
                                if(!empty($_GET['threshold'])) $query_string .= "&threshold=" . urlencode($_GET['threshold']);
                                if(!empty($_GET['at_date'])) $query_string .= "&at_date=" . urlencode($_GET['at_date']);
                                
                                // Nút Trước
                                if ($current_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page - 1) . $query_string . '">&laquo; Trước</a></li>';
                                } else {
                                    echo '<li class="page-item disabled"><span class="page-link">&laquo; Trước</span></li>';
                                }
                                
                                // Số trang
                                $start = max(1, $current_page - 2);
                                $end = min($total_pages, $current_page + 2);
                                
                                if($start > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . $query_string . '">1</a></li>';
                                    if($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                
                                for ($i = $start; $i <= $end; $i++) {
                                    $active = ($i == $current_page) ? "active" : "";
                                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . $query_string . '">' . $i . '</a></li>';
                                }
                                
                                if($end < $total_pages){
                                    if($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . $query_string . '">' . $total_pages . '</a></li>';
                                }
                                
                                // Nút Sau
                                if ($current_page < $total_pages) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page + 1) . $query_string . '">Sau &raquo;</a></li>';
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
