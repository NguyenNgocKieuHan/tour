<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Traveltour</title>
    <link rel="shortcut icon" href="../admin/vendors/images/dolphin.png">

    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600&family=Roboto&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <!-- CSS Owl Carousel -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <!-- JS Owl Carousel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <!-- Rasa Webchat -->
    <!-- <div id="rasa-chat-widget" data-websocket-url="http://localhost:5005"></div>
    <script src="https://unpkg.com/@rasahq/rasa-chat" type="application/javascript"></script> -->

</head>

<body>



    <!-- Spinner Start -->
    <!-- <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div> -->
    <!-- Spinner End -->

    <!-- Topbar Start -->
    <div class="container-fluid bg-primary px-5 d-none d-lg-block">
        <div class="row gx-0">
            <div class="col-lg-8 text-center text-lg-start mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <!-- <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-instagram fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle" href=""><i class="fab fa-youtube fw-normal"></i></a> -->
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">

                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <?php
                    if (isset($_SESSION['userid'])): ?>


                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle text-light" data-bs-toggle="dropdown">
                                <small><i class="fa fa-home me-2"></i> Xin chào,
                                    <?php echo htmlspecialchars($_SESSION['username']); ?></small>
                            </a>
                            <div class="dropdown-menu rounded">
                                <a href="profile.php" class="dropdown-item"><i class="fas fa-user-alt me-2"></i> Hồ sơ của
                                    tôi</a>
                                <a href="booking_history.php" class="dropdown-item"><i
                                        class="fas fa-shopping-cart me-2"></i> Lịch sử đặt tour</a>
                                <a href="notification.php" class="dropdown-item"><i class="fas fa-bell me-2"></i> Thông
                                    báo</a>
                                <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i> Đăng
                                    xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="register.php"><small class="me-3 text-light"><i class="fa fa-user me-2"></i>Đăng
                                ký</small></a>
                        <a href="login.php"><small class="me-3 text-light"><i class="fa fa-sign-in-alt me-2"></i>Đăng
                                nhập</small></a>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid position-relative p-0">
        <nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
            <a href="index.php">
                <img src="../admin/vendors/images/tenlogo.png" alt="Logo" style=" width: 200px; height: 100px;">
            </a>
            <button class=" navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <li class="menu-item traveltour-normal-menu <?php echo ($activate == "index" ? "active" : "") ?>"><a
                            href="index.php" class="nav-link">Trang chủ</a></li>
                    <li class="menu-item traveltour-normal-menu <?php echo ($activate == "about" ? "active" : "") ?>"><a
                            href="about.php" class="nav-link">Về chúng tôi</a></li>
                    <li
                        class="menu-item traveltour-normal-menu <?php echo ($activate == "destination" ? "active" : "") ?>">
                        <a href="destination.php" class="nav-link">Điểm đến</a>
                    </li>
                    <li class="menu-item traveltour-normal-menu <?php echo ($activate == "tour" ? "active" : "") ?>"><a
                            href="tour.php" class="nav-link">Khám phá tour</a></li>
                    <!-- <li class="menu-item traveltour-normal-menu <?php echo ($activate == "blog" ? "active" : "") ?>"><a href="blog.php" class="nav-link">Bài viết</a></li> -->
                    <li class="menu-item traveltour-normal-menu <?php echo ($activate == "contact" ? "active" : "") ?>">
                        <a href="contact.php" class="nav-link">Liên hệ</a>
                    </li>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Trang</a>
                        <div class="dropdown-menu m-0">
                            <a href="gallery.php" class="dropdown-item">Thư viện ảnh</a>
                            <?php
                            if (isset($_SESSION['userid'])): ?>
                                <a href="profile.php" class="dropdown-item"><i class="fas fa-user-alt me-2"></i> Hồ sơ của
                                    tôi</a>
                                <a href="booking_history.php" class="dropdown-item"><i
                                        class="fas fa-shopping-cart me-2"></i> Lịch sử đặt tour</a>
                                <a href="notification.php" class="dropdown-item"><i class="fas fa-bell me-2"></i> Thông
                                    báo</a>
                                <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i> Đăng
                                    xuất</a>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <li
                    class="btn btn-primary rounded-pill py-2 px-4 ms-lg-4 active <?php echo ($activate == "bookingg" ? "active" : "") ?>">
                    <a href="bookingg.php" class="nav-link">Đặt tour</a>
                </li>
            </div>
        </nav>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>