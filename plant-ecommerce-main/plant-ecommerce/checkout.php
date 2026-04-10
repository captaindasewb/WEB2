<?php
session_start();
require './includes/conn.php';
require "./includes/head.php";

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    echo "<script>location.href='login.php';</script>"; // Chuyển về login sẽ hợp lý hơn
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. LẤY THÔNG TIN USER
$stmt = $con->prepare("SELECT first_name, last_name, mobile, email, address FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Không tìm thấy thông tin tài khoản.");
}

// 2. TÁCH ĐỊA CHỈ (Giả định lưu dạng: Số nhà, Phường/Xã, Quận/Huyện, Tỉnh/TP)
$address_parts = array_map('trim', explode(',', $user['address'] ?? ''));
$street_val   = $address_parts[0] ?? '';
$ward_val     = $address_parts[1] ?? '';
$district_val = $address_parts[2] ?? '';

// 3. LẤY GIỎ HÀNG
$query = "SELECT p.id, p.title, p.price, p.qty AS stock, c.qty 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = ?";

$stmt_cart = $con->prepare($query);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result = $stmt_cart->get_result();

$sum = 0;
$quantity = 0;
$cart_items = [];

while ($row = $result->fetch_assoc()) {
    if ($row['qty'] > $row['stock']) {
        echo "<script>alert('Sản phẩm \"".htmlspecialchars($row['title'])."\" chỉ còn {$row['stock']} sản phẩm trong kho. Vui lòng cập nhật lại giỏ hàng!'); window.location='cart.php';</script>";
        exit();
    }
    $row['subtotal'] = $row['price'] * $row['qty'];
    $sum += $row['subtotal'];
    $quantity += $row['qty'];
    $cart_items[] = $row;
}
$stmt_cart->close();

if ($quantity == 0) {
    echo "<script>alert('Giỏ hàng của bạn đang trống!'); window.location='cart.php';</script>";
    exit();
}

// 4. TÍNH SHIPPING (Ví dụ: 10k mỗi sản phẩm)
$shipping = $quantity * 10000;
$total = $sum + $shipping;
?>

<section class="checkout_area padding_top">
    <div class="container">
        <form class="contact_form" action="confirmation.php" method="post">
            <div class="row">
                <div class="col-lg-7">
                    <div class="billing_details">
                        <h3 class="mb-4">Thông tin giao hàng</h3>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Họ</label>
                                <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tên</label>
                                <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Số điện thoại</label>
                                <input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Quận/Huyện</label>
                                <select name="district" id="district" class="form-control" required>
                                    <option value="">Chọn Quận/Huyện</option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Phường/Xã</label>
                                <select name="ward" id="ward" class="form-control" required>
                                    <option value="">Chọn Phường/Xã</option>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Số nhà, Tên đường</label>
                                <input type="text" class="form-control" name="street" id="street" value="<?= htmlspecialchars($street_val) ?>" placeholder="Ví dụ: 123 Đường ABC" required>
                            </div>
                           
                            <!-- PHƯƠNG THỨC THANH TOÁN -->
                            <div class="col-md-12 mt-4">
                                <h4 class="mb-3">Phương thức thanh toán</h4>
                                <div class="p-3 border rounded bg-light mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="Tiền mặt" checked onChange="document.getElementById('bank_details').style.display='none'">
                                        <label class="form-check-label font-weight-bold" for="payment_cash" style="cursor: pointer;">
                                            Thanh toán khi nhận hàng (Tiền mặt)
                                        </label>
                                    </div>
                                </div>
                                                                <div class="p-3 border rounded bg-light mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="Tiền mặt" checked onChange="document.getElementById('bank_details').style.display='none'">
                                        <label class="form-check-label font-weight-bold" for="payment_cash" style="cursor: pointer;">
                                            Thanh toán trực tuyến
                                        </label>
                                    </div>
                                </div>
                                <div class="p-3 border rounded bg-light">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="Chuyển khoản" onChange="document.getElementById('bank_details').style.display='block'">
                                        <label class="form-check-label font-weight-bold" for="payment_bank" style="cursor: pointer;">
                                            Chuyển khoản ngân hàng
                                        </label>
                                    </div>
                                    <div id="bank_details" class="mt-3 p-3 bg-white border border-info rounded" style="display: none;">
                                        <p class="mb-2"><strong>Ngân hàng:</strong> Vietcombank (VCB)</p>
                                        <p class="mb-2"><strong>Số tài khoản:</strong> 0123456789</p>
                                        <p class="mb-2"><strong>Chủ tài khoản:</strong> TKI PLANT SHOP</p>
                                        <p class="mb-0 text-muted"><small><em>Lưu ý: Quý khách có thể chuyển khoản sau khi nhân viên gọi điện xác nhận đơn hàng thành công.</em></small></p>
                                    </div>
                                </div>
                            </div>
                             
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="order_box">
                        <h2 class="border-bottom pb-3">Đơn hàng của bạn</h2>
                        <ul class="list list_2">
                            <li class="d-flex justify-content-between font-weight-bold">
                                <span>Sản phẩm</span>
                                <span>Tổng cộng</span>
                            </li>
                            <hr>
                            <?php foreach ($cart_items as $item): ?>
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-truncate" style="max-width: 200px;">
                                    <?= htmlspecialchars($item['title']) ?> 
                                    <strong class="text-muted">x <?= $item['qty'] ?></strong>
                                </span>
                                <span><?= number_format($item['subtotal'], 0, ',', '.') ?> VNĐ</span>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <ul class="list list_2 mt-4 pt-3 border-top">
                            <li class="d-flex justify-content-between">
                                <span>Tạm tính</span>
                                <span><?= number_format($sum, 0, ',', '.') ?> VNĐ</span>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span>Phí vận chuyển</span>
                                <span><?= number_format($shipping, 0, ',', '.') ?> VNĐ</span>
                            </li>
                            <li class="d-flex justify-content-between font-weight-bold mt-2" style="font-size: 1.2rem; color: #ff3366;">
                                <span>TỔNG CỘNG</span>
                                <span><?= number_format($total, 0, ',', '.') ?> VNĐ</span>
                            </li>
                        </ul>

                        <div class="payment_item mt-4">
                            <p>Vui lòng kiểm tra kỹ thông tin trước khi đặt hàng.</p>
                        </div>

                        <button type="submit" class="btn_3 w-100 mt-3" style="border: none; cursor: pointer;">
                            XÁC NHẬN THANH TOÁN
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script src="js/hcm_data.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const districtSelect = document.getElementById("district");
    const wardSelect = document.getElementById("ward");
    
    const savedDistrict = "<?= addslashes($district_val) ?>";
    const savedWard = "<?= addslashes($ward_val) ?>";

    hcmData.forEach(function (district) {
        let option = document.createElement("option");
        option.value = district.name;
        option.text = district.name;
        if (district.name === savedDistrict) {
            option.selected = true;
        }
        districtSelect.appendChild(option);
    });

    function loadWards(selectedDistrictName) {
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        const districtObj = hcmData.find(d => d.name === selectedDistrictName);
        if (districtObj) {
            districtObj.wards.forEach(function (ward) {
                let option = document.createElement("option");
                option.value = ward;
                option.text = ward;
                if (ward === savedWard) {
                    option.selected = true;
                }
                wardSelect.appendChild(option);
            });
        }
    }

    if (savedDistrict) {
        loadWards(savedDistrict);
    }

    districtSelect.addEventListener("change", function () {
        loadWards(this.value);
    });
});
</script>

<?php require "./includes/footer.php" ?>