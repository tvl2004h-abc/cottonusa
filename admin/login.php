<?php
session_start();
require_once '../config/db.php';   // Từ admin lên 1 cấp rồi vào config

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($_POST['password'], $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        header('Location: index.php');
        exit;
    }
    $error = 'Sai tài khoản hoặc mật khẩu!';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin — Cotton USA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f5f5f5; 
               display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; padding: 40px; border-radius: 12px; 
                box-shadow: 0 2px 16px rgba(0,0,0,.1); width: 360px; }
        h1 { font-size: 22px; margin-bottom: 8px; }
        p.sub { color: #666; font-size: 14px; margin-bottom: 28px; }
        label { display: block; font-size: 13px; color: #444; margin-bottom: 4px; }
        input { width: 100%; padding: 10px 14px; border: 1px solid #ddd; 
                border-radius: 8px; font-size: 15px; margin-bottom: 16px; }
        button { width: 100%; padding: 12px; background: #222; color: #fff; 
                 border: none; border-radius: 8px; font-size: 15px; cursor: pointer; }
        .error { background: #fef2f2; color: #b91c1c; padding: 10px 14px; 
                 border-radius: 8px; font-size: 14px; margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="card">
    <h1>Cotton USA Admin</h1>
    <p class="sub">Đăng nhập để quản lý cửa hàng</p>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>Tài khoản</label>
        <input type="text" name="username" required autofocus>
        <label>Mật khẩu</label>
        <input type="password" name="password" required>
        <button type="submit">Đăng nhập</button>
    </form>
</div>
</body>
</html>
