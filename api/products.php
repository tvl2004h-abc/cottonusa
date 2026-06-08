<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

$code = $_GET['code'] ?? null;

try {
    if ($code) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_code = ? AND is_active = 1");
        $stmt->execute([$code]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['images'] = json_decode($product['images'], true) ?: [];
            $product['sizes']  = json_decode($product['sizes'], true) ?: [];
            $product['colors'] = json_decode($product['colors'], true) ?: [];
        }
        
        echo json_encode($product ?: ['error' => 'Không tìm thấy sản phẩm']);
    } else {
        $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        
        $sql = "SELECT * FROM products WHERE is_active = 1";
        $params = [];
        
        if ($category && $category > 0) {
            $sql .= " AND category_id = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY id DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        foreach ($products as &$p) {
            $p['images'] = json_decode($p['images'], true) ?: [];
            $p['sizes']  = json_decode($p['sizes'], true) ?: [];
            $p['colors'] = json_decode($p['colors'], true) ?: [];
        }
        
        echo json_encode($products);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi database: ' . $e->getMessage()]);
}
?>