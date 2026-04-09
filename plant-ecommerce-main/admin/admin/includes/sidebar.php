
<?php
    // Get current page name for active state
    $current_page = basename($_SERVER['PHP_SELF']);
?>
    <div class="sidebar">
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Trang chủ
        </a>
        <a href="categories.php" class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">
            <i class="fas fa-th-list"></i> Danh mục SP
        </a>
        <a href="product.php" class="<?= ($current_page == 'product.php' || $current_page == 'edit_product.php') ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Thêm sản phẩm
        </a>
        <a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
            <i class="fas fa-leaf"></i> Tất cả sản phẩm
        </a>
        <a href="imports.php" class="<?= ($current_page == 'imports.php' || $current_page == 'import_create.php' || $current_page == 'import_edit.php') ? 'active' : '' ?>">
            <i class="fas fa-truck-loading"></i> Nhập hàng
        </a>
        <a href="pricing.php" class="<?= $current_page == 'pricing.php' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Giá bán
        </a>
        <a href="order.php" class="<?= ($current_page == 'order.php' || $current_page == 'order_detail.php') ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> Đơn hàng
        </a>
        <a href="inventory.php" class="<?= $current_page == 'inventory.php' ? 'active' : '' ?>">
            <i class="fas fa-warehouse"></i> Tồn kho
        </a>
        <a href="reports.php" class="<?= $current_page == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Báo cáo
        </a>
        <a href="users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Khách hàng
        </a>
    </div>