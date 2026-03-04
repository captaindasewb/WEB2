<?php
session_start();
require './includes/conn.php';
require "./includes/head.php";

if (!isset($_SESSION['email'])) {
    echo "<script> location.href='/ecommerce'; </script>";
    exit();
}
?>

<section class="breadcrumb breadcrumb_bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="breadcrumb_iner">
                    <div class="breadcrumb_iner_item">
                        <h2>Giỏ hàng</h2>
                        <p>Trang chủ <span>-</span> Giỏ hàng</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$sum = 0;
$user_id = $_SESSION['user_id'];

// Truy vấn giỏ hàng
$stmt = $con->prepare("SELECT cart.id AS cart_id, cart.qty, cart.product_id, 
                              products.title, products.image, products.price 
                       FROM cart 
                       INNER JOIN products ON cart.product_id = products.id 
                       WHERE cart.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<section id="cart" class="cart_area padding_top">
    <div class="container">
        <div class="cart_inner">
            <div class="table-responsive">
                <table class="table" id="cart-table">
                    <thead>
                        <tr>
                            <th scope="col">Sản phẩm</th>
                            <th scope="col">Giá</th>
                            <th scope="col">Số lượng</th>
                            <th scope="col">Tổng</th>
                            <th scope="col">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { 
                            $subtotal = $row['qty'] * $row['price'];
                            $sum += $subtotal;
                        ?>
                            <tr data-cart-id="<?php echo $row['cart_id']; ?>" 
                                data-product-id="<?php echo $row['product_id']; ?>" 
                                data-price="<?php echo $row['price']; ?>">
                                <td>
                                    <div class="media">
                                        <div class="d-flex">
                                            <img width="100px" src="img/product/<?php echo htmlspecialchars($row['image']); ?>" alt="" />
                                        </div>
                                        <div class="media-body">
                                            <p><?php echo htmlspecialchars($row['title']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <h5><?php echo number_format($row['price'], 0, ',', '.') . ' VNĐ'; ?></h5>
                                </td>
                      <td>
                        <div class="product_count">
                          <span class="input-number-decrement decrement"> <i class="ti-angle-down"></i></span>
                          <input class="input-number qty-input" type="text" value="<?php echo $row['qty']; ?>" min="1" max="99" readonly>
                          <span class="input-number-increment increment"> <i class="ti-angle-up"></i></span>
                        </div>
                      </td>
                                <td class="subtotal">
                                    <h5><?php echo number_format($subtotal, 0, ',', '.') . ' VNĐ'; ?></h5>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-danger remove-item" 
                                            data-cart-id="<?php echo $row['cart_id']; ?>">
                                        ×
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td colspan="2"></td>
                            <td>
                                <h5>Tổng cộng</h5>
                            </td>
                            <td colspan="2">
                                <h5 id="total-amount"><?php echo number_format($sum, 0, ',', '.') . ' VNĐ'; ?></h5>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="checkout_btn_inner float-right mt-4">
                    <a class="btn_1" href="index.php">Tiếp tục mua sắm</a>
                    <a class="btn_1 checkout_btn_1" href="checkout.php">Thanh toán</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require './includes/footer.php'; ?>

<!-- Scripts -->
<script src="js/jquery-1.12.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.magnific-popup.js"></script>
<script src="js/swiper.min.js"></script>
<script src="js/masonry.pkgd.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/slick.min.js"></script>
<script src="js/jquery.counterup.min.js"></script>
<script src="js/waypoints.min.js"></script>
<script src="js/contact.js"></script>
<script src="js/jquery.ajaxchimp.min.js"></script>
<script src="js/jquery.form.js"></script>
<script src="js/jquery.validate.min.js"></script>
<script src="js/mail-script.js"></script>
<script src="js/stellar.js"></script>
<script src="js/price_rangs.js"></script>
<script src="js/custom.js"></script>

<!-- JS xử lý tăng/giảm số lượng & xóa sản phẩm -->
<script>
$(document).ready(function() {

    // Bỏ qua sự kiện click đã cung cấp bởi js/custom.js để tránh double count (tăng/giảm 2)
    $('.increment, .decrement').off('click');

    // Tăng số lượng
    $('.increment').on('click', function() {
        let input = $(this).siblings('.qty-input');
        let val = parseInt(input.val()) || 1;
        let row = $(this).closest('tr');
        let cartId = row.data('cart-id');
        if (val < 99) {
            let newVal = val + 1;
            input.val(newVal);
            updateSubtotal(input);
            updateTotal();
            updateQtyOnServer(cartId, newVal, input);
        }
    });

    // Giảm số lượng
    $('.decrement').on('click', function() {
        let input = $(this).siblings('.qty-input');
        let val = parseInt(input.val()) || 1;
        let row = $(this).closest('tr');
        let cartId = row.data('cart-id');
        if (val > 1) {
            let newVal = val - 1;
            input.val(newVal);
            updateSubtotal(input);
            updateTotal();
            updateQtyOnServer(cartId, newVal, input);
        }
    });

    // XÓA SẢN PHẨM - phiên bản debug & ổn định hơn
    $('.remove-item').on('click', function(e) {
        e.preventDefault(); // phòng trường hợp button nằm trong form

        if (!confirm('Bạn chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
            return;
        }

        let btn         = $(this);
        let row         = btn.closest('tr');
        let cartId      = row.data('cart-id');

        if (!cartId) {
            alert('Không tìm thấy thông tin giỏ hàng. Vui lòng tải lại trang.');
            return;
        }

        console.log('[XÓA] Bắt đầu xóa bản ghi giỏ hàng:', { 
            cart_id: cartId, 
            url: './scripts/cart_remove.php',
        });

        btn.prop('disabled', true).text('Đang xóa...');

        $.ajax({
            url: './scripts/cart_remove.php',
            type: 'GET',
            data: { 
                id: cartId
            },
            timeout: 8000,                      // timeout 8 giây
            cache: false,
            success: function(response, status, xhr) {
                console.log('[XÓA] Success - HTTP status:', xhr.status);
                console.log('[XÓA] Response (một phần):', response.substring(0, 200)); // xem nội dung trả về

                // Dù file redirect, miễn status 200/302 → coi như thành công
                location.reload(true); // reload cứng, bỏ cache
            },
            error: function(xhr, status, errorMsg) {
                console.error('[XÓA] Lỗi AJAX:', {
                    status: status,
                    http_code: xhr.status,
                    error: errorMsg,
                    response: xhr.responseText ? xhr.responseText.substring(0, 300) : 'không có phản hồi'
                });

                let msg = 'Lỗi khi xóa sản phẩm.\n';
                if (xhr.status === 404) {
                    msg += 'Không tìm thấy file cart_remove.php (kiểm tra đường dẫn)';
                } else if (xhr.status === 500) {
                    msg += 'Lỗi server (xem error_log hoặc console để biết chi tiết)';
                } else if (status === 'timeout') {
                    msg += 'Hết thời gian chờ phản hồi từ server';
                } else {
                    msg += 'Chi tiết: ' + status + ' - ' + errorMsg;
                }

                alert(msg);
                btn.prop('disabled', false).text('×');
            }
        });
    });

    // Hàm cập nhật số lượng lên server
    function updateQtyOnServer(cartId, qty, inputElement) {
        $.ajax({
            url: './scripts/cart_update.php',
            type: 'GET',
            data: { id: cartId, qty: qty },
            success: function(response) {
                if(response.indexOf("INSUFFICIENT_STOCK") === 0) {
                    let stock = response.split(":")[1];
                    alert("Kho không đủ! Số lượng tối đa có thể mua là " + stock + ".");
                    inputElement.val(stock); // trả về giá trị max có thể
                    updateSubtotal(inputElement);
                    updateTotal();
                    
                    // Cập nhật lại số lượng (max stock) lên dbs
                    updateQtyOnServer(cartId, stock, inputElement);
                } else {
                    console.log('[UPDATE QTY] Success:', response);
                }
            },
            error: function() {
                console.error('[UPDATE QTY] Error');
            }
        });
    }

    // Cập nhật subtotal của dòng
    function updateSubtotal(input) {
        let row = input.closest('tr');
        let price = parseFloat(row.data('price')) || 0;
        let qty = parseInt(input.val()) || 1;
        let subtotal = price * qty;
        row.find('.subtotal h5').text(subtotal.toLocaleString('vi-VN') + ' VNĐ');
    }

    // Cập nhật tổng tiền toàn giỏ
    function updateTotal() {
        let total = 0;
        $('.subtotal h5').each(function() {
            let amount = parseFloat($(this).text().replace(/[^0-9]/g, '')) || 0;
            total += amount;
        });
        $('#total-amount').text(total.toLocaleString('vi-VN') + ' VNĐ');
    }
});
</script>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-23581568-13');
</script>

</body>
</html>