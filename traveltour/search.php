<?php
session_start();

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

// Truy vấn SQL tìm kiếm theo các trường: tên tour, mô tả, giá, loại tour và điểm đến
$sql = "SELECT tour.*, 
               tourtype.TOURTYPENAME as TOURTYPENAME, 
               destination.DENAME, 
               destination.DESTINATIONID, 
               destination.DEDESCRIPTION, 
               destination.DEIMAGE as DEIMAGE, 
               tour.IMAGE as TOUR_IMAGE, 
               city.CITYNAME as CITYNAME, 
               district.DISTRICTNAME as DISTRICTNAME
        FROM tour 
        JOIN tourtype ON tour.TOURTYPEID = tourtype.TOURTYPEID
        LEFT JOIN destination ON tour.TOURID = destination.TOURID
        LEFT JOIN district ON destination.DISTRICTID = district.DISTRICTID
        -- LEFT JOIN city ON destination.CITYID = city.CITYID
        LEFT JOIN city ON district.CITYID = city.CITYID
        WHERE tour.TOURNAME LIKE ? 
        OR tour.DESCRIPTION LIKE ? 
        OR tour.PRICE LIKE ? 
        OR tourtype.TOURTYPENAME LIKE ? 
        OR destination.DENAME LIKE ? 
        OR city.CITYNAME LIKE ? 
        OR district.DISTRICTNAME LIKE ?";

// Chuẩn bị câu lệnh SQL
$stmt = $conn->prepare($sql);
$searchTerm = "%" . $query . "%";  // Thêm ký tự wildcard cho LIKE search

if ($stmt) {
    // Bind các tham số vào câu lệnh SQL
    $stmt->bind_param(
        "sssssss",  // Số lượng tham số 's' phải bằng số lượng tham số trong câu lệnh SQL
        $searchTerm, // tour.TOURNAME LIKE ?
        $searchTerm, // tour.DESCRIPTION LIKE ?
        $searchTerm, // tour.PRICE LIKE ?
        $searchTerm, // tourtype.TOURTYPENAME LIKE ?
        $searchTerm, // destination.DENAME LIKE ?
        $searchTerm, // city.CITYNAME LIKE ?
        $searchTerm  // district.DISTRICTNAME LIKE ?
    );

    // Thực thi câu lệnh SQL
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Có lỗi khi chuẩn bị câu lệnh SQL.";
}

// Hiển thị kết quả tìm kiếm
include('includes/header.php');  // Include file header
?>

<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4">Kết quả tìm kiếm</h3>
    </div>
</div>

<div class="container-fluid packages py-5">
    <div class="container py-5">
        <div class="mx-auto text-center mb-5" style="max-width: 900px;">
            <h5 class="section-title px-3">Kết quả tìm kiếm cho từ khóa: "<?php echo htmlspecialchars($query); ?>"</h5>
        </div>

        <?php
        // Kiểm tra nếu có kết quả
        if ($result->num_rows > 0) {
            // Tạo mảng để phân loại kết quả thành tour và điểm đến
            $tours = [];
            $destinations = [];

            // Phân loại kết quả vào tour và điểm đến
            while ($row = $result->fetch_assoc()) {
                // Phân loại theo TOURID
                if ($row['TOURID'] && !isset($tours[$row['TOURID']])) {
                    $tours[$row['TOURID']] = $row;
                }
                // Phân loại theo DESTINATIONID
                if ($row['DESTINATIONID'] && !isset($destinations[$row['DESTINATIONID']])) {
                    $destinations[$row['DESTINATIONID']] = $row;
                }
            }

            // Hiển thị kết quả tìm kiếm cho Tour và Điểm đến trong cùng một hàng
            echo "<div class='row'>";  // Mở một hàng mới

            // Hiển thị Tour
            foreach ($tours as $tour) {
                $tourId = $tour['TOURID'];
                $tourName = $tour['TOURNAME'];
                $tourTypeName = $tour['TOURTYPENAME'];
                $description = $tour['DESCRIPTION'];
                $price = $tour['PRICE'];
                $time = $tour['TIMETOUR'];
                $tourImage = $tour['TOUR_IMAGE']; // Ảnh từ bảng tour
                $shortDescription = substr($description, 0, 100); // Rút ngắn mô tả

                // Truy vấn để tính điểm trung bình từ bảng reviews
                $ratingQuery = "SELECT AVG(RATING) AS avgRating FROM reviews WHERE TOURID = ?";
                $ratingStmt = $conn->prepare($ratingQuery);
                $ratingStmt->bind_param("i", $tourId);
                $ratingStmt->execute();
                $ratingResult = $ratingStmt->get_result();
                $ratingRow = $ratingResult->fetch_assoc();
                $averageRating = $ratingRow['avgRating'] ? $ratingRow['avgRating'] : 0;  // Nếu không có đánh giá, mặc định là 0

                echo "<div class='col-lg-4 col-md-6 mb-4'>";  // Mỗi phần tử trong một cột
                echo "<div class='card h-100'>";

                // Hiển thị hình ảnh nếu có
                if ($tourImage) {
                    echo "<img src='data:image/jpeg;base64," . base64_encode($tourImage) . "' class='card-img-top' alt='Tour Image'>";
                } else {
                    echo "<img src='path_to_default_image.jpg' class='card-img-top' alt='Tour Image'>";
                }

                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($tourName) . "</h5>";
                echo "<p class='card-text'>" . htmlspecialchars($shortDescription) . "...</p>";
                echo "<p class='text-muted'>" . number_format($price, 0, ',', '.') . " VNĐ</p>";
                echo "<p>Loại tour: " . htmlspecialchars($tourTypeName) . "</p>";
                echo "<p>Thời gian: " . htmlspecialchars($time) . " ngày</p>";

                // Hiển thị đánh giá
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
                echo "<a href='booking.php?tourid=" . $tourId . "&tourname=" . urlencode($tourName) . "&price=" . $price . "' class='btn btn-success ms-2'>Đặt ngay</a>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }

            // Hiển thị Điểm đến trong cùng một row
            foreach ($destinations as $destination) {
                $destinationImage = $destination['DEIMAGE'];  // Ảnh từ bảng destination
                $destinationId = $destination['DESTINATIONID'];
                $destinationName = $destination['DENAME'];
                $destinationDescription = $destination['DEDESCRIPTION'];
                $shortDescription = substr($destinationDescription, 0, 100); // Rút ngắn mô tả
                $cityName = $destination['CITYNAME'];  // Tên thành phố
                $districtName = $destination['DISTRICTNAME'];  // Tên quận huyện

                echo "<div class='col-lg-4 col-md-6 mb-4'>";  // Mỗi phần tử trong một cột
                echo "<div class='card h-100'>";

                // Hiển thị hình ảnh nếu có
                if ($destinationImage) {
                    echo "<img src='data:image/jpeg;base64," . base64_encode($destinationImage) . "' class='card-img-top' alt='Destination Image'>";
                } else {
                    echo "<img src='path_to_default_image.jpg' class='card-img-top' alt='Destination Image'>";
                }

                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($destinationName) . "</h5>";
                echo "<p class='card-text'>" . htmlspecialchars($shortDescription) . "...</p>";
                echo "<p>Quận huyện: " . htmlspecialchars($districtName) . "</p>";
                echo "<p>Thành phố: " . htmlspecialchars($cityName) . "</p>";
                echo "<a href='destinationDetail.php?id=" . $destinationId . "' class='btn btn-primary'>Xem chi tiết</a>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }

            echo "</div>";  // Đóng row
        } else {
            echo "<p>Không tìm thấy kết quả nào cho từ khóa: \"" . htmlspecialchars($query) . "\"</p>";
        }

        // Đóng kết nối
        $stmt->close();
        $conn->close();
        ?>
    </div>
</div>

<?php include('includes/footer.php');  // Include file footer 
?>