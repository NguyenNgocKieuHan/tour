<?php
session_start();
$activate = "contact";

include('includes/header.php');
include('includes/db.php');

// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT USNAME, USEMAIL FROM users WHERE userid = ?");
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if we got any results
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fullName = htmlspecialchars($row['USNAME']);
        $email = htmlspecialchars($row['USEMAIL']);
    } else {
        $fullName = "Không tìm thấy";
        $email = "Không tìm thấy";
    }

    $stmt->close();
} else {
    $fullName = "Khách hàng";
    $email = "guest@example.com";
}

?>
<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4">Liên hệ với chúng tôi</h1>
            <!-- <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="#">Trang</a></li>
                <li class="breadcrumb-item active text-white">Liên hệ</li>
            </ol> -->
    </div>
</div>
<!-- Header End -->

<!-- Contact Start -->
<div class="container-fluid contact bg-light py-5">
    <div class="container py-5">
        <div class="mx-auto text-center mb-5" style="max-width: 900px;">
            <h5 class="section-title px-3">Liên hệ với chúng tôi</h5>
            <h1 class="mb-0">Liên hệ để được giải đáp mọi thắc mắc</h1>
        </div>
        <div class="row g-5 align-items-center">
            <div class="col-lg-4">
                <div class="bg-white rounded p-4">
                    <div class="text-center mb-4">
                        <i class="fa fa-map-marker-alt fa-3x text-primary"></i>
                        <h4 class="text-primary">Địa chỉ</h4>
                        <p class="mb-0">123 đường du lịch , <br> Thành phố Cần Thơ, Việt Nam</p>
                    </div>
                    <div class="text-center mb-4">
                        <i class="fa fa-phone-alt fa-3x text-primary mb-3"></i>
                        <h4 class="text-primary">Số điện thoại</h4>
                        <p class="mb-0">+012 345 67890</p>
                    </div>

                    <div class="text-center">
                        <i class="fa fa-envelope-open fa-3x text-primary mb-3"></i>
                        <h4 class="text-primary">Email</h4>
                        <p class="mb-0"> support@traveltour.com</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <h3 class="mb-2">Gửi cho chúng tôi một tin nhắn</h3>
                <form action="process_contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control border-0" id="name" name="name"
                                    value="<?php echo htmlspecialchars($fullName); ?>" readonly>
                                <label for="name">Họ và tên</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control border-0" id="email" name="email"
                                    value="<?php echo htmlspecialchars($email); ?>" readonly>
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control border-0" placeholder="Leave a message here" id="message"
                                    name="message" style="height: 160px" required></textarea>
                                <label for="message">Tin nhắn</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit">Gửi tin nhắn</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12">
                <div class="rounded">
                    <iframe class="rounded w-100" style="height: 450px;"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d387191.33750346623!2d-73.97968099999999!3d40.6974881!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1694259649153!5m2!1sen!2sbd"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact End -->

<!-- Subscribe Start -->
<!-- <div class="container-fluid subscribe py-5">
    <div class="container text-center py-5">
        <div class="mx-auto text-center" style="max-width: 900px;">
            <h5 class="subscribe-title px-3">Subscribe</h5>
            <h1 class="text-white mb-4">Our Newsletter</h1>
            <p class="text-white mb-5">Lorem ipsum dolor sit amet consectetur adipisicing elit. Laborum tempore nam, architecto doloremque velit explicabo? Voluptate sunt eveniet fuga eligendi! Expedita laudantium fugiat corrupti eum cum repellat a laborum quasi.
            </p>
            <div class="position-relative mx-auto">
                <input class="form-control border-primary rounded-pill w-100 py-3 ps-4 pe-5" type="text" placeholder="Your email">
                <button type="button" class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 px-4 mt-2 me-2">Subscribe</button>
            </div>
        </div>
    </div>
</div> -->
<!-- Subscribe End -->

<!-- Footer Start -->
<?php include('includes/footer.php'); ?>
<!-- Footer End -->

<!-- Back to Top -->
<!-- <a href="#" class="btn btn-primary btn-primary-outline-0 btn-md-square back-to-top"><i class="fa fa-arrow-up"></i></a> -->


<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>


<!-- Template Javascript -->
<script src="js/main.js"></script>
</body>

</html>