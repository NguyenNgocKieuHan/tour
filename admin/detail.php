<?php
session_start();

if (!isset($_SESSION['ADID'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php');
include('includes/db.php');

if (isset($_GET['tourId'])) {
    $tourId = intval($_GET['tourId']);

    // Truy vấn để lấy tên tour, số lượng booking theo tháng, doanh thu và số lượng hủy theo tourId
    $bookingsMonthlyQuery = "
    SELECT 
        t.TOURNAME,
        MONTH(b.STARTDATE) AS month,
        COUNT(b.USERID) AS total_bookings,
        SUM(b.TOTALPRICE) AS total_revenue,
        SUM(CASE WHEN b.STATUS = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled_count
    FROM 
        bookings b
    INNER JOIN 
        TOUR t ON b.TOURID = t.TOURID
    WHERE 
        b.TOURID = ?
        AND b.STATUS = 1  -- Chỉ lấy các booking đã được duyệt
    GROUP BY 
        MONTH(b.STARTDATE)
    ORDER BY 
        MONTH(b.STARTDATE);
    ";

    // Sử dụng prepared statement để tránh SQL injection
    $stmt = $conn->prepare($bookingsMonthlyQuery);
    $stmt->bind_param("i", $tourId);
    $stmt->execute();
    $bookingsMonthlyResult = $stmt->get_result();

?>
    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix mb-20">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Doanh thu theo tour</h4>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Tour</th>
                                <th scope="col">Tháng</th>
                                <th scope="col">Tổng lần đặt</th>
                                <th scope="col">Doanh thu</th>
                                <th scope="col">Số lần tour bị hủy</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Kiểm tra và hiển thị kết quả
                        if ($bookingsMonthlyResult->num_rows > 0) {
                            while ($row = $bookingsMonthlyResult->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['TOURNAME']) . '</td>';  // Hiện tên tour
                                echo '<td>' . $row['month'] . '</td>';
                                echo '<td>' . $row['total_bookings'] . '</td>';
                                echo '<td>' . number_format($row['total_revenue'], 0, ',', '.') . ' VNĐ</td>';
                                echo '<td>' . $row['cancelled_count'] . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr>';
                            echo '<td colspan="5">Không có dữ liệu.</td>';  // Cột số 5 cho đủ cột
                            echo '</tr>';
                        }

                        echo '</tbody>';
                        echo '</table>';
                    }
                        ?>
                </div>
            </div>
        </div>
    </div>