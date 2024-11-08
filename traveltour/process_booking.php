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

    // Kiểm tra ngày khởi hành
    if (strtotime($startDate) < strtotime($currentDate)) {
        echo "<script>alert('Ngày khởi hành không hợp lệ.'); window.location.href='bookingg.php';</script>";
        exit();
    }

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
                    'mode' => 'sandbox', // Chế độ sandbox cho thử nghiệm, live là thật
                    'log.LogEnabled' => true,
                    'log.FileName' => '../PayPal.log',
                    'log.LogLevel' => 'DEBUG', // Cấp độ log: DEBUG, INFO, WARN hoặc ERROR
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
