<?php
require 'includes/conn.php';
session_start();

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}

// Handle status update
if(isset($_POST['update_status'])){
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $allowed = ['confirmed','shipped','cancelled'];
    if(in_array($new_status, $allowed)){
        mysqli_query($conn, "UPDATE orders SET status = '$new_status' WHERE id = $order_id");
        header("Location: order.php?success=Cập nhật trạng thái đơn hàng #$order_id thành công");
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
                <h1 class="display-4">Quản Lý Đơn Hàng</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="confirmed" <?= ($_GET['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                <option value="shipped" <?= ($_GET['status'] ?? '') == 'shipped' ? 'selected' : '' ?>>Đã giao</option>
                                <option value="cancelled" <?= ($_GET['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sắp xếp theo</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?= ($_GET['sort'] ?? '') == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="oldest" <?= ($_GET['sort'] ?? '') == 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                                <option value="address" <?= ($_GET['sort'] ?? '') == 'address' ? 'selected' : '' ?>>Địa chỉ (phường)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tìm địa chỉ/phường</label>
                            <input type="text" name="search_address" class="form-control" placeholder="VD: Phường 5..." value="<?= htmlspecialchars($_GET['search_address'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Lọc</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Ngày đặt</th>
                                    <th>Khách hàng</th>
                                    <th>Địa chỉ giao hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $where_clauses = [];
                                if(!empty($_GET['status'])){
                                    $status = mysqli_real_escape_string($conn, $_GET['status']);
                                    $where_clauses[] = "o.status = '$status'";
                                }
                                if(!empty($_GET['from_date'])){
                                    $from = mysqli_real_escape_string($conn, $_GET['from_date']);
                                    $where_clauses[] = "o.order_date >= '$from'";
                                }
                                if(!empty($_GET['to_date'])){
                                    $to = mysqli_real_escape_string($conn, $_GET['to_date']);
                                    $where_clauses[] = "o.order_date <= '$to 23:59:59'";
                                }
                                if(!empty($_GET['search_address'])){
                                    $addr_search = mysqli_real_escape_string($conn, $_GET['search_address']);
                                    $where_clauses[] = "u.address LIKE '%$addr_search%'";
                                }
                                
                                $where = '';
                                if(!empty($where_clauses)){
                                    $where = ' AND ' . implode(' AND ', $where_clauses);
                                }
                                
                                // Sort order
                                $sort = $_GET['sort'] ?? 'newest';
                                $order_by = 'o.id DESC';
                                if($sort == 'oldest') $order_by = 'o.id ASC';
                                elseif($sort == 'address') $order_by = 'u.address ASC, o.id DESC';
                                
                                // Phân trang
                                $items_per_page = 10;
                                $count_query = "SELECT COUNT(DISTINCT o.id) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1 $where";
                                $count_result = mysqli_query($conn, $count_query);
                                $total_items = mysqli_fetch_assoc($count_result)['total'];
                                $total_pages = ceil($total_items / $items_per_page);
                                $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                                if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
                                $offset = ($current_page - 1) * $items_per_page;

                                $query = "SELECT o.*, u.first_name, u.last_name, u.address,
                                    GROUP_CONCAT(p.title SEPARATOR ', ') as product_names,
                                    COUNT(oi.id) as item_count
                                    FROM orders o 
                                    LEFT JOIN order_items oi ON oi.order_id = o.id
                                    LEFT JOIN products p ON oi.product_id = p.id 
                                    LEFT JOIN users u ON o.user_id = u.id 
                                    WHERE 1=1 $where 
                                    GROUP BY o.id
                                    ORDER BY $order_by 
                                    LIMIT $items_per_page OFFSET $offset";
                                $result = mysqli_query($conn, $query);

                                if(mysqli_num_rows($result) == 0){
                                    echo "<tr><td colspan='8' class='text-center py-4 text-muted'>Không có đơn hàng nào</td></tr>";
                                }

                                while ($row = mysqli_fetch_array($result)) {
                                    // Mảng dùng cho menu dropdown
                                    $statusMap = [
                                        'confirmed' => ['Đã xác nhận', 'bg-info'],
                                        'shipped' => ['Đã giao', 'bg-success'],
                                        'cancelled' => ['Đã hủy', 'bg-danger'],
                                    ];
                                    // Mảng dùng để hiển thị text (kể cả những đơn cũ có status cũ)
                                    $allStatusMap = array_merge([
                                        'pending' => ['Chưa xử lý', 'bg-warning text-dark'],
                                        'delivered' => ['Đã giao', 'bg-success'],
                                        'Confirmed' => ['Đã xác nhận', 'bg-info']
                                    ], $statusMap);
                                    
                                    $st = $allStatusMap[$row['status']] ?? [$row['status'], 'bg-secondary'];
                                    
                                    $product_display = $row['product_names'] ?? 'N/A';
                                    if ($row['item_count'] > 1) {
                                        $product_display = mb_substr($product_display, 0, 30) . '... (' . $row['item_count'] . ' SP)';
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td><strong>#" . $row['id'] . "</strong></td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['order_date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                    echo "<td><small>" . htmlspecialchars($row['address'] ?? 'N/A') . "</small></td>";
                                    echo "<td>" . htmlspecialchars($product_display) . "</td>";
                                    echo "<td><strong>" . number_format($row['order_amount'], 0, ',', '.') . "</strong></td>";
                                    echo "<td><span class='badge {$st[1]}'>{$st[0]}</span></td>";
                                    echo "<td>
                                        <a href='order_detail.php?id={$row['id']}' class='btn btn-info btn-sm me-1' title='Chi tiết'><i class='fas fa-eye'></i></a>
                                        <div class='btn-group'>
                                            <button type='button' class='btn btn-sm btn-outline-secondary dropdown-toggle' data-bs-toggle='dropdown'>
                                                <i class='fas fa-exchange-alt'></i>
                                            </button>
                                            <ul class='dropdown-menu'>";
                                    
                                    foreach($statusMap as $key => $val){
                                        if($key != $row['status']){
                                            echo "<li>
                                                <form method='POST' style='display:inline;'>
                                                    <input type='hidden' name='order_id' value='{$row['id']}'>
                                                    <input type='hidden' name='new_status' value='$key'>
                                                    <input type='hidden' name='update_status' value='1'>
                                                    <button type='submit' class='dropdown-item'>{$val[0]}</button>
                                                </form>
                                            </li>";
                                        }
                                    }
                                    
                                    echo "          </ul>
                                        </div>
                                    </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-center">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $query_string = "";
                                if(!empty($_GET['status'])) $query_string .= "&status=" . urlencode($_GET['status']);
                                if(!empty($_GET['from_date'])) $query_string .= "&from_date=" . urlencode($_GET['from_date']);
                                if(!empty($_GET['to_date'])) $query_string .= "&to_date=" . urlencode($_GET['to_date']);
                                if(!empty($_GET['search_address'])) $query_string .= "&search_address=" . urlencode($_GET['search_address']);
                                if(!empty($_GET['sort'])) $query_string .= "&sort=" . urlencode($_GET['sort']);
                                
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