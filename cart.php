<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Xử lý xóa phần mềm khỏi giỏ hàng
if (isset($_POST['remove_item'])) {
    $software_id = (int)$_POST['software_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ? AND software_id = ?");
        $stmt->execute([$_SESSION['cart_id'], $software_id]);
        $success = 'Đã xóa phần mềm khỏi giỏ hàng';
    } catch(PDOException $e) {
        $error = 'Không thể xóa phần mềm';
    }
}

// Lấy thông tin giỏ hàng
try {
    // Kiểm tra/tạo giỏ hàng mới
    if (!isset($_SESSION['cart_id'])) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'pending')");
        $stmt->execute([$_SESSION['user']['id']]);
        $_SESSION['cart_id'] = $pdo->lastInsertId();
    }

    // Lấy danh sách phần mềm trong giỏ hàng
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as category_name, c.color as category_color
        FROM order_items oi
        JOIN softwares s ON oi.software_id = s.id
        LEFT JOIN categories c ON s.category_id = c.id
        WHERE oi.order_id = ?
        ORDER BY oi.created_at DESC
    ");
    $stmt->execute([$_SESSION['cart_id']]);
    $cart_items = $stmt->fetchAll();

    // Cập nhật tổng số item
    $stmt = $pdo->prepare("UPDATE orders SET total_items = ? WHERE id = ?");
    $stmt->execute([count($cart_items), $_SESSION['cart_id']]);

} catch(PDOException $e) {
    $error = 'Đã có lỗi xảy ra';
}

$page_title = 'Giỏ hàng - Thư Viện Phần Mềm';

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-cart me-2"></i>Giỏ hàng của tôi
                    </h4>
                </div>
                <div class="card-body">
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

                    <?php if ($cart_items): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Ảnh</th>
                                        <th>Tên phần mềm</th>
                                        <th>Danh mục</th>
                                        <th style="width: 100px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                     class="img-thumbnail" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="detail.php?id=<?= $item['id'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge" 
                                                  style="background-color: <?= htmlspecialchars($item['category_color']) ?>">
                                                <?= htmlspecialchars($item['category_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="software_id" 
                                                       value="<?= $item['id'] ?>">
                                                <button type="submit" name="remove_item" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted"></i>
                            <p class="mt-3 text-muted">Giỏ hàng trống</p>
                            <a href="index.php" class="btn btn-primary mt-3">
                                <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 