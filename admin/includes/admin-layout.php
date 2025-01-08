<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Quản trị' ?> - Thư Viện Phần Mềm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>
                Thư Viện Phần Mềm
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['user']['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="../profile.php">
                                    <i class="bi bi-person me-2"></i>Tài khoản
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="../logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container my-4">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar sticky-top" style="top: 5rem;">
                    <div class="p-3">
                        <h6 class="text-uppercase text-muted mb-3 px-2" style="font-size: 0.75rem;">
                            Quản lý
                        </h6>
                        <nav class="nav flex-column">
                            <a class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>" 
                               href="index.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Tổng quan
                            </a>
                            <a class="nav-link <?= $active_page === 'softwares' ? 'active' : '' ?>" 
                               href="softwares.php">
                                <i class="bi bi-grid me-2"></i>
                                Phần mềm
                            </a>
                            <a class="nav-link <?= $active_page === 'categories' ? 'active' : '' ?>" 
                               href="categories.php">
                                <i class="bi bi-folder me-2"></i>
                                Danh mục
                            </a>
                            <a class="nav-link <?= $active_page === 'users' ? 'active' : '' ?>" 
                               href="users.php">
                                <i class="bi bi-people me-2"></i>
                                Người dùng
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <div class="col-lg-9">
                <?= $content ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html> 