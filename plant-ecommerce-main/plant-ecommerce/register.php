<?php
    session_start();
    require "./includes/head.php" ;

    if(isset($_SESSION['email'])){
        echo "<script> location.href='/ecommerce'; </script>";
        exit();
    }
?>

    <section class="breadcrumb breadcrumb_bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="breadcrumb_iner">
                        <div class="breadcrumb_iner_item">
                            <h2>Đăng Ký</h2>
                            <p>Home <span>-</span> Đăng Ký Người Dùng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="login_part padding_top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_text text-center">
                        <div class="login_part_text_iner">
                            <h2>Chưa có tài khoản</h2>
                            <p>
                                Đăng ký ngay để khám phá và mua sắm các loại cây xanh chất lượng cho không gian của bạn.
                            </p>
                            <a href="login.php" class="btn_3">Đăng ký ngay bây giờ</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="login_part_form">
                        <div class="login_part_form_iner">
                            <h3>
                                Chào mừng đến cửa hàng của chúng tôi! <br /> Hãy đăng ký ngay
                            </h3>
                            <?php if (!empty($_GET['error'])): ?>
                                <div class="alert alert-danger d-flex align-items-center mb-3" style="border-radius:8px; font-size:14px;">
                                    <span style="margin-right:8px;">⚠️</span>
                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                </div>
                            <?php endif; ?>
                            <style>
                                .addr-select-wrap {
                                    position: relative;
                                    width: 100%;
                                }
                                .addr-select-wrap select {
                                    appearance: none;
                                    -webkit-appearance: none;
                                    width: 100%;
                                    background-color: transparent;
                                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23bbbbbb'/%3E%3C/svg%3E");
                                    background-repeat: no-repeat;
                                    background-position: right 4px center;
                                    background-size: 10px 6px;
                                    border: none;
                                    border-bottom: 1px solid #e8e8e8;
                                    border-radius: 0;
                                    padding: 13px 28px 13px 0;
                                    font-size: 14px;
                                    color: #999;
                                    outline: none;
                                    cursor: pointer;
                                    transition: border-color 0.3s, color 0.3s;
                                }
                                .addr-select-wrap select:focus {
                                    border-bottom-color: #fe3a6e;
                                    color: #333;
                                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23fe3a6e'/%3E%3C/svg%3E");
                                }
                                .addr-select-wrap select option:not([value=""]) {
                                    color: #333;
                                }
                            </style>
                            <form class="row contact_form" action="scripts/signup_script.php" method="post">
                                <div class="col-md-6 form-group p_star">
                                    <input type="text" class="form-control" required id="fname" name="fname" value="" placeholder="Họ" />
                                </div>
                                <div class="col-md-6 form-group p_star">
                                    <input type="text" class="form-control" required id="lname" name="lname" value="" placeholder="Tên" />
                                </div>
                                <div class="col-md-12 form-group p_star">
                                    <input type="tel" class="form-control" required id="mobile" name="mobile" value="" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Số điện thoại phải đúng 10 chữ số" placeholder="Số điện thoại (10 số)" />
                                </div>
                                <div class="col-md-6 form-group p_star">
                                    <input type="email" class="form-control" required id="email" name="email" value="" placeholder="Địa chỉ email" />
                                </div>
                                <div class="col-md-12 form-group p_star">
                                    <input type="password" class="form-control" required id="password" name="password" value="" placeholder="Mật khẩu" />
                                </div>
                                <div class="col-md-12 form-group" style="margin-bottom:8px;">
                                    <div class="addr-select-wrap">
                                        <select name="district" id="district">
                                            <option value="">Quận / Huyện</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group" style="margin-bottom:8px;">
                                    <div class="addr-select-wrap">
                                        <select name="ward" id="ward">
                                            <option value="">Phường / Xã</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group p_star">
                                    <input type="text" class="form-control" required id="street" name="street" value="" placeholder="Số nhà, Tên đường (VD: 123 Đường ABC)" />
                                </div>
                                <div class="col-md-12 form-group">
                                    <button type="submit" value="submit" class="btn_3">
											Đăng ký
										</button>
                                   
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <?php require "./includes/footer.php" ?>

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
    <script src="js/hcm_data.js"></script>
    <script>
    $(document).ready(function () {
        // Populate district dropdown
        hcmData.forEach(function (district) {
            $('#district').append('<option value="' + district.name + '">' + district.name + '</option>');
        });
        // Refresh nice-select UI after adding options
        $('#district').niceSelect('update');

        // When district changes (listen via native select which nice-select syncs)
        $('#district').on('change', function () {
            var selectedDistrict = $(this).val();
            $('#ward').html('<option value="">Phường / Xã</option>');
            var districtObj = hcmData.find(function(d) { return d.name === selectedDistrict; });
            if (districtObj) {
                districtObj.wards.forEach(function (ward) {
                    $('#ward').append('<option value="' + ward + '">' + ward + '</option>');
                });
            }
            $('#ward').niceSelect('update');
        });

        // JS validation before submit (replace HTML required on select)
        $('form.contact_form').on('submit', function (e) {
            if (!$('#district').val()) {
                alert('Vui lòng chọn Quận/Huyện!');
                e.preventDefault();
                return false;
            }
            if (!$('#ward').val()) {
                alert('Vui lòng chọn Phường/Xã!');
                e.preventDefault();
                return false;
            }
        });
    });
    </script>

    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-23581568-13');
    </script>
    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vaafb692b2aea4879b33c060e79fe94621666317369993" integrity="sha512-0ahDYl866UMhKuYcW078ScMalXqtFJggm7TmlUtp0UlD4eQk0Ixfnm5ykXKvGJNFjLMoortdseTfsRT8oCfgGA==" data-cf-beacon='{"rayId":"7721ac11f91d3390","token":"cd0b4b3a733644fc843ef0b185f98241","version":"2022.11.3","si":100}'
        crossorigin="anonymous"></script>
</body>

</html>