<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Bạn chưa đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tour";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Nhận từ khóa tìm kiếm từ form
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Truy vấn SQL tìm kiếm theo các trường: tên tour, mô tả, giá, loại tour
$sql = "SELECT tour.*, tourtype.TOURTYPENAME as TOURTYPENAME
        FROM tour 
        JOIN tourtype ON tour.TOURTYPEID = tourtype.TOURTYPEID
        WHERE tour.TOURNAME LIKE ? 
        OR tour.DESCRIPTION LIKE ? 
        OR tour.PRICE LIKE ? 
        OR tourtype.TOURTYPENAME LIKE ?";

$stmt = $conn->prepare($sql);
$searchTerm = "%" . $query . "%";
$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Hiển thị kết quả tìm kiếm
include('includes/header.php');  // Include file header

if ($result->num_rows > 0) { ?>
    <div class="container-fluid bg-breadcrumb">
        <div class="container text-center py-5" style="max-width: 900px;">
            <h3 class="text-white display-3 mb-4">Kết quả tìm kiếm</h3>
            <!-- <ol class="breadcrumb justify-content-center mb-0">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active text-white">Kết quả tìm kiếm</li>
            </ol> -->
        </div>
    </div>
    <div class="container-fluid packages py-5">
        <div class="container py-5">
            <div class="mx-auto text-center mb-5" style="max-width: 900px;">
                <h5 class="section-title px-3">Kết quả tìm kiếm cho từ khóa: "<?php echo htmlspecialchars($query); ?>"</h5>
            </div>
            <div class="row">
                <?php
                while ($row = $result->fetch_assoc()) {
                    $tourId = $row['TOURID'];
                    $tourName = $row['TOURNAME'];
                    $tourTypeName = $row['TOURTYPENAME'];
                    $description = $row['DESCRIPTION'];
                    $price = $row['PRICE'];
                    $time = $row['TIMETOUR'];
                    $shortDescription = substr($description, 0, 100); // Rút ngắn mô tả
                    $averageRating = 4.5; // Giả định đánh giá trung bình là 4.5

                    echo "<div class='col-lg-4 col-md-6 mb-4'>";
                    echo "<div class='card h-100'>";
                    echo "<img src='data:image/jpeg;base64," . base64_encode($row['IMAGE']) . "' class='card-img-top' alt='Tour Image'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>" . htmlspecialchars($tourName) . "</h5>";
                    echo "<p class='card-text'>" . htmlspecialchars($shortDescription) . "...</p>";
                    echo "<p class='text-muted'>" . number_format($price, 0, ',', '.') . " VNĐ</p>";
                    echo "<p>Loại tour: " . htmlspecialchars($tourTypeName) . "</p>";
                    echo "<p>Thời gian: " . htmlspecialchars($time) . " ngày</p>";

                    // Hiển thị đánh giá (số sao)
                    $fullStars = floor($averageRating);
                    $halfStar = ($averageRating - $fullStars) >= 0.5;
                    echo "<div class='mb-2'>";
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $fullStars) {
                            echo '<small class="fa fa-star text-primary"></small>';
                        } elseif ($halfStar && $i == $fullStars) {
                            echo '<small class="fa fa-star-half-alt text-primary"></small>';
                            $halfStar = false;
                        } else {
                            echo '<small class="fa fa-star-o text-primary"></small>';
                        }
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='card-footer'>";
                    echo "<a href='tour_detail.php?tourid=" . $tourId . "' class='btn btn-primary'>Xem chi tiết</a>";
                    if (isset($_SESSION["userid"])) {
                        echo "<a href='booking.php?tourid=" . $tourId . "&tourname=" . urlencode($tourName) . "&price=" . $price . "' class='btn btn-success ms-2'>Đặt ngay</a>";
                    } else {
                        echo "<a href='login.php?redirect=" . urlencode('booking.php?tourid=' . $tourId . '&tourname=' . urlencode($tourName) . '&price=' . $price) . "' class='btn btn-warning ms-2'>Đặt ngay</a>";
                    }
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
<?php
} else {
    echo "<script>alert('Không tìm thấy kết quả phù hợp cho từ khóa: \"" . htmlspecialchars($query) . "\".'); window.location.href='index.php';</script>";

    // echo "<div class='container'><p>Không tìm thấy kết quả phù hợp cho từ khóa: \"" . htmlspecialchars($query) . "\".</p></div>";
}

// Đóng kết nối cơ sở dữ liệu
$conn->close();

include('includes/footer.php');  // Include file footer
?>