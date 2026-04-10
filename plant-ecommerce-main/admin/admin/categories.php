<?php
require 'includes/conn.php';

if(!isset($_SESSION['admin_email'])){
    echo "<script> location.href='login.php'; </script>";
    exit();
}
require "includes/header.php";
?>

<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-8 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Quản Lý Danh Mục Sản Phẩm</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <div class="mb-3 text-end">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Thêm Danh Mục
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Tên Danh Mục</th>
                                <th scope="col">Số Sản Phẩm</th>
                                <th scope="col">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category = c.id) as product_count FROM categories c ORDER BY c.id";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_array($result)) {
                                echo "<tr>";
                                echo "<th>" . $row['id'] . "</th>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td><span class='badge bg-info'>" . $row['product_count'] . " SP</span></td>";
                                echo "<td>
                                        <button class='btn btn-primary btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editCategoryModal' 
                                            data-id='{$row['id']}' data-title='" . htmlspecialchars($row['title'], ENT_QUOTES) . "'>
                                            <i class='fas fa-edit'></i> Sửa
                                        </button>
                                        <a href='scripts/delete_category.php?id={$row['id']}' class='btn btn-danger btn-sm' 
                                            onclick=\"return confirm('Bạn có chắc muốn xóa danh mục này?');\">
                                            <i class='fas fa-trash'></i> Xóa
                                        </a>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Danh Mục -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="scripts/add_category.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Thêm Danh Mục Mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Tên Danh Mục</label>
                <input type="text" class="form-control" name="title" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Lưu Danh Mục</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Sửa Danh Mục -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="scripts/edit_category.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Sửa Danh Mục</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="edit_cat_id">
            <div class="mb-3">
                <label class="form-label">Tên Danh Mục</label>
                <input type="text" class="form-control" name="title" id="edit_cat_title" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary">Cập Nhật</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script>
// Populate edit modal with data
document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    document.getElementById('edit_cat_id').value = button.getAttribute('data-id');
    document.getElementById('edit_cat_title').value = button.getAttribute('data-title');
});
</script>
</body>
</html>
