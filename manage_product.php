<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die("Access denied.");
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);


$id = intval($_GET['product_id']);
$product = $mysqli->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if (!$product) die("Product not found.");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $qty = intval($_POST['quantity']);


    // Image update
    $imagePath = $product['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $fileName = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $fileName);
        $imagePath = 'uploads/' . $fileName;
    }


$stmt = $mysqli->prepare("UPDATE products SET name=?, description=?, price=?, quantity=?, image_path=? WHERE id=?");
$stmt->bind_param('ssdisi', $name, $desc, $price, $qty, $imagePath, $id);




    $stmt->execute();
    $stmt->close();
    header("Location: product_management.php");
    exit;
}


// delete
if (isset($_GET['delete'])) {
    $mysqli->query("DELETE FROM products WHERE id=$id");
    header("Location: product_management.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Product - Admin</title>
<link rel="stylesheet" href="style.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<style>
.admin-header-bar { background: #5D4037; color: #fff; display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; border-bottom: 4px solid #D2B48C; }
.admin-header-bar nav { display: flex; gap: 25px; }
.admin-header-bar nav a { color: #fff; text-decoration: none; font-weight: 500; transition: .2s; }
.admin-header-bar nav a:hover { color: #D2B48C; }


.container {  
  max-width: 600px;        
  margin: 40px auto;      
  padding: 20px;          
  background: #f9f9f9;    
  border-radius: 12px;    
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}


.form-group { margin-bottom: 15px; }
.form-group label { display:block; margin-bottom:5px; font-weight:600; }
.form-group input,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 16px;
  box-sizing: border-box;
}


.form-group textarea {
  resize: vertical;
  min-height: 80px;
}


input[type="file"] {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 16px;
  box-sizing: border-box;
}


.button {                  
  background: #5D4037;
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 687A61px;      
  cursor: pointer;
  transition: background 0.3s;
  margin-right: 10px;
  text-decoration: none;  
  display: inline-block;  
}


.button:hover { background:#4A2C20; }


.button.delete-btn {
  background: #B71C1C;
}


.button.delete-btn:hover {
  background: #8B0000;
}


.button.close-btn {
  background: #757575;
}


.button.close-btn:hover {
  background: #616161;
}


.button-group {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}


.product-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
  margin-bottom: 10px;
  border: 2px solid #ddd;
}
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
 <h2>Manage Product: <?= htmlspecialchars($product['name']) ?></h2>
 <form method="post" enctype="multipart/form-data">
     <div class="form-group">
         <label>Name</label>
         <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
     </div>
     
     <div class="form-group">
         <label>Description</label>
         <textarea name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>
     </div>
     
     <div class="form-group">
         <label>Price</label>
         <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
     </div>
     
     <div class="form-group">
         <label>Quantity</label>
         <input type="number" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required>
     </div>
     
     <div class="form-group">
         <label>Image</label>
         <img src="<?= htmlspecialchars($product['image_path'] ?: 'https://placehold.co/100x100/cccccc/000000?text=No+Image') ?>" class="product-image">
         <input type="file" name="image" accept="image/*">
     </div>
     
     <div class="button-group">
         <button class="button" type="submit">Update</button>
         <a class="button delete-btn" href="?product_id=<?= $id ?>&delete=1" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
         <a class="button close-btn" href="javascript:history.back()">Close</a>
     </div>
 </form>
</main>
</body>
</html>
