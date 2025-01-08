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

// Xử lý khóa/mở khóa tài khoản
if (isset($_POST['toggle_status'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Đã cập nhật trạng thái tài khoản';
    } catch(PDOException $e) {
        $error = 'Không thể cập nhật trạng thái';
    }
}

// Xử lý xóa tài khoản
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    if ($id === $_SESSION['user']['id']) {
        $error = 'Không thể xóa tài khoản của chính mình';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Đã xóa tài khoản thành công';
        } catch(PDOException $e) {
            $error = 'Không thể xóa tài khoản';
        }
    }
}

// Lấy danh sách người dùng
try {
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(DISTINCT o.id) as total_orders,
               COUNT(DISTINCT oi.software_id) as total_softwares
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Không thể tải danh sách người dùng';
}

$page_title = 'Quản lý người dùng - Quản trị';
$active_page = 'users';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-people me-2"></i>Quản lý người dùng
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
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Thống kê</th>
                                    <th>Đăng nhập cuối</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <div class="fw-medium">
                                            <?= htmlspecialchars($user['username']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Thành viên</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Bị khóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <div>Đơn hàng: <?= number_format($user['total_orders']) ?></div>
                                            <div>Phần mềm: <?= number_format($user['total_softwares']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Chưa đăng nhập' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="toggle_status" 
                                                            class="btn btn-outline-<?= $user['is_active'] ? 'warning' : 'success' ?>"
                                                            title="<?= $user['is_active'] ? 'Khóa tài khoản' : 'Mở khóa' ?>">
                                                        <i class="bi bi-<?= $user['is_active'] ? 'lock' : 'unlock' ?>"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                    <button type="submit" name="delete" 
                                                            class="btn btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 