<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die("Access denied.");
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);
$order_id = intval($_GET['order_id'] ?? 0);

// Handle POST updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $new_days = intval($_POST['estimated_days']);
    $order_note = trim($_POST['order_notes']);
    $shipping_status = trim($_POST['shipping_status']);
    
    $stmt = $mysqli->prepare("UPDATE orders SET estimated_days=?, order_notes=?, shipping_status=? WHERE order_id=?");
    $stmt->bind_param('issi', $new_days, $order_note, $shipping_status, $order_id);
    $stmt->execute();
    $stmt->close();
    $message = "Order updated successfully.";
}

// Fetch this order details
$stmt = $mysqli->prepare("
    SELECT orders.*, users.username, users.email, products.name AS product_name
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN products ON orders.product_id = products.id
    WHERE orders.order_id = ?
");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
if (!$order) die("Order not found.");

// Define status options
$status_options = [
    'preparing' => 'Preparing',
    'shipping' => 'Shipping',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Order #<?= $order_id ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Navigation bar styles - matching orders.php */
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
        
        /* Container styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Success message styling */
        .success-message {
            background: #DFF2BF;
            color: #4F8A10;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #4F8A10;
        }
        
        /* Main content layout */
        .manage-order-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .order-details, .update-form {
            flex: 1;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .order-details h3, .update-form h3 {
            color: #5D4037;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #D2B48C;
            padding-bottom: 10px;
        }
        
        .order-details p {
            margin: 10px 0;
            line-height: 1.5;
        }
        
        .order-details strong {
            color: #5D4037;
            display: inline-block;
            min-width: 100px;
        }
        
        /* Status badge styling */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-preparing {
            background: #FFF3E0;
            color: #F57C00;
        }
        
        .status-shipping {
            background: #E3F2FD;
            color: #1976D2;
        }
        
        .status-out_for_delivery {
            background: #F3E5F5;
            color: #7B1FA2;
        }
        
        .status-delivered {
            background: #E8F5E8;
            color: #388E3C;
        }
        
        /* Form styling */
        .update-form form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .update-form label {
            font-weight: 600;
            color: #5D4037;
            margin-bottom: 5px;
        }
        
        .update-form input, .update-form textarea, .update-form select {
            padding: 10px;
            border: 1px solid #D7CCC8;
            border-radius: 4px;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .update-form input:focus, .update-form textarea:focus, .update-form select:focus {
            outline: none;
            border-color: #5D4037;
            box-shadow: 0 0 0 2px rgba(93, 64, 55, 0.1);
        }
        
        .update-form textarea {
            width: 100%;
            height: 80px;
            resize: vertical;
            font-family: inherit;
        }
        
        .update-form select {
            width: 100%;
            background: #fff;
            cursor: pointer;
        }
        
        /* Button styling */
        .button {
            background: #5D4037;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
        }
        
        .button:hover {
            background: #4A2C20;
        }
        
        .button.secondary {
            background: #ccc;
            color: #333;
        }
        
        .button.secondary:hover {
            background: #bbb;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .manage-order-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .admin-header-bar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-header-bar nav {
                flex-wrap: wrap;
                justify-content: center;
            }
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
    <h2>Manage Order #<?= htmlspecialchars($order_id) ?></h2>
    
    <?php if (isset($message)): ?>
    <div class="success-message">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <div class="manage-order-container">
        <div class="order-details">
            <h3><i class="fas fa-info-circle"></i> Order Details</h3>
            <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
            <p><strong>Product:</strong> <?= htmlspecialchars($order['product_name']) ?></p>
            <p><strong>Quantity:</strong> <?= htmlspecialchars($order['quantity']) ?></p>
            <p><strong>Total:</strong> ₱<?= number_format($order['total_price'],2) ?></p>
            <p><strong>Shipping:</strong> <?= htmlspecialchars($order['shipping_method']) ?> (<?= $order['estimated_days'] ?> days)</p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-<?= str_replace(' ', '_', strtolower($order['shipping_status'])) ?>">
                    <?= htmlspecialchars($order['shipping_status']) ?>
                </span>
            </p>
            <p><strong>Ordered Date:</strong> <?= htmlspecialchars($order['ordered_date']) ?></p>
            <p><strong>Notes:</strong></p>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 5px;">
                <?= nl2br(htmlspecialchars($order['order_notes'])) ?: '<em>No notes</em>' ?>
            </div>
        </div>
        
        <div class="update-form">
            <h3><i class="fas fa-edit"></i> Update Order</h3>
            <form method="post">
                <div>
                    <label for="shipping_status">Shipping Status:</label>
                    <select id="shipping_status" name="shipping_status" required>
                        <?php foreach ($status_options as $value => $label): ?>
                            <option value="<?= htmlspecialchars($label) ?>" 
                                    <?= $order['shipping_status'] === $label ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="estimated_days">Estimated Delivery Days:</label>
                    <input type="number" id="estimated_days" name="estimated_days" min="1" value="<?= $order['estimated_days'] ?? 1 ?>" required>
                </div>
                
                <div>
                    <label for="order_notes">Message to Customer:</label>
                    <textarea id="order_notes" name="order_notes" placeholder="E.g. Delay due to weather conditions. We apologize for any inconvenience."><?= htmlspecialchars($order['order_notes']) ?></textarea>
                </div>
                
                <button type="submit" name="update_order" class="button">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                
                <a href="orders.php" class="button secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </form>
        </div>
    </div>
</main>
</body>
</html>