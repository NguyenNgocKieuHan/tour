<?php
session_start(); // Start the session

include('includes/header.php');
include('includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['ADID'])) {
    header("Location: login.php");
    exit();
}

// Sample FAQ data
$faqs = [
    [
        'question' => 'Câu hỏi 1: Làm thế nào để đăng nhập?',
        'answer' => 'Để đăng nhập, bạn cần vào trang đăng nhập và nhập tên người dùng và mật khẩu của bạn.'
    ],
    [
        'question' => 'Câu hỏi 2: Làm thế nào để khôi phục mật khẩu?',
        'answer' => 'Bạn có thể khôi phục mật khẩu của mình bằng cách nhấp vào liên kết "Quên mật khẩu?" trên trang đăng nhập.'
    ],
    [
        'question' => 'Câu hỏi 3: Tôi có thể thay đổi thông tin cá nhân ở đâu?',
        'answer' => 'Bạn có thể thay đổi thông tin cá nhân của mình trong phần hồ sơ của mình sau khi đăng nhập.'
    ],
    [
        'question' => 'Câu hỏi 4: Ai có thể giúp tôi nếu tôi gặp vấn đề?',
        'answer' => 'Nếu bạn gặp vấn đề, bạn có thể liên hệ với bộ phận hỗ trợ qua email hoặc số điện thoại được cung cấp trên trang liên hệ.'
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Câu hỏi thường gặp</title>
    <link rel="stylesheet" href="vendors/styles/style.css"> <!-- Link đến file CSS của bạn -->
</head>

<body>
    <div class="main-container">
        <div class="pd-ltr-20 xs-pd-20-10">
            <div class="min-height-200px">
                <div class="page-header">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <div class="title">
                                <h4>Câu hỏi thường gặp</h4>
                            </div>
                            <nav aria-label="breadcrumb" role="navigation">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Trang chủ</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Câu hỏi thường gặp</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-30">
                        <div class="card-box pd-20 height-100-p">
                            <h5 class="h5 text-blue">Danh sách câu hỏi</h5>
                            <div class="faq-section">
                                <?php foreach ($faqs as $faq): ?>
                                    <div class="faq-item">
                                        <h6 class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></h6>
                                        <p class="faq-answer"><?php echo htmlspecialchars($faq['answer']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JS -->
    <script src="vendors/scripts/core.js"></script>
    <script src="vendors/scripts/script.min.js"></script>
    <script src="vendors/scripts/process.js"></script>
    <script src="vendors/scripts/layout-settings.js"></script>
</body>

</html>