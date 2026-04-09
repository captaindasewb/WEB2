<?php
require 'includes/conn.php';
require 'includes/is_added_to_cart.php';
session_start();
require "./includes/head.php";

// if(!isset($_SESSION['email'])){
//     echo "<script> location.href='/ecommerce'; </script>";
//     exit();
// }
?>

<?php
$query = "SELECT * FROM products WHERE visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL)";

// Lọc theo danh mục
if (isset($_GET['cat_id']) && $_GET['cat_id'] != "") {
    $cat_id = mysqli_real_escape_string($con, $_GET['cat_id']);
    $query .= " AND category = '$cat_id'";
}

// Lọc theo TÌM KIẾM (Mới bổ sung)
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $query .= " AND title LIKE '%$search%'"; 
}

// Lọc theo giá
if (isset($_GET['min_price']) && $_GET['min_price'] !== "" && $_GET['min_price'] >= 0) {
    $min = (int)$_GET['min_price'];
    $query .= " AND price >= $min";
}
if (isset($_GET['max_price']) && $_GET['max_price'] !== "" && $_GET['max_price'] >= 0) {
    $max = (int)$_GET['max_price'];
    $query .= " AND price <= $max";
}

// Sắp xếp (Luôn để cuối cùng)
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == "1") $query .= " ORDER BY price DESC";
    elseif ($_GET['sort'] == "2") $query .= " ORDER BY price ASC";
}

// Pagination settings
$items_per_page = 12;
$count_result = mysqli_query($con, $query);
$sum = mysqli_num_rows($count_result);
$total_pages = max(1, ceil($sum / $items_per_page));
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
if ($current_page > $total_pages) $current_page = $total_pages;
$offset = ($current_page - 1) * $items_per_page;
$query .= " LIMIT $items_per_page OFFSET $offset";

// Build pagination query string (preserve all filters)
$pagination_params = [];
if (isset($_GET['cat_id']) && $_GET['cat_id'] != "") $pagination_params['cat_id'] = $_GET['cat_id'];
if (isset($_GET['search']) && $_GET['search'] != "") $pagination_params['search'] = $_GET['search'];
if (isset($_GET['min_price']) && $_GET['min_price'] !== "") $pagination_params['min_price'] = $_GET['min_price'];
if (isset($_GET['max_price']) && $_GET['max_price'] !== "") $pagination_params['max_price'] = $_GET['max_price'];
if (isset($_GET['sort']) && $_GET['sort'] != "") $pagination_params['sort'] = $_GET['sort'];
$pagination_base = 'category.php?' . http_build_query($pagination_params);
?>

<section class="breadcrumb breadcrumb_bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="breadcrumb_iner">
                    <div class="breadcrumb_iner_item">
                        <h2>All Products</h2>
                        <p>Home <span>-</span> Buy Products</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cat_product_area section_padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="left_sidebar_area">
                    <aside class="left_widgets p_filter_widgets">
                        <div class="l_w_title">
                            <h3>Danh mục</h3>
                        </div>
                        <div class="widgets_inner">
                            <ul class="list">
    <li><a href="category.php">Tất cả sản phẩm</a></li>
    <?php
    // Lấy danh sách danh mục từ bảng categories
    $cat_query = "SELECT * FROM categories";
    $cat_result = mysqli_query($con, $cat_query);
    while ($cat_row = mysqli_fetch_array($cat_result)) {
        // Gửi id danh mục qua biến 'cat_id' trên URL
        echo '<li><a href="category.php?cat_id=' . $cat_row['id'] . '">' . $cat_row['title'] . '</a></li>';
    }
    ?>
</ul>
                        </div>
                    </aside>
                    
                </div>
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="product_top_bar d-flex justify-content-between align-items-center flex-wrap" style="margin-bottom: 30px; gap: 15px;">
                            <div class="single_product_menu mb-0">
                                <p class="mb-0"><span class="font-weight-bold"><?php echo $sum ?> </span> Sản phẩm (Trang <?= $current_page ?>/<?= $total_pages ?>)</p>
                            </div>
                            
                            <form action="category.php" method="GET" class="d-flex align-items-center flex-wrap" style="gap: 15px;">
                                <?php if(isset($_GET['cat_id'])): ?>
                                    <input type="hidden" name="cat_id" value="<?php echo htmlspecialchars($_GET['cat_id']); ?>">
                                <?php endif; ?>

                                <!-- Filter by Price -->
                                <div class="d-flex align-items-center">
                                    <span class="mr-2 font-weight-bold">Giá:</span>
                                    <input type="number" name="min_price" min="0" class="form-control form-control-sm" 
                                           style="width: 90px;" placeholder="Từ" 
                                           value="<?php echo (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? $_GET['min_price'] : ''; ?>">
                                    <span class="mx-2">-</span>
                                    <input type="number" name="max_price" min="0" class="form-control form-control-sm" 
                                           style="width: 90px;" placeholder="Đến" 
                                           value="<?php echo (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? $_GET['max_price'] : ''; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary ml-2 px-3" style="border-radius: 4px;">Lọc</button>
                                </div>

                                <!-- Sort -->
                                <div class="d-flex align-items-center">
                                    <span class="mr-2 font-weight-bold text-nowrap">Sắp xếp:</span>
                                    <select name="sort" class="form-control form-control-sm nice-select" onchange="this.form.submit()" style="min-width: 140px; display: block;">
                                        <option value=""  <?php if(!isset($_GET['sort']) || $_GET['sort'] == "") echo 'selected'; ?>>Mặc định</option>
                                        <option value="1" <?php if(isset($_GET['sort']) && $_GET['sort'] == "1") echo 'selected'; ?>>Giá cao -> thấp</option>
                                        <option value="2" <?php if(isset($_GET['sort']) && $_GET['sort'] == "2") echo 'selected'; ?>>Giá thấp -> cao</option>
                                    </select>
                                </div>

                                <!-- Search -->
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control" 
                                           placeholder="Tìm kiếm sản phẩm..." 
                                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text bg-primary text-white border-0" style="cursor: pointer; border-radius: 0 4px 4px 0;">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center latest_product_inner">

                <?php
                    $result = mysqli_query($con, $query);

                while ($row = mysqli_fetch_array($result)) {
    echo '<div class="col-lg-4 col-sm-6">
            <a href="single-product.php?id=' . $row['id'] . '">
                <div class="single_product_item">
                    <img width="200px" src="img/product/'.$row['image'].'" alt="djwij" />
                    <div class="single_product_text">
                        <h4>'. $row['title'] .'</h4>
                        <h3>'. number_format($row['price'], 0, ',', '.') .' VNĐ</h3>';
                        
                        if ($row['qty'] <= 0) {
                            echo '<div class="mt-2"><span class="badge badge-danger" style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 4px;">Hết hàng</span></div>';
                        }
                       
                  echo ' </div>
                </div>
            </a>
        </div>';
}
                ?>
                   
                </div>

                <?php if($total_pages > 1): ?>
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-center">
                        <nav aria-label="Product pagination">
                            <ul class="pagination" style="gap: 4px;">
                                <?php if($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination_base . '&page=' . ($current_page - 1) ?>" aria-label="Previous" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">
                                        &laquo; Trước
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php
                                // Show max 5 page numbers around current page
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="' . $pagination_base . '&page=1" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">1</a></li>';
                                    if ($start_page > 2) echo '<li class="page-item disabled"><span class="page-link" style="border-radius: 6px;">...</span></li>';
                                }
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $pagination_base . '&page=' . $i ?>" 
                                       style="border-radius: 6px; <?= $i == $current_page ? 'background-color: #71cd14; border-color: #71cd14; color: #fff;' : 'color: #71cd14; border-color: #dee2e6;' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor;
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link" style="border-radius: 6px;">...</span></li>';
                                    echo '<li class="page-item"><a class="page-link" href="' . $pagination_base . '&page=' . $total_pages . '" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <?php if($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $pagination_base . '&page=' . ($current_page + 1) ?>" aria-label="Next" style="border-radius: 6px; color: #71cd14; border-color: #dee2e6;">
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
        </div>
    </div>
</section>


<?php require './includes/footer.php' ?>

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

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-23581568-13');
</script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vaafb692b2aea4879b33c060e79fe94621666317369993" integrity="sha512-0ahDYl866UMhKuYcW078ScMalXqtFJggm7TmlUtp0UlD4eQk0Ixfnm5ykXKvGJNFjLMoortdseTfsRT8oCfgGA==" data-cf-beacon='{"rayId":"7721ac04f8bd3390","token":"cd0b4b3a733644fc843ef0b185f98241","version":"2022.11.3","si":100}' crossorigin="anonymous"></script>
</body>

</html>