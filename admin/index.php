<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../errors/403.php');
    exit;
}

// Lấy thống kê
try {
    // Tổng số phần mềm
    $stmt = $pdo->query("SELECT COUNT(*) FROM softwares");
    $total_softwares = $stmt->fetchColumn();

    // Tổng số danh mục
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $total_categories = $stmt->fetchColumn();

    // Thống kê người dùng
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN is_admin = 1 THEN 1 ELSE 0 END) as total_admins,
        SUM(CASE WHEN is_admin = 0 THEN 1 ELSE 0 END) as total_members
        FROM users");
    $user_stats = $stmt->fetch();

    // Phần mềm mới thêm gần đây
    $stmt = $pdo->query("SELECT s.*, c.name as category_name, c.color as category_color 
                        FROM softwares s 
                        LEFT JOIN categories c ON s.category_id = c.id 
                        ORDER BY s.created_at DESC 
                        LIMIT 5");
    $recent_softwares = $stmt->fetchAll();

} catch(PDOException $e) {
    $error = 'Không thể lấy thống kê';
}

$page_title = 'Quản trị - Thư Viện Phần Mềm';
$active_page = 'dashboard';

$extra_css = '
<style>
.stats-card {
    transition: transform 0.2s;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}
.recent-software-list {
    max-height: 400px;
    overflow-y: auto;
}
</style>
';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>Tổng quan
                </h4>
            </div>
            
            <!-- Stats cards -->
            <div class="row g-4 mb-4">
                <!-- Software stats -->
                <div class="col-md-6 col-lg-3">
                    <div class="card stats-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-grid-3x3-gap fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-1"><?= number_format($total_softwares) ?></h3>
                                    <div class="text-muted small">Phần mềm</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories stats -->
                <div class="col-md-6 col-lg-3">
                    <div class="card stats-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-folder fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-1"><?= number_format($total_categories) ?></h3>
                                    <div class="text-muted small">Danh mục</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users stats -->
                <div class="col-md-6 col-lg-3">
                    <div class="card stats-card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-people fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-1"><?= number_format($user_stats['total_users']) ?></h3>
                                    <div class="text-muted small">Người dùng</div>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-top">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>
                                        <i class="bi bi-shield-check me-1"></i>
                                        Admin: <?= number_format($user_stats['total_admins']) ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-person me-1"></i>
                                        Member: <?= number_format($user_stats['total_members']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent softwares -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Phần mềm mới thêm
                    </h5>
                </div>
                <div class="card-body">
                    <div class="recent-software-list">
                        <?php if ($recent_softwares): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">ID</th>
                                            <th>Tên phần mềm</th>
                                            <th>Danh mục</th>
                                            <th>Ngày thêm</th>
                                            <th style="width: 100px;">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_softwares as $software): ?>
                                        <tr>
                                            <td><?= $software['id'] ?></td>
                                            <td>
                                                <div class="fw-medium"><?= htmlspecialchars($software['name']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge" 
                                                      style="background-color: <?= htmlspecialchars($software['category_color']) ?>">
                                                    <?= htmlspecialchars($software['category_name']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($software['created_at'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="edit-software.php?id=<?= $software['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1"></i>
                                <p class="mt-2">Chưa có phần mềm nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 