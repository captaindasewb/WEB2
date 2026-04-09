<?php
session_start();
require "./includes/head.php";

// Nếu đã đăng nhập thì redirect về trang chủ
if (isset($_SESSION['email']) || isset($_SESSION['user_id'])) {
    echo "<script> location.href = '/ecommerce' || '../index.php'; </script>";
    exit();
}
?>

    <!-- Breadcrumb -->
    <section class="breadcrumb breadcrumb_bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="breadcrumb_iner">
                        <div class="breadcrumb_iner_item">
                            <h2>Đăng Nhập</h2>
                            <p>Trang chủ <span>-</span> Đăng nhập tài khoản</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Phần Login -->
    <section class="login_part section_padding">
        <div class="container">
            <div class="row align-items-center">

                <!-- Bên trái: Lời mời đăng ký -->
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_text text-center">
                        <div class="login_part_text_iner">
                            <h2>Bạn chưa có tài khoản?</h2>
                            <p>
                                Tham gia ngay để nhận ưu đãi đặc biệt, theo dõi đơn hàng và trải nghiệm mua sắm cây cảnh dễ dàng hơn!
                            </p>
                            <a href="register.php" class="btn_3">Tạo tài khoản mới</a>
                        </div>
                    </div>
                </div>

                <!-- Bên phải: Form đăng nhập -->
                <div class="col-lg-6 col-md-6">
                    <!-- Điểm neo đặt ngay trước form để cuộn xuống đúng chỗ -->
                    <a id="login-form"></a>

                    <div class="login_part_form">
                        <div class="login_part_form_iner">

                            <h3>Chào mừng quay trở lại!<br>Đăng nhập ngay</h3>

                            <!-- Hiển thị thông báo lỗi nếu có -->
                            <?php if (isset($_SESSION['login_error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 25px;">
                                    <strong>Lỗi:</strong> <?php echo htmlspecialchars($_SESSION['login_error']); ?>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <?php unset($_SESSION['login_error']); ?>
                            <?php endif; ?>

                            <form class="row contact_form" action="scripts/login_script.php" method="POST" novalidate="novalidate">
                                
                                <div class="col-md-12 form-group p_star">
                                    <input type="email" class="form-control" 
                                           id="email" name="email" 
                                           value="<?php echo isset($_SESSION['old_email']) ? htmlspecialchars($_SESSION['old_email']) : ''; ?>" 
                                           placeholder="Nhập email của bạn" required />
                                    <?php unset($_SESSION['old_email']); // Xóa sau khi dùng ?>
                                </div>

                                <div class="col-md-12 form-group p_star">
                                    <input type="password" class="form-control" 
                                           id="password" name="password" 
                                           placeholder="Nhập mật khẩu" required />
                                </div>

                                <div class="col-md-12 form-group">
                                    <div class="creat_account d-flex align-items-center">
                                        <input type="checkbox" id="remember_me" name="remember_me" />
                                        <label for="remember_me">Ghi nhớ đăng nhập</label>
                                    </div>

                                    <button type="submit" class="btn_3">
                                        Đăng nhập
                                    </button>

                                    
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php require "./includes/footer.php"; ?>

<!-- Scripts -->
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

<!-- Smooth scroll đến #login-form khi trang load nếu có lỗi -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Nếu URL có #login-form (tức là vừa redirect về vì lỗi)
        if (window.location.hash === '#login-form') {
            setTimeout(function() {
                const element = document.getElementById('login-form');
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 100); // Delay nhẹ để trang load xong
        }
    });
</script>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-23581568-13');
</script>

</body>
</html>