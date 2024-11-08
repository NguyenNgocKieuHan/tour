<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['userid'])) {
    echo "<script>
    alert('Bạn chưa đăng nhập!');
    window.location.href = 'login.php';
</script>";
    exit();
}

$userid = $_SESSION['userid'];
include 'includes/header.php';

include 'includes/db.php';
// Lấy thông tin từ biểu mẫu đặt tour
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tourid = intval($_POST['tourid']);
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $startdate = htmlspecialchars($_POST['startdate']);
    $people_count = intval($_POST['people_count']);
    $price = htmlspecialchars($_POST['price']);
    $payment_method = htmlspecialchars($_POST['payment_method']);
    $phone = htmlspecialchars($_POST['phone']);
} else {
    echo "<p>Không có thông tin đặt tour!</p>";
    exit;
}

// Lấy thông tin tour từ cơ sở dữ liệu
include('includes/db.php');
$queryTour = "SELECT TOURNAME FROM tour WHERE TOURID = ?";
$stmtTour = $conn->prepare($queryTour);
$stmtTour->bind_param("i", $tourid);
$stmtTour->execute();
$resultTour = $stmtTour->get_result();
$tourData = $resultTour->fetch_assoc();

if (!$tourData) {
    echo "<p>Không tìm thấy thông tin tour!</p>";
    exit;
}

$tourName = htmlspecialchars($tourData['TOURNAME']);

// Tính tổng giá vé
$total_price = $people_count * floatval(str_replace(',', '', $price));
?>

<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4">Xác nhận thanh toán</h3>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active text-white">Xác nhận thanh toán</li>
        </ol>
    </div>
</div>
<!-- Header End -->

<!-- Xác Nhận Thanh Toán Start -->
<div class="container-fluid payment-confirmation py-5">
    <div class="container py-5">
        <h5 class="text-white mb-4">Thông tin đặt tour của bạn</h5>
        <table class="table table-bordered text-white">
            <tbody>
                <tr>
                    <th>Tên tour</th>
                    <td><?php echo $tourName; ?></td>
                </tr>
                <tr>
                    <th>Ngày khởi hành</th>
                    <td><?php echo $startdate; ?></td>
                </tr>
                <tr>
                    <th>Số người</th>
                    <td><?php echo $people_count; ?></td>
                </tr>
                <tr>
                    <th>Giá vé</th>
                    <td><?php echo number_format($total_price, 0, ',', '.') . ' VND'; ?></td>
                </tr>
                <tr>
                    <th>Phương thức thanh toán</th>
                    <td><?php echo ucfirst($payment_method); ?></td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td><?php echo $phone; ?></td>
                </tr>
            </tbody>
        </table>

    </div>
</div>
<!-- Xác Nhận Thanh Toán End -->

<?php include('includes/footer.php'); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>


<!-- Template Javascript -->
<script src="js/main.js"></script>