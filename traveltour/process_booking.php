<?php
session_start();

if (!isset($_SESSION['userid'])) {
    echo "<script>
    alert('Bạn chưa đăng nhập!');
    window.location.href = 'login.php';
    </script>";
    exit();
}

include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tourId = intval($_POST['tourid']);
    $userId = $_SESSION['userid'];
    $startDate = $_POST['startdate'];
    $peopleCount = intval($_POST['people_count']);
    $paymentMethod = htmlspecialchars($_POST['payment_method']);


    $status = ($paymentMethod === "PayPal") ? "1" : "2";  // 1 nếu đã thanh toán, 2 nếu đang chờ duyệt
    // Đặt múi giờ mặc định trong PHP (nếu chưa cấu hình)
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    // Sau đó sử dụng `date()` hoặc `DateTime` như bình thường
    $bookingDate = date('Y-m-d H:i:s'); // Hoặc
    $dateTime = new DateTime();
    $bookingDate = $dateTime->format('Y-m-d H:i:s');

    if (!$tourId || !$userId || !$startDate || $peopleCount < 1) {
        echo "<script>
        alert('Dữ liệu không hợp lệ!');
        window.location.href = 'tour_booking.php?tourid=$tourId';
        </script>";
        exit();
    }

    // Lấy thông tin tour
    $queryTour = "SELECT TOURNAME, PRICE, MAXSLOTS FROM tour WHERE TOURID = ?";
    $stmtTour = $conn->prepare($queryTour);
    $stmtTour->bind_param("i", $tourId);
    $stmtTour->execute();
    $resultTour = $stmtTour->get_result();
    $tourData = $resultTour->fetch_assoc();

    if (!$tourData) {
        echo "<script>
        alert('Không tìm thấy thông tin tour!');
        window.location.href = 'index.php';
        </script>";
        exit();
    }

    $tourName = $tourData['TOURNAME'];
    $pricePerPerson = $tourData['PRICE'];
    $totalPrice = $pricePerPerson * $peopleCount;
    $totalSeats = $tourData['MAXSLOTS'];  // Số ghế tối đa cho tour

    // Kiểm tra số lượng khách hiện tại đã đặt cho tour vào ngày xuất phát
    $queryBookedSeats = "
        SELECT SUM(NUMOFPEOPLE) AS bookedSeats
        FROM bookings
        WHERE TOURID = ? AND STARTDATE = ?";
    $stmtBookedSeats = $conn->prepare($queryBookedSeats);
    $stmtBookedSeats->bind_param("is", $tourId, $startDate);
    $stmtBookedSeats->execute();
    $resultBookedSeats = $stmtBookedSeats->get_result();
    $bookedSeats = $resultBookedSeats->fetch_assoc()['bookedSeats'];

    // Kiểm tra xem số ghế còn lại có đủ cho khách hàng không
    $availableSeats = $totalSeats - ($bookedSeats ? $bookedSeats : 0);

    if ($peopleCount > $availableSeats) {
        echo "<script>
        alert('Số lượng ghế không đủ! Còn $availableSeats ghế cho tour này.');
        window.location.href = 'booking.php?tourid=$tourId';
        </script>";
        exit();
    }

    // Kiểm tra xem người dùng đã đặt tour vào ngày này chưa
    $queryExistingBooking = "SELECT * FROM bookings WHERE USERID = ? AND TOURID = ? AND STARTDATE = ?";
    $stmtExistingBooking = $conn->prepare($queryExistingBooking);
    $stmtExistingBooking->bind_param("iis", $userId, $tourId, $startDate);
    $stmtExistingBooking->execute();
    $resultExistingBooking = $stmtExistingBooking->get_result();

    if ($resultExistingBooking->num_rows > 0) {
        echo "<script>
        alert('Bạn đã đặt tour này vào ngày $startDate. Vui lòng chọn ngày khác.');
        window.location.href = 'booking.php?tourid=$tourId';
        </script>";
        exit();
    }

    // Bắt đầu giao dịch
    $conn->begin_transaction();

    try {
        // 1. Lưu thông tin vào bảng bookings
        $queryBooking = "
            INSERT INTO bookings (TOURID, USERID, STARTDATE, NUMOFPEOPLE, TOTALPRICE, STATUS, BOOKINGDATE)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtBooking = $conn->prepare($queryBooking);
        $stmtBooking->bind_param("iisdsis", $tourId, $userId, $startDate, $peopleCount, $totalPrice, $status, $bookingDate);
        $stmtBooking->execute();

        // 2. Lưu thông tin vào bảng payments
        $queryPayment = "
            INSERT INTO payments (TOURID, USERID, STARTDATE, PAYMENT_DATE, AMOUNT, PAYMENT_METHOD) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmtPayment = $conn->prepare($queryPayment);
        $paymentDate = date('Y-m-d H:i:s'); // Ngày thanh toán hiện tại
        $stmtPayment->bind_param("iissds", $tourId, $userId, $startDate, $paymentDate, $totalPrice, $paymentMethod);
        $stmtPayment->execute();

        // 3. Cập nhật lại số ghế còn lại chỉ khi trạng thái là 1 (đặt thành công)
        if ($status == "1") {
            $queryUpdateSeats = "
                UPDATE tour 
                SET MAXSLOTS = MAXSLOTS - ?
                WHERE TOURID = ?";
            $stmtUpdateSeats = $conn->prepare($queryUpdateSeats);
            $stmtUpdateSeats->bind_param("ii", $peopleCount, $tourId);
            $stmtUpdateSeats->execute();
        }

        // 4. Hoàn tất giao dịch
        $conn->commit();

        // Điều hướng dựa trên phương thức thanh toán
        if ($paymentMethod === "PayPal") {
            echo "<script>
            // alert('Vui lòng tiếp tục thanh toán qua PayPal.');
            window.location.href = 'paypal_payment.php?tourid=$tourId&userid=$userId&startdate=$startDate';
            </script>";
        } else {
            echo "<script>
            alert('Đặt tour thành công! Hãy thanh toán tại quầy để hoàn tất.');
            window.location.href = 'booking_history.php';
            </script>";
        }
    } catch (Exception $e) {
        // Nếu có lỗi, hoàn tác giao dịch
        $conn->rollback();
        echo "<script>
        alert('Đã xảy ra lỗi khi xử lý dữ liệu. Vui lòng thử lại!');
        window.location.href = 'booking.php?tourid=$tourId';
        </script>";
    }
} else {
    echo "<script>
    alert('Yêu cầu không hợp lệ!');
    window.location.href = 'index.php';
    </script>";
}
