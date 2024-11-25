<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Bạn chưa đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_SESSION['userid'];
    $tourid = $_POST['tourid'];
    $startdate = $_POST['startdate'];
    $people_count = $_POST['people_count'];
    $price = $_POST['price'];
    $payment_method = $_POST['payment_method'];
    $phone = $_POST['phone'];

    if (empty($tourid) || empty($startdate) || empty($people_count) || empty($payment_method) || empty($phone)) {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin!'); window.history.back();</script>";
        exit();
    }

    $price = str_replace('.', '', explode(' ', $price)[0]);
    $price = floatval($price);

    $total_price = $people_count * $price;

    // Kiểm tra xem ngày khởi hành có hợp lệ không
    $currentDate = date("Y-m-d");
    if (strtotime($startdate) < strtotime($currentDate)) {
        echo "<script>alert('Ngày khởi hành không hợp lệ. Chọn ngày trong tương lai nhé!'); window.location.href='bookingg.php';</script>";
        exit();
    }

    // Truy vấn lấy MAXSLOTS và PRICE của tour
    $sqlMaxSlots = "SELECT MAXSLOTS, PRICE FROM tour WHERE TOURID = ?";
    $stmt = $conn->prepare($sqlMaxSlots);
    $stmt->bind_param("i", $tourid);
    $stmt->execute();
    $stmt->bind_result($maxSlots, $tourPrice);
    $stmt->fetch();
    $stmt->close();

    if ($people_count <= 0 || $people_count > $maxSlots) {
        echo "<script>alert('Số lượng người không hợp lệ.'); window.location.href='bookingg.php';</script>";
        exit();
    }

    // Truy vấn số người đã đặt cho tour (chỉ những booking đã được duyệt)
    $sqlTotalPeople = "SELECT COALESCE(SUM(NUMOFPEOPLE), 0) AS totalPeople FROM bookings WHERE TOURID = ? AND STATUS = '1'";
    $stmt = $conn->prepare($sqlTotalPeople);
    $stmt->bind_param("i", $tourid);
    $stmt->execute();
    $stmt->bind_result($totalPeople);
    $stmt->fetch();
    $stmt->close();

    if (($totalPeople + $people_count) > $maxSlots) {
        echo "<script>alert('Tour đã đầy chỗ!'); window.location.href='bookingg.php';</script>";
        exit();
    }

    // Tạo booking nếu không có trùng lặp
    $sqlCheckDuplicate = "SELECT * FROM bookings WHERE TOURID = ? AND STARTDATE = ?";
    $stmt = $conn->prepare($sqlCheckDuplicate);
    $stmt->bind_param("is", $tourid, $startdate);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Tour này đã được đặt vào ngày đó. Kiểm tra lại nhé!'); window.location.href='bookingg.php';</script>";
        exit();
    } else {
        $total_price = $people_count * $tourPrice;
        $sqlInsertBooking = "INSERT INTO bookings (TOURID, USERID, BOOKINGDATE, NUMOFPEOPLE, TOTALPRICE, STATUS, STARTDATE)
            VALUES (?, ?, NOW(), ?, ?, '2', ?)";

        $stmt = $conn->prepare($sqlInsertBooking);
        $stmt->bind_param("iisis", $tourid, $userid, $people_count, $total_price, $startdate);

        if ($stmt->execute()) {
            // Thêm vào bảng payments
            $sqlInsertPayment = "INSERT INTO payments (TOURID, USERID, STARTDATE, PAYMENT_DATE, AMOUNT, PAYMENT_METHOD) 
                        VALUES (?, ?, ?, NOW(), ?, ?)";
            $payment_stmt = $conn->prepare($sqlInsertPayment);
            $payment_stmt->bind_param("iisss", $tourid, $userid, $startdate, $total_price, $payment_method);

            if ($payment_stmt->execute()) {
                echo "<script>alert('Đặt tour thành công!'); window.location.href='booking_success.php';</script>";
            } else {
                echo "<script>alert('Lỗi khi xử lý thanh toán!'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Lỗi khi đặt tour!'); window.history.back();</script>";
        }
    }
}
