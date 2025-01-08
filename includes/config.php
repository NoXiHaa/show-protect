<?php
if (!file_exists(__DIR__ . '/../config.json')) {
    header('Location: /install.php');
    exit;
}

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
} 

// Thêm constant cho BASE_URL
define('BASE_URL', ''); // Để trống nếu ở root domain, hoặc '/subfolder' nếu trong thư mục con 