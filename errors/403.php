<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Truy cập bị từ chối</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: var(--bg-color);
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: var(--primary-color);
        }
        .error-message {
            font-size: 24px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="container">
            <div class="error-code">403</div>
            <div class="error-message">Truy cập bị từ chối</div>
            <p class="text-muted">Bạn không có quyền truy cập trang này</p>
            <a href="../index.php" class="btn btn-primary mt-3">
                <i class="bi bi-house-fill me-2"></i>Về trang chủ
            </a>
        </div>
    </div>
</body>
</html> 