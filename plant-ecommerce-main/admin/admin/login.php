<?php
require_once "includes/conn.php";
require_once "includes/header.php";


// Nếu đã đăng nhập admin thì chuyển hướng về trang chính
if (isset($_SESSION['admin_email'])) {
    header("Location: index.php");
    exit();
}

// Lấy thông báo lỗi từ query string (nếu có)
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Lấy email cũ để điền lại form (nếu có)
$old_email = isset($_SESSION['old_admin_email']) ? htmlspecialchars($_SESSION['old_admin_email']) : '';
unset($_SESSION['old_admin_email']); // Xóa sau khi sử dụng
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Card đăng nhập -->
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">Đăng Nhập Quản Trị</h3>
                </div>

                <div class="card-body p-5">
                    <!-- Hiển thị lỗi nếu có -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Lỗi:</strong> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="scripts/login_script.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email quản trị</label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo $old_email; ?>" 
                                   placeholder="Nhập email của bạn" 
                                   required 
                                   autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Mật khẩu</label>
                            <input type="password" 
                                   class="form-control form-control-lg" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Nhập mật khẩu" 
                                   required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Đăng nhập
                            </button>
                        </div>
                    </form>

                    <!-- Link quên mật khẩu (có thể thêm chức năng sau) -->
                    <div class="text-center mt-4">
                        <small><a href="#" class="text-muted">Quên mật khẩu?</a></small>
                    </div>
                </div>
            </div>

            <!-- Thông tin footer nhỏ -->
            <div class="text-center mt-4 text-muted">
                <small>© <?php echo date('Y'); ?> Plant Ecommerce - Bảng điều khiển Quản trị</small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>