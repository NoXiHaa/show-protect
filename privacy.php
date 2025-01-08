<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'privacy_policy'");
    $stmt->execute();
    $privacy = $stmt->fetchColumn();
} catch(PDOException $e) {
    $error = 'Không thể tải nội dung';
}

$page_title = 'Chính sách bảo mật - Thư Viện Phần Mềm';

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">Chính sách bảo mật</h1>
                    <div class="privacy-content">
                        <?= nl2br(htmlspecialchars($privacy)) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 