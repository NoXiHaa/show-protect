<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        try {
            // Kiểm tra username đã tồn tại
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Tên đăng nhập đã được sử dụng';
            } else {
                // Kiểm tra email đã tồn tại
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = 'Email đã được sử dụng';
                } else {
                    // Thêm người dùng mới
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$username, $email, $hash]);
                    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                }
            }
        } catch(PDOException $e) {
            $error = 'Đã có lỗi xảy ra';
        }
    }
}

$page_title = 'Đăng ký - Thư Viện Phần Mềm';

$extra_css = '
<style>
.register-page {
    min-height: calc(100vh - 260px);
    display: flex;
    align-items: center;
}
.register-form {
    max-width: 450px;
    width: 100%;
    margin: 0 auto;
}
</style>
';

include 'includes/header.php';
?>

<div class="register-page">
    <div class="container">
        <div class="register-form">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">
                        <i class="bi bi-person-plus me-2"></i>Đăng ký tài khoản
                    </h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-success btn-sm">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập ngay
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Tên đăng nhập</label>
                                <input type="text" class="form-control" name="username" required
                                       pattern="[A-Za-z0-9_]{3,20}"
                                       title="3-20 ký tự, chỉ cho phép chữ cái, số và dấu gạch dưới">
                                <div class="form-text">3-20 ký tự, chỉ cho phép chữ cái, số và dấu gạch dưới</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" name="password" required
                                       minlength="6">
                                <div class="form-text">Tối thiểu 6 ký tự</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus me-2"></i>Đăng ký
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="mb-0">Đã có tài khoản? 
                                <a href="login.php" class="text-decoration-none">Đăng nhập</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_scripts = '
<script>
// Form validation
(function() {
    "use strict";
    var forms = document.querySelectorAll(".needs-validation");
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener("submit", function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add("was-validated");
        }, false);
    });
})();
</script>
';

include 'includes/footer.php';
?> 