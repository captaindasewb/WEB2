<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}
require "includes/header.php";
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Tất Cả Sản Phẩm</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <a href="product.php" class="btn btn-success"><i class="fas fa-plus"></i> Thêm Sản Phẩm</a>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-3 bg-light">
                <div class="card-body">
                    <form class="row g-2 align-items-end" method="GET">
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" placeholder="Tên sản phẩm..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-1">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <?php
                                $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY title");
                                while($cat = mysqli_fetch_assoc($cats)):
                                ?>
                                <option value="<?= $cat['id'] ?>" <?= ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['title']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-1">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="selling" <?= ($_GET['status'] ?? '') == 'selling' ? 'selected' : '' ?>>Đang bán</option>
                                <option value="out" <?= ($_GET['status'] ?? '') == 'out' ? 'selected' : '' ?>>Hết hàng</option>
                                <option value="hidden" <?= ($_GET['status'] ?? '') == 'hidden' ? 'selected' : '' ?>>Ẩn</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-1">Sắp xếp</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?= ($_GET['sort'] ?? '') == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="name_asc" <?= ($_GET['sort'] ?? '') == 'name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                                <option value="price_asc" <?= ($_GET['sort'] ?? '') == 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                                <option value="price_desc" <?= ($_GET['sort'] ?? '') == 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                                <option value="qty_asc" <?= ($_GET['sort'] ?? '') == 'qty_asc' ? 'selected' : '' ?>>Tồn kho thấp</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Lọc</button>
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
                                    <th>Hình</th>
                                    <th>Tên SP</th>
                                    <th>Loại</th>
                                    <th>ĐVT</th>
                                    <th>Tồn kho</th>
                                    <th>Giá vốn</th>
                                    <th>Giá bán</th>
                                    <th>Hiện trạng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                                $category = isset($_GET['category']) ? intval($_GET['category']) : '';
                                $status = isset($_GET['status']) ? $_GET['status'] : '';
                                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
                                
                                $where_clauses = ["(p.is_deleted_admin = 0 OR p.is_deleted_admin IS NULL)"];
                                if (!empty($search)) {
                                    $where_clauses[] = "p.title LIKE '%$search%'";
                                }
                                if (!empty($category)) {
                                    $where_clauses[] = "p.category = $category";
                                }
                                if ($status == 'selling') {
                                    $where_clauses[] = "p.visibility = 1 AND p.qty > 0";
                                } elseif ($status == 'out') {
                                    $where_clauses[] = "p.visibility = 1 AND p.qty <= 0";
                                } elseif ($status == 'hidden') {
                                    $where_clauses[] = "p.visibility = 0";
                                }
                                
                                $where_sql = "WHERE " . implode(' AND ', $where_clauses);
                                
                                $order_by = "p.id DESC"; // newest
                                if ($sort == 'name_asc') {
                                    $order_by = "p.title ASC";
                                } elseif ($sort == 'price_asc') {
                                    $order_by = "(p.cost_price * (1 + p.profit_margin / 100)) ASC";
                                } elseif ($sort == 'price_desc') {
                                    $order_by = "(p.cost_price * (1 + p.profit_margin / 100)) DESC";
                                } elseif ($sort == 'qty_asc') {
                                    $order_by = "p.qty ASC";
                                }

                                // Phân trang
                                $items_per_page = 10;
                                $count_query = "SELECT COUNT(*) as total FROM products p $where_sql";
                                $count_result = mysqli_query($conn, $count_query);
                                $total_items = mysqli_fetch_assoc($count_result)['total'];
                                $total_pages = ceil($total_items / $items_per_page);
                                $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                                if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
                                $offset = ($current_page - 1) * $items_per_page;
                                
                                $query = "SELECT p.*, c.title as category_name FROM products p LEFT JOIN categories c ON p.category = c.id $where_sql ORDER BY $order_by LIMIT $items_per_page OFFSET $offset";
                                $result = mysqli_query($conn, $query);

                                while ($row = mysqli_fetch_array($result)) {
                                    if ($row['visibility'] == 1) {
                                        $visibilityBadge = ($row['qty'] > 0) 
                                            ? "<span class='badge bg-success badge-visibility'>Đang bán</span>" 
                                            : "<span class='badge bg-danger badge-visibility'>Hết hàng</span>";
                                    } else {
                                        $visibilityBadge = "<span class='badge bg-secondary badge-visibility'>Ẩn</span>";
                                    }
                                    
                                    $selling_price = round($row['cost_price'] * (1 + $row['profit_margin'] / 100));
                                    
                                    echo "<tr>";

                                    
                                    if(!empty($row['image'])){
                                        echo "<td><img class='adminimg' src='../../plant-ecommerce/img/product/{$row['image']}' /></td>";
                                    } else {
                                        echo "<td><span class='text-muted'><i class='fas fa-image'></i></span></td>";
                                    }
                                    
                                    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['category_name'] ?? 'N/A') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                                    echo "<td>" . $row['qty'] . "</td>";
                                    echo "<td>" . number_format($row['cost_price'], 0, ',', '.') . "</td>";
                                    echo "<td><strong>" . number_format($selling_price, 0, ',', '.') . "</strong></td>";
                                    echo "<td>$visibilityBadge</td>";
                                    echo "<td>
                                            <a href='edit_product.php?id={$row['id']}' class='btn btn-primary btn-sm me-1'><i class='fas fa-edit'></i></a>
                                            <a href='scripts/delete_script_product.php?id={$row['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Bạn có chắc muốn xóa sản phẩm này?');\"><i class='fas fa-trash'></i></a>
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
                                if (!empty($_GET['search'])) $query_string .= "&search=" . urlencode($_GET['search']);
                                if (!empty($_GET['category'])) $query_string .= "&category=" . urlencode($_GET['category']);
                                if (!empty($_GET['status'])) $query_string .= "&status=" . urlencode($_GET['status']);
                                if (!empty($_GET['sort'])) $query_string .= "&sort=" . urlencode($_GET['sort']);
                                
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