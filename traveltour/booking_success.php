<?php
session_start();
include('includes/db.php');

if (isset($_GET['tour_id'], $_GET['user_id'], $_GET['start_date'])) {
    $tourId = $_GET['tour_id'];
    $userId = $_GET['user_id'];
    $startDate = $_GET['start_date'];

    // Prepare the SQL query to retrieve booking details
    $queryBooking = "SELECT * FROM bookings WHERE TOURID = ? AND USERID = ? AND STARTDATE = ?";
    $stmtBooking = $conn->prepare($queryBooking);
    $stmtBooking->bind_param("sis", $tourId, $userId, $startDate); // Use appropriate types (e.g., 's' for string, 'i' for integer)
    $stmtBooking->execute();
    $resultBooking = $stmtBooking->get_result();
    $bookingData = $resultBooking->fetch_assoc();

    if (!$bookingData) {
        echo "<script>alert('Không tìm thấy đơn đặt tour.'); window.location.href = 'index.php';</script>";
        exit();
    }

    // Display booking details
    echo "<h2>Đặt tour thành công!</h2>";
    echo "<p>Thông tin tour: " . htmlspecialchars($bookingData['TOURID']) . "</p>";
    echo "<p>Số người: " . htmlspecialchars($bookingData['NUMOFPEOPLE']) . "</p>";
    echo "<p>Tổng tiền: " . number_format($bookingData['TOTALPRICE'], 0, ',', '.') . " VND</p>";
    echo "<p>Trạng thái: " . htmlspecialchars($bookingData['STATUS']) . "</p>";
} else {
    echo "<script>alert('Thông tin đặt tour không hợp lệ.'); window.location.href = 'index.php';</script>";
}
