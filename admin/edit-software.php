<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../errors/403.php');
    exit;
}

$error = '';
$success = '';
$software = null;
$categories = getCategories($pdo);

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM softwares WHERE id = ?");
    $stmt->execute([$id]);
    $software = $stmt->fetch();

    if (!$software) {
        header('Location: index.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $download_url = $_POST['download_url'] ?? '';
    $current_image = $software['image_url'];

    // Xử lý upload ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Xóa ảnh cũ nếu có
            if ($current_image && file_exists('../' . $current_image)) {
                unlink('../' . $current_image);
            }
            $current_image = 'uploads/' . $new_filename;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE softwares SET name = ?, description = ?, 
                              category_id = ?, image_url = ?, download_url = ? 
                              WHERE id = ?");
        $stmt->execute([$name, $description, $category_id, $current_image, 
                       $download_url, $id]);
        $success = "Đã cập nhật phần mềm thành công";
        $software = array_merge($software, [
            'name' => $name,
            'description' => $description,
            'category_id' => $category_id,
            'image_url' => $current_image,
            'download_url' => $download_url
        ]);
    } catch(PDOException $e) {
        $error = "Không thể cập nhật phần mềm: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phần mềm - Quản trị</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'includes/admin-navbar.php'; ?>

    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chỉnh sửa phần mềm</h5>
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Tên phần mềm</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= htmlspecialchars($software['name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="description" rows="5" required>
                                    <?= htmlspecialchars($software['description']) ?>
                                </textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Danh mục</label>
                                <select class="form-select" name="category_id" required>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                            <?= $category['id'] == $software['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hình ảnh hiện tại</label>
                                <?php if ($software['image_url']): ?>
                                    <div class="mb-2">
                                        <img src="../<?= htmlspecialchars($software['image_url']) ?>" 
                                             class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <div class="form-text">Để trống nếu không muốn thay đổi ảnh</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Link tải xuống</label>
                                <input type="url" class="form-control" name="download_url" 
                                       value="<?= htmlspecialchars($software['download_url']) ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Lưu thay đổi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 