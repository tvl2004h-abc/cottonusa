<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Chỉ chấp nhận phương thức POST']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Validate dữ liệu
$required = ['name', 'phone', 'address', 'items'];

foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['error' => "Thiếu trường bắt buộc: $field"]);
        exit;
    }
}

if (!is_array($data['items']) || count($data['items']) === 0) {
    echo json_encode(['error' => 'Giỏ hàng trống']);
    exit;
}

// Tạo mã đơn hàng
$orderCode = 'CU' . date('ymd') . strtoupper(substr(uniqid(), -6));

// Tính tổng tiền
$total = 0;

foreach ($data['items'] as $item) {

    if (!isset($item['price']) || !isset($item['quantity'])) {
        echo json_encode(['error' => 'Dữ liệu sản phẩm không hợp lệ']);
        exit;
    }

    $total += (float)$item['price'] * (int)$item['quantity'];
}

$shipping = 30000;
$grandTotal = $total + $shipping;

$pdo->beginTransaction();

try {

    // Kiểm tra tồn kho
    foreach ($data['items'] as $item) {

        $productCode = $item['code'] ?? '';
        $quantity = (int)$item['quantity'];

        $checkStock = $pdo->prepare("
            SELECT id, name, stock
            FROM products
            WHERE product_code = ?
        ");

        $checkStock->execute([$productCode]);

        $product = $checkStock->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Không tìm thấy sản phẩm: " . $productCode);
        }

        if ($product['stock'] < $quantity) {
            throw new Exception(
                "Sản phẩm '{$product['name']}' chỉ còn {$product['stock']} sản phẩm trong kho"
            );
        }
    }

    // Thêm đơn hàng
    $stmt = $pdo->prepare("
        INSERT INTO orders
        (
            order_code,
            user_name,
            user_phone,
            user_email,
            address,
            total_amount,
            shipping_fee,
            payment_method,
            note,
            order_status,
            created_at
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()
        )
    ");

    $stmt->execute([
        $orderCode,
        trim($data['name']),
        trim($data['phone']),
        trim($data['email'] ?? ''),
        trim($data['address']),
        $grandTotal,
        $shipping,
        $data['payment_method'] ?? 'cod',
        trim($data['note'] ?? '')
    ]);

    $orderId = $pdo->lastInsertId();

    // Thêm chi tiết đơn hàng
    $stmt2 = $pdo->prepare("
        INSERT INTO order_items
        (
            order_id,
            product_code,
            product_name,
            size,
            color,
            quantity,
            unit_price
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?
        )
    ");

    foreach ($data['items'] as $item) {

        $productCode = $item['code'] ?? '';
        $quantity = (int)$item['quantity'];

        $stmt2->execute([
            $orderId,
            $productCode,
            $item['name'] ?? '',
            $item['size'] ?? '',
            $item['color'] ?? '',
            $quantity,
            (float)$item['price']
        ]);

        // Trừ tồn kho
        $updateStock = $pdo->prepare("
            UPDATE products
            SET stock = stock - ?
            WHERE product_code = ?
              AND stock >= ?
        ");

        $updateStock->execute([
            $quantity,
            $productCode,
            $quantity
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'order_code' => $orderCode,
        'total' => $grandTotal,
        'message' => 'Đặt hàng thành công!'
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>