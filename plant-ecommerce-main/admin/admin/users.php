<?php 
require 'includes/conn.php';
session_start();

if(!isset($_SESSION['admin_email'])){
    echo "<script> location.href='login.php'; </script>";
    exit();
}
require "includes/header.php";
?>

<div class="mainContainer">
    <?php require "includes/sidebar.php" ?>

    <div class="allContainer">
        <div class="container jumbotron jumbotron-fluid col-md-6 bg-light my-4 p-4 text-center">
            <div class="container">
                <h1 class="display-4">Tất cả Khách hàng</h1>
            </div>
        </div>

        <div class="container">
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Quận/Huyện</label>
                            <select name="district" id="filterDistrict" class="form-select">
                                <option value="">Tất cả</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phường/Xã</label>
                            <select name="ward" id="filterWard" class="form-select">
                                <option value="">Tất cả</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" placeholder="Tên, email, SĐT..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Lọc</button>
                            <a href="users.php" class="btn btn-outline-secondary">Xóa lọc</a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus"></i> Thêm
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table container table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">User Id</th>
                        <th scope="col">Tên</th>
                        <th scope="col">Số điện thoại</th>
                        <th scope="col">Email</th>
                        <th scope="col">Địa chỉ</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $where_clauses = [];
                    if(!empty($_GET['district'])){
                        $district = mysqli_real_escape_string($conn, $_GET['district']);
                        $where_clauses[] = "address LIKE '%$district%'";
                    }
                    if(!empty($_GET['ward'])){
                        $ward = mysqli_real_escape_string($conn, $_GET['ward']);
                        $where_clauses[] = "address LIKE '%$ward%'";
                    }
                    if(!empty($_GET['search'])){
                        $search = mysqli_real_escape_string($conn, $_GET['search']);
                        $where_clauses[] = "(first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR mobile LIKE '%$search%')";
                    }
                    $where = '';
                    if(!empty($where_clauses)){
                        $where = ' WHERE ' . implode(' AND ', $where_clauses);
                    }
                    $query = "SELECT * FROM `users` $where ORDER BY id DESC";
                    $result = mysqli_query($conn, $query);

                    while ($row = mysqli_fetch_array($result)) {
                        // Giả sử bạn có cột 'status' trong CSDL (1: Hoạt động, 0: Bị khóa)
                        // Nếu chưa có, bạn cần vào phpMyAdmin thêm cột 'status' (INT, default 1) vào bảng users
                        $status = isset($row['status']) ? $row['status'] : 1; 

                        // Hiển thị trạng thái bằng Badge của Bootstrap
                        $statusBadge = ($status == 1) 
                            ? "<span class='badge bg-success'>Hoạt động</span>" 
                            : "<span class='badge bg-secondary'>Đã khóa</span>";

                        // Nút Khóa / Mở khóa
                        $toggleAction = ($status == 1)
                            ? "<a href='scripts/toggle_status.php?id={$row['id']}&action=lock' class='btn btn-warning btn-sm me-1 mb-1'>Khóa</a>"
                            : "<a href='scripts/toggle_status.php?id={$row['id']}&action=unlock' class='btn btn-info btn-sm me-1 mb-1 text-white'>Mở khóa</a>";

                        $resetAction = "<a href='scripts/reset_password.php?id={$row['id']}' class='btn btn-danger btn-sm me-1 mb-1' onclick=\"return confirm('Bạn có chắc chắn muốn đặt lại mật khẩu của tài khoản ".$row['email']." thành 123456 không?');\">Reset MK</a>";

                        $editData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                        $editAction = "<button class='btn btn-primary btn-sm me-1 mb-1' data-bs-toggle='modal' data-bs-target='#editUserModal' data-user='{$editData}' onclick='populateEditUserModal(this)'>Sửa</button>";

                        echo "<tr>";
                        echo "<th>" . $row['id'] . "</th>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) ."</td>";
                        echo "<td>" . htmlspecialchars($row['mobile']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                        echo "<td>" . $statusBadge . "</td>";
                        echo "<td>
                                $editAction
                                $toggleAction
                                $resetAction
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm Tài Khoản -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="scripts/add_user.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Thêm Tài Khoản Mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Họ (First Name)</label>
                <input type="text" class="form-control" name="first_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tên (Last Name)</label>
                <input type="text" class="form-control" name="last_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Số điện thoại phải đúng 10 chữ số" required placeholder="VD: 0901234567">
            </div>
            <div class="mb-3">
                <label class="form-label">Quận/Huyện</label>
                <select name="district" id="district" class="form-control" required>
                    <option value="">Chọn Quận/Huyện</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Phường/Xã</label>
                <select name="ward" id="ward" class="form-control" required>
                    <option value="">Chọn Phường/Xã</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Số nhà, Tên đường</label>
                <input type="text" name="street" id="street" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" name="add_user" class="btn btn-primary">Lưu Tài Khoản</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Sửa Tài Khoản -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="scripts/edit_user.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Sửa Thông Tin Khách Hàng</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="mb-3">
                <label class="form-label">Họ (First Name)</label>
                <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tên (Last Name)</label>
                <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="edit_email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" class="form-control" name="mobile" id="edit_mobile" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Số điện thoại phải đúng 10 chữ số" required placeholder="VD: 0901234567">
            </div>
            <div class="mb-3">
                <label class="form-label">Quận/Huyện</label>
                <select name="district" id="edit_district" class="form-control" required>
                    <option value="">Chọn Quận/Huyện</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Phường/Xã</label>
                <select name="ward" id="edit_ward" class="form-control" required>
                    <option value="">Chọn Phường/Xã</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Số nhà, Tên đường</label>
                <input type="text" name="street" id="edit_street" class="form-control" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          <button type="submit" name="edit_user" class="btn btn-primary">Cập Nhật</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<script>
function populateEditUserModal(btn) {
    const user = JSON.parse(btn.getAttribute('data-user'));
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_first_name').value = user.first_name;
    document.getElementById('edit_last_name').value = user.last_name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_mobile').value = user.mobile;
    
    let addressParts = user.address ? user.address.split(', ') : [];
    if(addressParts.length >= 3) {
        document.getElementById('edit_street').value = addressParts[0];
        document.getElementById('edit_district').value = addressParts[2];
        
        let districtSelect = document.getElementById("edit_district");
        districtSelect.dispatchEvent(new Event('change'));
        setTimeout(() => {
            document.getElementById('edit_ward').value = addressParts[1];
        }, 50);
    } else {
        document.getElementById('edit_street').value = user.address;
        document.getElementById('edit_district').value = "";
        document.getElementById('edit_ward').innerHTML = '<option value="">Chọn Phường/Xã</option>';
    }
}
</script>

<script src="../../plant-ecommerce/js/hcm_data.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Modal selects
    const districtSelect = document.getElementById("district");
    const wardSelect = document.getElementById("ward");
    // Filter selects
    const filterDistrictSelect = document.getElementById("filterDistrict");
    const filterWardSelect = document.getElementById("filterWard");

    const currentFilterDistrict = <?= json_encode($_GET['district'] ?? '') ?>;
    const currentFilterWard = <?= json_encode($_GET['ward'] ?? '') ?>;
    
    if (typeof hcmData !== 'undefined') {
        // Populate both district dropdowns
        hcmData.forEach(function (district) {
            // Modal
            let option1 = document.createElement("option");
            option1.value = district.name;
            option1.text = district.name;
            districtSelect.appendChild(option1);
            // Filter
            let option2 = document.createElement("option");
            option2.value = district.name;
            option2.text = district.name;
            if (district.name === currentFilterDistrict) option2.selected = true;
            filterDistrictSelect.appendChild(option2);
            // Edit form
            let option3 = document.createElement("option");
            option3.value = district.name;
            option3.text = district.name;
            document.getElementById("edit_district").appendChild(option3);
        });

        // Load wards for modal
        function loadWards(selectEl, selectedDistrictName, preselect) {
            selectEl.innerHTML = '<option value="">Chọn Phường/Xã</option>';
            if (selectEl === filterWardSelect) {
                selectEl.innerHTML = '<option value="">Tất cả</option>';
            }
            const districtObj = hcmData.find(d => d.name === selectedDistrictName);
            if (districtObj) {
                districtObj.wards.forEach(function (ward) {
                    let option = document.createElement("option");
                    option.value = ward;
                    option.text = ward;
                    if (ward === preselect) option.selected = true;
                    selectEl.appendChild(option);
                });
            }
        }

        districtSelect.addEventListener("change", function () {
            loadWards(wardSelect, this.value, '');
        });

        filterDistrictSelect.addEventListener("change", function () {
            loadWards(filterWardSelect, this.value, '');
        });

        document.getElementById("edit_district").addEventListener("change", function () {
            loadWards(document.getElementById("edit_ward"), this.value, '');
        });

        // Restore filter ward on page load
        if (currentFilterDistrict) {
            loadWards(filterWardSelect, currentFilterDistrict, currentFilterWard);
        }
    } else {
        console.error("hcm_data.js not loaded properly.");
    }
});
</script>

</body>
</html>