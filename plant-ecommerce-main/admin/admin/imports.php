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
                <h1 class="display-4">Quản Lý Nhập Hàng</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <a href="import_create.php" class="btn btn-success mb-2"><i class="fas fa-plus"></i> Tạo Phiếu Nhập Mới</a>
            </div>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" placeholder="Mã phiếu hoặc NCC..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="draft" <?= ($_GET['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Nháp</option>
                                <option value="completed" <?= ($_GET['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
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
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Tìm kiếm</button>
                            <a href="imports.php" class="btn btn-outline-secondary">Xóa lọc</a>
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
                                    <th>Mã phiếu</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Ngày nhập</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ghi chú</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $where_clauses = [];
                                if(!empty($_GET['status'])){
                                    $status = mysqli_real_escape_string($conn, $_GET['status']);
                                    $where_clauses[] = "ir.status = '$status'";
                                }
                                if(!empty($_GET['search'])){
                                    $search = mysqli_real_escape_string($conn, $_GET['search']);
                                    $where_clauses[] = "(ir.supplier_name LIKE '%$search%' OR ir.id LIKE '%$search%')";
                                }
                                if(!empty($_GET['from_date'])){
                                    $from = mysqli_real_escape_string($conn, $_GET['from_date']);
                                    $where_clauses[] = "ir.import_date >= '$from'";
                                }
                                if(!empty($_GET['to_date'])){
                                    $to = mysqli_real_escape_string($conn, $_GET['to_date']);
                                    $where_clauses[] = "ir.import_date <= '$to'";
                                }
                                
                                $where = '';
                                if(!empty($where_clauses)){
                                    $where = " WHERE " . implode(' AND ', $where_clauses);
                                }
                                
                                $query = "SELECT ir.*, 
                                    (SELECT SUM(ird.quantity * ird.import_price) FROM import_receipt_details ird WHERE ird.receipt_id = ir.id) as total_amount,
                                    (SELECT COUNT(*) FROM import_receipt_details ird WHERE ird.receipt_id = ir.id) as item_count
                                    FROM import_receipts ir $where ORDER BY ir.created_at DESC";
                                $result = mysqli_query($conn, $query);

                                if(mysqli_num_rows($result) == 0) {
                                    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Chưa có phiếu nhập nào</td></tr>";
                                }

                                while ($row = mysqli_fetch_array($result)) {
                                    $statusBadge = ($row['status'] == 'completed') 
                                        ? "<span class='badge bg-success'>Hoàn thành</span>" 
                                        : "<span class='badge bg-warning text-dark'>Nháp</span>";
                                    
                                    $total = $row['total_amount'] ?? 0;
                                    
                                    echo "<tr>";
                                    echo "<td><strong>PN-" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row['supplier_name']) . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['import_date'])) . "</td>";
                                    echo "<td>" . number_format($total, 0, ',', '.') . " VNĐ <small class='text-muted'>({$row['item_count']} SP)</small></td>";
                                    echo "<td>$statusBadge</td>";
                                    echo "<td>" . htmlspecialchars(mb_substr($row['notes'] ?? '', 0, 30)) . "</td>";
                                    echo "<td>";
                                    
                                    if($row['status'] == 'draft'){
                                        echo "<a href='import_edit.php?id={$row['id']}' class='btn btn-primary btn-sm me-1' title='Sửa'><i class='fas fa-edit'></i></a>";
                                        echo "<a href='scripts/complete_import.php?id={$row['id']}' class='btn btn-success btn-sm me-1' title='Hoàn thành' onclick=\"return confirm('Hoàn thành phiếu nhập? Tồn kho và giá vốn sẽ được cập nhật.');\"><i class='fas fa-check'></i></a>";
                                        echo "<a href='scripts/delete_import.php?id={$row['id']}' class='btn btn-danger btn-sm' title='Xóa' onclick=\"return confirm('Xóa phiếu nhập này?');\"><i class='fas fa-trash'></i></a>";
                                    } else {
                                        echo "<a href='import_edit.php?id={$row['id']}' class='btn btn-info btn-sm' title='Xem'><i class='fas fa-eye'></i></a>";
                                    }
                                    
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
