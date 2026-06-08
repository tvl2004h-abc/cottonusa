
<?php 
require_once '../includes/header.php'; 
require_once '../config/db.php'; 
?>
<?php
// Xử lý thêm / sửa / xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $images = array_filter(explode("\n", trim($_POST['images'])));
        $sizes  = array_map('trim', explode(',', $_POST['sizes']));
        $colors = array_map('trim', explode(',', $_POST['colors']));

        $discount = (int)$_POST['discount_percent'];

$salePrice =
    (float)$_POST['price']
    * (100 - $discount)
    / 100;

$data = [
    json_encode(array_values($images)),
    json_encode($sizes),
    json_encode($colors),
    $_POST['name'],
    (float)$_POST['price'],
    $discount,
    $salePrice,
    (int)$_POST['category_id'],
    (int)$_POST['stock'],
    $_POST['product_code']
];

        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO products
(
images,
sizes,
colors,
name,
price,
discount_percent,
sale_price,
category_id,
stock,
product_code
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($data);
    } else {

    $data[] = (int)$_POST['id'];

    $stmt = $pdo->prepare("UPDATE products SET
    images=?,
    sizes=?,
    colors=?,
    name=?,
    price=?,
    discount_percent=?,
    sale_price=?,
    category_id=?,
    stock=?,
    product_code=?
    WHERE id=?");

    $stmt->execute($data);
    }
    }
    
    if ($action === 'delete') {
        $stmt = $pdo->prepare("UPDATE products SET is_active=0 WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
    }
    
    header('Location: products.php');
    exit;
}

$categories = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY name
")->fetchAll();

$products = $pdo->query("
    SELECT
        p.*,
        c.name as cat_name,

        (
            SELECT COALESCE(SUM(oi.quantity),0)
            FROM order_items oi
            WHERE oi.product_code = p.product_code
        ) as sold_quantity

    FROM products p

    LEFT JOIN categories c
        ON p.category_id = c.id

    WHERE p.is_active = 1

    ORDER BY p.id DESC
")->fetchAll();

?>

<h1>Quản lý sản phẩm 
    <button class="btn btn-primary" onclick="showForm()">+ Thêm mới</button>
</h1>

<!-- Form thêm/sửa (ẩn mặc định) -->
<div id="product-form" style="display:none;background:#fff;padding:24px;border-radius:10px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
    <h3 id="form-title" style="margin-bottom:16px">Thêm sản phẩm</h3>
    <form method="POST" id="productForm">
        <input type="hidden" name="action" id="form-action" value="add">
        <input type="hidden" name="id" id="form-id">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                <label style="font-size:13px;font-weight:500">Mã sản phẩm *</label>
                <input type="text" name="product_code" id="f-code" required 
                       style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:500">Tên sản phẩm *</label>
                <input type="text" name="name" id="f-name" required
                       style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
            </div>
            <div>
                <label style="font-size:13px;font-weight:500">Giá (VNĐ) *</label>
                <input type="number" name="price" id="f-price" required
                       style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
            </div>
            
            <div>
    <label style="font-size:13px;font-weight:500">
        Giảm giá (%)
    </label>

    <input
        type="number"
        name="discount_percent"
        id="f-discount"
        value="0"
        min="0"
        max="100"
        style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px"
    >
</div>
<div>
    <label style="font-size:13px;font-weight:500">
        Danh mục
    </label>

    <select
        name="category_id"
        id="f-cat"
        style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">

        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>">
                <?= htmlspecialchars($c['name']) ?>
            </option>
        <?php endforeach; ?>

    </select>
</div>
            <div>
                <label style="font-size:13px;font-weight:500">Sizes (cách nhau dấu phẩy)</label>
                <input type="text" name="sizes" id="f-sizes" value="S,M,L,XL"
                       style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
                <small style="color:#666">Ví dụ: S,M,L,XL</small>
            </div>
            <div>
                <label style="font-size:13px;font-weight:500">Màu sắc (cách nhau dấu phẩy)</label>
                <input type="text" name="colors" id="f-colors" value="Đen,Trắng,Kem"
                       style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
                <small style="color:#666">Ví dụ: Đen,Trắng,Xanh</small>
            </div>
            <div>
                <label style="font-size:13px;font-weight:500">Tồn kho</label>
                <input type="number" name="stock" id="f-stock" value="0"
                       style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px">
            </div>
        </div>
        <div style="margin-top:12px">
            <label style="font-size:13px;font-weight:500">Đường dẫn ảnh (mỗi dòng một ảnh)</label>
            <textarea name="images" id="f-images" rows="3" 
                style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;font-family:monospace"
                placeholder="images/sanpham1.jpg&#10;images/sanpham2.jpg&#10;images/sanpham3.jpg"></textarea>
            <small style="color:#666">Nhập đường dẫn ảnh, mỗi ảnh một dòng</small>
        </div>
        <div style="margin-top:16px;display:flex;gap:8px">
            <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
            <button type="button" class="btn" onclick="hideForm()" style="background:#ccc">Hủy</button>
        </div>
    </form>
</div>

<?php if (empty($products)): ?>
    <p style="padding:20px;background:#fff;border-radius:8px;text-align:center;color:#666">Chưa có sản phẩm nào. Hãy thêm sản phẩm đầu tiên!</p>
<?php else: ?>
<table>
    <thead>
        <tr><th>Mã</th><th>Tên sản phẩm</th><th>Giá</th><th>Danh mục</th><th>Đã bán</th><th>Tồn kho</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><code><?= htmlspecialchars($p['product_code']) ?></code></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td>

<?php if($p['discount_percent'] > 0): ?>

    <del style="color:#999">
        <?= number_format($p['price']) ?>đ
    </del>

    <br>

    <span style="color:red;font-weight:bold">
        <?= number_format($p['sale_price']) ?>đ
    </span>

    <br>

    <small>
        -<?= $p['discount_percent'] ?>%
    </small>

<?php else: ?>

    <?= number_format($p['price']) ?>đ

<?php endif; ?>

</td>
            
<td><?= htmlspecialchars($p['cat_name'] ?? 'Chưa phân loại') ?></td>

<td>
    <strong style="color:#2563eb">
        <?= $p['sold_quantity'] ?>
    </strong>
</td>

<td>
    <?php if($p['stock'] <= 5): ?>

        <span style="
            color:red;
            font-weight:bold;
        ">
            <?= $p['stock'] ?>
        </span>

    <?php else: ?>

        <?= $p['stock'] ?>

    <?php endif; ?>
</td>
            <td>
                <button class="btn btn-primary" onclick='editProduct(<?= json_encode($p) ?>)'>Sửa</button>
                <form method="POST" style="display:inline" 
                      onsubmit="return confirm('Xóa sản phẩm <?= htmlspecialchars($p['name']) ?>?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<script>
function showForm() { 
    document.getElementById('product-form').style.display = 'block'; 
    document.getElementById('form-title').textContent = 'Thêm sản phẩm';
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('f-code').value = '';
    document.getElementById('f-name').value = '';
    document.getElementById('f-price').value = '';
    document.getElementById('f-discount').value = '0';
    document.getElementById('f-stock').value = '0';
    document.getElementById('f-sizes').value = 'S,M,L,XL';
    document.getElementById('f-colors').value = 'Đen,Trắng,Kem';
    document.getElementById('f-images').value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hideForm() { 
    document.getElementById('product-form').style.display = 'none'; 
}

function editProduct(p) {
    showForm();
    document.getElementById('form-title').textContent = 'Sửa sản phẩm';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = p.id;
    document.getElementById('f-code').value = p.product_code || '';
    document.getElementById('f-name').value = p.name || '';
    document.getElementById('f-price').value = p.price || 0;
    document.getElementById('f-discount').value =
    p.discount_percent || 0;
    document.getElementById('f-cat').value = p.category_id || '';
    document.getElementById('f-stock').value = p.stock || 0;
    
    // Xử lý JSON an toàn - tránh lỗi khi dữ liệu null hoặc undefined
    let sizes = [];
    let colors = [];
    let images = [];
    
    try {
        if (p.sizes) sizes = JSON.parse(p.sizes);
    } catch(e) { sizes = []; }
    
    try {
        if (p.colors) colors = JSON.parse(p.colors);
    } catch(e) { colors = []; }
    
    try {
        if (p.images) images = JSON.parse(p.images);
    } catch(e) { images = []; }
    
    document.getElementById('f-sizes').value = Array.isArray(sizes) ? sizes.join(',') : '';
    document.getElementById('f-colors').value = Array.isArray(colors) ? colors.join(',') : '';
    document.getElementById('f-images').value = Array.isArray(images) ? images.join('\n') : '';
}
</script>

<?php require_once '../includes/footer.php'; ?>