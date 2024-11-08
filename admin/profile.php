<?php
session_start(); // Bắt đầu phiên làm việc

include('includes/header.php');
include('includes/db.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['ADID'])) {
	// Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
	header("Location: login.php");
	exit();
}

// Fetch user information from the database
$ADID = $_SESSION['ADID'];
$sql = "SELECT ADNAME, ADEMAIL, ADSDT FROM ADMIN WHERE ADID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ADID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
	$user = $result->fetch_assoc();
} else {
	echo "No user found.";
	exit();
}

// Handle form submission for updating user information
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$newName = trim($_POST['adname']);
	$newEmail = trim($_POST['ademail']);
	$newPhone = trim($_POST['adsdt']);

	// Update user information in the database
	$updateSql = "UPDATE ADMIN SET ADNAME = ?, ADEMAIL = ?, ADSDT = ? WHERE ADID = ?";
	$updateStmt = $conn->prepare($updateSql);
	$updateStmt->bind_param("sssi", $newName, $newEmail, $newPhone, $ADID);

	if ($updateStmt->execute()) {
		// Update successful, refresh user data
		$user['ADNAME'] = $newName;
		$user['ADEMAIL'] = $newEmail;
		$user['ADSDT'] = $newPhone;
		echo "<script>alert('Thông tin cá nhân đã được cập nhật thành công!');</script>";
	} else {
		echo "<script>alert('Có lỗi xảy ra khi cập nhật thông tin.');</script>";
	}
}
?>
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="title">
                            <h4>Profile</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Thông tin cá nhân</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 mb-30">
                    <div class="pd-20 card-box height-100-p">
                        <div class="profile-photo">
                            <a href="modal" data-toggle="modal" data-target="#modal" class="edit-avatar"><i
                                    class="fa fa-pencil"></i></a>
                            <img src="vendors/images/dolphin.png" alt="" class="avatar-photo">
                            <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body pd-5">
                                            <div class="img-container">
                                                <img id="image" src="vendors/images/dolphin.png" alt="Picture">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="submit" value="Update" class="btn btn-primary">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h5 class="text-center h5 mb-0"> <?php echo htmlspecialchars($user['ADNAME']); ?>
                        </h5>
                        <p class="text-center text-muted font-14"><?php echo htmlspecialchars($user['ADEMAIL']); ?></p>
                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Thông tin liên lạc</h5>
                            <ul>
                                <li>
                                    <span>Họ và tên:</span>
                                    <?php echo htmlspecialchars($user['ADNAME']); ?>
                                </li>
                                <li>
                                    <span>Địa chỉ Email:</span>
                                    <?php echo htmlspecialchars($user['ADEMAIL']); ?>
                                </li>
                                <li>
                                    <span>Số điện thoại:</span>
                                    <?php echo htmlspecialchars($user['ADSDT']); ?>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- Form chỉnh sửa thông tin -->
                <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 mb-30">
                    <div class="pd-20 card-box height-100-p">
                        <h5 class="mb-20 h5 text-blue">Chỉnh sửa thông tin cá nhân</h5>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="adname">Họ và tên</label>
                                <input type="text" class="form-control" id="adname" name="adname" required
                                    value="<?php echo htmlspecialchars($user['ADNAME']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="ademail">Địa chỉ Email</label>
                                <input type="email" class="form-control" id="ademail" name="ademail" required
                                    value="<?php echo htmlspecialchars($user['ADEMAIL']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="adsdt">Số điện thoại</label>
                                <input type="text" class="form-control" id="adsdt" name="adsdt" required
                                    value="<?php echo htmlspecialchars($user['ADSDT']); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- js -->
<script src="vendors/scripts/core.js"></script>
<script src="vendors/scripts/script.min.js"></script>
<script src="vendors/scripts/process.js"></script>
<script src="vendors/scripts/layout-settings.js"></script>
<script src="src/plugins/cropperjs/dist/cropper.js"></script>
<script>
window.addEventListener('DOMContentLoaded', function() {
    var image = document.getElementById('image');
    var cropBoxData;
    var canvasData;
    var cropper;

    $('#modal').on('shown.bs.modal', function() {
        cropper = new Cropper(image, {
            autoCropArea: 0.5,
            dragMode: 'move',
            aspectRatio: 3 / 3,
            restore: false,
            guides: false,
            center: false,
            highlight: false,
            cropBoxMovable: false,
            cropBoxResizable: false,
            toggleDragModeOnDblclick: false,
            ready: function() {
                cropper.setCropBoxData(cropBoxData).setCanvasData(canvasData);
            }
        });
    }).on('hidden.bs.modal', function() {
        cropBoxData = cropper.getCropBoxData();
        canvasData = cropper.getCanvasData();
        cropper.destroy();
    });
});
</script>
</body>

</html>