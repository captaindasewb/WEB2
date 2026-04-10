<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);
$query = "SELECT * FROM products WHERE id = $product_id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if(!$product){
    header("Location: products.php?error=Sản phẩm không tồn tại");
    exit();
}

// Get categories
$cat_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY title");

require "includes/header.php";
?>

<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Sửa Sản Phẩm</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
        </div>

        <div class="container col-md-8 my-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form class="row g-3" action="scripts/update_product.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
                        
                        <div class="col-md-12">
                            <label for="title" class="form-label">Tên sản phẩm</label>
                            <input type="text" name="title" class="form-control" id="title" value="<?= htmlspecialchars($product['title']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Loại sản phẩm</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Chọn danh mục</option>
                                <?php 
                                while($cat = mysqli_fetch_assoc($cat_result)) {
                                    $selected = ($cat['id'] == $product['category']) ? 'selected' : '';
                                    echo "<option value='{$cat['id']}' $selected>" . htmlspecialchars($cat['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label">Đơn vị tính</label>
                            <select name="unit" id="unit" class="form-control">
                                <?php 
                                $units = ['cây','chậu','bó','cái','kg'];
                                foreach($units as $u){
                                    $sel = ($product['unit'] == $u) ? 'selected' : '';
                                    echo "<option value='$u' $sel>" . ucfirst($u) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="desc" class="form-label">Mô tả</label>
                            <textarea name="desc" class="form-control" id="desc" rows="3"><?= htmlspecialchars($product['desc']) ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="cost_price" class="form-label">Giá vốn (VNĐ)</label>
                            <input type="number" name="cost_price" class="form-control" id="cost_price" value="<?= $product['cost_price'] ?>" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="profit_margin" class="form-label">Tỉ lệ lợi nhuận (%)</label>
                            <input type="number" step="0.01" name="profit_margin" class="form-control" id="profit_margin" value="<?= $product['profit_margin'] ?>" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Giá bán (tự tính)</label>
                            <input type="text" class="form-control bg-light" id="selling_price_preview" readonly>
                        </div>

                        <!-- Visibility - IMPORTANT -->
                        <div class="col-md-6">
                            <label for="visibility" class="form-label fw-bold text-danger">
                                <i class="fas fa-eye me-1"></i> Hiện trạng sản phẩm
                            </label>
                            <select name="visibility" id="visibility" class="form-control border-danger">
                                <option value="1" <?= $product['visibility'] == 1 ? 'selected' : '' ?>>Hiển thị (Đang bán)</option>
                                <option value="0" <?= $product['visibility'] == 0 ? 'selected' : '' ?>>Ẩn (Không bán)</option>
                            </select>
                            <small class="text-danger">⚠️ Thay đổi hiện trạng sẽ ảnh hưởng đến hiển thị trên website</small>
                        </div>

                        <!-- Current Image -->
                        <div class="col-md-6">
                            <label class="form-label">Hình ảnh hiện tại</label>
                            <?php if(!empty($product['image'])): ?>
                                <div class="d-flex align-items-center">
                                    <img src="../../plant-ecommerce/img/product/<?= htmlspecialchars($product['image']) ?>" style="max-height:100px; border-radius:8px; border:1px solid #ddd;" class="me-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                                        <label class="form-check-label text-danger" for="remove_image">
                                            <i class="fas fa-trash"></i> Bỏ hình
                                        </label>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Chưa có hình ảnh</p>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="image" class="form-label">Thay hình mới (nếu muốn)</label>
                            <input type="file" name="image" class="form-control" id="image" accept="image/*">
                        </div>
                        <div class="col-md-6" id="imagePreviewContainer" style="display:none;">
                            <label class="form-label">Xem trước hình mới</label><br>
                            <img id="imagePreview" src="" style="max-height:100px; border-radius:8px; border:1px solid #ddd;">
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Cập Nhật Sản Phẩm
                            </button>
                            <a href="products.php" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script>
// Calculate selling price
function calcPrice() {
    var cost = parseFloat(document.getElementById('cost_price').value) || 0;
    var margin = parseFloat(document.getElementById('profit_margin').value) || 0;
    var selling = Math.round(cost * (1 + margin / 100));
    document.getElementById('selling_price_preview').value = selling.toLocaleString('vi-VN') + ' VNĐ';
}
document.getElementById('cost_price').addEventListener('input', calcPrice);
document.getElementById('profit_margin').addEventListener('input', calcPrice);
calcPrice();

// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('imagePreview').src = ev.target.result;
            document.getElementById('imagePreviewContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
