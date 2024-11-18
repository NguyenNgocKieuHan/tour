<?php
session_start();
include('includes/db.php'); // Kết nối cơ sở dữ liệu

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $tour_id = mysqli_real_escape_string($conn, $_POST['tour_id']);
    $destination_name = mysqli_real_escape_string($conn, $_POST['destination_name']);
    $district_id = mysqli_real_escape_string($conn, $_POST['district_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Kiểm tra và tải ảnh lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        // Lấy thông tin file
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        // Kiểm tra kích thước và loại file
        if ($_FILES['image']['size'] > 5000000) {
            echo "<script>alert('Kích thước ảnh quá lớn!'); window.location.href='destinationManagement.php';</script>";
            // echo "<script>alert('Kích thước ảnh quá lớn.');</script>";
            exit();
        }

        if (!in_array($imageFileType, $allowedTypes)) {
            echo "<script>alert('Chỉ cho phép các định dạng JPG, JPEG, PNG & GIF.');</script>";
            exit();
        }

        // Đọc dữ liệu ảnh
        $imageData = file_get_contents($imageTmpName);
    } else {
        echo "<script>alert('Có lỗi xảy ra khi tải ảnh lên.');</script>";
        exit();
    }

    // Tìm ID DESTINATION lớn nhất hiện tại và tạo ID mới
    $result = mysqli_query($conn, "SELECT MAX(DESTINATIONID) AS max_id FROM destination");
    $row = mysqli_fetch_assoc($result);
    $next_id = $row['max_id'] + 1; // ID kế tiếp

    // Chèn dữ liệu vào cơ sở dữ liệu
    $sql = "INSERT INTO destination (DESTINATIONID, DISTRICTID, TOURID, DENAME, DEDESCRIPTION, DEIMAGE) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iiisss', $next_id, $district_id, $tour_id, $destination_name, $description, $imageData);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Thêm địa điểm thành công!'); window.location.href='destinationManagement.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi thêm địa điểm: " . mysqli_error($conn) . "');</script>";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('Yêu cầu không hợp lệ.'); window.location.href='destinationManagement.php';</script>";
}

mysqli_close($conn); // Đóng kết nối