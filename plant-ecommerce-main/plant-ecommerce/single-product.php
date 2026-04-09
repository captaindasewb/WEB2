<?php
session_start();
require './includes/conn.php';
require_once './includes/is_added_to_cart.php';  // Dùng require_once để tránh lỗi redeclare

/* KIỂM TRA ID */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

/* LẤY SẢN PHẨM THEO ID */
$sql = "SELECT * FROM products WHERE id = $id AND visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL) LIMIT 1";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "<h3 style='text-align:center'>Sản phẩm không tồn tại</h3>";
    exit();
}

$product = mysqli_fetch_assoc($result);
?>

<?php require './includes/head.php'; ?>

<!-- BREADCRUMB -->
<section class="breadcrumb breadcrumb_bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="breadcrumb_iner">
                    <div class="breadcrumb_iner_item">
                        <h2><?php echo htmlspecialchars($product['title']); ?></h2>
                        <p>Home <span>-</span> Shop Single</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCT DETAIL -->
<div class="product_image_area section_padding">
    <div class="container">
        <div class="row s_product_inner justify-content-between">

            <!-- ẢNH -->
            <div class="col-lg-7 col-xl-7">
                <div class="product_slider_img">
                    <img 
                        src="img/product/<?php echo htmlspecialchars($product['image']); ?>" 
                        alt="<?php echo htmlspecialchars($product['title']); ?>" 
                        style="width:100%;max-width:500px; object-fit: contain;"
                    />
                </div>
            </div>

            <!-- THÔNG TIN -->
            <div class="col-lg-5 col-xl-4">
                <div class="s_product_text">
                    <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                    <h2><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</h2>

                    <ul class="list">
                        <li>
                            <a class="active" href="#">
                                <span>Category</span> : <?php echo htmlspecialchars($product['category']); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <span>Availability</span> : In Stock
                            </a>
                        </li>
                    </ul>

                    <p><?php echo nl2br(htmlspecialchars($product['desc'])); ?></p>

             <!-- PHẦN SỐ LƯỢNG + NÚT THÊM VÀO GIỎ HÀNG (luôn hiển thị, cùng một dòng) -->
<div class="card_area d-flex align-items-center mt-4">
    <?php if ($product['qty'] > 0): ?>
    <!-- Bộ tăng/giảm số lượng -->
    <div class="product_count d-flex align-items-center mr-3" style="border: 1px solid #e1e1e1; border-radius: 30px; height: 50px; width: 140px; overflow: hidden; background: #fff;">
        <span class="decrement d-flex align-items-center justify-content-center" style="width: 40px; height: 100%; cursor: pointer; color: #777; transition: 0.3s; background: transparent;">
            <i class="fas fa-minus"></i>
        </span>
        <input class="text-center font-weight-bold" type="text" id="qty" name="qty" value="1" min="1" max="<?php echo $product['qty']; ?>" style="width: 60px; height: 100%; border: none; outline: none; background: transparent; font-size: 16px;" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
        <span class="increment d-flex align-items-center justify-content-center" style="width: 40px; height: 100%; cursor: pointer; color: #777; transition: 0.3s; background: transparent;">
            <i class="fas fa-plus"></i>
        </span>
    </div>

    <!-- Nút Thêm vào giỏ hàng -->
    <a href="#" id="add-to-cart-btn" 
       data-id="<?php echo $product['id']; ?>"
       class="btn_3 d-flex align-items-center justify-content-center m-0" style="height: 50px; padding: 0 35px; white-space: nowrap; border-radius: 30px; border: none;">
        Thêm vào giỏ hàng
    </a>
    <?php else: ?>
    <!-- Nút Hết Hàng -->
    <button type="button" class="btn btn-secondary disabled d-flex align-items-center justify-content-center m-0" style="height: 50px; padding: 0 35px; white-space: nowrap; border-radius: 30px; border: none; cursor: not-allowed; background-color: #6c757d; color: #fff;">
        Sản phẩm tạm hết hàng
    </button>
    <?php endif; ?>
</div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- DESCRIPTION TAB -->
<section class="product_description_area">
    <div class="container">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#desc">Description</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="desc">
                <p><?php echo nl2br(htmlspecialchars($product['desc'])); ?></p>
            </div>
        </div>
    </div>
</section>

<?php require './includes/footer.php'; ?>

<!-- JS -->
<script src="js/jquery-1.12.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/theme.js"></script>

<!-- JavaScript xử lý tăng/giảm số lượng -->
<script>
$(document).ready(function() {
    const qtyInput = $('#qty');

    // Tăng số lượng
    $('.increment').on('click', function() {
        let current = parseInt(qtyInput.val()) || 1;
        let maxQty = parseInt(qtyInput.attr('max')) || 100;
        if (current < maxQty) {
            qtyInput.val(current + 1);
        } else {
            alert('Sản phẩm này chỉ còn ' + maxQty + ' cái trong kho!');
        }
    });

    // Giảm số lượng (không dưới 1)
    $('.decrement').on('click', function() {
        let current = parseInt(qtyInput.val()) || 1;
        if (current > 1) {
            qtyInput.val(current - 1);
        }
    });

    // Xử lý click nút Add to cart
    $('#add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const qty = qtyInput.val() || 1;

        // Chuyển hướng đến cart_add.php với id và qty
        window.location.href = 'scripts/cart_add.php?id=' + productId + '&qty=' + qty;
    });
});
</script>

</body>
</html>