<?php
// Start the session to manage user login state and cart data.
session_start();


// Redirect to the login page if the user is not logged in.
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit(); // Stop further script execution.
}


$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}


// Get the product ID from the URL query parameter (e.g., product_detail.php?product_id=1).
$product_id = $_GET['product_id'] ?? null;
$current_product_info = null;


if ($product_id) {
    $stmt = $mysqli->prepare('SELECT id, name, description, price, image_path FROM products WHERE id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_product_info = [
            "id" => $row['id'],
            "name" => $row['name'],
            "description" => $row['description'],
            "price" => $row['price'],
            "image_path" => $row['image_path'] // This will be the main image
        ];
    }
    $stmt->close();
}


if (!$current_product_info) {
    echo "Product not found or invalid ID!";
    exit();
}


$product_name_display = $current_product_info['name'];
$base_price = $current_product_info['price'];
$product_description = $current_product_info['description'];




// For slideshow: we will only use the single image_path from the database for now.
// If you want multiple images per product, you'd need another database table (e.g., product_images)
// or store multiple paths in a delimited string (less recommended).
// For simplicity, we'll just use the main 'image_path' from the products table for the slideshow.
$productImages = [];
if ($current_product_info['image_path']) {
    // Check if the image path exists
    if (file_exists($current_product_info['image_path'])) {
        $productImages[] = $current_product_info['image_path'];
    } else {
        // Fallback to placeholder if the image file is missing
        $productImages[] = "https://via.placeholder.com/420x420?text=Image+Not+Found";
    }
} else {
    // Ultimate Fallback: If still no images, use a generic placeholder.
    $productImages[] = "https://via.placeholder.com/420x420?text=No+Image+Provided";
}


$mysqli->close();




// Define price adjustments based on product size.
$adjustment = [
    "XS" => -100, "S" => -50, "M" => 0,
    "L" => 150, "XL" => 200, "XXL" => 250, "XXXL" => 300
];


// Initialize variables for size, quantity, total price, and success message.
$size = $_POST['size'] ?? ($_GET['size'] ?? 'M'); // Prioritize POST, then GET, default to 'M'.
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
if ($quantity < 1) $quantity = 1;
$success_message = "";


// Handle form submission (e.g., Add to Cart, Buy Now).
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $size_adjustment = $adjustment[$size] ?? 0;
    $total_price = ($base_price + $size_adjustment) * $quantity;


    if (isset($_POST['add_to_cart'])) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
       $_SESSION['cart'][] = [
    "product_id"           => $current_product_info['id'],
    "product_name_display" => $product_name_display,
    "size"                 => $size,
    "quantity"             => $quantity,
    "price"                => $total_price,
    "image_path"           => $productImages[0],
    "all_item_images"      => $productImages // add this line!
];

        $success_message = "Successfully added to cart!";
    }


    if (isset($_POST['buy'])) {
    $_SESSION['buy_now'] = [
        "product_id" => $current_product_info['id'],
        "product_name_display" => $product_name_display,
        "size" => $size,
        "quantity" => $quantity,
        "price" => $total_price,
        "image_path" => $productImages[0], // single main image
        "images" => $productImages           // array for slideshow
    ];
    header("Location: buy.php");
    exit();
}

} else {
    // If the page is loaded via GET (not a form submission), calculate default total price for initial display.
    $size_adjustment = $adjustment[$size] ?? 0;
    $total_price = ($base_price + $size_adjustment) * $quantity;
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(ucfirst($product_name_display)) ?> - Details</title>
    <link rel="stylesheet" href="style.css">
    <!-- Enhanced styling for better visual appeal -->
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
            background: linear-gradient(45deg, #8B4513 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; // Include the common site header. ?>


    <main class="container">
        <!-- //i add this - Enhanced product title styling -->
        <h2 class="enhanced-product-title">Product Details: <?= htmlspecialchars(ucfirst($product_name_display)) ?></h2>


        <div class="slideshow-container">
            <?php foreach ($productImages as $index => $imgPath): ?>
                <img src="<?= htmlspecialchars($imgPath); ?>" class="slides" style="<?= $index === 0 ? 'display:block;' : ''; ?>">
            <?php endforeach; ?>
            <?php if (count($productImages) > 1): ?>
                <a class="arrow left-arrow" onclick="plusSlides(-1)">&#10094;</a>
                <a class="arrow right-arrow" onclick="plusSlides(1)">&#10095;</a>
            <?php endif; ?>
        </div>
        <div class="controls">
            </div>


        <!-- Enhanced description box with better styling -->
            <h3 style="text-align: center;">Description</h3>
            <p><?= htmlspecialchars($product_description) ?></p>


            <!-- Enhanced form section -->
                <form method="POST" action="product_detail.php?product_id=<?= htmlspecialchars($current_product_info['id']) ?>">
                    <div class="enhanced-form-group">
                        <label for="sizeSelect">Size:</label>
                        <select name="size" id="sizeSelect" required class="enhanced-size-select">
                            <option value="XS" <?= ($size == 'XS') ? 'selected' : '' ?>>XS (-₱100)</option>
                            <option value="S" <?= ($size == 'S') ? 'selected' : '' ?>>S (-₱50)</option>
                            <option value="M" <?= ($size == 'M') ? 'selected' : '' ?>>M (base price)</option>
                            <option value="L" <?= ($size == 'L') ? 'selected' : '' ?>>L (+₱150)</option>
                            <option value="XL" <?= ($size == 'XL') ? 'selected' : '' ?>>XL (+₱200)</option>
                            <option value="XXL" <?= ($size == 'XXL') ? 'selected' : '' ?>>XXL (+₱250)</option>
                            <option value="XXXL" <?= ($size == 'XXXL') ? 'selected' : '' ?>>XXXL (+₱300)</option>
                        </select>
                    </div>


                    <div class="enhanced-form-group">
                        <label for="quantityInput">Quantity:</label>
                        <input type="number" name="quantity" id="quantityInput" min="1" value="<?= htmlspecialchars($quantity) ?>" required class="enhanced-quantity-input">
                    </div>


                    <?php if ($success_message): // Display success message if item was added to cart. ?>
                        <!-- Enhanced success message styling -->
                        <div class="enhanced-success-message"><?= htmlspecialchars($success_message) ?></div>
                    <?php endif; ?>


                    <!-- Enhanced price display -->
                    <div class="enhanced-price-display">
                        <strong>Total Price:</strong> ₱<span id="totalPrice"><?= number_format($total_price, 2) ?></span>
                    </div>


                    <!-- Enhanced button group -->
                    <div class="enhanced-button-group">
                        <button class="enhanced-button primary" type="submit" name="add_to_cart">Add to Cart</button>
                        <button class="enhanced-button secondary" type="submit" name="buy">Buy Now</button>
                        <a href="home.php" class="enhanced-button tertiary">Back to Home</a>
                    </div>
                </form>
    </main>
   
    <footer>
        <p>Explore unique finds and Filipino craftsmanship.</p>
    </footer>


    <script>
    // JavaScript for dynamic price update based on size and quantity.
    const adjustment = { // Price adjustments for sizes (must match PHP $adjustment array).
      "XS": -100, "S": -50, "M": 0,
      "L": 150, "XL": 200, "XXL": 250, "XXXL": 300
    };


    const basePrice = <?= $base_price ?>; // Get base price from PHP.
    const sizeSelect = document.getElementById('sizeSelect');
    const quantityInput = document.getElementById('quantityInput');
    const totalPriceElem = document.getElementById('totalPrice');


    // Function to update the displayed total price.
    function updateTotalPrice() {
      const selectedSize = sizeSelect.value;
      let currentQuantity = parseInt(quantityInput.value);
     
      // Validate quantity.
      if (isNaN(currentQuantity) || currentQuantity < 1) {
        currentQuantity = 1;
        quantityInput.value = 1; // Reset invalid quantity in the input field.
      }
     
      const total = (basePrice + (adjustment[selectedSize] || 0)) * currentQuantity; // Calculate total.
      totalPriceElem.textContent = total.toFixed(2); // Update display, formatted to 2 decimal places.
    }


    // Add event listeners to size and quantity inputs to update price on change.
    sizeSelect.addEventListener('change', updateTotalPrice);
    quantityInput.addEventListener('input', updateTotalPrice);
   
    // Initial call to set the price when the page loads.
    updateTotalPrice();


    // JavaScript for image slideshow.
    let slideIndex = 0;
    const slides = document.getElementsByClassName("slides");


    function showSlide(index) {
        if (slides.length === 0) return;
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        slides[index].style.display = "block";
    }


    function plusSlides(n) {
        if (slides.length === 0) return;
        slideIndex = (slideIndex + n + slides.length) % slides.length; // Handle wrapping.
        showSlide(slideIndex);
    }


    // Show initial slide when DOM is ready.
    document.addEventListener('DOMContentLoaded', () => {
        if (slides.length > 0) {
            showSlide(slideIndex);
        }
    });


    // JavaScript function for logout confirmation.
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "logout.php";
        }
    }
    </script>
</body>
</html>