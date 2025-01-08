<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

// Xử lý gửi form liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contacts (name, email, subject, message) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất có thể!';
            
            // Reset form
            $name = $email = $subject = $message = '';
        } catch(PDOException $e) {
            $error = 'Không thể gửi tin nhắn';
        }
    }
}

// Lấy thông tin liên hệ từ settings
try {
    $stmt = $pdo->prepare("
        SELECT setting_key, setting_value 
        FROM settings 
        WHERE setting_key IN ('contact_email', 'contact_phone', 'contact_address')
    ");
    $stmt->execute();
    $contact_info = [];
    while ($row = $stmt->fetch()) {
        $contact_info[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    $error = 'Không thể tải thông tin liên hệ';
}

$page_title = 'Liên hệ - Thư Viện Phần Mềm';

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">Liên hệ với chúng tôi</h1>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Contact Info -->
                        <div class="col-md-4 mb-4 mb-md-0">
                            <div class="vstack gap-4">
                                <?php if (!empty($contact_info['contact_address'])): ?>
                                <div>
                                    <h5 class="fw-bold">
                                        <i class="bi bi-geo-alt text-primary me-2"></i>Địa chỉ
                                    </h5>
                                    <p class="text-muted mb-0">
                                        <?= nl2br(htmlspecialchars($contact_info['contact_address'])) ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($contact_info['contact_email'])): ?>
                                <div>
                                    <h5 class="fw-bold">
                                        <i class="bi bi-envelope text-primary me-2"></i>Email
                                    </h5>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:<?= htmlspecialchars($contact_info['contact_email']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($contact_info['contact_email']) ?>
                                        </a>
                                    </p>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($contact_info['contact_phone'])): ?>
                                <div>
                                    <h5 class="fw-bold">
                                        <i class="bi bi-telephone text-primary me-2"></i>Điện thoại
                                    </h5>
                                    <p class="text-muted mb-0">
                                        <a href="tel:<?= htmlspecialchars($contact_info['contact_phone']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($contact_info['contact_phone']) ?>
                                        </a>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contact Form -->
                        <div class="col-md-8">
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label">Họ tên</label>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?= htmlspecialchars($name ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($email ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tiêu đề</label>
                                    <input type="text" class="form-control" name="subject" 
                                           value="<?= htmlspecialchars($subject ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nội dung</label>
                                    <textarea class="form-control" name="message" rows="5" 
                                              required><?= htmlspecialchars($message ?? '') ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Gửi tin nhắn
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 