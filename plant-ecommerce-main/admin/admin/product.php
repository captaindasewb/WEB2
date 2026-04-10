<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}
require "includes/header.php";

// Get categories from DB
$cat_query = "SELECT * FROM categories ORDER BY title";
$cat_result = mysqli_query($conn, $cat_query);
?>

<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Thêm Sản Phẩm</h1>
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
                    <form class="row g-3" action="manage/addproduct.php" method="POST" enctype="multipart/form-data">
                        <div class="col-md-12">
                            <label for="title" class="form-label">Tên sản phẩm</label>
                            <input type="text" name="title" class="form-control" id="title" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Loại sản phẩm</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="">Chọn danh mục</option>
                                <?php 
                                while($cat = mysqli_fetch_assoc($cat_result)) {
                                    echo "<option value='{$cat['id']}'>" . htmlspecialchars($cat['title']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label">Đơn vị tính</label>
                            <select name="unit" id="unit" class="form-control">
                                <option value="cây">Cây</option>
                                <option value="chậu">Chậu</option>
                                <option value="bó">Bó</option>
                                <option value="cái">Cái</option>
                                <option value="kg">Kg</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="desc" class="form-label">Mô tả</label>
                            <textarea name="desc" class="form-control" id="desc" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="qty" class="form-label">Số lượng tồn ban đầu</label>
                            <input type="number" name="qty" class="form-control" id="qty" value="0" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cost_price" class="form-label">Giá vốn (VNĐ)</label>
                            <input type="number" name="cost_price" class="form-control" id="cost_price" value="0" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="profit_margin" class="form-label">Tỉ lệ lợi nhuận (%)</label>
                            <input type="number" step="0.01" name="profit_margin" class="form-control" id="profit_margin" value="20" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá bán (tự tính)</label>
                            <input type="text" class="form-control bg-light" id="selling_price_preview" readonly>
                            <small class="text-muted">= Giá vốn × (1 + % lợi nhuận)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="visibility" class="form-label">Hiện trạng</label>
                            <select name="visibility" id="visibility" class="form-control">
                                <option value="1">Hiển thị (Đang bán)</option>
                                <option value="0">Ẩn (Không bán)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                            <input type="file" name="image" class="form-control" id="image" accept="image/*">
                        </div>
                        <div class="col-md-6" id="imagePreviewContainer" style="display:none;">
                            <label class="form-label">Xem trước</label><br>
                            <img id="imagePreview" src="" style="max-height:120px; border-radius:8px; border:1px solid #ddd;">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle me-2"></i>Thêm Sản Phẩm
                            </button>
                            <a href="products.php" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-list me-2"></i>Danh Sách SP
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
// Calculate selling price preview
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