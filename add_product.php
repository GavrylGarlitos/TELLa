<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die("Access denied.");
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $qty = intval($_POST['quantity']);

    // Image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileName = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $fileName);
        $imagePath = 'uploads/' . $fileName;
    }

    $stmt = $mysqli->prepare("INSERT INTO products (name, description, price, quantity, image_path) VALUES (?, ?, ?, ?, ?)");
    // FIX: The type definition string was corrected from 'ssd is' to 'ssdis'
    $stmt->bind_param('ssdis', $name, $desc, $price, $qty, $imagePath);
    $stmt->execute();
    $stmt->close();
    header("Location: product_management.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Product - Admin</title>
<link rel="stylesheet" href="style.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<style>
.admin-header-bar { /* same styles as before */ background: #5D4037; color: #fff; display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; border-bottom: 4px solid #D2B48C; }
.admin-header-bar nav { display: flex; gap: 25px; }
.admin-header-bar nav a { color: #fff; text-decoration: none; font-weight: 500; transition: .2s; }
.admin-header-bar nav a:hover { color: #D2B48C; }
.container { max-width: 600px; margin: 0 auto; padding: 20px; }
.form-group { margin-bottom: 15px; }
.form-group label { display:block; margin-bottom:5px; font-weight:600; }
.form-group input, .form-group textarea { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
.button { background:#5D4037; color:#fff; padding:10px 20px; border:none; border-radius:4px; cursor:pointer; transition:background 0.3s; }
.button:hover { background:#4A2C20; }
</style>
</head>
<body>
<header class="admin-header-bar">
 <div>Welcome "<?= htmlspecialchars($_SESSION['username']) ?>"</div>
 <nav>
      <a href="admin.php">User Management</a>
      <a href="product_management.php">Product Management</a>
      <a href="orders.php">Order Management</a>
      <a href="customers.php">Customer Management</a>
      <a href="logout.php" style="color:#FFCDD2">Logout</a>
 </nav>
</header>

<main class="container">
 <h2>Add New Product</h2>
 <form method="post" enctype="multipart/form-data">
      <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" required>
      </div>
      <div class="form-group">
          <label>Description</label>
          <textarea name="description" rows="4" required></textarea>
      </div>
      <div class="form-group">
          <label>Price</label>
          <input type="number" name="price" step="0.01" required>
      </div>
      <div class="form-group">
          <label>Quantity</label>
          <input type="number" name="quantity" required>
      </div>
      <div class="form-group">
          <label>Image</label>
          <input type="file" name="image" accept="image/*">
      </div>
      <button class="button" type="submit">Add Product</button>
 </form>
</main>
</body>
</html>