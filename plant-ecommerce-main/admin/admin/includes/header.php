<?php require 'conn.php' ?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - Plant Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><h1><i class="fas fa-leaf text-success"></i> Admin Panel</h1></a>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    
                </ul>

                <div class="d-flex align-items-center">
                    <?php
                    if(isset($_SESSION['admin_email'])){
                        echo '<span class="me-3 text-muted"><i class="fas fa-user-shield"></i> ' . htmlspecialchars($_SESSION['admin_email']) . '</span>';
                        echo '<a class="nav-link d-inline" href="scripts/logout_script.php"><button type="button" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất</button> 
                            </a>';
                    } else {
                        echo '<a class="nav-link d-inline" href="login.php"><button type="button" class="btn btn-primary">Đăng nhập</button></a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </nav>