<?php 
require_once '../includes/header.php'; 
require_once '../config/db.php'; 
?>

<?php
// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], (int)$_POST['order_id']]);
    header('Location: orders.php');
    exit;
}

// Xử lý xóa đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([(int)$_POST['order_id']]);
        $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([(int)$_POST['order_id']]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
    header('Location: orders.php');
    exit;
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Đếm tổng số đơn hàng
$total = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalPages = ceil($total / $limit);

// Lấy danh sách đơn hàng có phân trang - SỬA LỖI Ở ĐÂY
$sql = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    ORDER BY o.created_at DESC
    LIMIT $limit OFFSET $offset
";
$orders = $pdo->query($sql)->fetchAll();
?>

<h1>Quản lý đơn hàng</h1>

<?php if (empty($orders)): ?>
    <p style="padding:20px;background:#fff;border-radius:8px;text-align:center;color:#666">Chưa có đơn hàng nào</p>
<?php else: ?>

<table>
    <thead>
        <tr><th>Mã đơn</th><th>Khách hàng</th><th>Tổng tiền</th><th>Số lượng SP</th><th>Trạng thái</th><th>Ngày đặt</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><strong><?= htmlspecialchars($order['order_code']) ?></strong></td>
            <td>
                <?= htmlspecialchars($order['user_name']) ?><br>
                <small style="color:#888"><?= htmlspecialchars($order['user_phone']) ?></small>
                <?php if (!empty($order['user_email'])): ?>
                    <br><small style="color:#888"><?= htmlspecialchars($order['user_email']) ?></small>
                <?php endif; ?>
             </div>
            <td><?= number_format($order['total_amount']) ?>đ</div>
            <td><?= $order['item_count'] ?></div>
            <td>
                <span class="badge badge-<?= $order['order_status'] ?>">
                    <?php 
                    $statusLabels = [
                        'pending' => 'Chờ xử lý',
                        'confirmed' => 'Đã xác nhận',
                        'delivered' => 'Đã giao',
                        'cancelled' => 'Đã hủy'
                    ];
                    echo $statusLabels[$order['order_status']] ?? $order['order_status'];
                    ?>
                </span>
             </div>
            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
            <td>
                <form method="POST" style="display: inline-block; margin-right: 5px;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <select name="status" onchange="this.form.submit()" style="padding:4px;border-radius:4px;border:1px solid #ddd">
                        <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="confirmed" <?= $order['order_status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                        <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                        <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        <option value="pending" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Đã thanh toán</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
                <form method="POST" style="display: inline-block;" onsubmit="return confirm('Xóa đơn hàng <?= htmlspecialchars($order['order_code']) ?>?')">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="delete_order" value="1">
                    <button type="submit" class="btn btn-danger" style="padding:4px 8px;font-size:12px">Xóa</button>
                </form>
             </div>
         </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Phân trang -->
<?php if ($totalPages > 1): ?>
<div style="margin-top:20px;display:flex;justify-content:center;gap:8px;flex-wrap:wrap">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>" class="btn" style="background:#f0f0f0;text-decoration:none">&laquo; Trước</a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $page): ?>
            <span class="btn btn-primary" style="background:#222"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>" class="btn" style="background:#f0f0f0;text-decoration:none"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>" class="btn" style="background:#f0f0f0;text-decoration:none">Sau &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
<li>
    <a href="revenue.php">
        Thống kê doanh thu
    </a>
</li>