<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: /errors/403.php');
    exit;
}

$error = '';
$success = '';

// Xử lý cập nhật cấu hình
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Cập nhật từng cấu hình
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        $success = 'Đã cập nhật cấu hình thành công';
    } catch(PDOException $e) {
        $error = 'Không thể cập nhật cấu hình';
    }
}

// Lấy cấu hình hiện tại
try {
    $stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_key");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch(PDOException $e) {
    $error = 'Không thể tải cấu hình';
}

$page_title = 'Quản lý cấu hình - Quản trị';
$active_page = 'settings';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-gear me-2"></i>Quản lý cấu hình
                </h4>
            </div>

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

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <!-- Thông tin chung -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Thông tin chung</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tên website</label>
                                    <input type="text" class="form-control" 
                                           name="settings[site_name]"
                                           value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mô tả website</label>
                                    <textarea class="form-control" name="settings[site_description]" 
                                              rows="3"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Thông tin liên hệ -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Thông tin liên hệ</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email liên hệ</label>
                                    <input type="email" class="form-control" 
                                           name="settings[contact_email]"
                                           value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" 
                                           name="settings[contact_phone]"
                                           value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" name="settings[contact_address]" 
                                              rows="3"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Mạng xã hội -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Mạng xã hội</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Facebook</label>
                                    <input type="url" class="form-control" 
                                           name="settings[social_facebook]"
                                           value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Twitter</label>
                                    <input type="url" class="form-control" 
                                           name="settings[social_twitter]"
                                           value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Github</label>
                                    <input type="url" class="form-control" 
                                           name="settings[social_github]"
                                           value="<?= htmlspecialchars($settings['social_github'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Nội dung trang -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Nội dung trang</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Điều khoản sử dụng</label>
                                    <textarea class="form-control" name="settings[terms_of_service]" 
                                              rows="5"><?= htmlspecialchars($settings['terms_of_service'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Chính sách bảo mật</label>
                                    <textarea class="form-control" name="settings[privacy_policy]" 
                                              rows="5"><?= htmlspecialchars($settings['privacy_policy'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-2"></i>Lưu cấu hình
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 