<?php
session_start();

include('includes/db.php');

if (isset($_GET['tourid'], $_GET['userid'], $_GET['startdate'])) {
    $tourId = intval($_GET['tourid']);
    $userId = intval($_GET['userid']);
    $startDate = $_GET['startdate'];

    // Truy vấn thông tin đặt tour và tour từ cơ sở dữ liệu
    $query = "SELECT b.*, t.TOURNAME, t.PRICE 
              FROM bookings b 
              JOIN tour t ON b.TOURID = t.TOURID 
              WHERE b.TOURID = ? AND b.USERID = ? AND b.STARTDATE = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $tourId, $userId, $startDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookingData = $result->fetch_assoc();

    if (!$bookingData) {
        echo "<script>
        alert('Không tìm thấy thông tin đặt tour!');
        window.location.href = 'index.php';
        </script>";
        exit();
    }

    $tourName = $bookingData['TOURNAME'];
    $totalPrice = $bookingData['TOTALPRICE'];
    $priceInUSD = $totalPrice / 23000; // Giả định tỷ giá

    // Cập nhật trạng thái thanh toán cho booking
    $updateQuery = "UPDATE bookings SET STATUS = '1' WHERE TOURID = ? AND USERID = ? AND STARTDATE = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("iis", $tourId, $userId, $startDate);
    $stmtUpdate->execute();
} else {
    echo "<script>
    alert('Yêu cầu không hợp lệ!');
    window.location.href = 'index.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thanh toán PayPal</title>
    <script
        src="https://www.paypal.com/sdk/js?client-id=AWHNWa7HF-1xlxrHPlD7RDudvOmxkJ5nBzUxEFiqEyHaLN-L5Zmdl8nF9YzRojlPXG4ipg0r5hub4AGB&currency=USD">
    </script>
</head>

<body>
    <h1>Thanh toán qua PayPal cho tour: <?php echo htmlspecialchars($tourName); ?></h1>
    <p>Tổng tiền: $<?php echo number_format($priceInUSD, 2); ?></p>
    <div id="paypal-button-container"></div>

    <script>
        paypal.Buttons({
            createOrder: (data, actions) => {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: "<?php echo number_format($priceInUSD, 2, '.', ''); ?>"
                        }
                    }]
                });
            },
            onApprove: (data, actions) => {
                return actions.order.capture().then(details => {
                    alert('Thanh toán thành công!');

                    // Cập nhật trạng thái thanh toán vào cơ sở dữ liệu
                    fetch('update_payment_status.php', {
                            method: 'POST',
                            body: JSON.stringify({
                                tourid: <?php echo $tourId; ?>,
                                userid: <?php echo $userId; ?>,
                                startdate: "<?php echo $startDate; ?>",
                                status: '1',
                                amount: "<?php echo number_format($priceInUSD, 2, '.', ''); ?>"
                            }),
                            headers: {
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            window.location.href =
                                `booking_success.php?tourid=<?php echo $tourId; ?>&userid=<?php echo $userId; ?>&startdate=<?php echo $startDate; ?>`;
                        })
                        .catch(error => {
                            alert('Lỗi khi cập nhật trạng thái thanh toán!');
                            window.location.href = 'index.php?tourid=$tourId';
                        });
                });
            },
            onError: (err) => {
                alert('Lỗi thanh toán: ' + err);
            }
        }).render('#paypal-button-container');
    </script>
</body>

</html>