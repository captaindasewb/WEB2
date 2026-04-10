<?php
require 'includes/conn.php';

if (!isset($_SESSION['admin_email'])) {
    echo "<script> location.href='login.php'; </script>";
    exit();
}
require "includes/header.php";

// Get all visible products for dropdown
$products_result = mysqli_query($conn, "SELECT id, title, cost_price, unit FROM products WHERE visibility = 1 AND (is_deleted_admin = 0 OR is_deleted_admin IS NULL) ORDER BY title");
$products = [];
while($p = mysqli_fetch_assoc($products_result)){
    $products[] = $p;
}
?>
<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Tạo Phiếu Nhập Hàng</h1>
            </div>
        </div>

        <div class="container col-md-10 my-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="importForm" action="scripts/save_import.php" method="POST">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="supplier_name" class="form-label">Nhà cung cấp</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="import_date" class="form-label">Ngày nhập</label>
                                <input type="date" name="import_date" id="import_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="notes" class="form-label">Ghi chú</label>
                                <input type="text" name="notes" id="notes" class="form-control" placeholder="Ghi chú phiếu nhập (tùy chọn)">
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3"><i class="fas fa-boxes me-2"></i>Chi tiết sản phẩm nhập</h5>
                        
                        <!-- Add product row -->
                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">Chọn sản phẩm</label>
                                <select id="addProductSelect" class="form-control">
                                    <option value="">-- Tìm/chọn sản phẩm --</option>
                                    <?php foreach($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" 
                                            data-name="<?= htmlspecialchars($p['title']) ?>"
                                            data-cost="<?= $p['cost_price'] ?>"
                                            data-unit="<?= htmlspecialchars($p['unit']) ?>">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Số lượng</label>
                                <input type="number" id="addQty" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Đơn giá nhập</label>
                                <input type="number" id="addUnitPrice" class="form-control" min="0">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btnAddProduct" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-1"></i>Thêm
                                </button>
                            </div>
                        </div>

                        <!-- Products table -->
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="importTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên SP</th>
                                        <th>ĐVT</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá nhập</th>
                                        <th>Thành tiền</th>
                                        <th>Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="importTableBody">
                                    <!-- Dynamic rows -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td colspan="4" class="text-end">TỔNG CỘNG:</td>
                                        <td id="grandTotal">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnSave">
                                <i class="fas fa-save me-2"></i>Lưu Phiếu Nhập (Nháp)
                            </button>
                            <a href="imports.php" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-arrow-left me-2"></i>Hủy
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
let rowIndex = 0;

// Auto-fill unit price from product cost
document.getElementById('addProductSelect').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if(opt.value){
        document.getElementById('addUnitPrice').value = opt.getAttribute('data-cost');
    }
});

// Add product row
document.getElementById('btnAddProduct').addEventListener('click', function() {
    var select = document.getElementById('addProductSelect');
    var opt = select.options[select.selectedIndex];
    if(!opt.value) { alert('Vui lòng chọn sản phẩm'); return; }
    
    var productId = opt.value;
    var name = opt.getAttribute('data-name');
    var unit = opt.getAttribute('data-unit');
    var qty = parseInt(document.getElementById('addQty').value) || 1;
    var unitPrice = parseFloat(document.getElementById('addUnitPrice').value) || 0;
    var total = qty * unitPrice;

    // Check duplicate
    var existing = document.querySelector('input[name="items[' + productId + '][product_id]"]');
    if(existing) {
        alert('Sản phẩm này đã có trong danh sách!');
        return;
    }

    var tbody = document.getElementById('importTableBody');
    var tr = document.createElement('tr');
    tr.id = 'row_' + productId;
    tr.innerHTML = `
        <td>${name}
            <input type="hidden" name="items[${productId}][product_id]" value="${productId}">
        </td>
        <td>${unit}</td>
        <td>
            <input type="number" name="items[${productId}][quantity]" value="${qty}" min="1" class="form-control form-control-sm item-qty" data-id="${productId}" style="width:80px;">
        </td>
        <td>
            <input type="number" name="items[${productId}][unit_price]" value="${unitPrice}" min="0" class="form-control form-control-sm item-price" data-id="${productId}" style="width:120px;">
        </td>
        <td class="item-total" id="total_${productId}">${total.toLocaleString('vi-VN')}</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm btn-remove" data-id="${productId}"><i class="fas fa-times"></i></button>
        </td>
    `;
    tbody.appendChild(tr);

    // Reset form
    select.value = '';
    document.getElementById('addQty').value = 1;
    document.getElementById('addUnitPrice').value = '';
    
    recalculate();
    bindEvents();
});

function bindEvents() {
    // Recalculate on change
    document.querySelectorAll('.item-qty, .item-price').forEach(function(el) {
        el.removeEventListener('input', handleRecalc);
        el.addEventListener('input', handleRecalc);
    });
    // Remove row
    document.querySelectorAll('.btn-remove').forEach(function(btn) {
        btn.removeEventListener('click', handleRemove);
        btn.addEventListener('click', handleRemove);
    });
}

function handleRecalc(e) {
    var id = e.target.getAttribute('data-id');
    var qty = parseInt(document.querySelector('input[name="items[' + id + '][quantity]"]').value) || 0;
    var price = parseFloat(document.querySelector('input[name="items[' + id + '][unit_price]"]').value) || 0;
    document.getElementById('total_' + id).textContent = (qty * price).toLocaleString('vi-VN');
    recalculate();
}

function handleRemove(e) {
    var btn = e.target.closest('.btn-remove');
    var id = btn.getAttribute('data-id');
    document.getElementById('row_' + id).remove();
    recalculate();
}

function recalculate() {
    var total = 0;
    document.querySelectorAll('.item-qty').forEach(function(qtyInput) {
        var id = qtyInput.getAttribute('data-id');
        var qty = parseInt(qtyInput.value) || 0;
        var price = parseFloat(document.querySelector('input[name="items[' + id + '][unit_price]"]').value) || 0;
        total += qty * price;
    });
    document.getElementById('grandTotal').textContent = total.toLocaleString('vi-VN') + ' VNĐ';
}

// Validate before submit
document.getElementById('importForm').addEventListener('submit', function(e) {
    if(document.querySelectorAll('#importTableBody tr').length === 0) {
        e.preventDefault();
        alert('Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập!');
    }
});
</script>
</body>
</html>
