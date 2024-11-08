<?php
ob_start(); // Bắt đầu output buffering
session_start();
include('includes/header.php');
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

if (!isset($_SESSION['ADID'])) {
	// Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
	header("Location: login.php");
	exit();
}
// Truy vấn lấy doanh thu theo từng tháng
$sql = "SELECT MONTH(PAYMENTDATE) as month, SUM(AMOUNT) as revenue 
        FROM payments 
        WHERE YEAR(PAYMENTDATE) = YEAR(CURDATE()) 
        GROUP BY MONTH(PAYMENTDATE) 
        ORDER BY MONTH(PAYMENTDATE)";

$result = $conn->query($sql);

// Khởi tạo mảng dữ liệu
$revenue_data = [];
for ($i = 1; $i <= 12; $i++) {
	$revenue_data[$i] = 0; // Gán giá trị mặc định là 0 cho mỗi tháng
}

// Điền dữ liệu từ kết quả truy vấn
if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$revenue_data[$row['month']] = $row['revenue'];
	}
}

// Chuyển đổi mảng doanh thu sang dạng JSON để sử dụng trong ApexCharts
$revenue_data_json = json_encode(array_values($revenue_data));

// Đóng kết nối cơ sở dữ liệu
$conn->close();
?>

<div class="main-container">
	<div class="pd-ltr-20 xs-pd-20-10">
		<div class="min-height-200px">
			<div class="page-header">
				<div class="row">
					<div class="col-md-6 col-sm-12">
						<div class="title">
							<h4>Apexcharts</h4>
						</div>
						<nav aria-label="breadcrumb" role="navigation">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
								<li class="breadcrumb-item active" aria-current="page">Biểu đồ</li>
							</ol>
						</nav>
					</div>
					<div class="col-md-6 col-sm-12 text-right">
						<div class="dropdown">
							<a class="btn btn-primary dropdown-toggle" href="#" role="button" data-toggle="dropdown">
								Tháng 1 2024
							</a>
							<div class="dropdown-menu dropdown-menu-right">
								<a class="dropdown-item" href="#">Export List</a>
								<a class="dropdown-item" href="#">Policies</a>
								<a class="dropdown-item" href="#">View Assets</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="bg-white pd-20 card-box mb-30">
				<!-- <h4 class="h4 text-blue">line Chart</h4>
				<div id="chart1"></div>

				<div class="mobile-menu-overlay"></div> -->
				<!-- <div class="bg-white pd-20 card-box mb-30"> -->
				<h4 class="h4 text-blue">Doanh thu bán tour theo tháng</h4>
				<div id="revenueChart"></div>
			</div>

			<!-- Thêm ApexCharts -->
			<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
			<script>
				// Dữ liệu doanh thu từ PHP
				var revenueData = <?php echo $revenue_data_json; ?>;

				// Tạo biểu đồ doanh thu theo tháng (biểu đồ đường)
				var options = {
					chart: {
						type: 'line'
					},
					series: [{
						name: 'Doanh thu',
						data: revenueData
					}],
					xaxis: {
						categories: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7',
							'Tháng 8',
							'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
						]
					},
					title: {
						text: 'Doanh thu bán tour theo từng tháng trong năm'
					},
					yaxis: {
						labels: {
							formatter: function(val) {
								return val.toLocaleString() + " VNĐ"; // Định dạng số cho giá trị VNĐ
							}
						}
					},
					stroke: {
						curve: 'smooth' // Làm mượt đường biểu đồ
					}
				};

				var chart = new ApexCharts(document.querySelector("#revenueChart"), options);
				chart.render();
			</script>

		</div>
		<!-- <div class="bg-white pd-20 card-box mb-30">
                <h4 class="h4 text-blue">Area Chart</h4>
                <div id="chart2"></div>
            </div>
            <div class="bg-white pd-20 card-box mb-30">
                <h4 class="h4 text-blue">Column Chart</h4>
                <div id="chart3"></div>
            </div>
            <div class="bg-white pd-20 card-box mb-30">
                <h4 class="h4 text-blue">Bar Chart</h4>
                <div id="chart4"></div>
            </div>
            <div class="bg-white pd-20 card-box mb-30">
                <h4 class="h4 text-blue">Mixed Chart</h4>
                <div id="chart5"></div>
            </div>
            <div class="bg-white pd-20 card-box mb-30">
                <h4 class="h4 text-blue">Timeline Chart</h4>
                <div id="chart6"></div>
            </div>
            <div class="bg-white pd-20 card-box mb-30">
                <h4 class="h4 text-blue">Candlestick Chart</h4>
                <div id="chart7"></div>
            </div> -->
		<!-- <div class="row">
                <div class="col-md-6 mb-30">
                    <div class="pd-20 card-box height-100-p">
                        <h4 class="h4 text-blue">Pie Chart</h4>
                        <div id="chart8"></div>
                    </div>
                </div>
                <div class="col-md-6 mb-30">
                    <div class="pd-20 card-box height-100-p">
                        <h4 class="h4 text-blue">Radial Bar Chart</h4>
                        <div id="chart9"></div>
                    </div>
                </div>
            </div> -->
	</div>
	<!-- <div class="footer-wrap pd-20 mb-20 card-box">
        DeskApp - Bootstrap 4 Admin Template By <a href="https://github.com/dropways" target="_blank">Ankit
            Hingarajiya</a>
    </div> -->
</div>
</div>
<!-- js -->
<script src="vendors/scripts/core.js"></script>
<script src="vendors/scripts/script.min.js"></script>
<script src="vendors/scripts/process.js"></script>
<script src="vendors/scripts/layout-settings.js"></script>
<script src="src/plugins/apexcharts/apexcharts.min.js"></script>
<script src="vendors/scripts/apexcharts-setting.js"></script>
</body>

</html>