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
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        try {
            // Tìm user theo username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'is_admin' => $user['is_admin']
                ];

                // Cập nhật thời gian đăng nhập cuối
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                // Nếu có remember me
                if (isset($_POST['remember'])) {
                    // Tạo token mới
                    $token = bin2hex(random_bytes(32));
                    $hash = password_hash($token, PASSWORD_DEFAULT);
                    
                    // Lưu token vào database
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$hash, $user['id']]);
                    
                    // Set cookie với thời hạn 30 ngày
                    setcookie('remember_token', $token, time() + 30*24*60*60, '/');
                    setcookie('user_id', $user['id'], time() + 30*24*60*60, '/');
                }

                // Chuyển hướng dựa vào quyền
                if ($user['is_admin']) {
                    header('Location: admin/index.php');
                } else {
                    header('Location: ' . url('trang-chu'));
                }
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
            }
        } catch(PDOException $e) {
            $error = 'Đã có lỗi xảy ra';
        }
    }
}

$page_title = 'Đăng nhập - Thư Viện Phần Mềm';

$extra_css = '
<style>
.login-page {
    min-height: calc(100vh - 260px);
    display: flex;
    align-items: center;
}
.login-form {
    max-width: 400px;
    width: 100%;
    margin: 0 auto;
}
</style>
';

include 'includes/header.php';
?>

<div class="login-page">
    <div class="container">
        <div class="login-form">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">
                        <i class="bi bi-person-circle me-2"></i>Đăng nhập
                    </h4>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?= htmlspecialchars($username ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                        </button>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p class="mb-2">Chưa có tài khoản? 
                            <a href="register.php" class="text-decoration-none">Đăng ký ngay</a>
                        </p>
                        <a href="forgot-password.php" class="text-decoration-none">Quên mật khẩu?</a>
                    </div>
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