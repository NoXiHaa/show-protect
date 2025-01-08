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
$software = [
    'id' => '',
    'name' => '',
    'description' => '',
    'category_id' => '',
    'version' => '',
    'download_url' => '',
    'image_url' => ''
];

// Lấy thông tin phần mềm nếu là sửa
if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM softwares WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        if ($row = $stmt->fetch()) {
            $software = $row;
        }
    } catch(PDOException $e) {
        $error = 'Không thể tải thông tin phần mềm';
    }
}

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $version = trim($_POST['version'] ?? '');
    $download_url = trim($_POST['download_url'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($name)) {
        $error = 'Vui lòng nhập tên phần mềm';
    } else {
        try {
            if ($software['id']) {
                // Cập nhật
                $stmt = $pdo->prepare("
                    UPDATE softwares 
                    SET name = ?, description = ?, category_id = ?, 
                        version = ?, download_url = ?, image_url = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $name, $description, $category_id, 
                    $version, $download_url, $image_url,
                    $software['id']
                ]);
                $success = 'Đã cập nhật phần mềm thành công';
            } else {
                // Thêm mới
                $stmt = $pdo->prepare("
                    INSERT INTO softwares (name, description, category_id, 
                                         version, download_url, image_url)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name, $description, $category_id,
                    $version, $download_url, $image_url
                ]);
                $success = 'Đã thêm phần mềm thành công';
                
                // Chuyển đến trang sửa
                $id = $pdo->lastInsertId();
                header('Location: ' . admin_url("phan-mem/sua/$id"));
                exit;
            }

            // Cập nhật lại thông tin hiển thị
            $software = [
                'id' => $software['id'],
                'name' => $name,
                'description' => $description,
                'category_id' => $category_id,
                'version' => $version,
                'download_url' => $download_url,
                'image_url' => $image_url
            ];
        } catch(PDOException $e) {
            $error = 'Không thể lưu phần mềm';
        }
    }
}

// Lấy danh sách danh mục
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Không thể tải danh sách danh mục';
}

$page_title = ($software['id'] ? 'Sửa' : 'Thêm') . ' phần mềm - Quản trị';
$active_page = 'softwares';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-<?= $software['id'] ? 'pencil' : 'plus-lg' ?> me-2"></i>
                    <?= $software['id'] ? 'Sửa' : 'Thêm' ?> phần mềm
                </h4>
                <a href="<?= admin_url('phan-mem') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại
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
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Tên phần mềm</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?= htmlspecialchars($software['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" name="description" 
                                      rows="5"><?= htmlspecialchars($software['description']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select class="form-select" name="category_id">
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= $category['id'] == $software['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phiên bản</label>
                            <input type="text" class="form-control" name="version" 
                                   value="<?= htmlspecialchars($software['version']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link tải</label>
                            <input type="url" class="form-control" name="download_url" 
                                   value="<?= htmlspecialchars($software['download_url']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link ảnh</label>
                            <input type="url" class="form-control" name="image_url" 
                                   value="<?= htmlspecialchars($software['image_url']) ?>">
                            <?php if ($software['image_url']): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($software['image_url']) ?>" 
                                         class="img-thumbnail" style="height: 100px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-2"></i>Lưu phần mềm
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 