<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['ADID'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php');
include('includes/db.php');

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy tổng số tour
$totalToursQuery = "SELECT COUNT(*) as totalTours FROM TOUR";
$totalToursResult = $conn->query($totalToursQuery);
$totalTours = $totalToursResult ? $totalToursResult->fetch_assoc()['totalTours'] : 0;

// Lấy tổng số booking
$totalBookingsQuery = "SELECT COUNT(*) as totalBookings FROM bookings WHERE STATUS = 1"; // chỉ tính booking đã duyệt
$totalBookingsResult = $conn->query($totalBookingsQuery);
$totalBookings = $totalBookingsResult ? $totalBookingsResult->fetch_assoc()['totalBookings'] : 0;

// Tính tổng doanh thu từ các tour đã được duyệt
$totalRevenueQuery = "SELECT SUM(TOTALPRICE) AS totalRevenue FROM bookings WHERE STATUS = 1"; // 1 là trạng thái đã được duyệt
$totalRevenueResult = $conn->query($totalRevenueQuery);
$totalRevenue = $totalRevenueResult ? $totalRevenueResult->fetch_assoc()['totalRevenue'] : 0;

// Lấy tổng số booking bị hủy
$cancelledBookingsQuery = "SELECT COUNT(*) as cancelledBookings FROM bookings WHERE STATUS = 'Cancelled'";
$cancelledBookingsResult = $conn->query($cancelledBookingsQuery);
$cancelledBookings = $cancelledBookingsResult ? $cancelledBookingsResult->fetch_assoc()['cancelledBookings'] : 0;

// Lấy tổng số booking chờ xác nhận
$pendingBookingsQuery = "SELECT COUNT(*) as pendingBookings FROM bookings WHERE STATUS = 'Pending'";
$pendingBookingsResult = $conn->query($pendingBookingsQuery);
$pendingBookings = $pendingBookingsResult ? $pendingBookingsResult->fetch_assoc()['pendingBookings'] : 0;

// Lấy số lượng booking theo từng tour đã duyệt
$topToursQuery = "
    SELECT t.TOURNAME, COUNT(b.USERID) AS booking_count 
    FROM TOUR t 
    LEFT JOIN bookings b ON t.TOURID = b.TOURID AND b.STATUS = 1 -- Chỉ lấy booking đã duyệt
    GROUP BY t.TOURID 
    ORDER BY booking_count DESC 
    LIMIT 5";
$topToursResult = $conn->query($topToursQuery);

// Lấy số lượng booking theo từng tour ít nhất
$leastToursQuery = "
    SELECT t.TOURNAME, COUNT(b.USERID) AS booking_count 
    FROM TOUR t 
    LEFT JOIN bookings b ON t.TOURID = b.TOURID AND b.STATUS = 1 -- Chỉ lấy booking đã duyệt
    GROUP BY t.TOURID 
    ORDER BY booking_count ASC 
    LIMIT 5";
$leastToursResult = $conn->query($leastToursQuery);

// Lấy số lượng booking theo từng tháng trong năm hiện tại
$bookingsByMonthQuery = "
    SELECT 
        MONTH(STARTDATE) AS month, 
        COUNT(*) AS booking_count,
        CASE 
            WHEN SUM(CASE WHEN STATUS = 'Cancelled' THEN 1 ELSE 0 END) > 0 THEN 'Có hủy' 
            ELSE 'Không có hủy' 
        END AS status
    FROM 
        bookings 
    WHERE 
        YEAR(STARTDATE) = YEAR(CURDATE())
    GROUP BY 
        MONTH(STARTDATE)";

// Lấy doanh thu theo từng tour đã duyệt
$revenueByTourQuery = "
    SELECT 
        t.TOURID, 
        t.TOURNAME, 
        SUM(b.TOTALPRICE) AS revenue 
    FROM 
        TOUR t 
    LEFT JOIN 
        bookings b ON t.TOURID = b.TOURID AND b.STATUS = 1  -- Chỉ lấy các booking đã được duyệt
    GROUP BY 
        t.TOURID";

$bookingsByMonthResult = $conn->query($bookingsByMonthQuery);
$revenueByTourResult = $conn->query($revenueByTourQuery);

// Lấy doanh thu theo từng tháng trong năm hiện tại
$revenueByMonthQuery = "
    SELECT 
        MONTH(STARTDATE) AS month, 
        SUM(TOTALPRICE) AS total_revenue 
    FROM 
        bookings 
    WHERE 
        STATUS = 1 AND YEAR(STARTDATE) = YEAR(CURDATE())
    GROUP BY 
        MONTH(STARTDATE)
    ORDER BY 
        MONTH(STARTDATE)";
$revenueByMonthResult = $conn->query($revenueByMonthQuery);

// Khởi tạo mảng doanh thu theo tháng
$totalRevenueByMonth = array_fill(1, 12, 0); // 0 cho 12 tháng

// Lưu doanh thu vào mảng
if ($revenueByMonthResult) {
    while ($row = $revenueByMonthResult->fetch_assoc()) {
        $totalRevenueByMonth[$row['month']] = $row['total_revenue'];
    }
}

?>

<div class="main-container">
    <div class="pd-ltr-20">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <div class="clearfix mb-20">
                    <div class="pull-left">
                        <h4 class="text-blue h4">Thống kê</h4>
                    </div>
                </div>
                <div class="row">
                    <!-- Tổng số tour -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Tổng số tour có tại TRAVELTOUR</h5>
                                <h6 class="card-text"><?php echo $totalTours; ?></h6>
                            </div>
                        </div>
                    </div>
                    <!-- Tổng số booking -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Tổng số Lượt đặt</h5>
                                <h6 class="card-text"><?php echo $totalBookings; ?></h6>
                            </div>
                        </div>
                    </div>
                    <!-- Doanh thu tổng -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Tổng doanh thu </h5>
                                <h6 class="card-text"><?php echo number_format($totalRevenue, 0, ',', '.') . ' VNĐ'; ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                    <!-- Booking bị hủy -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Số tour bị hủy</h5>
                                <h6 class="card-text"><?php echo $cancelledBookings; ?></h6>
                            </div>
                        </div>
                    </div>
                    <!-- Booking chờ xác nhận -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Số tour chờ xác nhận</h5>
                                <h6 class="card-text"><?php echo $pendingBookings; ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ -->
            <!-- Biểu đồ -->
            <div class="min-height-200px">
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix mb-20">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Doanh thu theo tháng</h4>
                        </div>
                    </div>
                    <div style="max-width: 800px; margin: auto;">
                        <canvas id="myChart" style="width: 100%; height: 400px;"></canvas>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('myChart').getContext('2d');
                        const myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                                    'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
                                ],
                                datasets: [{
                                    label: 'Doanh thu (VNĐ)',
                                    data: [<?php echo implode(',', $totalRevenueByMonth); ?>],
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>


            <!-- Top 5 tour được đặt nhiều nhất -->
            <div class="min-height-200px">
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix mb-20">
                        <div class="pull-left">
                            <h4 class="text-blue h4">5 Tour được đặt nhiều nhất</h4>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Tên Tour</th>
                                <th>Số lượng đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topToursResult->num_rows > 0): ?>
                                <?php while ($row = $topToursResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['TOURNAME']; ?></td>
                                        <td><?php echo $row['booking_count'] . ' lần'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">Không có dữ liệu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 5 Tour được đặt ít nhất -->
            <div class="min-height-200px">
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix mb-20">
                        <div class="pull-left">
                            <h4 class="text-blue h4">5 Tour được đặt ít nhất</h4>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Tên Tour</th>
                                <th>Số lượng đặt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($leastToursResult->num_rows > 0): ?>
                                <?php while ($row = $leastToursResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['TOURNAME']; ?></td>
                                        <td><?php echo $row['booking_count'] . ' lần'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">Không có dữ liệu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Doanh thu theo từng tour -->
            <div class="min-height-200px">
                <div class="pd-20 card-box mb-30">
                    <div class="clearfix mb-20">
                        <div class="pull-left">
                            <h4 class="text-blue h4">Doanh thu theo từng tour</h4>
                        </div>
                    </div>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Tên Tour</th>
                                <th>Doanh thu (VNĐ)</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($revenueByTourResult->num_rows > 0): ?>
                                <?php while ($row = $revenueByTourResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['TOURNAME']; ?></td>
                                        <td><?php echo number_format($row['revenue'], 0, ',', '.') . ' VNĐ'; ?></td>
                                        <td><a href="detail.php?tourId=<?php echo $row['TOURID']; ?>"><i
                                                    class="fa fa-eye"></i>Xem chi tiết</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">Không có dữ liệu</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- js -->
<script src="vendors/scripts/core.js"></script>
<script src="vendors/scripts/script.min.js"></script>
<script src="vendors/scripts/process.js"></script>
<script src="vendors/scripts/layout-settings.js"></script>
<script src="src/plugins/apexcharts/apexcharts.min.js"></script>
<script src="src/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="src/plugins/datatables/js/dataTables.responsive.min.js"></script>
<script src="src/plugins/datatables/js/responsive.bootstrap4.min.js"></script>
<script src="vendors/scripts/dashboard.js"></script>
</body>

</html>