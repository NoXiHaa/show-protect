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

// Xử lý cập nhật trạng thái
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $success = 'Đã cập nhật trạng thái';
    } catch(PDOException $e) {
        $error = 'Không thể cập nhật trạng thái';
    }
}

// Xử lý xóa liên hệ
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Đã xóa liên hệ thành công';
    } catch(PDOException $e) {
        $error = 'Không thể xóa liên hệ';
    }
}

// Lấy danh sách liên hệ
try {
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
    $contacts = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Không thể tải danh sách liên hệ';
}

$page_title = 'Quản lý liên hệ - Quản trị';
$active_page = 'contacts';

include '../includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="bi bi-envelope me-2"></i>Quản lý liên hệ
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
                                    <th>Người gửi</th>
                                    <th>Email</th>
                                    <th>Tiêu đề</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày gửi</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td><?= $contact['id'] ?></td>
                                    <td>
                                        <div class="fw-medium">
                                            <?= htmlspecialchars($contact['name']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($contact['email']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($contact['subject']) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                            <select class="form-select form-select-sm" name="status" 
                                                    onchange="this.form.submit()" style="width: 100px;">
                                                <option value="pending" <?= $contact['status'] == 'pending' ? 'selected' : '' ?>>
                                                    Chờ xử lý
                                                </option>
                                                <option value="processing" <?= $contact['status'] == 'processing' ? 'selected' : '' ?>>
                                                    Đang xử lý
                                                </option>
                                                <option value="completed" <?= $contact['status'] == 'completed' ? 'selected' : '' ?>>
                                                    Đã xử lý
                                                </option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <?= date('d/m/Y H:i', strtotime($contact['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                    onclick="viewMessage(<?= htmlspecialchars(json_encode($contact)) ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                                <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                                <button type="submit" name="delete" 
                                                        class="btn btn-outline-danger">
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

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-envelope-open me-2"></i>
                    <span id="messageSubject"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Người gửi:</strong>
                    <span id="messageName"></span>
                </div>
                <div class="mb-3">
                    <strong>Email:</strong>
                    <span id="messageEmail"></span>
                </div>
                <div class="mb-3">
                    <strong>Nội dung:</strong>
                    <div id="messageContent" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_scripts = '
<script>
function viewMessage(contact) {
    document.getElementById("messageSubject").textContent = contact.subject;
    document.getElementById("messageName").textContent = contact.name;
    document.getElementById("messageEmail").textContent = contact.email;
    document.getElementById("messageContent").textContent = contact.message;
    
    new bootstrap.Modal(document.getElementById("messageModal")).show();
}
</script>
';

include '../includes/footer.php';
?> 