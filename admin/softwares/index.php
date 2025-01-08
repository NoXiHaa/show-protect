<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . url('errors/403'));
    exit;
}

$error = '';
$success = '';

// Xử lý xóa phần mềm
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM softwares WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Đã xóa phần mềm thành công';
    } catch(PDOException $e) {
        $error = 'Không thể xóa phần mềm';
    }
}

// Lấy danh sách phần mềm
try {
    $stmt = $pdo->query("
        SELECT s.*, c.name as category_name, c.color as category_color,
               COUNT(DISTINCT o.id) as total_orders
        FROM softwares s
        LEFT JOIN categories c ON s.category_id = c.id
        LEFT JOIN order_items oi ON s.id = oi.software_id
        LEFT JOIN orders o ON oi.order_id = o.id
        GROUP BY s.id
        ORDER BY s.created_at DESC
    ");
    $softwares = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Không thể tải danh sách phần mềm';
}

$page_title = 'Quản lý phần mềm - Quản trị';
$active_page = 'softwares';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Quản lý phần mềm
                </h4>
                <a href="<?= admin_url('phan-mem/them') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>Thêm phần mềm
                </a>
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
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 80px;">Ảnh</th>
                                    <th>Tên phần mềm</th>
                                    <th>Danh mục</th>
                                    <th>Phiên bản</th>
                                    <th>Lượt tải</th>
                                    <th>Ngày tạo</th>
                                    <th style="width: 100px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($softwares as $software): ?>
                                <tr>
                                    <td><?= $software['id'] ?></td>
                                    <td>
                                        <?php if ($software['image_url']): ?>
                                            <img src="<?= htmlspecialchars($software['image_url']) ?>" 
                                                 class="img-thumbnail" 
                                                 alt="<?= htmlspecialchars($software['name']) ?>"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            <?= htmlspecialchars($software['name']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= mb_strimwidth(strip_tags($software['description']), 0, 50, '...') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" 
                                              style="background-color: <?= htmlspecialchars($software['category_color']) ?>">
                                            <?= htmlspecialchars($software['category_name']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($software['version']) ?></td>
                                    <td>
                                        <div class="text-muted">
                                            <?= number_format($software['total_orders']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <?= date('d/m/Y H:i', strtotime($software['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= admin_url('phan-mem/sua/' . $software['id']) ?>" 
                                               class="btn btn-outline-primary" title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                                <input type="hidden" name="id" value="<?= $software['id'] ?>">
                                                <button type="submit" name="delete" 
                                                        class="btn btn-outline-danger" title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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