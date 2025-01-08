<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Xử lý tìm kiếm và lọc
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;

try {
    // Query cơ bản
    $query = "SELECT s.*, c.name as category_name, c.color as category_color 
              FROM softwares s 
              LEFT JOIN categories c ON s.category_id = c.id 
              WHERE 1=1";
    $params = [];

    // Thêm điều kiện tìm kiếm
    if ($search) {
        $query .= " AND (s.name LIKE ? OR s.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Lọc theo danh mục
    if ($category > 0) {
        $query .= " AND s.category_id = ?";
        $params[] = $category;
    }

    // Đếm tổng số kết quả
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ($query) as t");
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $per_page);
    
    // Giới hạn page hợp lệ
    $page = max(1, min($page, $total_pages));

    // Thêm sắp xếp
    switch ($sort) {
        case 'name_asc':
            $query .= " ORDER BY s.name ASC";
            break;
        case 'name_desc':
            $query .= " ORDER BY s.name DESC";
            break;
        case 'oldest':
            $query .= " ORDER BY s.created_at ASC";
            break;
        default: // newest
            $query .= " ORDER BY s.created_at DESC";
    }

    // Thêm phân trang
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = ($page - 1) * $per_page;

    // Thực thi query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $softwares = $stmt->fetchAll();

    // Lấy danh sách danh mục cho filter
    $categories = getCategories($pdo);

} catch(PDOException $e) {
    $error = 'Đã có lỗi xảy ra';
}

$page_title = 'Trang chủ - Thư Viện Phần Mềm';

$extra_css = '
<style>
.software-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}
.software-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.software-card .card-body {
    flex: 1;
}
.software-card .card-img-top {
    height: 200px;
    object-fit: cover;
}
.filter-sidebar {
    position: sticky;
    top: 1rem;
}
</style>
';

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar filters -->
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="filter-sidebar">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel me-2"></i>Bộ lọc
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="vstack gap-4">
                            <?php if ($search): ?>
                                <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                            <?php endif; ?>

                            <!-- Categories -->
                            <div>
                                <label class="form-label fw-medium">Danh mục</label>
                                <div class="vstack gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category" 
                                               id="cat_0" value="0" <?= $category == 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cat_0">
                                            Tất cả
                                        </label>
                                    </div>
                                    <?php foreach ($categories as $cat): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category"
                                               id="cat_<?= $cat['id'] ?>" value="<?= $cat['id'] ?>"
                                               <?= $category == $cat['id'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cat_<?= $cat['id'] ?>">
                                            <span class="color-dot me-2" 
                                                  style="background-color: <?= htmlspecialchars($cat['color']) ?>">
                                            </span>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Sort -->
                            <div>
                                <label class="form-label fw-medium">Sắp xếp</label>
                                <select class="form-select" name="sort">
                                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>
                                        Mới nhất
                                    </option>
                                    <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>
                                        Cũ nhất
                                    </option>
                                    <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>
                                        Tên A-Z
                                    </option>
                                    <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>
                                        Tên Z-A
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel-fill me-2"></i>Lọc kết quả
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-lg-9">
            <!-- Search results info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <?php if ($search): ?>
                            Kết quả tìm kiếm: "<?= htmlspecialchars($search) ?>"
                        <?php elseif ($category > 0): ?>
                            <?php 
                            $cat_name = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $category) {
                                    $cat_name = $cat['name'];
                                    break;
                                }
                            }
                            ?>
                            Danh mục: <?= htmlspecialchars($cat_name) ?>
                        <?php else: ?>
                            Tất cả phần mềm
                        <?php endif; ?>
                    </h4>
                    <div class="text-muted">
                        Hiển thị <?= number_format($total_items) ?> kết quả
                    </div>
                </div>
            </div>

            <?php if ($softwares): ?>
                <!-- Software grid -->
                <div class="software-grid">
                    <?php foreach ($softwares as $software): ?>
                    <div class="card software-card shadow-sm">
                        <?php if ($software['image_url']): ?>
                            <img src="<?= htmlspecialchars($software['image_url']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($software['name']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($software['name']) ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?= mb_strimwidth(htmlspecialchars($software['description']), 0, 100, '...') ?>
                            </p>
                            <div class="mb-3">
                                <span class="badge" 
                                      style="background-color: <?= htmlspecialchars($software['category_color']) ?>">
                                    <?= htmlspecialchars($software['category_name']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0">
                            <a href="detail.php?id=<?= $software['id'] ?>" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-eye me-2"></i>Chi tiết
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="mt-3 text-muted">Không tìm thấy kết quả nào</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 