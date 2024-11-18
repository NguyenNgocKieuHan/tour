<?php
header("Content-Type: application/json");
$conn = new mysqli('localhost', 'root', '', 'tour');

// Lấy dữ liệu từ Dialogflow
$content = file_get_contents("php://input");
$requestJson = json_decode($content, true);

// Lấy intent và các tham số từ request
$intent = $requestJson['queryResult']['intent']['displayName'] ?? null;
$parameters = $requestJson['queryResult']['parameters'] ?? [];
$responseText = "Xin lỗi, tôi không hiểu yêu cầu của bạn.";

// Xử lý từng intent
if ($intent === "get-cheapest-tour") {
    // Intent: Lấy tour rẻ nhất
    $stmt = $conn->prepare("SELECT TOURNAME, PRICE FROM tour ORDER BY PRICE ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $responseText = "Tour rẻ nhất là " . $row['TOURNAME'] . " với giá " . number_format($row['PRICE']) . " VND.";
    } else {
        $responseText = "Không tìm thấy tour nào.";
    }
    $stmt->close();
} elseif ($intent === "get-tour-details") {
    // Intent: Lấy thông tin chi tiết của tour
    $tourName = $parameters['eTourname'] ?? '';
    if (!empty($tourName)) {
        $stmt = $conn->prepare("SELECT DESCRIPTION, PRICE FROM tour WHERE TOURNAME = ?");
        $stmt->bind_param("s", $tourName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $responseText = "Tour " . $tourName . " có mô tả: " . $row['DESCRIPTION'] . " với giá " . number_format($row['PRICE']) . " VND.";
        } else {
            $responseText = "Không tìm thấy tour có tên " . $tourName . ".";
        }
        $stmt->close();
    } else {
        $responseText = "Vui lòng cung cấp tên tour.";
    }
} elseif ($intent === "find-tour-by-type") {
    // Intent: Tìm tour theo loại
    $tourType = $parameters['eTourtype'] ?? '';
    if (!empty($tourType)) {
        $stmt = $conn->prepare("
            SELECT TOURNAME, PRICE 
            FROM tour 
            INNER JOIN tourtype ON tour.TOURTYPEID = tourtype.TOURTYPEID 
            WHERE tourtype.TYPENAME = ?
        ");
        $stmt->bind_param("s", $tourType);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $responseText = "Các tour thuộc loại " . $tourType . ":\n";
            while ($row = $result->fetch_assoc()) {
                $responseText .= "- " . $row['TOURNAME'] . " với giá " . number_format($row['PRICE']) . " VND\n";
            }
        } else {
            $responseText = "Không tìm thấy tour nào thuộc loại " . $tourType . ".";
        }
        $stmt->close();
    } else {
        $responseText = "Vui lòng cung cấp loại tour.";
    }
} elseif ($intent === "get-tour-price") {
    // Intent: Lấy giá của một tour
    $tourName = $parameters['eTourname'] ?? '';
    if (!empty($tourName)) {
        $stmt = $conn->prepare("SELECT PRICE FROM tour WHERE TOURNAME = ?");
        $stmt->bind_param("s", $tourName);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $responseText = "Tour " . $tourName . " có giá " . number_format($row['PRICE']) . " VND.";
        } else {
            $responseText = "Không tìm thấy tour có tên " . $tourName . ".";
        }
        $stmt->close();
    } else {
        $responseText = "Vui lòng cung cấp tên tour.";
    }
}

$conn->close();

// Trả kết quả về Dialogflow
$response = [
    "fulfillmentMessages" => [
        [
            "text" => [
                "text" => [$responseText]
            ]
        ]
    ]
];
echo json_encode($response, JSON_UNESCAPED_UNICODE);
