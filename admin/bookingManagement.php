<?php
session_start();
include('includes/header.php');
include('includes/db.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['ADID'])) {
    header("Location: login.php");
    exit();
}

// Truy vấn thông tin tất cả bookings và sắp xếp theo BOOKINGDATE từ mới đến cũ
$sql = "
    SELECT b.BOOKINGDATE, b.STARTDATE, b.NUMOFPEOPLE, b.TOTALPRICE, b.STATUS, t.TOURNAME, t.TOURID, b.USERID, u.USNAME AS USERNAME, b.APPROVALDATE
    FROM bookings b
    JOIN tour t ON b.TOURID = t.TOURID
    JOIN users u ON b.USERID = u.USERID
    ORDER BY b.BOOKINGDATE DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="mobile-menu-overlay"></div>

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <div class="clearfix mb-20">
                    <div class="pull-left">
                        <h4 class="text-blue h4">Quản lý Đặt Tour</h4>
                    </div>
                </div>
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col">Tên Người Đặt</th>
                            <th scope="col">Tên Tour</th>
                            <th scope="col">Ngày Đặt Tour</th>
                            <th scope="col">Ngày Xuất Phát</th>
                            <th scope="col">Ngày Duyệt</th>
                            <th scope="col">Số Người</th>
                            <th scope="col">Tổng Giá</th>
                            <th scope="col">Trạng Thái</th>
                            <th scope="col">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Nếu trạng thái là "1" (đã duyệt) và APPROVALDATE chưa được thiết lập
                                if ($row['STATUS'] == 1 && $row['APPROVALDATE'] == NULL) {
                                    // Lấy phương thức thanh toán từ bảng payments
                                    $queryPaymentMethod = "
                                        SELECT PAYMENT_METHOD 
                                        FROM payments 
                                        WHERE TOURID = ? AND USERID = ? AND STARTDATE = ?";
                                    $stmtPaymentMethod = $conn->prepare($queryPaymentMethod);
                                    $stmtPaymentMethod->bind_param("iis", $row['TOURID'], $row['USERID'], $row['STARTDATE']);
                                    $stmtPaymentMethod->execute();
                                    $paymentResult = $stmtPaymentMethod->get_result();
                                    $paymentData = $paymentResult->fetch_assoc();
                                    $paymentMethod = $paymentData['PAYMENT_METHOD'] ?? '';

                                    // Kiểm tra phương thức thanh toán
                                    if ($paymentMethod === "PayPal") {
                                        // Cập nhật APPROVALDATE = BOOKINGDATE
                                        $updateApprovalQuery = "
                                            UPDATE bookings 
                                            SET APPROVALDATE = BOOKINGDATE 
                                            WHERE TOURID = ? AND USERID = ? AND STARTDATE = ?";
                                    } else {
                                        // Cập nhật APPROVALDATE = thời gian hiện tại
                                        $updateApprovalQuery = "
                                            UPDATE bookings 
                                            SET APPROVALDATE = CURRENT_TIMESTAMP 
                                            WHERE TOURID = ? AND USERID = ? AND STARTDATE = ?";
                                    }

                                    $stmtUpdateApproval = $conn->prepare($updateApprovalQuery);
                                    $stmtUpdateApproval->bind_param("iis", $row['TOURID'], $row['USERID'], $row['STARTDATE']);
                                    $stmtUpdateApproval->execute();
                                    $stmtUpdateApproval->close();

                                    // Cập nhật giá trị APPROVALDATE trong mảng $row để phản ánh sự thay đổi
                                    $row['APPROVALDATE'] = ($paymentMethod === "PayPal")
                                        ? $row['BOOKINGDATE']
                                        : date('Y-m-d H:i:s'); // Thời gian hiện tại
                                }

                                // Trạng thái
                                $statusText = '';
                                switch ($row['STATUS']) {
                                    case 0:
                                        $statusText = 'Đã từ chối';
                                        break;
                                    case 1:
                                        $statusText = 'Đã xác nhận';
                                        break;
                                    case 2:
                                        $statusText = 'Chờ xác nhận';
                                        break;
                                }

                                // Ngày duyệt hoặc từ chối
                                $approvalDate = ($row['APPROVALDATE'] != NULL)
                                    ? date('d-m-Y H:i:s', strtotime($row['APPROVALDATE']))
                                    : 'Chưa được duyệt';

                                // Hành động
                                $statusActions = ($row['STATUS'] == 2) ?
                                    "<a href='approveBooking.php?TOURID=" . $row['TOURID'] . "&userid=" . $row['USERID'] . "&startdate=" . $row['STARTDATE'] . "' class='btn btn-success btn-sm'>Duyệt</a>
                                     <a href='rejectBooking.php?TOURID=" . $row['TOURID'] . "&userid=" . $row['USERID'] . "&startdate=" . $row['STARTDATE'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn từ chối không?\");'>Từ chối</a>"
                                    : "";

                                // Hiển thị từng hàng
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['USERNAME']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['TOURNAME']) . "</td>";
                                echo "<td>" . date('d-m-Y H:i:s', strtotime($row['BOOKINGDATE'])) . "</td>";
                                echo "<td>" . date('d-m-Y', strtotime($row['STARTDATE'])) . "</td>";
                                echo "<td>" . $approvalDate . "</td>"; // Ngày duyệt hoặc từ chối
                                echo "<td>" . htmlspecialchars($row['NUMOFPEOPLE']) . "</td>";
                                echo "<td><span class='price'>" . number_format($row['TOTALPRICE'], 0, ',', '.') . " VNĐ</span></td>";
                                echo "<td>" . htmlspecialchars($statusText) . "</td>";
                                echo "<td>
                                    <a href='viewBooking.php?userid=" . $row['USERID'] . "&tourid=" . $row['TOURID'] . "&startdate=" . $row['STARTDATE'] . "' class='btn btn-info btn-sm'>
                                        <i class='fa fa-eye'></i> Xem
                                    </a>
                                    $statusActions
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="9" class="text-center">Chưa có đơn đặt tour nào!</td></tr>';
                        }

                        $stmt->close(); // Đóng prepared statement
                        $conn->close(); // Đóng kết nối
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- js -->
<script src="vendors/scripts/core.js"></script>
<script src="vendors/scripts/script.min.js"></script>
<script src="vendors/scripts/process.js"></script>
<script src="vendors/scripts/layout-settings.js"></script>
</body>

</html>