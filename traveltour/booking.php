<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    echo "<script>
    alert('Bạn chưa đăng nhập!');
    window.location.href = 'login.php';
    </script>";
    exit();
}

include('includes/header.php');
include('includes/db.php');

// Get the tour details from the URL
if (isset($_GET['tourid'])) {
    $tourId = intval($_GET['tourid']);

    // Fetch tour details from database
    $queryTour = "SELECT TOURNAME, PRICE FROM tour WHERE TOURID = ?";
    $stmtTour = $conn->prepare($queryTour);
    $stmtTour->bind_param("i", $tourId);
    $stmtTour->execute();
    $resultTour = $stmtTour->get_result();
    $tourData = $resultTour->fetch_assoc();

    if (!$tourData) {
        echo "<p>Không tìm thấy thông tin tour!</p>";
        exit;
    }

    $tourName = htmlspecialchars($tourData['TOURNAME']);
    $price = htmlspecialchars($tourData['PRICE']);

    // Convert price to USD
    $exchangeRate = 23000; // Giả định: 1 USD = 23,000 VND
    $priceInUSD = $price / $exchangeRate;
} else {
    echo "<p>Không tìm thấy thông tin tour!</p>";
    exit;
}

// Fetch user details based on USERID
$userid = $_SESSION['userid'];
$query = "SELECT USNAME, USEMAIL, USSDT FROM users WHERE USERID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    echo "<p>Không tìm thấy thông tin người dùng!</p>";
    exit;
}

$fullName = htmlspecialchars($userData['USNAME']);
$email = htmlspecialchars($userData['USEMAIL']);
$phone = htmlspecialchars($userData['USSDT']);
?>
<!-- Tour Booking Start -->
<script
    src="https://www.paypal.com/sdk/js?client-id=AWHNWa7HF-1xlxrHPlD7RDudvOmxkJ5nBzUxEFiqEyHaLN-L5Zmdl8nF9YzRojlPXG4ipg0r5hub4AGB&currency=USD&components=buttons&enable-funding=venmo,paylater,card"
    data-sdk-integration-source="developer-studio"></script>
<div class="container-fluid booking py-5">
    <div class="container py-5">
        <div class="row g-5 align-items-start">
            <!-- Form Container -->
            <form action="process_booking.php" method="post" class="d-flex">
                <input type="hidden" name="tourid" value="<?php echo $tourId; ?>">
                <!-- Left Column (Tour and User Info) -->
                <div class="col-lg-7">
                    <h1 class="text-white mb-4">Đặt tour trực tuyến</h1>
                    <div class="row g-3">
                        <!-- Tour Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white" value="<?php echo $tourName; ?>"
                                    readonly>
                                <label for="tourname">Tên Tour</label>
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control bg-white" name="email"
                                    value="<?php echo $email; ?>" readonly>
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <!-- Price (VND) -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white"
                                    value="<?php echo number_format($price, 0, ',', '.') . ' VND'; ?>" readonly>
                                <label for="price_vnd">Giá vé 1 người (VND)</label>
                            </div>
                        </div>
                        <!-- Price (USD) -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white"
                                    value="$<?php echo number_format($priceInUSD, 2, '.', '') . ' USD'; ?>" readonly>
                                <label for="price_usd">Giá vé 1 người(USD)</label>
                            </div>
                        </div>
                        <!-- User Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white" name="name"
                                    value="<?php echo $fullName; ?>" readonly>
                                <label for="name">Họ và tên</label>
                            </div>
                        </div>
                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white" name="phone"
                                    value="<?php echo $phone; ?>" readonly>
                                <label for="phone">Số điện thoại</label>
                            </div>
                        </div>
                        <!-- Start Date -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control bg-white" name="startdate"
                                    min="<?php echo date('Y-m-d'); ?>" required>
                                <label for="startdate">Ngày khởi hành</label>
                            </div>
                        </div>
                        <!-- Number of People -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control bg-white" name="people_count" min="1" max="30"
                                    required>
                                <label for="people_count">Số người</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Payment Methods) -->
                <div class="col-lg-6 mt-4">
                    <div class="col-12">
                        <!-- <h4 class="text-white mb-4">Phương thức thanh toán</h4> -->
                        <label class="text-white mb-4" for="people_count">Phương thức thanh toán</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_at_counter"
                                value="Thanh toán tại quầy" required>
                            <label class="text-white mb-4" for="pay_at_counter">Thanh toán tại
                                quầy</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="paypal"
                                value="PayPal" required>
                            <label class="text-white mb-4" for="paypal">
                                Thanh toán qua Paypal </label>
                        </div>
                        <div id="paypal-button-container"></div>


                    </div>
                    <div class="col-12 mt-4">
                        <button class="btn btn-primary w-100 py-3" type="submit">Đặt ngay</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include('includes/footer.php'); ?>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script>

<!-- ---------------------- -->
<script>
    const priceInUSD = "<?php echo number_format($priceInUSD, 2, '.', ''); ?>";

    paypal.Buttons({
        createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: priceInUSD
                    }
                }]
            });
        },
        onApprove: (data, actions) => {
            return actions.order.capture().then(details => {
                alert('Thanh toán thành công! Cảm ơn bạn đã đặt tour.');
                window.location.href =
                    `booking_success.php?tourid=<?php echo $tourId; ?>`;
            });
        },
        onError: (err) => {
            alert('Đã xảy ra lỗi khi thanh toán: ' + err);
        }
    }).render('#paypal-button-container');
</script>
</body>

</html>