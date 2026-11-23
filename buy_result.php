<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die("Database connection failed: " . $mysqli->connect_error);

// Get logged-in user's ID
$userStmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
$userStmt->bind_param("s", $_SESSION['username']);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['id'] ?? null;

if (!$user_id) {
    die("User not found.");
}

// Get data from POST
$product_name = $_POST['product_name'] ?? '';
$quantity = intval($_POST['quantity'] ?? 1);
$total_price = floatval($_POST['total_price'] ?? 0);
$payment_method = $_POST['payment'] ?? '';
$shipping_method = $_POST['shipping_options'] ?? '';
$is_multi_checkout = $_POST['is_multi_checkout'] ?? '0';

// Estimate days based on shipping_method
$estimated_days = 7; // default
if ($shipping_method == "PickUp") $estimated_days = 5;
elseif ($shipping_method == "Order&Pick") $estimated_days = 1;
elseif ($shipping_method == "priority_shipping") $estimated_days = 3;
elseif ($shipping_method == "express_shipping") $estimated_days = 2;

if ($is_multi_checkout === '1') {
    // Handle multiple items checkout
    $cartItems = $_SESSION['checkout_all_items'] ?? [];
    
    if (empty($cartItems)) {
        die("No items found for checkout.");
    }
    
    // Process each item in the cart
    foreach ($cartItems as $item) {
        $item_product_name = $item['product_name_display'] ?? $item['product'];
        
        // Find product_id based on product_name
        $productStmt = $mysqli->prepare("SELECT id FROM products WHERE name=?");
        $productStmt->bind_param("s", $item_product_name);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $product = $productResult->fetch_assoc();
        $product_id = $product['id'] ?? null;
        
        if (!$product_id) {
            // If product not found by display name, try with the product key
            $productStmt = $mysqli->prepare("SELECT id FROM products WHERE name=?");
            $productStmt->bind_param("s", $item['product']);
            $productStmt->execute();
            $productResult = $productStmt->get_result();
            $product = $productResult->fetch_assoc();
            $product_id = $product['id'] ?? null;
        }
        
        if (!$product_id) {
            continue; // Skip this item if product not found
        }
        
        // Insert order for this item
        $insertStmt = $mysqli->prepare("
            INSERT INTO orders 
            (user_id, product_id, quantity, total_price, payment_method, payment_status, shipping_method, estimated_days, shipping_status, ordered_date)
            VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, 'Preparing', NOW())
        ");
        
        $insertStmt->bind_param("iiissii", 
            $user_id, 
            $product_id, 
            $item['quantity'], 
            $item['price'], 
            $payment_method,
            $shipping_method,
            $estimated_days
        );
        
        $insertStmt->execute();
    }
    
    // Clear cart and checkout session data
    unset($_SESSION['cart']);
    unset($_SESSION['checkout_all_items']);
    unset($_SESSION['checkout_total']);
    
} else {
    // Handle single item checkout
    // Find product_id based on product_name
    $productStmt = $mysqli->prepare("SELECT id FROM products WHERE name=?");
    $productStmt->bind_param("s", $product_name);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    $product = $productResult->fetch_assoc();
    $product_id = $product['id'] ?? null;

    if (!$product_id) {
        die("Product not found.");
    }

    // Insert into orders
    $insertStmt = $mysqli->prepare("
        INSERT INTO orders 
        (user_id, product_id, quantity, total_price, payment_method, payment_status, shipping_method, estimated_days, shipping_status, ordered_date)
        VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, 'Preparing', NOW())
    ");

    $insertStmt->bind_param("iiissii", 
        $user_id, 
        $product_id, 
        $quantity, 
        $total_price, 
        $payment_method,
        $shipping_method,
        $estimated_days
    );

    $insertStmt->execute();
}

// Clear buy_now so user cannot re-buy accidentally
unset($_SESSION['buy_now']);

// Prepare estimated delivery date for display
$estimated_delivery = date('F j, Y', strtotime("+$estimated_days days"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Confirmation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<main class="container">
    <h2>Thank You for Your Purchase!</h2>
    <p>Your order has been placed successfully.</p>
    <p>Estimated arrival of product is: <strong><?= htmlspecialchars($estimated_delivery) ?></strong></p>
    <div class="button-group">
        <a href="home.php" class="button">Back to Home Page</a>
    </div>
</main>
<footer>
    <p>Need help? Contact us at: store-email@gmail.com</p>
</footer>
</body>
</html>