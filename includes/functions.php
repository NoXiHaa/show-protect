<?php
function getSoftwares($pdo, $category = null, $search = null) {
    $sql = "SELECT s.*, c.name as category_name 
            FROM softwares s 
            LEFT JOIN categories c ON s.category_id = c.id 
            WHERE 1=1";
    
    if ($category) {
        $sql .= " AND s.category_id = :category";
    }
    
    if ($search) {
        $sql .= " AND (s.name LIKE :search OR s.description LIKE :search)";
    }
    
    $stmt = $pdo->prepare($sql);
    
    if ($category) {
        $stmt->bindValue(':category', $category);
    }
    
    if ($search) {
        $search = "%$search%";
        $stmt->bindValue(':search', $search);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSoftwareById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT s.*, c.name as category_name 
                          FROM softwares s 
                          LEFT JOIN categories c ON s.category_id = c.id 
                          WHERE s.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function url($path = '') {
    $base_url = rtrim(BASE_URL, '/');
    return $base_url . '/' . ltrim($path, '/');
}

function software_url($id, $slug = '') {
    if ($slug) {
        return url("phan-mem/$id/$slug");
    }
    return url("phan-mem/$id");
}

function admin_url($path = '') {
    return url('quan-tri/' . ltrim($path, '/'));
} 