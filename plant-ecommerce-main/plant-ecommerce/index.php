<?php
session_start();

// Require các file cần thiết (dùng đường dẫn tương đối an toàn)
require_once __DIR__ . '/includes/head.php';
require_once __DIR__ . '/includes/conn.php';
require_once __DIR__ . '/includes/is_added_to_cart.php';

// Xử lý thông báo đăng nhập thành công
$show_success_alert = isset($_SESSION['login_success']);
if ($show_success_alert) {
    unset($_SESSION['login_success']);
}
?>
<!-- Banner Slider -->
<section class="banner_part">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="banner_slider owl-carousel">
                    <div class="single_banner_slider">
                        <div class="row">
                            <div class="col-lg-5 col-md-8">
                                <div class="banner_text">
                                    <div class="banner_text_iner">
                                        <h1>Mua xanh – Tiết kiệm xanh</h1>
                                        <p>
                                            Cây cảnh trong nhà đẹp nhất – Có cây cho mọi không gian nhà bạn!
                                        </p>
                                        <a href="category.php" class="btn_2">Mua ngay</a>
                                    </div>
                                </div>
                            </div>
                            <div class="banner_img d-none d-lg-block">
                                <img src="img/banner.png" alt="Cây cảnh trong nhà" />
                            </div>
                        </div>
                    </div>


                    <!-- Các slide khác giữ nguyên hoặc thêm hình mới nếu cần -->
                    <div class="single_banner_slider">
                        <div class="row">
                            <div class="col-lg-5 col-md-8">
                                <div class="banner_text">
                                    <div class="banner_text_iner">
                                        <h1>Cây trong nhà đa dạng</h1>
                                        <p>Nhiều loại cây đẹp, dễ chăm sóc cho không gian sống.</p>
                                        <a href="category.php" class="btn_2">Khám phá ngay</a>
                                    </div>
                                </div>
                            </div>
                            <div class="banner_img d-none d-lg-block">
                                <img src="img/banner.png" alt="Cây cảnh đa dạng" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-counter"></div>
            </div>
        </div>
    </div>
</section>

<!-- Danh sách sản phẩm -->
<!-- Danh sách sản phẩm với phân trang -->
<section class="product_list section_padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="section_tittle text-center">
                    <h2>Cửa hàng <span>tuyệt vời</span></h2>
                </div>
            </div>
        </div>

        <?php
        // Cài đặt phân trang
        $products_per_page = 8;
        $current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($current_page < 1) $current_page = 1;

        $offset = ($current_page - 1) * $products_per_page;

        // Đếm tổng số sản phẩm
        $count_query = "SELECT COUNT(*) AS total FROM products WHERE visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL)";
        $count_result = mysqli_query($con, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total_products = $count_row['total'];

        // Tính tổng số trang
        $total_pages = ceil($total_products / $products_per_page);

        // Truy vấn sản phẩm cho trang hiện tại
        $query = "SELECT * FROM products WHERE visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL) LIMIT $products_per_page OFFSET $offset";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            echo '<div class="row">';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '
                <div class="col-lg-3 col-sm-6 mb-4">
                    <a href="single-product.php?id=' . $row['id'] . '" 
                       style="text-decoration: none; color: inherit;">
                        <div class="single_product_item text-center">
                            <img src="img/product/' . htmlspecialchars($row['image']) . '" 
                                 alt="' . htmlspecialchars($row['title']) . '" 
                                 style="max-width: 100%; height: auto;" />
                            <div class="single_product_text">
                                <h4>' . htmlspecialchars($row['title']) . '</h4>
                                <h3>' . number_format($row['price'], 0, ',', '.') . ' VNĐ</h3>
                                ' . ($row['qty'] <= 0 ? '<div class="mt-2"><span class="badge badge-danger" style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 4px;">Hết hàng</span></div>' : '') . '
                            </div>
                        </div>
                    </a>
                </div>';
            }
            echo '</div>';
        } else {
            echo '<p class="text-center">Hiện chưa có sản phẩm nào.</p>';
        }
        ?>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
        <div class="row">
            <div class="col-lg-12">
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center" style="gap: 4px;">
                        <?php if($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">
                                &laquo; Trước
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $current_page - 2);
                        $end = min($total_pages, $current_page + 2);
                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">1</a></li>';
                            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link" style="border-radius: 6px;">...</span></li>';
                        }
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>" 
                               style="border-radius: 6px; <?= $i == $current_page ? 'background-color: #71cd14; border-color: #71cd14; color: #fff;' : 'color: #71cd14; border-color: #dee2e6;' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor;
                        if ($end < $total_pages) {
                            if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link" style="border-radius: 6px;">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">' . $total_pages . '</a></li>';
                        }
                        ?>

                        <?php if($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">
                                Sau &raquo;
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>



<?php require_once __DIR__ . '/includes/footer.php'; ?>

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
<script src="js/custom.js"></script>

<!-- SweetAlert2 cho thông báo đăng nhập thành công -->
<?php if ($show_success_alert): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        title: 'Đăng nhập thành công!',
        text: 'Chào mừng <?php echo htmlspecialchars($_SESSION['email'] ?? 'bạn'); ?> quay trở lại!',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3500,
        timerProgressBar: true
    });
</script>
<?php endif; ?>

<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-23581568-13');
</script>

</body>
</html>