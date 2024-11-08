<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Bạn chưa đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('includes/header.php');
include('includes/db.php');

// Lấy ID người dùng từ phiên
$userid = $_SESSION['userid'];

// Kiểm tra xem có dữ liệu từ form gửi lên không
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận dữ liệu từ form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $sdt = $_POST['sdt'];

    // Cập nhật thông tin người dùng trong cơ sở dữ liệu
    $stmt = $conn->prepare("UPDATE users SET USNAME = ?, USEMAIL = ?, USSDT = ? WHERE userid = ?");
    $stmt->bind_param("sssi", $name, $email, $sdt, $userid);
    $stmt->execute();

    // Kiểm tra xem cập nhật thành công không
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Cập nhật thông tin thành công!');</script>";
    } else {
        echo "<script>alert('Không có thay đổi nào để cập nhật.');</script>";
    }
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$stmt = $conn->prepare("SELECT USNAME, USEMAIL, USSDT FROM users WHERE userid = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$stmt->bind_result($name, $email, $sdt);
$stmt->fetch();
$stmt->close();

// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>

<!-- Header Start -->
<div class="container-fluid bg-breadcrumb">
    <div class="container text-center py-5" style="max-width: 900px;">
        <h3 class="text-white display-3 mb-4">Thông tin cá nhân</h3>
    </div>
</div>
<!-- Header End -->

<div class="container-fluid contact bg-light py-5">
    <div class="container py-5">
        <div class="mx-auto text-center mb-5" style="max-width: 900px;">
            <h1 class="mb-0">Hồ sơ cá nhân của bạn</h1>
        </div>
        <div class="row g-5 align-items-center">
            <div class="col-lg-8 mx-auto">
                <h3 class="mb-4">Thông tin chi tiết</h3>
                <ul class="list-group mb-4">
                    <li class="list-group-item"><strong>Họ và tên: </strong><?php echo htmlspecialchars($name); ?></li>
                    <li class="list-group-item"><strong>Email: </strong><?php echo htmlspecialchars($email); ?></li>
                    <li class="list-group-item"><strong>Số điện thoại: </strong><?php echo htmlspecialchars($sdt); ?>
                    </li>
                </ul>

                <h3 class="mb-4">Chỉnh sửa thông tin</h3>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Họ và tên</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="sdt" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="sdt" name="sdt"
                            value="<?php echo htmlspecialchars($sdt); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                </form>

                <a href="logout.php" class="btn btn-danger mt-4">Đăng xuất</a>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>