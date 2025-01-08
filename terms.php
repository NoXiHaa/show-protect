<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'terms_of_service'");
    $stmt->execute();
    $terms = $stmt->fetchColumn();
} catch(PDOException $e) {
    $error = 'Không thể tải nội dung';
}

$page_title = 'Điều khoản sử dụng - Thư Viện Phần Mềm';

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">Điều khoản sử dụng</h1>
                    <div class="terms-content">
                        <?= nl2br(htmlspecialchars($terms)) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 