<?php
session_start();
include('includes/db.php');
require 'vendor/autoload.php'; // Thêm PayPal SDK
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

if (isset($_GET['paymentId']) && isset($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $payerId = $_GET['PayerID'];

    $apiContext = new ApiContext(
        new OAuthTokenCredential(
            'Acrr192EATsqm9Ijx8l-UANZkrjejXyHtE_WYPoGZbOrvkY5O9ar1laWKSw_qVEAZdgQXvV0-phtJjXW',     // Thay bằng Client ID của bạn
            'EEZddIPOfGP2V-WqvgotNcdoBpXfl2z1T7a5aK9GWzfA5S-46VnPhRrdn_qCSS5xzQyjkOBYLMZrMu9X'  // Thay bằng Client Secret của bạn
        )
    );

    $payment = Payment::get($paymentId, $apiContext);

    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    try {
        $result = $payment->execute($execution, $apiContext);
        // Thanh toán thành công, lưu thông tin booking vào cơ sở dữ liệu
        $tourID = $_SESSION['tourid'];
        $userID = $_SESSION['userid'];
        $startDate = $_SESSION['startdate'];
        $numOfPeople = $_SESSION['people_count'];
        $bookingDate = date("Y-m-d H:i:s");
        $totalPrice = $_SESSION['totalPrice'];

        // Lưu thông tin booking
        $sqlInsertBooking = "INSERT INTO bookings (TOURID, USERID, BOOKINGDATE, NUMOFPEOPLE, TOTALPRICE, STATUS, STARTDATE) VALUES (?, ?, ?, ?, ?, '1', ?)";
        $stmt = $conn->prepare($sqlInsertBooking);
        $stmt->bind_param("iissds", $tourID, $userID, $bookingDate, $numOfPeople, $totalPrice, $startDate);

        if ($stmt->execute()) {
            echo "<script>alert('Thanh toán thành công!'); window.location.href='booking_success.php';</script>";
        } else {
            echo "<script>alert('Lỗi khi lưu thông tin đặt tour.');</script>";
        }
        $stmt->close();
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else {
    echo "<script>alert('Thanh toán thất bại hoặc bị hủy.'); window.location.href='list_tours.php';</script>";
}