<?php
require 'includes/conn.php';

session_start();

if(!isset($_SESSION['admin_email'])){
    echo "<script> location.href='login.php'; </script>";
    exit();
}   

$mail = $_SESSION["admin_email"];
$admin_name = '';
$admin_mobile = '';

$query = "SELECT * FROM admin WHERE email = '" . mysqli_real_escape_string($conn, $mail) . "'";
$result = mysqli_query($conn, $query);
$admin_row = mysqli_fetch_assoc($result);
if($admin_row){
    $admin_name = $admin_row['name'];
    $admin_mobile = $admin_row['mobile'];
}

// Dashboard stats
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products WHERE visibility=1"))['cnt'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users"))['cnt'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM orders WHERE status='Pending' OR status='Chưa xử lý'"))['cnt'];
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products WHERE qty <= 5 AND visibility=1"))['cnt'];

?>
<?php require_once "includes/header.php" ?>

<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <!-- Admin Info -->
        <div class="container my-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-user-shield fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-1">Xin chào, <?php echo htmlspecialchars($admin_name) ?></h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($mail) ?> 
                                &nbsp;|&nbsp; 
                                <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($admin_mobile) ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success fs-6"><i class="fas fa-circle me-1" style="font-size:8px"></i> Quản trị viên</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="container">
            <h5 class="text-muted mb-3"><i class="fas fa-chart-bar me-2"></i>Tổng quan</h5>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card dashboard-card bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="card-icon text-success me-3"><i class="fas fa-leaf"></i></div>
                            <div>
                                <h3 class="mb-0"><?= $total_products ?></h3>
                                <small class="text-muted">Sản phẩm</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="card-icon text-primary me-3"><i class="fas fa-users"></i></div>
                            <div>
                                <h3 class="mb-0"><?= $total_users ?></h3>
                                <small class="text-muted">Khách hàng</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="card-icon text-warning me-3"><i class="fas fa-clock"></i></div>
                            <div>
                                <h3 class="mb-0"><?= $pending_orders ?></h3>
                                <small class="text-muted">Đơn chờ xử lý</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card bg-white">
                        <div class="card-body d-flex align-items-center">
                            <div class="card-icon text-danger me-3"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>
                                <h3 class="mb-0"><?= $low_stock ?></h3>
                                <small class="text-muted">SP sắp hết hàng</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin accounts table -->
        <div class="container mt-4">
            <h5 class="text-muted mb-3"><i class="fas fa-user-cog me-2"></i>Danh sách quản trị viên</h5>
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tên</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Email</th>
                                <th scope="col">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = 'SELECT * FROM `admin`';
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_array($result)) {
                                $editData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                $editAction = "<button class='btn btn-primary btn-sm me-1 mb-1' data-bs-toggle='modal' data-bs-target='#editAdminModal' data-admin='{$editData}' onclick='populateEditAdminModal(this)'>Sửa</button>";
                                
                                echo "<tr>";
                                echo "<th>" . $row['id'] . "</th>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['mobile']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>$editAction</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="container mt-4">
            <h5 class="text-muted mb-3"><i class="fas fa-bolt me-2"></i>Truy cập nhanh</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="product.php" class="btn btn-outline-success w-100 py-3">
                        <i class="fas fa-plus-circle me-2"></i>Thêm sản phẩm mới
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="import_create.php" class="btn btn-outline-primary w-100 py-3">
                        <i class="fas fa-truck-loading me-2"></i>Tạo phiếu nhập hàng
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="order.php" class="btn btn-outline-warning w-100 py-3">
                        <i class="fas fa-shopping-cart me-2"></i>Xem đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sửa Admin -->
<div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="scripts/edit_admin.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editAdminModalLabel">Sửa cấu hình Quản trị viên</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="admin_id" id="edit_admin_id">
            <div class="mb-3">
                <label class="form-label">Tên</label>
                <input type="text" class="form-control" name="name" id="edit_admin_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="edit_admin_email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" class="form-control" name="mobile" id="edit_admin_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Số điện thoại phải đúng 10 chữ số" required placeholder="VD: 0901234567">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" name="edit_admin" class="btn btn-primary">Cập Nhật</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function populateEditAdminModal(btn) {
    const admin = JSON.parse(btn.getAttribute('data-admin'));
    document.getElementById('edit_admin_id').value = admin.id;
    document.getElementById('edit_admin_name').value = admin.name;
    document.getElementById('edit_admin_email').value = admin.email;
    document.getElementById('edit_admin_mobile').value = admin.mobile;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>