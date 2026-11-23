<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die("Access denied.");
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);

$result = $mysqli->query("SELECT * FROM products ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Product Management - Admin</title>
<link rel="stylesheet" href="style.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<style>
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
.admin-header-bar nav a:hover { color: #D2B48C; }
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
.orders-table img {
 width: 50px; height: 50px;
 object-fit: cover; border-radius: 4px;
 border: 1px solid #D7CCC8;
}
.button {
    background: #5D4037;
    color: #fff;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: background 0.3s;
    cursor: pointer;
}
.button:hover { background: #4A2C20; }
.button.add-new { background: #687A61; }
.button.delete-selected { background: #dc3545; margin-left: 10px; }
.button.delete-selected:hover { background: #c82333; }

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
    color: #5D4037;
}
.actions-menu .menu-item.manage-btn:hover {
    background: linear-gradient(135deg, #e0f7fa 0%, #b2dfdb 100%);
    color: #004d40;
}
.actions-menu .menu-item.manage-btn::before {
    content: '⚙️';
    font-size: 14px;
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
 <h2>Product Management</h2>

 <div style="margin-bottom:15px;">
     <a href="add_product.php" class="button">+ Add Product</a>
 </div>

 <table class="orders-table">
     <thead>
         <tr>
             <th>Image</th>
             <th>Name</th>
             <th>Description</th>
             <th>Price</th>
             <th>Quantity</th>
             <th>Actions</th>
         </tr>
     </thead>
     <tbody>
     <?php if ($result && $result->num_rows > 0): ?>
         <?php while ($p = $result->fetch_assoc()): ?>
         <tr>
             <td>
                 <img src="<?= htmlspecialchars($p['image_path'] ?: 'https://placehold.co/50x50/cccccc/000000?text=No+Image') ?>">
             </td>
             <td><?= htmlspecialchars($p['name']) ?></td>
             <td><?= htmlspecialchars(substr($p['description'],0,50)) ?><?= strlen($p['description'])>50 ? '...' : '' ?></td>
             <td>₱<?= number_format($p['price'],2) ?></td>
             <td><?= htmlspecialchars($p['quantity']) ?></td>
             <td class="actions-cell">
                 <button class="actions-btn" title="More actions"></button>
                 <div class="actions-menu">
                     <a href="manage_product.php?product_id=<?= $p['id'] ?>" class="menu-item manage-btn">Manage</a>
                 </div>
             </td>
         </tr>
         <?php endwhile; ?>
     <?php else: ?>
         <tr><td colspan="6" style="text-align:center;padding:20px;">No products found.</td></tr>
     <?php endif; ?>
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