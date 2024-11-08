<?php
session_start();
include('includes/header.php');
include('includes/db.php');

// Truy vấn thông tin tour
$sql = "SELECT TOURID, TOURNAME, DESCRIPTION, PRICE, TIMETOUR, IMAGE FROM tour";
$result = mysqli_query($conn, $sql);
?>
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4">Khám phá Tour</h3>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
            <li class="breadcrumb-item active text-white">Khám phá</li>
        </ol>
    </div>
</div>
<div class="container-fluid packages py-5">
    <div class="container py-5">
        <!-- <div class="mx-auto text-center mb-5" style="max-width: 900px;"> -->
        <!-- <div class="container"> -->
        <h1 class="text-center my-4">Tất cả các tour hiện có bên TravelTour</h1>

        <div class="row">
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Chuyển đổi dữ liệu ảnh từ BLOB sang base64 để hiển thị
                    $imageData = base64_encode($row['IMAGE']);
                    $imageSrc = 'data:image/jpeg;base64,' . $imageData;
            ?>

                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <img src="<?php echo $imageSrc; ?>" class="card-img-top" alt="Tour Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['TOURNAME']); ?></h5>
                                <p class="card-text">
                                    <?php
                                    // Giới hạn mô tả tour
                                    $shortDescription = substr($row['DESCRIPTION'], 0, 100) . '...';
                                    echo htmlspecialchars($shortDescription);
                                    ?>
                                </p>
                                <p class="card-text">
                                    <strong>Thời gian:</strong> <?php echo htmlspecialchars($row['TIMETOUR']); ?><br>
                                    <strong>Giá:</strong> <?php echo htmlspecialchars($row['PRICE']); ?> VNĐ
                                </p>
                                <a href="tour_detail.php?tourid=<?php echo $row['TOURID']; ?>" class="btn btn-primary">Xem
                                    chi
                                    tiết</a>
                            </div>
                        </div>
                    </div>

            <?php
                }
            } else {
                echo "<p class='text-center'>Chưa có tour nào được giới thiệu.</p>";
            }
            ?>
        </div>
    </div>
</div>
</div>

<?php
include('includes/footer.php');
?>