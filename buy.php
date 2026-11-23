<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$checkoutAll = isset($_GET['from_cart']) && $_GET['from_cart'] == 1;
$product_details_from_session = null;

if ($checkoutAll) {
    $cartItems = $_SESSION['checkout_all_items'] ?? [];

    if (empty($cartItems)) {
        header("Location: home.php");
        exit();
    }

    $productName = "Multiple Items (" . count($cartItems) . " items)";
    $item_total_price = $_SESSION['checkout_total'] ?? 0;
    $quantity = array_sum(array_column($cartItems, 'quantity'));
    
    // Improved image collection logic for cart checkout
    $productImages = [];
    foreach ($cartItems as $item) {
        $product_key = $item['product'] ?? $item['product_key'] ?? '';
        $product_name = $item['product_name_display'] ?? '';
        
        // First, try to use the image_path from the cart item
        if (!empty($item['image_path']) && file_exists($item['image_path'])) {
            $productImages[] = $item['image_path'];
        } else {
            // Generate image paths based on product key
            $image_prefix = '';
            if (!empty($product_key)) {
                $image_prefix = strtolower($product_key);
            } elseif (!empty($product_name)) {
                $image_prefix = strtolower(str_replace(' ', '_', $product_name));
            }
            
            if (!empty($image_prefix)) {
                // Try multiple naming patterns
                $naming_patterns = [
                    'images/' . $image_prefix . '_1.jpg',
                    'images/' . $image_prefix . '1.jpg',
                    'images/' . $image_prefix . '.jpg',
                    'image/' . $image_prefix . '_1.jpg',
                    'image/' . $image_prefix . '1.jpg',
                    'image/' . $image_prefix . '.jpg'
                ];
                
                foreach ($naming_patterns as $pattern) {
                    if (file_exists($pattern)) {
                        $productImages[] = $pattern;
                        break; // Only add one image per item to avoid duplicates
                    }
                }
            }
        }
    }
    
    // If no images found, try to collect all available images for all products
    if (empty($productImages)) {
        foreach ($cartItems as $item) {
            $product_key = $item['product'] ?? $item['product_key'] ?? '';
            $product_name = $item['product_name_display'] ?? '';
            
            $image_prefix = '';
            if (!empty($product_key)) {
                $image_prefix = strtolower($product_key);
            } elseif (!empty($product_name)) {
                $image_prefix = strtolower(str_replace(' ', '_', $product_name));
            }
            
            if (!empty($image_prefix)) {
                // Try to find multiple images for each product
                for ($i = 1; $i <= 3; $i++) {
                    $possible_paths = [
                        "images/" . $image_prefix . "_" . $i . ".jpg",
                        "images/" . $image_prefix . $i . ".jpg",
                        "image/" . $image_prefix . "_" . $i . ".jpg",
                        "image/" . $image_prefix . $i . ".jpg"
                    ];
                    
                    foreach ($possible_paths as $path) {
                        if (file_exists($path)) {
                            $productImages[] = $path;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    // Remove duplicates while preserving order
    $productImages = array_unique($productImages);
    
} else {
    $product_details_from_session = $_SESSION['buy_now'] ?? null;

    if (!$product_details_from_session) {
        header("Location: home.php");
        exit();
    }

    $productName = $product_details_from_session['product_name_display'] ?? 'Unknown Product';
    $item_total_price = $product_details_from_session['price'];
    $quantity = $product_details_from_session['quantity'];
    $productImages = $product_details_from_session['images'] ?? [];
}

// Defaults
$shipping_fee = 0;
$overall_total_price = $item_total_price + $shipping_fee;

// Fallback logic for images if the 'images' array wasn't passed or is empty.
if (empty($productImages)) {
    if (!$checkoutAll && $product_details_from_session) {
        $product_image_prefix = $product_details_from_session['image_prefix'] ?? strtolower($product_details_from_session['product_key'] ?? 'unknown');
        for ($i = 1; $i <= 3; $i++) {
            $possible_paths = [
                "images/" . $product_image_prefix . "_" . $i . ".jpg",
                "images/" . $product_image_prefix . $i . ".jpg",
                "image/" . $product_image_prefix . "_" . $i . ".jpg",
                "image/" . $product_image_prefix . $i . ".jpg"
            ];
            
            foreach ($possible_paths as $path) {
                if (file_exists($path)) {
                    $productImages[] = $path;
                }
            }
        }
    }
}

// If still no images are found, use placeholder images.
if (empty($productImages)) {
    $productImages = ['images/placeholder.jpg'];
}

// Debug: Log the images being used (you can remove this later)
error_log("Checkout images: " . print_r($productImages, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Product - <?= htmlspecialchars($productName) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .enhanced-description-box {
            background: linear-gradient(135deg, #687A61 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid #e1e8ed;
            min-height: 400px;
            position: relative;
            overflow: hidden;
        }
       
        .enhanced-description-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
       
        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }
       
        .enhanced-description-box h3 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
       
        .enhanced-description-box p {
            color: #34495e;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 25px;
            text-align: justify;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }
       
        .enhanced-form-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }
       
        .enhanced-form-group {
            margin-bottom: 20px;
        }
       
        .enhanced-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
       
        .enhanced-size-select, .enhanced-quantity-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
       
        .enhanced-size-select:focus, .enhanced-quantity-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
            transform: translateY(-2px);
        }
       
        .enhanced-price-display {
            background: linear-gradient(45deg, #6D4C41 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
       
        .enhanced-button-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 25px;
        }
       
        .enhanced-button {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 120px;
            text-align: center;
        }
       
        .enhanced-button.primary {
            background: linear-gradient(45deg, #6D4C41 100%);
            color: white;
        }
       
        .enhanced-button.secondary {
            background: linear-gradient(45deg, #6D4C41 100%);
            color: white;
        }
       
        .enhanced-button.tertiary {
            background: linear-gradient(45deg, #6D4C41 100%);
            color: white;
        }
       
        .enhanced-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
       
        .enhanced-success-message {
            background: linear-gradient(45deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            margin: 15px 0;
            animation: slideIn 0.5s ease-out;
        }
       
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
       
        .enhanced-product-title {
            background: linear-gradient(45deg, #6D4C41 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Enhanced slideshow styles */
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: auto;
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .slides {
            display: none;
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .slides.active {
            display: block;
        }

        .arrow {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            margin-top: -22px;
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.3s ease;
            border-radius: 0 3px 3px 0;
            background-color: rgba(0, 0, 0, 0.5);
            user-select: none;
        }

        .right-arrow {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        .arrow:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .slide-indicators {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
        }

        .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            margin: 0 5px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .dot:hover, .dot.active {
            background-color: #717171;
        }

        /* Multi-item summary styles */
        .multi-item-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }

        .item-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .item-summary:last-child {
            border-bottom: none;
        }

        .item-details {
            flex: 1;
        }

        .item-price {
            font-weight: bold;
            color: #6D4C41;
        }

        /* Image debug info (remove in production) */
        .debug-images {
            background: #e9ecef;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
        <div class="debug-images">
            <strong>Debug - Images found:</strong><br>
            <?php foreach ($productImages as $img): ?>
                <?= htmlspecialchars($img) ?> (exists: <?= file_exists($img) ? 'Yes' : 'No' ?>)<br>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="slideshow-container">
            <?php foreach ($productImages as $index => $imgPath): ?>
                <img src="<?php echo htmlspecialchars($imgPath); ?>" class="slides <?php echo $index === 0 ? 'active' : ''; ?>" alt="Product Image" onerror="this.src='images/placeholder.jpg'">
            <?php endforeach; ?>
            
            <?php if (count($productImages) > 1): ?>
                <a class="arrow left-arrow" onclick="changeSlide(-1)">&#10094;</a>
                <a class="arrow right-arrow" onclick="changeSlide(1)">&#10095;</a>
                
                <div class="slide-indicators">
                    <?php for ($i = 0; $i < count($productImages); $i++): ?>
                        <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="currentSlide(<?php echo $i + 1; ?>)"></span>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <h3 class="enhanced-product-title">Order Form</h3>
        <form action="buy_result.php" method="POST" class="enhanced-form-section">

            <div class="enhanced-form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" id="name" class="enhanced-quantity-input" required
                    value="<?= htmlspecialchars($_SESSION['user_data']['username'] ?? '') ?>">
            </div>

            <div class="enhanced-form-group">
                <label for="address">Address:</label>
                <textarea name="address" id="address" class="enhanced-quantity-input" required><?= htmlspecialchars($_SESSION['user_data']['address'] ?? '') ?></textarea>
            </div>

            <div class="enhanced-form-group">
                <label for="shipping_options">Shipping Options:</label>
                <select name="shipping_options" id="shipping_options" class="enhanced-size-select" required>
                    <option value="PickUp">Pick Up In-Store</option>
                    <option value="Order&Pick">Order & Pick</option>
                    <option value="standard_shipping">Standard Shipping</option>
                    <option value="priority_shipping">Priority Shipping</option>
                    <option value="express_shipping">Express Shipping</option>
                </select>
            </div>

            <div class="enhanced-form-group">
                <label for="payment">Payment Method:</label>
                <select name="payment" id="payment" class="enhanced-size-select" required>
                    <option value="credit_card">Credit Card</option>
                    <option value="cod">Cash on Delivery</option>
                </select>
            </div>

            <h2>Order Summary</h2>
            
            <?php if ($checkoutAll): ?>
                <div class="multi-item-summary">
                    <h4>Items to Purchase:</h4>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="item-summary">
                            <div class="item-details">
                                <strong><?= htmlspecialchars($item['product_name_display'] ?? $item['product_key']) ?></strong><br>
                                Size: <?= htmlspecialchars($item['size']) ?> | Quantity: <?= htmlspecialchars($item['quantity']) ?>
                            </div>
                            <div class="item-price">
                                ₱<?= number_format($item['price'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p><strong>Total Items:</strong> <?= count($cartItems) ?></p>
                <p><strong>Total Quantity:</strong> <?= $quantity ?></p>
            <?php else: ?>
                <p><strong>Product:</strong> <?= htmlspecialchars($productName) ?></p>
                <p><strong>Size:</strong> <?= htmlspecialchars($product_details_from_session['size'] ?? 'N/A') ?></p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($quantity) ?></p>
                <?php if ($quantity > 0): ?>
                    <p><strong>Price per item:</strong> ₱<?= number_format($item_total_price / $quantity, 2) ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <p><strong>Subtotal for product(s):</strong> ₱<?php echo number_format($item_total_price, 2); ?></p>
            <p><strong>Shipping Fee:</strong> ₱<span id="shippingFee"><?= number_format($shipping_fee, 2) ?></span></p>

            <p style="border: 2px solid #8B4513; padding: 10px; display: inline-block; border-radius: 10px;">
                <strong>Total: ₱<span id="totalPrice"><?= number_format($overall_total_price, 2) ?></span></strong>
            </p>

            <input type="hidden" name="product_name" value="<?= htmlspecialchars($productName) ?>">
            <input type="hidden" name="size" value="<?= htmlspecialchars($product_details_from_session['size'] ?? 'Multiple') ?>">
            <input type="hidden" name="quantity" value="<?= htmlspecialchars($quantity ?? 1) ?>">
            <input type="hidden" name="total_price" value="<?= htmlspecialchars($overall_total_price) ?>">
            <input type="hidden" name="is_multi_checkout" value="<?= $checkoutAll ? '1' : '0' ?>">

            <div class="button-group">
                <button type="submit" id="buyButton" class="button">Confirm Purchase</button>
                <button type="button" id="backButton" class="button" onclick="window.history.back();">Back</button>
            </div>
        </form>

        <div class="delivery-table" style="margin-top: 40px;">
            <h3 style="font-size: 25px; margin-bottom: 5px; border-bottom: none;">Estimated Delivery Time</h3>
            <table border="2" cellpadding="10" cellspacing="15" style="border-collapse: collapse; width: 100%; font-size: 20px;">
                <thead style="background-color: #708238; color: white;">
                    <tr>
                        <th>Shipping Option</th>
                        <th>Fee</th>
                        <th>Estimated Delivery Dates</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Pick Up In-Store<br><small>*Fulfilled by our Warehouse</small></td>
                        <td>FREE</td>
                        <td>3–5 business days from Order Date</td>
                    </tr>
                    <tr>
                        <td>TeLLa Order&Pick<br><small>*Fulfilled from Store Inventory</small></td>
                        <td>FREE</td>
                        <td>1–24 hours from the Order Date</td>
                    </tr>
                    <tr>
                        <td>Ship to Home<br>Standard Shipping</td>
                        <td>₱15</td>
                        <td>3–7 business days from Order Date</td>
                    </tr>
                    <tr>
                        <td>Ship to Home<br>Priority Shipping</td>
                        <td>₱35</td>
                        <td>2–3 business days from Order Date</td>
                    </tr>
                    <tr>
                        <td>Ship to Home<br>Express Shipping</td>
                        <td>₱50</td>
                        <td>1–2 business days from Order Date</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </main>

    <footer>
        <p>Need help? Contact us at: store-email@gmail.com</p>
    </footer>

    <script>
        // Enhanced slideshow functionality
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.slides');
        const dots = document.querySelectorAll('.dot');

        function showSlide(index) {
            if (slides.length === 0) return;
            
            // Hide all slides
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Show current slide
            if (slides[index]) {
                slides[index].classList.add('active');
            }
            if (dots[index]) {
                dots[index].classList.add('active');
            }
        }

        function changeSlide(direction) {
            if (slides.length === 0) return;
            
            currentSlideIndex += direction;
            
            if (currentSlideIndex >= slides.length) {
                currentSlideIndex = 0;
            } else if (currentSlideIndex < 0) {
                currentSlideIndex = slides.length - 1;
            }
            
            showSlide(currentSlideIndex);
        }

        function currentSlide(index) {
            currentSlideIndex = index - 1;
            showSlide(currentSlideIndex);
        }

        // Auto-advance slideshow (optional)
        <?php if (count($productImages) > 1): ?>
        setInterval(() => {
            changeSlide(1);
        }, 5000); // Change slide every 5 seconds
        <?php endif; ?>

        // Shipping fee calculator
        const itemTotalPrice = <?= json_encode($item_total_price) ?>;

        document.getElementById("shipping_options").addEventListener("change", function () {
            let shippingFee = 0;

            switch (this.value) {
                case "PickUp":
                case "Order&Pick":
                    shippingFee = 0;
                    break;
                case "standard_shipping":
                    shippingFee = 15;
                    break;
                case "priority_shipping":
                    shippingFee = 35;
                    break;
                case "express_shipping":
                    shippingFee = 50;
                    break;
            }

            document.getElementById("shippingFee").innerText = shippingFee.toFixed(2);
            document.getElementById("totalPrice").innerText = (itemTotalPrice + shippingFee).toFixed(2);
        });

        // Logout confirmation
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }

        // Legacy support for the old function name
        function plusSlides(n) {
            changeSlide(n);
        }
    </script>

</body>
</html>