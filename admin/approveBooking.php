<?php
session_start();
include('includes/db.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['ADID'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra nếu có TOURID, USERID và STARTDATE trong URL
if (isset($_GET['TOURID'], $_GET['userid'], $_GET['startdate'])) {
    $tour_id = $_GET['TOURID'];
    $user_id = $_GET['userid'];
    $start_date = $_GET['startdate'];

    try {
        // Bắt đầu giao dịch
        $conn->begin_transaction();

        // Truy vấn để lấy thông tin booking và số lượng chỗ tối đa
        $sql = "SELECT b.STARTDATE, b.NUMOFPEOPLE, t.MAXSLOTS 
                FROM bookings b
                JOIN tour t ON b.TOURID = t.TOURID 
                WHERE b.TOURID = ? AND b.USERID = ? AND b.STARTDATE = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('iis', $tour_id, $user_id, $start_date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                $startDate = $row['STARTDATE'];
                $numOfPeople = $row['NUMOFPEOPLE'];
                $maxSlots = $row['MAXSLOTS'];

                // Kiểm tra ngày hiện tại và số lượng chỗ tối đa
                if (strtotime($startDate) > time() && ($maxSlots - $numOfPeople) >= 0) {
                    // Cập nhật trạng thái đơn đặt tour thành đã xác nhận (trạng thái 1) và lưu ngày duyệt
                    $updateSql = "UPDATE bookings 
                                  SET STATUS = 1, APPROVALDATE = NOW() 
                                  WHERE TOURID = ? AND USERID = ? AND STARTDATE = ?";
                    if ($updateStmt = $conn->prepare($updateSql)) {
                        $updateStmt->bind_param('iis', $tour_id, $user_id, $start_date);

                        if ($updateStmt->execute()) {
                            // Giảm số lượng chỗ trống trong bảng tour
                            $updateSlotsSql = "UPDATE tour 
                                               SET MAXSLOTS = MAXSLOTS - ? 
                                               WHERE TOURID = ?";
                            if ($updateSlotsStmt = $conn->prepare($updateSlotsSql)) {
                                $updateSlotsStmt->bind_param('ii', $numOfPeople, $tour_id);

                                if ($updateSlotsStmt->execute()) {
                                    // Xác nhận giao dịch
                                    $conn->commit();
                                    echo "<script>alert('Duyệt đơn đặt tour thành công và cập nhật số lượng chỗ trống!'); window.location.href='bookingManagement.php';</script>";
                                } else {
                                    throw new Exception("Lỗi khi cập nhật số lượng chỗ trống: " . $conn->error);
                                }

                                $updateSlotsStmt->close();
                            } else {
                                throw new Exception("Không thể chuẩn bị truy vấn cập nhật số lượng chỗ trống.");
                            }
                        } else {
                            throw new Exception("Lỗi khi duyệt đơn đặt tour: " . $conn->error);
                        }

                        $updateStmt->close();
                    } else {
                        throw new Exception("Không thể chuẩn bị truy vấn cập nhật trạng thái.");
                    }
                } else {
                    throw new Exception("Không thể duyệt đơn đặt tour. Vui lòng kiểm tra ngày bắt đầu hoặc số lượng chỗ trống.");
                }
            } else {
                throw new Exception("Thông tin không hợp lệ.");
            }

            $stmt->close();
        } else {
            throw new Exception("Không thể chuẩn bị truy vấn kiểm tra.");
        }
    } catch (Exception $e) {
        // Rollback giao dịch nếu có lỗi
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "'); window.location.href='bookingManagement.php';</script>";
    }

    $conn->close();
} else {
    echo "<script>alert('Thông tin không hợp lệ.'); window.location.href='bookingManagement.php';</script>";
}
