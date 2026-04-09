<?php require 'conn.php' ?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>aranoz</title>
    <link rel="icon" href="img/favicon.png" />
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css" />
    <link rel="stylesheet" href="css/owl.carousel.min.css" />
    <link rel="stylesheet" href="css/nice-select.css" />
    <link rel="stylesheet" href="css/lightslider.min.css" />
    <link rel="stylesheet" href="css/all.css" />
    <link rel="stylesheet" href="css/flaticon.css" />
    <link rel="stylesheet" href="css/themify-icons.css" />
    <link rel="stylesheet" href="css/magnific-popup.css" />
    <link rel="stylesheet" href="css/slick.css" />
    <link rel="stylesheet" href="css/price_rangs.css" />
    <link rel="stylesheet" href="css/style.css" />
    <!-- FontAwesome 5 CDN to ensure fa-shopping-cart works -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <header class="main_menu home_menu">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="navbar-brand" href="index.php">
                            <img src="img/logo.png" alt="logo" />
                        </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="menu_icon"><i class="fas fa-bars"></i></span>
                        </button>
                        <div class="collapse navbar-collapse main-menu-item" id="navbarSupportedContent">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Trang chủ</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link" href="category.php">Sản phẩm</a>
                                </li>
            
                            </ul>
                        </div>
                        <div class="hearer_icon d-flex">
                            <?php
                                $mail = '';
                                if (isset($_SESSION['email'])){
                                    $mail = $_SESSION["email"];
                                }
                                $name= '';

                                $query = 'SELECT * FROM users';
                                $result = mysqli_query($con, $query);
                                while($row = mysqli_fetch_array($result)){
                                    if($row['email'] == $mail){
                                        $name = $row['first_name'] . " " . $row['last_name'];
                                    }
                                }

                            if (isset($_SESSION['email'])) {
                                $user_id = $_SESSION['user_id'];
                                $cart_count_query = "SELECT SUM(qty) AS total FROM cart WHERE user_id = '$user_id'";
                                $cart_count_result = mysqli_query($con, $cart_count_query);
                                $cart_count_row = mysqli_fetch_assoc($cart_count_result);
                                $cart_count = $cart_count_row['total'] ? $cart_count_row['total'] : 0;

                                echo '
                                        <div class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" id="navbarDropdown_3" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Xin chào, '.$name.'
                                            </a>
                                            <div class="dropdown-menu shadow border-0" aria-labelledby="navbarDropdown_3">
                                                <a class="dropdown-item" href="allorders.php"> Đơn hàng của tôi</a>
                                                <a class="dropdown-item" href="tracking.php"> Thông tin cá nhân</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="scripts/logout_script.php"> Đăng xuất</a>
                                            </div>
                                        </div>
                                        <div class="nav-item" style="margin-left: 15px;">
                                            <a class="nav-link" href="cart.php" id="navbarDropdown3">
                                                Giỏ hàng
                                            </a>
                                        </div>
                                    ';
                            } else {
                                echo '<div class="nav-item">
                                        <a href="login.php"><button class="nav-link custom">Đăng nhập</button></a>
                                    </div>';
                                echo '<div class="nav-item">
                                        <a href="register.php"><button class="nav-link custom">Đăng ký</button></a>
                                    </div>';
                            }

                            ?>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        
    </header>