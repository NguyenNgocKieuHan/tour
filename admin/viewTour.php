<?php
session_start(); // Bắt đầu phiên làm việc

include('includes/header.php');
include('includes/db.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['ADID'])) {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header("Location: login.php");
    exit();
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Lấy ID của tour từ tham số URL
if (isset($_GET['id'])) {
    $tourId = intval($_GET['id']); // Chuyển đổi sang số nguyên
} else {
    header("Location: manageTours.php"); // Nếu không có ID, quay về trang quản lý
    exit();
}

// Truy vấn thông tin tour
$query = "SELECT t.TOURID, t.TOURNAME, tt.TOURTYPENAME, t.PRICE, t.TIMETOUR, t.IMAGE, t.DESCRIPTION, a.ADNAME
          FROM TOUR t
          JOIN TOURTYPE tt ON t.TOURTYPEID = tt.TOURTYPEID
          JOIN ADMIN a ON t.ADID = a.ADID
          WHERE t.TOURID = $tourId";

$result = mysqli_query($conn, $query);

// Kiểm tra có bản ghi nào không
if ($row = mysqli_fetch_assoc($result)) {
?>
    <div class="mobile-menu-overlay"></div>

    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                <div class="pd-20 card-box mb-30">
                    <h4 class="text-blue h4"><?php echo htmlspecialchars($row['TOURNAME']); ?></h4>
                    <p><strong>Quản trị viên:</strong> <?php echo htmlspecialchars($row['ADNAME']); ?></p>
                    <p><strong>Loại Tour:</strong> <?php echo htmlspecialchars($row['TOURTYPENAME']); ?></p>
                    <p><strong>Giá:</strong> <?php echo number_format((float) htmlspecialchars($row['PRICE']), 0, ',', '.') . " VNĐ"; ?></p>
                    <p><strong>Thời Gian:</strong> <?php echo htmlspecialchars($row['TIMETOUR']); ?> Ngày</p>
                    <p><strong>Mô Tả:</strong> <?php echo htmlspecialchars($row['DESCRIPTION']); ?></p>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['IMAGE']); ?>" alt="Ảnh Tour" style="width: 100%; max-width: 600px; border-radius: 5px;">
                    <br><br>
                    <a href="manageTours.php" class="btn btn-secondary btn-sm">Quay lại danh sách tour</a>
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
<?php
} else {
    echo "<div class='text-center'>Không tìm thấy tour.</div>";
}
?>