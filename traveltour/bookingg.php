<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
    echo "<script>alert('Bạn chưa đăng nhập! Vui lòng đăng nhập để tiếp tục.'); window.location.href='login.php';</script>";
    exit();
}

include('includes/header.php');
include('includes/db.php');

// Lấy danh sách các tour từ cơ sở dữ liệu
$sql = "SELECT TOURID, TOURNAME, PRICE FROM tour";
$result = $conn->query($sql);

$tours = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tours[] = $row;
    }
}

// Lấy thông tin người dùng
$userid = $_SESSION['userid'];
$query = "SELECT USNAME, USEMAIL, USSDT FROM users WHERE USERID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData) {
    echo "<script>alert('Thông tin người dùng không khả dụng. Vui lòng thử lại sau.');</script>";
    exit();
}

$fullName = $userData['USNAME'];
$email = $userData['USEMAIL'];
$phone = $userData['USSDT'];
$conn->close();
?>

<!-- Giao diện -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4">Đặt tour trực tuyến</h3>
        <ol class="breadcrumb justify-content-center mb-0">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="#">Trang</a></li>
            <li class="breadcrumb-item active text-white">Đặt tour trực tuyến</li>
        </ol>
    </div>
</div>

<div class="container-fluid booking py-5">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <!-- Cột bên trái: Thông tin tour -->
            <div class="col-lg-6">
                <h1 class="text-white mb-4">Đặt tour trực tuyến</h1>
                <form id="bookingForm" action="process_bookingg.php" method="POST">
                    <div class="row g-3">
                        <!-- Chọn tour -->
                        <div class="col-md-12">
                            <div class="form-floating">
                                <select id="tourDropdown" class="form-select bg-white border-0" name="tourid" required>
                                    <option value="">Chọn tour</option>
                                    <?php foreach ($tours as $tour) { ?>
                                        <option value="<?php echo $tour['TOURID']; ?>"
                                            data-price="<?php echo $tour['PRICE']; ?>">
                                            <?php echo htmlspecialchars($tour['TOURNAME']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="tourDropdown">Tên Tour</label>
                            </div>
                        </div>

                        <!-- Giá tour -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" id="price" class="form-control bg-light border-0"
                                    name="price_display" placeholder="Giá vé" readonly>
                                <label for="price">Giá vé cho 1 người (VNĐ)</label>
                            </div>
                        </div>

                        <!-- Họ và tên -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white" name="name"
                                    value="<?php echo htmlspecialchars($fullName); ?>" readonly>
                                <label for="name">Họ và tên</label>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control bg-white" name="email"
                                    value="<?php echo htmlspecialchars($email); ?>" readonly>
                                <label for="email">Email</label>
                            </div>
                        </div>

                        <!-- Số điện thoại -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white" name="phone"
                                    value="<?php echo htmlspecialchars($phone); ?>" readonly>
                                <label for="phone">Số điện thoại</label>
                            </div>
                        </div>

                        <!-- Ngày khởi hành -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control bg-white" name="startdate"
                                    min="<?php echo date('Y-m-d'); ?>" required>
                                <label for="startdate">Ngày khởi hành</label>
                            </div>
                        </div>

                        <!-- Số người -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control bg-white" name="people_count" min="1" max="30"
                                    required>
                                <label for="people_count">Số người</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cột bên phải: Thanh toán -->
            <div class="col-lg-6">
                <h4 class="text-white mb-4">Phương thức thanh toán</h4>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="pay_at_counter"
                        value="Thanh toán tại quầy" required>
                    <label class="text-white" for="pay_at_counter">Thanh toán tại quầy</label>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="PayPal"
                        required>
                    <label class="text-white" for="paypal">
                        <div id="paypal-button-container"></div>
                        <script src="https://www.paypal.com/sdk/js?client-id=test&currency=USD"></script>
                        <script>
                            paypal.Buttons({
                                createOrder: function(data, actions) {
                                    const price = document.getElementById('price').value.replace(/\D/g, '');
                                    if (!price) {
                                        alert('Vui lòng chọn tour trước khi thanh toán.');
                                        return;
                                    }
                                    return actions.order.create({
                                        purchase_units: [{
                                            amount: {
                                                value: (price / 23000).toFixed(2)
                                            } // Tính giá USD (23000 = tỷ giá)
                                        }]
                                    });
                                },
                                onApprove: function(data, actions) {
                                    return actions.order.capture().then(function(details) {
                                        alert('Thanh toán thành công! Cảm ơn bạn.');
                                        window.location.href = 'thank_you.php';
                                    });
                                }
                            }).render('#paypal-button-container');
                        </script>
                    </label>
                </div>
                <button class="btn btn-primary w-100 py-3 mt-4" form="bookingForm" type="submit">Đặt ngay</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Cập nhật giá khi chọn tour
    document.getElementById('tourDropdown').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        document.getElementById('price').value = price ? Number(price).toLocaleString('vi-VN') + ' VNĐ' : '';
    });

    // Kiểm tra đầu vào trước khi gửi form
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const tourId = document.getElementById('tourDropdown').value;
        if (!tourId) {
            alert('Vui lòng chọn một tour.');
            e.preventDefault();
        }
    });
</script>
<script>
    $('#datetime').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar-alt',
            up: 'fas fa-chevron-up',
            down: 'fas fa-chevron-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'far fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });
</script>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/lightbox/js/lightbox.min.js"></script>


<!-- Template Javascript -->
<script src="js/main.js"></script>
</body>

</html>