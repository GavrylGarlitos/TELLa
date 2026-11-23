<?php
session_start();


$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}


$products_on_home = [];
$result = $mysqli->query('SELECT id, name, price, image_path FROM products ORDER BY name ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Use a placeholder if no image is set, or if it's explicitly null/empty
        $image_path = $row['image_path'] ?: 'https://via.placeholder.com/220x220?text=No+Image'; // Placeholder for products without images
        $products_on_home[] = [
            "id" => $row['id'], // We'll now use the ID for linking
            "name" => $row['name'],
            "display_price" => $row['price'],
            "image" => $image_path,
            "alt" => $row['name'],
            "link_param" => $row['id'] // Link by product ID
        ];
    }
}
$mysqli->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Home - Our Store</title>
    <link rel="stylesheet" href="style.css"> </head>
<body>


    <?php
    // Include the common header (navigation, logo, etc.)
    include 'header.php';
    ?>


    <main class="container">
        <section class="hero-section">
                       <div class="hero-content">
                <h2>Discover Unique Products</h2>
                <p>From traditional weaves to modern designs, find unique pieces that tell a story.</p>
                <a href="#product-listings" class="button hero-button">Shop Now</a>
            </div>
        </section>


        <input type="text" placeholder="Search product..." id="searchBar" class="search-input"><br><br>


        <section class="product-grid" id="product-listings">
            <?php
            // Loop through the $products_on_home array to display each product.
            foreach ($products_on_home as $product_key => $product):
            ?>
                <a class="product-link" href="product_detail.php?product_id=<?php echo htmlspecialchars($product['link_param']); ?>">
    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['alt']); ?>">
    <span><?php echo htmlspecialchars($product['name']); ?></span>
    <span class="product-price">₱<?php echo number_format($product['display_price'], 2); ?></span>
        </a>
            <?php endforeach; // End of the product loop. ?>
        </section>


    </main>


    <footer>
        <p>&copy; <?php echo date("Y"); // Dynamically display the current year. ?> Our Store. All rights reserved. | <a href="about.php">About Us</a> | <a href="#">Privacy Policy</a></p>
    </footer>


    <script>
    // JavaScript function for logout confirmation.
    // This function is likely defined in header.php or in a global JS file if used across many pages.
    // If it's specific to this page and header.php doesn't have it, it should be here.
    // Assuming it's in header.php or a shared script.
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "logout.php";
        }
    }


    // JavaScript for the product search bar functionality.
    document.getElementById('searchBar').addEventListener('keyup', function() {
        const query = this.value.toLowerCase(); // Get the search query in lowercase.
        const productLinks = document.querySelectorAll('.product-grid .product-link'); // Get all product elements.
       
        productLinks.forEach(link => {
            // Get the product name text from within the link (assuming it's in the first span).
            const productName = link.querySelector('span').textContent.toLowerCase();
            // Check if the product name includes the search query.
            if (productName.includes(query)) {
                link.style.display = 'flex'; // Show the product (or 'block', 'inline-block' depending on original display style).
            } else {
                link.style.display = 'none'; // Hide the product.
            }
        });
    });
    </script>
</body>
</html>




