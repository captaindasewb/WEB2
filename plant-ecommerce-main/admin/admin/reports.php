<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}

// Default date range: current month
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-d');

require "includes/header.php";
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Báo Cáo Nhập - Xuất</h1>
            </div>
        </div>

        <div class="container">
            <!-- Date filter -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-chart-bar me-1"></i>Xem Báo Cáo</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            $from_escaped = mysqli_real_escape_string($conn, $from_date);
            $to_escaped = mysqli_real_escape_string($conn, $to_date);
            
            // Import stats
            $import_stats = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COUNT(*) as total_receipts, 
                    COALESCE(SUM(sub.total), 0) as total_amount
                FROM import_receipts ir
                LEFT JOIN (SELECT receipt_id, SUM(quantity * import_price) as total FROM import_receipt_details GROUP BY receipt_id) sub ON ir.id = sub.receipt_id
                WHERE ir.status = 'completed' AND ir.import_date BETWEEN '$from_escaped' AND '$to_escaped'"
            ));
            
            // Import quantity
            $import_qty = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COALESCE(SUM(ird.quantity), 0) as total_qty
                FROM import_receipt_details ird 
                JOIN import_receipts ir ON ird.receipt_id = ir.id
                WHERE ir.status = 'completed' AND ir.import_date BETWEEN '$from_escaped' AND '$to_escaped'"
            ));
            
            // Order stats
            $order_stats = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COUNT(*) as total_orders, COALESCE(SUM(order_amount), 0) as total_revenue
                FROM orders WHERE status IN ('delivered', 'shipped') AND order_date BETWEEN '$from_escaped' AND '$to_escaped 23:59:59'"
            ));
            
            // Profit stats
            $profit_stats = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COALESCE(SUM(oi.quantity * (oi.unit_price - p.cost_price)), 0) as total_profit
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.status IN ('delivered', 'shipped') AND o.order_date BETWEEN '$from_escaped' AND '$to_escaped 23:59:59'"
            ));
            
            $all_orders = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COUNT(*) as cnt FROM orders WHERE order_date BETWEEN '$from_escaped' AND '$to_escaped 23:59:59'"
            ));
            
            $cancelled = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT COUNT(*) as cnt FROM orders WHERE status = 'cancelled' AND order_date BETWEEN '$from_escaped' AND '$to_escaped 23:59:59'"
            ));
            
            // Revenue by category (for pie chart)
            $cat_revenue_query = "
                SELECT c.title as category_name, COALESCE(SUM(oi.quantity * oi.unit_price), 0) as total_revenue
                FROM orders o 
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN categories c ON p.category = c.id
                WHERE o.status IN ('delivered', 'shipped') AND o.order_date BETWEEN '$from_escaped' AND '$to_escaped 23:59:59'
                GROUP BY c.id
                ORDER BY total_revenue DESC
            ";
            $cat_revenue_result = mysqli_query($conn, $cat_revenue_query);
            $chart_labels = [];
            $chart_data = [];
            $chart_colors = [];
            
            $color_palette = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6c757d'];
            $color_index = 0;
            
            while($row = mysqli_fetch_assoc($cat_revenue_result)) {
                if($row['total_revenue'] > 0) {
                    $chart_labels[] = $row['category_name'] ? $row['category_name'] : 'Khác';
                    $chart_data[] = $row['total_revenue'];
                    $chart_colors[] = $color_palette[$color_index % count($color_palette)];
                    $color_index++;
                }
            }
            ?>

            <!-- Summary cards -->
            <div class="row row-cols-1 row-cols-md-5 g-3 mb-4">
                <div class="col">
                    <div class="card shadow-sm border-0 text-center p-3" style="border-left:4px solid #0d6efd !important;">
                        <h6 class="text-muted">Tổng Nhập</h6>
                        <h3 class="text-primary"><?= number_format($import_stats['total_amount'], 0, ',', '.') ?></h3>
                        <small><?= $import_stats['total_receipts'] ?> phiếu nhập | <?= $import_qty['total_qty'] ?> SP</small>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border-0 text-center p-3" style="border-left:4px solid #198754 !important;">
                        <h6 class="text-muted">Doanh Thu (Đã giao)</h6>
                        <h3 class="text-success"><?= number_format($order_stats['total_revenue'], 0, ',', '.') ?></h3>
                        <small><?= $order_stats['total_orders'] ?> đơn đã giao</small>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border-0 text-center p-3" style="border-left:4px solid #0dcaf0 !important;">
                        <h6 class="text-muted">Lợi Nhuận (Đã giao)</h6>
                        <h3 class="text-info"><?= number_format($profit_stats['total_profit'], 0, ',', '.') ?></h3>
                        <small>Từ đơn đã giao</small>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border-0 text-center p-3" style="border-left:4px solid #ffc107 !important;">
                        <h6 class="text-muted">Tổng Đơn Hàng</h6>
                        <h3 class="text-warning"><?= $all_orders['cnt'] ?></h3>
                        <small>Khoảng tgian trên</small>
                    </div>
                </div>
                <div class="col">
                    <div class="card shadow-sm border-0 text-center p-3" style="border-left:4px solid #dc3545 !important;">
                        <h6 class="text-muted">Đơn Đã Hủy</h6>
                        <h3 class="text-danger"><?= $cancelled['cnt'] ?></h3>
                        <small>Khoảng tgian trên</small>
                    </div>
                </div>
            </div>

            <!-- Revenue Pie Chart -->
            <?php if(count($chart_data) > 0): ?>
            <div class="row mb-4">
                <div class="col-md-6 offset-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Cơ Cấu Doanh Thu Theo Danh Mục</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="revenuePieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Import details table -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-download me-2 text-primary"></i>Chi Tiết Nhập Hàng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày nhập</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Số SP</th>
                                    <th>Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $imports = mysqli_query($conn, 
                                    "SELECT ir.*, 
                                        (SELECT SUM(ird.quantity * ird.import_price) FROM import_receipt_details ird WHERE ird.receipt_id = ir.id) as total_amount,
                                        (SELECT SUM(ird.quantity) FROM import_receipt_details ird WHERE ird.receipt_id = ir.id) as total_qty
                                    FROM import_receipts ir 
                                    WHERE ir.status = 'completed' AND ir.import_date BETWEEN '$from_escaped' AND '$to_escaped' 
                                    ORDER BY ir.import_date DESC"
                                );
                                if(mysqli_num_rows($imports) == 0){
                                    echo "<tr><td colspan='5' class='text-center py-3 text-muted'>Không có dữ liệu nhập hàng</td></tr>";
                                }
                                while($r = mysqli_fetch_assoc($imports)):
                                ?>
                                <tr>
                                    <td>PN-<?= str_pad($r['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= date('d/m/Y', strtotime($r['import_date'])) ?></td>
                                    <td><?= htmlspecialchars($r['supplier_name']) ?></td>
                                    <td><?= $r['total_qty'] ?></td>
                                    <td><?= number_format($r['total_amount'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order details table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-upload me-2 text-success"></i>Chi Tiết Đơn Hàng (Đã Giao)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Ngày</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Tổng tiền</th>
                                    <th>Lợi nhuận</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $orders = mysqli_query($conn, 
                                    "SELECT o.*, u.first_name, u.last_name,
                                        GROUP_CONCAT(p.title SEPARATOR ', ') as product_names,
                                        COUNT(oi.id) as item_count,
                                        SUM((oi.unit_price - p.cost_price) * oi.quantity) as order_profit
                                    FROM orders o 
                                    LEFT JOIN order_items oi ON oi.order_id = o.id
                                    LEFT JOIN products p ON oi.product_id = p.id 
                                    LEFT JOIN users u ON o.user_id = u.id 
                                    WHERE o.status IN ('delivered', 'shipped') AND o.order_date BETWEEN '$from_escaped' AND '$to_escaped 23:59:59' 
                                    GROUP BY o.id
                                    ORDER BY o.order_date DESC"
                                );
                                if(mysqli_num_rows($orders) == 0){
                                    echo "<tr><td colspan='6' class='text-center py-3 text-muted'>Không có đơn hàng đã giao</td></tr>";
                                }
                                while($r = mysqli_fetch_assoc($orders)):
                                    $product_display = $r['product_names'] ?? 'N/A';
                                    if ($r['item_count'] > 1) {
                                        $product_display = mb_substr($product_display, 0, 40) . '... (' . $r['item_count'] . ' SP)';
                                    }
                                ?>
                                <tr>
                                    <td>#<?= $r['id'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($r['order_date'])) ?></td>
                                    <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                    <td><?= htmlspecialchars($product_display) ?></td>
                                    <td><?= number_format($r['order_amount'], 0, ',', '.') ?></td>
                                    <td class="text-success fw-bold"><?= number_format($r['order_profit'] ?? 0, 0, ',', '.') ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if(count($chart_data) > 0): ?>
const ctx = document.getElementById('revenuePieChart');
if (ctx) {
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                data: <?= json_encode($chart_data) ?>,
                backgroundColor: <?= json_encode($chart_colors) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.raw !== null) {
                                label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.raw);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}
<?php endif; ?>
</script>
</body>
</html>
