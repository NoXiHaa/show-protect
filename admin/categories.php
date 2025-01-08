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

// Xử lý xóa danh mục
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    try {
        // Kiểm tra xem có phần mềm nào trong danh mục không
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM softwares WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Không thể xóa danh mục đang có phần mềm';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Đã xóa danh mục thành công';
        }
    } catch(PDOException $e) {
        $error = 'Không thể xóa danh mục';
    }
}

// Xử lý thêm/sửa danh mục
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $color = trim($_POST['color'] ?? '#4f46e5');

    if (empty($name)) {
        $error = 'Vui lòng nhập tên danh mục';
    } else {
        try {
            if ($id > 0) {
                // Cập nhật
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, color = ? WHERE id = ?");
                $stmt->execute([$name, $color, $id]);
                $success = 'Đã cập nhật danh mục thành công';
            } else {
                // Thêm mới
                $stmt = $pdo->prepare("INSERT INTO categories (name, color) VALUES (?, ?)");
                $stmt->execute([$name, $color]);
                $success = 'Đã thêm danh mục thành công';
            }
        } catch(PDOException $e) {
            $error = 'Không thể lưu danh mục';
        }
    }
}

// Lấy danh sách danh mục và số lượng phần mềm
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(s.id) as software_count 
        FROM categories c
        LEFT JOIN softwares s ON c.id = s.category_id
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Không thể tải danh sách danh mục';
}

$page_title = 'Quản lý danh mục - Quản trị';
$active_page = 'categories';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-folder me-2"></i>Quản lý danh mục
                </h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                        data-bs-target="#categoryModal">
                    <i class="bi bi-plus-lg me-2"></i>Thêm danh mục
                </button>
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
                                    <th style="width: 60px;">Màu</th>
                                    <th>Tên danh mục</th>
                                    <th>Số phần mềm</th>
                                    <th style="width: 100px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td>
                                        <div class="rounded" 
                                             style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($category['color']) ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= number_format($category['software_count']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                    onclick="editCategory(<?= htmlspecialchars(json_encode($category)) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                <button type="submit" name="delete" 
                                                        class="btn btn-outline-danger"
                                                        <?= $category['software_count'] > 0 ? 'disabled' : '' ?>>
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-folder-plus me-2"></i>
                        <span id="modalTitle">Thêm danh mục</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="categoryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục</label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <input type="color" class="form-control form-control-color w-100" 
                               name="color" id="categoryColor" value="#4f46e5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Đóng
                    </button>
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="bi bi-check2 me-2"></i>Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extra_scripts = '
<script>
function editCategory(category) {
    document.getElementById("modalTitle").textContent = "Sửa danh mục";
    document.getElementById("categoryId").value = category.id;
    document.getElementById("categoryName").value = category.name;
    document.getElementById("categoryColor").value = category.color;
    
    new bootstrap.Modal(document.getElementById("categoryModal")).show();
}
</script>
';

include '../includes/footer.php';
?> 