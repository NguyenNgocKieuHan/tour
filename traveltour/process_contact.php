<?php
session_start();
include('includes/db.php');

// Kiểm tra nếu form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra xem người dùng đã đăng nhập chưa
    if (isset($_SESSION['userid'])) {
        $userid = $_SESSION['userid'];

        // Lấy thông tin người dùng từ bảng users
        $stmt = $conn->prepare("SELECT USNAME, USEMAIL FROM users WHERE userid = ?");
        $stmt->bind_param("i", $userid);
        $stmt->execute();
        $result = $stmt->get_result();

        // Kiểm tra xem có kết quả hay không
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $fullName = htmlspecialchars($row['USNAME']);
            $email = htmlspecialchars($row['USEMAIL']);
        } else {
            echo "Không tìm thấy thông tin người dùng.";
            exit;
        }

        $stmt->close();
    } else {
        // Nếu người dùng chưa đăng nhập
        $fullName = "Khách hàng";
        $email = "guest@example.com";
    }

    // Kiểm tra xem trường MESSAGE có tồn tại trong POST không
    if (isset($_POST['message'])) {
        $message = mysqli_real_escape_string($conn, $_POST['message']);
    } else {
        echo "<script>alert('Tin nhắn không được để trống.'); window.location.href='contact.php';</script>";
        exit; // Ngừng nếu không có tin nhắn
    }

    // Chèn thông điệp vào bảng contact
    $query = "INSERT INTO contact (USERID, MESSAGE, CONTACTDATE) VALUES (?, ?, NOW())"; // Chỉ cần 3 cột

    // Sử dụng prepared statement để ngăn chặn SQL injection
    $stmt = $conn->prepare($query);

    // Kiểm tra nếu prepared statement thành công
    if ($stmt === false) {
        die('Lỗi: ' . htmlspecialchars($conn->error));
    }

    // Gán giá trị cho các tham số
    $stmt->bind_param("is", $userid, $message);

    // Thực hiện truy vấn
    if ($stmt->execute()) {
        echo "<script>alert('Tin nhắn đã được gửi thành công!'); window.location.href='contact.php';</script>";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
    mysqli_close($conn);
}