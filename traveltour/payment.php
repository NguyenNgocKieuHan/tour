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
    src="https://www.paypal.com/sdk/js?client-id=test&currency=USD&components=buttons&enable-funding=venmo,paylater,card"
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
                <div class="col-lg-5 mt-4">
                    <div class="col-12">
                        <h4 class="text-white mb-4">Phương thức thanh toán</h4>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="pay_at_counter"
                                value="Thanh toán tại quầy" required>
                            <label class="text-white mb-4" for="pay_at_counter">Thanh toán tại
                                quầy</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="paypal"
                                value="PayPal" required>
                            <div id="paypal-button-container"></div>
                        </div>
                        <!-- PayPal Button -->
                        <script>
                            const priceUSD = "<?php echo number_format($priceInUSD, 2, '.', ''); ?>";

                            window.paypal
                                .Buttons({
                                    style: {
                                        shape: "rect",
                                        layout: "vertical",
                                        color: "gold",
                                        label: "paypal",
                                    },
                                    createOrder: function(data, actions) {
                                        return actions.order.create({
                                            purchase_units: [{
                                                amount: {
                                                    value: priceUSD // Giá trị USD
                                                }
                                            }]
                                        });
                                    },
                                    onApprove: function(data, actions) {
                                        return actions.order.capture().then(function(details) {
                                            alert('Thanh toán thành công! Cảm ơn bạn đã đặt tour.');
                                            window.location.href = 'booking_success.php';
                                        });
                                    }
                                })
                                .render("#paypal-button-container");
                        </script>
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
</body>

</html>
<!-- ---------------------- -->
<?php
session_start();
include('includes/db.php');
require '../vendor/autoload.php'; // Thêm PayPal SDK
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Bạn chưa đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

// Hàm để chuyển đổi VNĐ sang USD
function convertVNDToUSD($amountVND)
{
    $exchangeRate = 23000; // Tỉ giá chuyển đổi (1 USD = 23000 VNĐ)
    return $amountVND / $exchangeRate;
}

// Lấy dữ liệu từ form đặt tour
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tourID = $_POST['tourid'];
    $userID = $_SESSION['userid'];
    $numOfPeople = $_POST['people_count'];
    $startDate = $_POST['startdate'];
    $paymentMethod = $_POST['payment_method']; // Phương thức thanh toán người dùng chọn
    $currentDate = date("Y-m-d");

    // Kiểm tra xem có bản ghi trùng lặp cho tour vào cùng một ngày khởi hành
    $sqlCheckDuplicate = "SELECT * FROM bookings WHERE TOURID = ? AND STARTDATE = ? AND USERID = ?";
    $stmt = $conn->prepare($sqlCheckDuplicate);
    $stmt->bind_param("isi", $tourID, $startDate, $userID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Nếu có bản ghi trùng lặp cho tour vào cùng một ngày khởi hành
        echo "<script>alert('Tour này đã được đặt vào ngày đó. Vui lòng kiểm tra thông tin đặt tour của bạn.'); window.location.href='bookingg.php';</script>";
        exit();
    }

    // Lấy thông tin tour (giá và số chỗ tối đa)
    $sqlMaxSlots = "SELECT MAXSLOTS, PRICE FROM tour WHERE TOURID = ?";
    $stmt = $conn->prepare($sqlMaxSlots);
    $stmt->bind_param("i", $tourID);
    $stmt->execute();
    $stmt->bind_result($maxSlots, $tourPrice);
    $stmt->fetch();
    $stmt->close();

    // Kiểm tra số người đặt không vượt quá slot
    if ($numOfPeople <= 0 || $numOfPeople > $maxSlots) {
        echo "<script>alert('Số lượng người đặt không hợp lệ.'); window.location.href='bookingg.php';</script>";
        exit();
    }

    // Lấy tổng số người đã đặt
    $sqlTotalPeople = "SELECT COALESCE(SUM(NUMOFPEOPLE), 0) AS totalPeople FROM bookings WHERE TOURID = ? AND STATUS = '1'";
    $stmt = $conn->prepare($sqlTotalPeople);
    $stmt->bind_param("i", $tourID);
    $stmt->execute();
    $stmt->bind_result($totalPeople);
    $stmt->fetch();
    $stmt->close();

    if (($totalPeople + $numOfPeople) <= $maxSlots) {
        // Nếu người dùng chọn phương thức thanh toán là PayPal
        if ($paymentMethod == 'PayPal') {
            // Khởi tạo API context cho PayPal
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    'AWHNWa7HF-1xlxrHPlD7RDudvOmxkJ5nBzUxEFiqEyHaLN-L5Zmdl8nF9YzRojlPXG4ipg0r5hub4AGB', // Client ID
                    'EFq7k_UIWZStI__pB-z61IqhXyhWyFtkTHx6mPfE3MA-iapI47i0jYDiXfWLb-66wSRKUY-MaDuH-YcU' // Client Secret
                )
            );

            $apiContext->setConfig(
                array(
                    'mode' => 'sandbox',
                    'log.LogEnabled' => true,
                    'log.FileName' => '../PayPal.log',
                    'log.LogLevel' => 'DEBUG',
                    'cache.enabled' => true,
                )
            );

            // Tính toán tổng giá tiền và chuyển đổi sang USD
            $totalPriceVND = $numOfPeople * $tourPrice; // Tổng giá tiền bằng VNĐ
            $totalPriceUSD = convertVNDToUSD($totalPriceVND); // Chuyển đổi sang USD

            // Thiết lập thông tin thanh toán
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amount = new Amount();
            $amount->setCurrency('USD') // Thay đổi theo loại tiền tệ bạn muốn
                ->setTotal(number_format($totalPriceUSD, 2, '.', '')); // Tổng số tiền thanh toán

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setDescription('Thanh toán đặt tour');

            // URL quay về sau khi thanh toán thành công hoặc hủy
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl("http://yourdomain.com/success.php")
                ->setCancelUrl("http://yourdomain.com/cancel.php");

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions(array($transaction))
                ->setRedirectUrls($redirectUrls);

            try {
                $payment->create($apiContext);
                header("Location: " . $payment->getApprovalLink());
                exit;
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                echo $ex->getData();
                die();
            }
        } else {
            // Xử lý thanh toán tại quầy
            $bookingDate = date("Y-m-d H:i:s"); // Ngày và giờ hiện tại
            $totalPrice = $numOfPeople * $tourPrice; // Tính tổng giá tiền dựa trên số người đặt
            $status = '2'; // Trạng thái ban đầu là 'Chờ xác nhận'

            // Thêm thông tin đặt tour vào bảng bookings
            $sqlInsertBooking = "INSERT INTO bookings (TOURID, USERID, BOOKINGDATE, NUMOFPEOPLE, TOTALPRICE, STATUS, STARTDATE)
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sqlInsertBooking);
            $stmt->bind_param("iissdss", $tourID, $userID, $bookingDate, $numOfPeople, $totalPrice, $status, $startDate);

            // Thực thi truy vấn
            if ($stmt->execute()) {
                // Thêm thông tin thanh toán vào bảng payments
                $sqlInsertPayment = "INSERT INTO payments (TOURID, USERID, STARTDATE, PAYMENT_DATE, AMOUNT, PAYMENT_METHOD) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                $paymentDate = date("Y-m-d H:i:s");
                $stmt = $conn->prepare($sqlInsertPayment);
                $stmt->bind_param("iissss", $tourID, $userID, $startDate, $paymentDate, $totalPrice, $paymentMethod);

                // Kiểm tra nếu lưu thông tin thanh toán thành công
                if ($stmt->execute()) {
                    echo "<script>alert('Đặt tour và thanh toán thành công! Vui lòng chờ xác nhận từ quản lý.'); window.location.href='booking_success.php';</script>";
                } else {
                    echo "<script>alert('Lỗi: Không thể hoàn tất thanh toán. Vui lòng thử lại sau.');</script>";
                }
            } else {
                echo "<script>alert('Lỗi: Không thể đặt tour. Vui lòng thử lại sau.');</script>";
            }
        }
    } else {
        // Thông báo hết chỗ
        echo "<script>alert('Xin lỗi, tour này đã hết chỗ!'); window.location.href='tour.php';</script>";
    }
}
