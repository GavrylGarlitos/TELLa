<?php
session_start();
if (!isset($_SESSION['username'])) die('Please log in.');
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die('Database connection failed: '.$mysqli->connect_error);

// Get user_id based on username in session (safer than joining on username directly)
$userStmt = $mysqli->prepare('SELECT id FROM users WHERE username=?');
$userStmt->bind_param('s', $_SESSION['username']);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userRow = $userResult->fetch_assoc();
$user_id = $userRow ? $userRow['id'] : null;
if (!$user_id) die('User not found.');

// Fetch orders for this user
$stmt = $mysqli->prepare(
    'SELECT orders.*, products.name AS product_name 
     FROM orders 
     JOIN products ON orders.product_id = products.id 
     WHERE orders.user_id = ?
     ORDER BY orders.ordered_date DESC'
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Delivery Status</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Header styles matching orders.php */
        .header-bar {
            background: #5D4037;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 4px solid #D2B48C;
        }
        .header-bar nav {
            display: flex;
            gap: 25px;
        }
        .header-bar nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: .2s;
        }
        .header-bar nav a:hover {
            color: #D2B48C;
        }
        
        /* Container and table styles */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .delivery-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .delivery-header h2 {
            color: #5D4037;
            margin-bottom: 10px;
        }
        
        .delivery-header p {
            color: #666;
            font-size: 16px;
        }
        
        .deliveries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .deliveries-table th {
            background: #5D4037;
            color: #fff;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .deliveries-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .deliveries-table tr:hover {
            background: #f8f9fa;
        }
        
        .deliveries-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #FFF3E0;
            color: #F57C00;
        }
        
        .status-shipped {
            background: #E3F2FD;
            color: #1976D2;
        }
        
        .status-delivered {
            background: #E8F5E8;
            color: #388E3C;
        }
        
        .status-cancelled {
            background: #FFEBEE;
            color: #D32F2F;
        }
        
        /* Notes styling */
        .notes-cell {
            max-width: 250px;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .notes-content {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            border-left: 3px solid #5D4037;
            font-style: italic;
        }
        
        .no-notes {
            color: #999;
            font-style: italic;
        }
        
        /* Days left styling */
        .days-left {
            font-weight: 500;
        }
        
        .days-left.overdue {
            color: #D32F2F;
            font-weight: 600;
        }
        
        .days-left.soon {
            color: #F57C00;
            font-weight: 600;
        }
        
        .days-left.normal {
            color: #388E3C;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 48px;
            color: #D7CCC8;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #5D4037;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
        }
        
        /* Order ID styling */
        .order-id {
            font-weight: 600;
            color: #5D4037;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .deliveries-table {
                font-size: 14px;
            }
            
            .deliveries-table th,
            .deliveries-table td {
                padding: 10px 8px;
            }
            
            .notes-cell {
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container">
    <div class="delivery-header">
        <h2><i class="fas fa-truck"></i> My Delivery Status</h2>
        <p>Track your orders and delivery updates</p>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Orders Yet</h3>
            <p>You haven't placed any orders yet. Start shopping to see your deliveries here!</p>
        </div>
    <?php else: ?>
        <table class="deliveries-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Expected Delivery</th>
                    <th>Days Left</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): 
                    $expected_delivery_date = '';
                    $days_left = '';
                    $days_left_class = 'normal';
                    
                    if ($o['ordered_date'] && $o['estimated_days']) {
                        $expectedDate = (new DateTime($o['ordered_date']))->modify("+{$o['estimated_days']} days");
                        $expected_delivery_date = $expectedDate->format('M d, Y');
                        $today = new DateTime();
                        $interval = $today->diff($expectedDate);
                        
                        if ($interval->invert) {
                            $days_left = "Past due by {$interval->days} day(s)";
                            $days_left_class = 'overdue';
                        } else {
                            $days_left = "{$interval->days} day(s) left";
                            $days_left_class = $interval->days <= 2 ? 'soon' : 'normal';
                        }
                    }
                    
                    // Determine status badge class
                    $status_class = 'status-pending';
                    $status_lower = strtolower($o['shipping_status']);
                    if (strpos($status_lower, 'shipped') !== false) $status_class = 'status-shipped';
                    if (strpos($status_lower, 'delivered') !== false) $status_class = 'status-delivered';
                    if (strpos($status_lower, 'cancelled') !== false) $status_class = 'status-cancelled';
                ?>
                <tr>
                    <td class="order-id">#<?= htmlspecialchars($o['order_id']) ?></td>
                    <td><?= htmlspecialchars($o['product_name']) ?></td>
                    <td><?= htmlspecialchars($o['quantity']) ?></td>
                    <td>₱<?= number_format($o['total_price'], 2) ?></td>
                    <td>
                        <span class="status-badge <?= $status_class ?>">
                            <?= htmlspecialchars($o['shipping_status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($expected_delivery_date) ?></td>
                    <td class="days-left <?= $days_left_class ?>">
                        <?= $days_left ?>
                    </td>
                    <td class="notes-cell">
                        <?php if (!empty($o['order_notes'])): ?>
                            <div class="notes-content">
                                <?= nl2br(htmlspecialchars($o['order_notes'])) ?>
                            </div>
                        <?php else: ?>
                            <span class="no-notes">No updates</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>