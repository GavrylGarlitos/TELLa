<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die("Access denied.");
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);

// Handle POST update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $new_days = intval($_POST['estimated_days']);
    $order_note = trim($_POST['order_notes']);
    $stmt = $mysqli->prepare("UPDATE orders SET estimated_days=?, order_notes=? WHERE order_id=?");
    $stmt->bind_param('isi', $new_days, $order_note, $order_id);
    $stmt->execute();
    $stmt->close();
    $message = "Order #$order_id updated successfully.";
}

// Fetch all orders (or only for a specific user if user_id is set)
$where = '';
$params = [];
$types = '';
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $where = 'WHERE orders.user_id = ?';
    $params[] = intval($_GET['user_id']);
    $types = 'i';
}
$sql = "
    SELECT orders.*, users.username, users.email, products.name AS product_name 
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN products ON orders.product_id = products.id
    $where
    ORDER BY orders.ordered_date DESC
";
if ($where) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $mysqli->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>All Orders - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Navigation bar styles */
        .admin-header-bar {
            background: #5D4037;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 4px solid #D2B48C;
        }
        .admin-header-bar nav {
            display: flex;
            gap: 25px;
        }
        .admin-header-bar nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: .2s;
        }
        .admin-header-bar nav a:hover {
            color: #D2B48C;
        }
        
        /* Container and table styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: visible;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .orders-table th {
            background: #5D4037;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .orders-table tr:hover {
            background: #f8f9fa;
        }
        
        .success-message {
            background: #DFF2BF;
            color: #4F8A10;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #4F8A10;
        }
        
        .customer-info {
            line-height: 1.4;
        }
        
        .customer-email {
            font-size: 0.9em;
            color: #666;
        }
        
        .notes-cell {
            max-width: 200px;
            word-wrap: break-word;
        }

        /* Actions button and menu styles - matching admin.php exactly */
        .actions-btn {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            font-size: 16px;
            cursor: pointer;
            color: #6c757d;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            min-width: 40px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .actions-btn:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            color: #495057;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .actions-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .actions-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 4px;
            height: 4px;
            background: currentColor;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 -8px 0 currentColor, 0 8px 0 currentColor;
        }
        .actions-cell {
            position: relative;
            text-align: center;
            overflow: visible;
        }
        .actions-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 4px);
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            z-index: 1000;
            min-width: 140px;
            white-space: nowrap;
            overflow: hidden;
        }
        .actions-menu.show {
            display: block;
        }
        .actions-menu::before {
            content: '';
            position: absolute;
            top: -8px;
            right: 16px;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid #fff;
            filter: drop-shadow(0 -2px 2px rgba(0,0,0,0.1));
        }
        .actions-menu .menu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 12px 16px;
            border: none;
            background: none;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            border-bottom: 1px solid #f8f9fa;
            text-decoration: none;
        }
        .actions-menu .menu-item:last-child {
            border-bottom: none;
        }
        .actions-menu .menu-item:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #212529;
            transform: translateX(2px);
        }
        .actions-menu .menu-item.manage-btn {
            color: #28a745;
        }
        .actions-menu .menu-item.manage-btn:hover {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #1e7e34;
        }
        .actions-menu .menu-item.manage-btn::before {
            content: '⚙️';
            font-size: 14px;
        }
    </style>
</head>
<body>
<header class="admin-header-bar">
    <div>Welcome "<?=htmlspecialchars($_SESSION['username'])?>"</div>
    <nav>
        <a href="admin.php">User Management</a>
        <a href="product_management.php">Product Management</a>
        <a href="orders.php">Order Management</a>
        <a href="customers.php">Customer Management</a>
        <a href="logout.php" style="color:#FFCDD2">Logout</a>
    </nav>
</header>

<main class="container">
    <h2>All Orders</h2>
    
    <?php if (isset($message)): ?>
        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Shipping</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Ordered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($o = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($o['order_id']) ?></td>
                <td class="customer-info">
                    <div><?= htmlspecialchars($o['username']) ?></div>
                    <div class="customer-email"><?= htmlspecialchars($o['email']) ?></div>
                </td>
                <td><?= htmlspecialchars($o['product_name']) ?></td>
                <td><?= htmlspecialchars($o['quantity']) ?></td>
                <td>₱<?= number_format($o['total_price'],2) ?></td>
                <td><?= htmlspecialchars($o['shipping_method']) ?> (<?= $o['estimated_days'] ?> days)</td>
                <td><?= htmlspecialchars($o['shipping_status']) ?></td>
                <td class="notes-cell"><?= nl2br(htmlspecialchars($o['order_notes'])) ?></td>
                <td><?= htmlspecialchars($o['ordered_date']) ?></td>
                <td class="actions-cell">
                    <button class="actions-btn" title="More actions"></button>
                    <div class="actions-menu">
                        <a href="manage_order.php?order_id=<?= $o['order_id'] ?>" class="menu-item manage-btn">Manage</a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<script>
// Actions dropdown functionality
document.querySelectorAll('.actions-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Close all other dropdowns
        document.querySelectorAll('.actions-menu').forEach(menu => {
            if (menu !== this.nextElementSibling) {
                menu.classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        const menu = this.nextElementSibling;
        if (menu.classList.contains('show')) {
            menu.classList.remove('show');
        } else {
            menu.classList.add('show');
        }
    });
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.actions-cell')) {
        document.querySelectorAll('.actions-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});
</script>
</body>
</html>