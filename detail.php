<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$software = getSoftwareById($pdo, $id);

if (!$software) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($software['name']) ?> - Chi tiết</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Thư Viện Phần Mềm</a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="row g-0">
                <div class="col-md-4">
                    <?php if ($software['image_url']): ?>
                    <img src="<?= htmlspecialchars($software['image_url']) ?>" 
                         class="img-fluid rounded-start" 
                         alt="<?= htmlspecialchars($software['name']) ?>">
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h2 class="card-title"><?= htmlspecialchars($software['name']) ?></h2>
                        <p class="card-text"><?= nl2br(htmlspecialchars($software['description'])) ?></p>
                        <div class="mb-3">
                            <span class="badge" 
                                  style="background-color: <?= htmlspecialchars($software['category_color']) ?>">
                                <i class="bi bi-tag me-1"></i>
                                <?= htmlspecialchars($software['category_name']) ?>
                            </span>
                        </div>
                        <?php if ($software['download_url']): ?>
                        <a href="<?= htmlspecialchars($software['download_url']) ?>" 
                           class="btn btn-primary" target="_blank">
                            Tải xuống
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 