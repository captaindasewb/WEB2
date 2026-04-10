<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: order.php");
    exit();
}

$order_id = intval($_GET['id']);
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.mobile, u.address
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if(!$order){
    header("Location: order.php?error=Đơn hàng không tồn tại");
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.title, p.image, p.price as current_price, p.unit
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id";
$items_result = mysqli_query($conn, $items_query);

$statusMap = [
    'pending' => ['Chưa xử lý', 'bg-warning text-dark'],
    'confirmed' => ['Đã xác nhận', 'bg-info'],
    'Confirmed' => ['Đã xác nhận', 'bg-info'],
    'shipped' => ['Đã giao', 'bg-success'],
    'delivered' => ['Đã giao', 'bg-success'],
    'cancelled' => ['Đã hủy', 'bg-danger'],
];
$st = $statusMap[$order['status']] ?? [$order['status'], 'bg-secondary'];

require "includes/header.php";
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Chi Tiết Đơn Hàng #<?= $order_id ?></h1>
                <span class="badge <?= $st[1] ?> fs-6"><?= $st[0] ?></span>
            </div>
        </div>

        <div class="container col-md-10 my-4">
            <div class="row g-4">
                <!-- Customer Info -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông Tin Khách Hàng</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:120px;">Họ tên:</td>
                                    <td><strong><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td><?= htmlspecialchars($order['email']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">SĐT:</td>
                                    <td><?= htmlspecialchars($order['mobile']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Địa chỉ:</td>
                                    <td><?= htmlspecialchars($order['address']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Info -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Thông Tin Đơn Hàng</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width:120px;">Mã đơn:</td>
                                    <td><strong>#<?= $order['id'] ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ngày đặt:</td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Trạng thái:</td>
                                    <td><span class="badge <?= $st[1] ?>"><?= $st[0] ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Thanh toán:</td>
                                    <td><strong><?= htmlspecialchars($order['payment_method'] ?? 'Tiền mặt') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tổng tiền:</td>
                                    <td><strong class="text-success fs-5"><?= number_format($order['order_amount'], 0, ',', '.') ?> VNĐ</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Product Items -->
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Sản Phẩm Đặt Hàng</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hình</th>
                                        <th>Tên SP</th>
                                        <th>Đơn giá</th>
                                        <th>SL</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal = 0;
                                    while($item = mysqli_fetch_assoc($items_result)): 
                                        $line_total = $item['unit_price'] * $item['quantity'];
                                        $subtotal += $line_total;
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if(!empty($item['image'])): ?>
                                                <img src="../../plant-ecommerce/img/product/<?= htmlspecialchars($item['image']) ?>" style="max-height:60px; border-radius:4px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['title'] ?? 'SP đã xóa') ?></td>
                                        <td><?= number_format($item['unit_price'], 0, ',', '.') ?> VNĐ</td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><strong><?= number_format($line_total, 0, ',', '.') ?> VNĐ</strong></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="text-end">Tạm tính:</td>
                                        <td><?= number_format($subtotal, 0, ',', '.') ?> VNĐ</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end">Phí vận chuyển:</td>
                                        <td><?= number_format($order['shipping_fee'] ?? 0, 0, ',', '.') ?> VNĐ</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end"><strong>Tổng cộng:</strong></td>
                                        <td><strong class="text-success"><?= number_format($order['order_amount'], 0, ',', '.') ?> VNĐ</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="order.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại Danh sách
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
