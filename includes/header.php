<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: /cottonusa/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin — Cotton USA</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            min-height: 100vh; 
            background: #f5f7fa; 
        }
        
        /* Sidebar màu trắng */
        .sidebar { 
            width: 260px; 
            background: #ffffff; 
            color: #1a1a2e; 
            padding: 24px 0; 
            flex-shrink: 0; 
            box-shadow: 2px 0 12px rgba(0,0,0,0.05);
            border-right: 1px solid #e5e5e5;
        }
        
        .sidebar .logo { 
            text-align: center; 
            padding: 0 20px 20px; 
            border-bottom: 1px solid #eee; 
            margin-bottom: 20px; 
        }
        
        .sidebar .logo img { 
            max-width: 160px; 
            height: auto; 
            display: block; 
            margin: 0 auto; 
        }
        
        .sidebar a { 
            display: block; 
            padding: 12px 24px; 
            color: #333; 
            text-decoration: none; 
            font-size: 14px; 
            font-weight: 500;
            transition: all 0.2s;
            margin: 4px 12px;
            border-radius: 10px;
        }
        
        .sidebar a:hover { 
            background: #f5f5f5; 
            color: #c62828; 
        }
        
        .sidebar a.active { 
            background: linear-gradient(135deg, #c62828, #ad1457); 
            color: #fff; 
            box-shadow: 0 2px 8px rgba(198,40,40,0.2);
        }
        
        /* Main content - background trắng, chữ đen */
        .main { 
            flex: 1; 
            padding: 28px 32px; 
            background: #ffffff;
            min-height: 100vh;
        }
        
        .main h1 { 
            font-size: 24px; 
            margin-bottom: 24px; 
            color: #1a1a2e;
            font-weight: 600;
        }
        
        /* Card stats màu trắng với shadow nhẹ */
        .card-stats { 
            display: grid; 
            grid-template-columns: repeat(4,1fr); 
            gap: 20px; 
            margin-bottom: 32px; 
        }
        
        .stat { 
            background: #ffffff; 
            padding: 24px; 
            border-radius: 16px;
            border: 1px solid #e5e5e5;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        
        .stat .val { 
            font-size: 28px; 
            font-weight: 700; 
            margin-top: 8px; 
            color: #1a1a2e;
        }
        
        .stat div:first-child {
            color: #888;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Bảng màu trắng */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #ffffff; 
            border-radius: 16px; 
            overflow: hidden; 
            border: 1px solid #e5e5e5;
        }
        
        th { 
            padding: 16px; 
            text-align: left; 
            font-size: 13px; 
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
            border-bottom: 1px solid #e5e5e5;
        }
        
        td { 
            padding: 16px; 
            text-align: left; 
            font-size: 14px; 
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        
        tr:hover td {
            background: #fafafa;
        }
        
        /* Buttons */
        .btn { 
            padding: 8px 16px; 
            border-radius: 8px; 
            border: none; 
            cursor: pointer; 
            font-size: 13px; 
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, #c62828, #ad1457); 
            color: #fff; 
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(198,40,40,0.3);
        }
        
        .btn-danger { 
            background: #fee2e2;
            color: #dc2626; 
        }
        
        .btn-danger:hover {
            background: #fecaca;
        }
        
        .btn-success { 
            background: #dcfce7;
            color: #16a34a; 
        }
        
        .btn-success:hover {
            background: #bbf7d0;
        }
        
        /* Badges */
        .badge { 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 500; 
            display: inline-block;
        }
        
        .badge-pending { 
            background: #fef3c7; 
            color: #92400e; 
        }
        
        .badge-confirmed { 
            background: #dbeafe; 
            color: #1e40af; 
        }
        
        .badge-delivered { 
            background: #dcfce7; 
            color: #166534; 
        }
        
        .badge-cancelled { 
            background: #fee2e2; 
            color: #991b1b; 
        }
        
        /* Card chung */
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e5e5e5;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-header h2 {
            color: #1a1a2e;
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Form elements */
        input, select, textarea {
            background: #ffffff;
            border: 1px solid #ddd;
            color: #333;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #c62828;
            box-shadow: 0 0 0 3px rgba(198,40,40,0.1);
        }
        
        label {
            color: #555;
            font-size: 13px;
            font-weight: 500;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            background: #f5f5f5;
            color: #333;
            border: 1px solid #e5e5e5;
        }
        
        .pagination a:hover {
            background: #c62828;
            color: #fff;
            border-color: #c62828;
        }
        
        .pagination .active {
            background: linear-gradient(135deg, #c62828, #ad1457);
            color: #fff;
            border: none;
        }
        
        /* Product form */
        #product-form {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 16px;
            margin-bottom: 24px;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .card-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                display: none;
            }
            .main {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <img src="/cottonusa/images/logo.avif" alt="Cotton USA">
    </div>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📊 Tổng quan</a>

<a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">👕 Sản phẩm</a>

<a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">📦 Đơn hàng</a>

<a href="revenue.php" class="<?= basename($_SERVER['PHP_SELF']) == 'revenue.php' ? 'active' : '' ?>">💰 Thống kê doanh thu</a>

<a href="logout.php">🚪 Đăng xuất</a>
</div>
<div class="main">