
<?php
require_once '../includes/header.php';
require_once '../config/db.php';

// Thống kê
$totalOrders = $pdo->query("
    SELECT COUNT(*)
    FROM orders
")->fetchColumn();

$totalRevenue = $pdo->query("
    SELECT SUM(total_amount)
    FROM orders
    WHERE order_status IN ('confirmed','delivered')
")->fetchColumn();

$totalProducts = $pdo->query("
    SELECT COUNT(*)
    FROM products
    WHERE is_active = 1
")->fetchColumn();
$pendingOrders = $pdo->query("
    SELECT COUNT(*)
    FROM orders
    WHERE order_status = 'pending'
")->fetchColumn();


// Nếu NULL thì cho bằng 0
$totalRevenue = $totalRevenue ?: 0;

// Đơn hàng gần đây
$recentOrders = $pdo->query("
    SELECT *
    FROM orders
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll();

// Sản phẩm sắp hết hàng
$lowStockProducts = $pdo->query("
    SELECT *
    FROM products
    WHERE stock <= 5
    ORDER BY stock ASC
")->fetchAll();
// Top 5 sản phẩm bán chạy
$bestSellingProducts = $pdo->query("
    SELECT
        product_name,
        SUM(quantity) AS total_sold
    FROM order_items
    GROUP BY product_name
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();
?>

<h1>Tổng quan</h1>

<div class="card-stats">

    <div class="stat">
        <div style="color:#666;font-size:13px">
            TỔNG ĐƠN HÀNG
        </div>
        <div class="val">
            <?= number_format($totalOrders) ?>
        </div>
    </div>

    <div class="stat">
        <div style="color:#666;font-size:13px">
            DOANH THU
        </div>
        <div class="val">
            <?= number_format($totalRevenue) ?>đ
        </div>
    </div>

    <div class="stat">
        <div style="color:#666;font-size:13px">
            SẢN PHẨM
        </div>
        <div class="val">
            <?= number_format($totalProducts) ?>
        </div>
    </div>

    <div class="stat">
        <div style="color:#666;font-size:13px">
            CHỜ XỬ LÝ
        </div>
        <div class="val" style="color:#d97706">
            <?= number_format($pendingOrders) ?>
        </div>
    </div>

</div>

<h2 style="margin-bottom:12px;font-size:16px">
    Đơn hàng gần đây
</h2>

<?php if (empty($recentOrders)): ?>

    <p style="
        padding:20px;
        background:#fff;
        border-radius:8px;
        text-align:center;
        color:#666;
    ">
        Chưa có đơn hàng nào
    </p>

<?php else: ?>

<table>

    <thead>
        <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
            <th>Trạng thái</th>
            <th>Ngày</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($recentOrders as $o): ?>

        <tr>

            <td>
                <strong>
                    <?= htmlspecialchars($o['order_code']) ?>
                </strong>
            </td>

            <td>
                <?= htmlspecialchars($o['user_name']) ?>
                <br>
                <small style="color:#888">
                    <?= htmlspecialchars($o['user_phone']) ?>
                </small>
            </td>

            <td>
                <?= number_format($o['total_amount']) ?>đ
            </td>

            <td>
                <?= $o['payment_method'] === 'cod'
                    ? 'COD'
                    : 'Chuyển khoản'; ?>
            </td>

            <td>

                <span class="badge badge-<?= $o['order_status'] ?>">

                    <?php

                    $statusLabels = [
                        'pending'   => 'Chờ xử lý',
                        'confirmed' => 'Đã xác nhận',
                        'delivered' => 'Đã giao',
                        'cancelled' => 'Đã hủy'
                    ];

                    echo $statusLabels[$o['order_status']]
                        ?? $o['order_status'];

                    ?>

                </span>

            </td>

            <td style="font-size:12px;color:#888">
                <?= date('d/m H:i', strtotime($o['created_at'])) ?>
            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php endif; ?>

<h2 style="margin-top:30px;margin-bottom:12px;font-size:16px">
    Sản phẩm sắp hết hàng
</h2>

<?php if(empty($lowStockProducts)): ?>

    <p style="
        padding:20px;
        background:#fff;
        border-radius:8px;
        color:green;
    ">
        Không có sản phẩm nào sắp hết hàng
    </p>

<?php else: ?>

<table>

    <thead>
        <tr>
            <th>Mã sản phẩm</th>
            <th>Tên sản phẩm</th>
            <th>Tồn kho</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($lowStockProducts as $p): ?>

        <tr>

            <td>
                <?= htmlspecialchars($p['product_code']) ?>
            </td>

            <td>
                <?= htmlspecialchars($p['name']) ?>
            </td>

            <td style="color:red;font-weight:bold">
                <?= $p['stock'] ?>
            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php endif; ?>
<h2 style="margin-top:30px;margin-bottom:12px;font-size:16px">
    Sản phẩm bán chạy nhất
</h2>

<?php if(empty($bestSellingProducts)): ?>

<p style="
    padding:20px;
    background:#fff;
    border-radius:8px;
">
    Chưa có dữ liệu bán hàng
</p>

<?php else: ?>

<table>

    <thead>
        <tr>
            <th>STT</th>
            <th>Tên sản phẩm</th>
            <th>Đã bán</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($bestSellingProducts as $index => $product): ?>

    <tr>

        <td>
            <?= $index + 1 ?>
        </td>

        <td>
            <?= htmlspecialchars($product['product_name']) ?>
        </td>

        <td style="color:green;font-weight:bold">
            <?= $product['total_sold'] ?>
        </td>

    </tr>

    <?php endforeach; ?>

    </tbody>

</table>

<?php endif; ?>
<?php require_once '../includes/footer.php'; ?>

