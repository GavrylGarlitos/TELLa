<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];

// Handle removing an item from the cart
if (isset($_POST['remove_item'])) {
    $indexToRemove = (int)$_POST['remove_item'];
    if (isset($_SESSION['cart'][$indexToRemove])) {
        unset($_SESSION['cart'][$indexToRemove]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle individual item checkout from cart
if (isset($_POST['checkout_item_index'])) {
    $indexToCheckout = (int)$_POST['checkout_item_index'];
    if (isset($_SESSION['cart'][$indexToCheckout])) {
        $itemToCheckout = $_SESSION['cart'][$indexToCheckout];

        $_SESSION['buy_now'] = [
            "product_name_display" => $itemToCheckout['product_name_display'],
            "product_key"          => $itemToCheckout['product'],
            "size"                 => $itemToCheckout['size'],
            "quantity"             => $itemToCheckout['quantity'],
            "price"                => $itemToCheckout['price'],
            "images"               => $itemToCheckout['all_item_images'] ?? [$itemToCheckout['display_image'] ?? "images/placeholder.jpg"]
        ];

        // Debug: Uncomment below to verify item
        // var_dump($_SESSION['buy_now']);
        // exit;

        header("Location: buy.php");
        exit();
    } else {
        echo "<p style='color:red;'>Invalid item selected for checkout.</p>";
    }
}

// Handle checkout all items - FIXED VERSION
if (isset($_POST['checkout_all_items'])) {
    // Re-fetch cart to ensure we have the latest data
    $cart = $_SESSION['cart'] ?? [];
    
    if (!empty($cart)) {
        // Store the cart items for checkout
        $_SESSION['checkout_all_items'] = $cart;
        
        // Calculate total amount
        $total_amount = 0;
        foreach ($cart as $item) {
            $total_amount += $item['price'];
        }
        $_SESSION['checkout_total'] = $total_amount;
        
        // Debug: Uncomment to verify data
        // echo "<pre>";
        // echo "Cart items: " . count($cart) . "\n";
        // echo "Total amount: " . $total_amount . "\n";
        // echo "Session checkout_all_items: " . (isset($_SESSION['checkout_all_items']) ? 'SET' : 'NOT SET') . "\n";
        // echo "</pre>";
        // exit;

        header("Location: buy.php?from_cart=1");
        exit();
    } else {
        echo "<p style='color:red;'>Your cart is empty.</p>";
    }
}

// Re-fetch cart after any operations
$cart = $_SESSION['cart'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h2>Your Cart</h2>
        <input type="text" class="search-input" placeholder="Search in your cart..." onkeyup="filterCart(this.value)">

        <?php if (empty($cart)): ?>
            <p class="no-items">You do not have items in your cart for now.</p>
        <?php endif; ?>

        <div id="cart-list">
            <?php
            $overall_total = 0;
            foreach ($cart as $index => $item):
                $product_key_in_cart = $item['product'] ?? '';
                $item_display_name = $item['product_name_display'] ?? ucfirst($product_key_in_cart);
                $itemImage = $item['image_path'] ?? "image/placeholder.jpg";
                if (!file_exists($itemImage)) {
                    $itemImage = "image/placeholder.jpg";
                }
                $overall_total += $item['price'];
            ?>
                <div class="cart-item" data-name="<?= htmlspecialchars(strtolower($item_display_name)) ?>">
                    <img src="<?= htmlspecialchars($itemImage) ?>" alt="<?= htmlspecialchars($item_display_name) ?>">
                    <div class="cart-item-info">
                        <a href="product_detail.php?product=<?= htmlspecialchars($product_key_in_cart) ?>"><strong><?= htmlspecialchars(ucfirst($item_display_name)) ?></strong></a><br>
                        Size: <?= htmlspecialchars($item['size']) ?> | Quantity: <?= htmlspecialchars($item['quantity']) ?><br>
                        Price: ₱<?= number_format($item['price'], 2) ?>
                    </div>
                    <div class="cart-item-buttons">
                        <form method="POST" action="cart.php" class="inline-action-form" style="all: unset;">
                            <input type="hidden" name="checkout_item_index" value="<?= $index ?>">
                            <button type="submit" class="button checkout-item-button">Checkout This Item</button>
                        </form>
                        <form method="POST" class="remove-form" onsubmit="return confirm('Are you sure you want to remove this item from your cart?')" style="all: unset;">
                            <input type="hidden" name="remove_item" value="<?= $index ?>">
                            <button type="submit" class="button remove-button">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($cart)): ?>
            <div class="cart-total">
                Overall Total: ₱<?= number_format($overall_total, 2) ?>
            </div>
        <?php endif; ?>

<div class="button-group" style="display: flex; justify-content: center; align-items: center; gap: 1.5rem; margin-top: 2.5rem;">
    <div style="min-width: max-content; max-width: 220px; width: 100%;">
        <a href="home.php" class="button"
           style="display: flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; width: 100%; text-align: center;">
            Back to Home Page
        </a>
    </div>
    <?php if (!empty($cart)): ?>
        <div style="min-width: max-content; max-width: 220px; width: 100%;">
            <form method="POST" action="cart.php" style="margin: 0; padding: 0;">
                <button type="submit" class="button"
                        style="display: flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; width: 100%; text-align: center;">
                    Checkout All Items
                </button>
                <input type="hidden" name="checkout_all_items" value="1">
            </form>
        </div>
    <?php endif; ?>
</div>







    </main>

    <footer>
        <p>Need help? Contact us at: store-email@gmail.com</p>
    </footer>

    <script>
        function filterCart(query) {
            const items = document.querySelectorAll('.cart-item');
            const lowerQuery = query.toLowerCase();
            items.forEach(item => {
                item.style.display = item.getAttribute('data-name').includes(lowerQuery) ? 'flex' : 'none';
            });
        }

        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>