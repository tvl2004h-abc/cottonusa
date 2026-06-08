```php
<?php
require_once '../includes/header.php';
require_once '../config/db.php';

$weekRevenue = $pdo->query("
    SELECT COALESCE(SUM(total_amount),0)
    FROM orders
    WHERE order_status IN ('confirmed','delivered')
    AND YEARWEEK(created_at,1)=YEARWEEK(NOW(),1)
")->fetchColumn();

$monthRevenue = $pdo->query("
    SELECT COALESCE(SUM(total_amount),0)
    FROM orders
    WHERE order_status IN ('confirmed','delivered')
    AND MONTH(created_at)=MONTH(NOW())
    AND YEAR(created_at)=YEAR(NOW())
")->fetchColumn();

$yearRevenue = $pdo->query("
    SELECT COALESCE(SUM(total_amount),0)
    FROM orders
    WHERE order_status IN ('confirmed','delivered')
    AND YEAR(created_at)=YEAR(NOW())
")->fetchColumn();

$totalRevenue = $pdo->query("
    SELECT COALESCE(SUM(total_amount),0)
    FROM orders
    WHERE order_status IN ('confirmed','delivered')
")->fetchColumn();
?>

<h1>Thống kê doanh thu</h1>

<div class="card-stats">

    <div class="stat">
        <div>Tổng doanh thu</div>
        <div class="val">
            <?= number_format($totalRevenue) ?>đ
        </div>
    </div>

    <div class="stat">
        <div>Doanh thu tuần</div>
        <div class="val">
            <?= number_format($weekRevenue) ?>đ
        </div>
    </div>

    <div class="stat">
        <div>Doanh thu tháng</div>
        <div class="val">
            <?= number_format($monthRevenue) ?>đ
        </div>
    </div>

    <div class="stat">
        <div>Doanh thu năm</div>
        <div class="val">
            <?= number_format($yearRevenue) ?>đ
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
```
