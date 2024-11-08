<?php
session_start(); // Bắt đầu phiên làm việc

// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tour";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Biến lưu thông báo lỗi
$error_message = "";

// Xử lý khi người dùng gửi biểu mẫu đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Truy vấn cơ sở dữ liệu để lấy thông tin người dùng
    $sql = "SELECT USERID, PASSWORD FROM users WHERE EMAIL = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userid, $stored_password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        // So sánh mật khẩu đã nhập với mật khẩu trong cơ sở dữ liệu
        if ($password === $stored_password) {
            // Đăng nhập thành công, lưu thông tin người dùng vào phiên làm việc
            $_SESSION['userid'] = $userid;
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Thông tin đăng nhập không chính xác";
        }
    } else {
        $error_message = "Email không tồn tại";
    }

    $stmt->close();
}

$conn->close();
