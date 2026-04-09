<?php
session_start();
require './includes/conn.php';
require "./includes/head.php";

if (!isset($_SESSION['user_id'])) {
    echo "<script> location.href='/ecommerce'; </script>";
    exit();
}

if (!isset($_GET['id'])) {
    echo "Order not found";
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get order info
$query = "SELECT * FROM orders WHERE id=$order_id AND user_id='$user_id'";
$result = mysqli_query($con, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "Order not found";
    exit();
}

// Get order items
$items_query = "SELECT oi.*, p.title, p.image, p.price as current_price 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = $order_id";
$items_result = mysqli_query($con, $items_query);
?>

<section class="confirmation_part padding_top">
<div class="container">
<h3>Chi tiết đơn hàng #<?= $order_id ?></h3>

<div class="row mt-4">
    <div class="col-md-6">
        <table class="table">
            <tr><td><strong>Mã đơn:</strong></td><td>#<?= $order['id'] ?></td></tr>
            <tr><td><strong>Ngày đặt:</strong></td><td><?= $order['order_date'] ?></td></tr>
            <tr><td><strong>Trạng thái:</strong></td><td><span class="badge bg-info"><?php
                $statusMap = [
                    'pending' => 'Chưa xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'Confirmed' => 'Đã xác nhận', // Fallback
                    'shipped' => 'Đã giao',
                    'delivered' => 'Đã giao',
                    'cancelled' => 'Đã hủy',
                ];
                echo $statusMap[$order['status']] ?? $order['status'];
            ?></span></td></tr>
            <tr><td><strong>Thanh toán:</strong></td><td><span class="badge bg-primary"><?= htmlspecialchars($order['payment_method'] ?? 'Tiền mặt') ?></span></td></tr>
        </table>
    </div>
</div>

<h4 class="mt-3">Sản phẩm</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Đơn giá</th>
            <th>Số lượng</th>
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
                    <img src="img/product/<?= htmlspecialchars($item['image']) ?>" width="80">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item['title'] ?? 'Sản phẩm đã xóa') ?></td>
            <td><?= number_format($item['unit_price'], 0, ',', '.') ?> VNĐ</td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($line_total, 0, ',', '.') ?> VNĐ</td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end"><strong>Tạm tính:</strong></td>
            <td><?= number_format($subtotal, 0, ',', '.') ?> VNĐ</td>
        </tr>
        <tr>
            <td colspan="4" class="text-end"><strong>Phí vận chuyển:</strong></td>
            <td><?= number_format($order['shipping_fee'] ?? 0, 0, ',', '.') ?> VNĐ</td>
        </tr>
        <tr>
            <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
            <td><strong style="color:#ff3366"><?= number_format($order['order_amount'], 0, ',', '.') ?> VNĐ</strong></td>
        </tr>
    </tfoot>
</table>

<a href="allorders.php" class="btn btn-secondary mt-3">← Quay lại</a>

</div>
</section>

<?php require './includes/footer.php'; ?>