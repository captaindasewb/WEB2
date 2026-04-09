<?php 
session_start();
require "./includes/head.php";
require "includes/conn.php";

if (!isset($_SESSION['email'])) {
    echo "<script>location.href='/ecommerce';</script>";
    exit();
}

$email = $_SESSION['email'];

// ====================================
//   PHẦN XỬ LÝ CẬP NHẬT THÔNG TIN (thêm mới)
// ====================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $mobile     = trim($_POST['mobile']);
    
    $street     = trim($_POST['street']);
    $ward       = trim($_POST['ward']);
    $district   = trim($_POST['district']);
    $address    = $street . ', ' . $ward . ', ' . $district . ', TP. Hồ Chí Minh';

    // Validate cơ bản (có thể mở rộng thêm)
    if (empty($first_name) || empty($last_name) || empty($mobile) || empty($street) || empty($ward) || empty($district)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } else {
        // Cập nhật vào database
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                mobile = ?, 
                address = ?
                WHERE email = ?";
        
        $stmt = $con->prepare($sql);
        $stmt->bind_param("sssss", $first_name, $last_name, $mobile, $address, $email);
        
        if ($stmt->execute()) {
            $success = "Cập nhật thông tin thành công!";
            // Cập nhật lại thông tin hiển thị ngay lập tức
            $user['first_name'] = $first_name;
            $user['last_name']  = $last_name;
            $user['mobile']     = $mobile;
            $user['address']    = $address;
        } else {
            $error = "Cập nhật thất bại: " . $con->error;
        }
        $stmt->close();
    }
}

// Lấy thông tin user (để hiển thị form - chạy sau khi update nếu có)
$sql = "SELECT first_name, last_name, mobile, email, address FROM users WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Tách địa chỉ để hiển thị
$address_parts = array_map('trim', explode(',', $user['address'] ?? ''));
$street_val   = $address_parts[0] ?? '';
$ward_val     = $address_parts[1] ?? '';
$district_val = $address_parts[2] ?? '';
?>

<div class="container" style="margin-top:120px; margin-bottom:80px;">
    <h2 class="mb-4">Thông tin cá nhân của bạn</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Họ</label>
            <input type="text" name="last_name"
                   class="form-control"
                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>Tên</label>
            <input type="text" name="first_name"
                   class="form-control"
                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" 
                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                   readonly>  <!-- Thêm readonly vì không cho sửa email -->
        </div>

        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="mobile"
                   class="form-control"
                   value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label>Quận/Huyện</label>
            <select name="district" id="district" class="form-control" required>
                <option value="">Chọn Quận/Huyện</option>
            </select>
        </div>

        <div class="form-group">
            <label>Phường/Xã</label>
            <select name="ward" id="ward" class="form-control" required>
                <option value="">Chọn Phường/Xã</option>
            </select>
        </div>

        <div class="form-group">
            <label>Số nhà, Tên đường</label>
            <input type="text" name="street" id="street"
                   class="form-control"
                   value="<?php echo htmlspecialchars($street_val); ?>" required>
        </div>

     <button type="submit"
        style="background-color: #e63946;
               color: white;
               border: none;
               padding: 12px 32px;
               font-size: 1.1rem;
               font-weight: 600;
               border-radius: 8px;
               cursor: pointer;
               box-shadow: 0 4px 8px rgba(0,0,0,0.15);
               transition: all 0.3s ease;"
        onmouseover="this.style.backgroundColor='#d00000'; this.style.transform='translateY(-2px)';"
        onmouseout="this.style.backgroundColor='#e63946'; this.style.transform='translateY(0)';">
    Cập nhật thông tin
</button>
    </form>
</div>

<script src="js/hcm_data.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const districtSelect = document.getElementById("district");
    const wardSelect = document.getElementById("ward");
    
    const savedDistrict = "<?php echo addslashes($district_val); ?>";
    const savedWard = "<?php echo addslashes($ward_val); ?>";

    hcmData.forEach(function (district) {
        let option = document.createElement("option");
        option.value = district.name;
        option.text = district.name;
        if (district.name === savedDistrict) {
            option.selected = true;
        }
        districtSelect.appendChild(option);
    });

    function loadWards(selectedDistrictName) {
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        const districtObj = hcmData.find(d => d.name === selectedDistrictName);
        if (districtObj) {
            districtObj.wards.forEach(function (ward) {
                let option = document.createElement("option");
                option.value = ward;
                option.text = ward;
                if (ward === savedWard) {
                    option.selected = true;
                }
                wardSelect.appendChild(option);
            });
        }
    }

    if (savedDistrict) {
        loadWards(savedDistrict);
    }

    districtSelect.addEventListener("change", function () {
        loadWards(this.value);
    });
});
</script>

<?php require "./includes/footer.php"; ?>