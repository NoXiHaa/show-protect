<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

try {
    // Lấy danh sách danh mục và số lượng phần mềm
    $stmt = $pdo->query("
        SELECT c.*, COUNT(s.id) as software_count 
        FROM categories c
        LEFT JOIN softwares s ON c.id = s.category_id
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Không thể tải danh mục';
}

$page_title = 'Danh mục - Thư Viện Phần Mềm';

$extra_css = '
<style>
.category-card {
    transition: transform 0.2s;
}
.category-card:hover {
    transform: translateY(-5px);
}
.category-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}
</style>
';

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Danh mục phần mềm</h1>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($categories as $category): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card category-card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="category-icon" 
                             style="background-color: <?= htmlspecialchars($category['color']) ?>25">
                            <i class="bi bi-folder fs-4" 
                               style="color: <?= htmlspecialchars($category['color']) ?>">
                            </i>
                        </div>
                        <div class="ms-3">
                            <h5 class="card-title mb-1">
                                <?= htmlspecialchars($category['name']) ?>
                            </h5>
                            <div class="text-muted small">
                                <?= number_format($category['software_count']) ?> phần mềm
                            </div>
                        </div>
                    </div>
                    <a href="index.php?category=<?= $category['id'] ?>" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-grid me-2"></i>Xem phần mềm
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 