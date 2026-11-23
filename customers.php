<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die('Access denied');
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die('Database connection failed: '.$mysqli->connect_error);


// Check if 'blocked' column exists
$hasBlocked = false;
$checkBlocked = $mysqli->query("SHOW COLUMNS FROM users LIKE 'blocked'");
if ($checkBlocked && $checkBlocked->num_rows > 0) $hasBlocked = true;


// Handle block/unblock
if (isset($_POST['block_user'])) {
    $uid = intval($_POST['block_user']);
    $blocked = intval($_POST['set_blocked']);
    if ($hasBlocked) {
        $stmt = $mysqli->prepare('UPDATE users SET blocked=? WHERE id=?');
        $stmt->bind_param('ii', $blocked, $uid);
        $stmt->execute();
        $stmt->close();
    }
    $message = $blocked ? 'Customer blocked.' : 'Customer unblocked.';
}


// Handle delete customer
if (isset($_POST['delete_user'])) {
    $uid = intval($_POST['delete_user']);
    $stmt = $mysqli->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->close();
    $message = 'Customer deleted.';
}


// Handle edit customer
if (isset($_POST['edit_user'])) {
    $uid = intval($_POST['edit_user']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $birthday = trim($_POST['birthday']);
    $stmt = $mysqli->prepare('UPDATE users SET firstname=?, lastname=?, email=?, address=?, contact=?, birthday=? WHERE id=?');
    $stmt->bind_param('ssssssi', $firstname, $lastname, $email, $address, $contact, $birthday, $uid);
    $stmt->execute();
    $stmt->close();
    $message = 'Customer updated.';
}


// Fetch all customers
$customers = [];
$res = $mysqli->query('SELECT id, username, firstname, lastname, address, contact, birthday, email, profile_image' . ($hasBlocked ? ', blocked' : '') . ' FROM users ORDER BY lastname, firstname');
while ($res && $row = $res->fetch_assoc()) {
    $customers[] = [
        'id' => $row['id'],
        'image' => $row['profile_image'] ?: 'https://placehold.co/40x40/cccccc/000000?text=' . strtoupper(substr($row['firstname'],0,1).substr($row['lastname'],0,1)),
        'username' => $row['username'],
        'first' => $row['firstname'],
        'last' => $row['lastname'],
        'birthday' => $row['birthday'],
        'address' => $row['address'],
        'contact' => $row['contact'],
        'email' => $row['email'],
        'blocked' => $hasBlocked ? $row['blocked'] : 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin – Customer Management</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    .admin-header-bar { background: #5D4037; color: #fff; display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; border-bottom: 4px solid #D2B48C; }
    .admin-header-bar nav { display: flex; gap: 25px; }
    .admin-header-bar nav a { color: #fff; text-decoration: none; font-weight: 500; transition: .2s; }
    .admin-header-bar nav a:hover { color: #D2B48C; }
    .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    .users-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 8px; overflow: visible; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .users-table th { background: #5D4037; color: #fff; padding: 12px; text-align: left; font-weight: 600; }
    .users-table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: top; }
    .users-table tr:hover { background: #f8f9fa; }
    .users-table img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #D7CCC8; }
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
    .success-message { background: #DFF2BF; color: #4F8A10; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #4F8A10; }
    .admin-toolbar { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; }
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
    .actions-menu .menu-item.block-btn { color: #dc3545; }
    .actions-menu .menu-item.block-btn:hover { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
    .actions-menu .menu-item.block-btn::before { content: '⛔'; font-size: 14px; }
    .actions-menu .menu-item.orders-btn { color: #687A61; }
    .actions-menu .menu-item.orders-btn:hover { background: linear-gradient(135deg, #e0f7fa 0%, #b2dfdb 100%); color: #004d40; }
    .actions-menu .menu-item.orders-btn::before { content: '🧾'; font-size: 14px; }
    .actions-menu .menu-item.edit-btn { color: #28a745; }
    .actions-menu .menu-item.edit-btn:hover { background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #1e7e34; }
    .actions-menu .menu-item.edit-btn::before { content: '✏️'; font-size: 14px; }
    .actions-menu .menu-item.delete-btn { color: #dc3545; }
    .actions-menu .menu-item.delete-btn:hover { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; }
    .actions-menu .menu-item.delete-btn::before { content: '🗑️'; font-size: 14px; }
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
    }
    .modal h3 {
        margin-top: 0;
        color: #5D4037;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .button-group {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
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
    <h2>Customer Management</h2>
    <?php if (isset($message)): ?>
        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <table class="users-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Username</th>
                <th>First</th>
                <th>Last</th>
                <th>Birthday</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($customers as $c):?>
            <tr data-id="<?=$c['id']?>">
                <td><img src="<?=htmlspecialchars($c['image'])?>"></td>
                <td><?=htmlspecialchars($c['username'])?></td>
                <td><?=htmlspecialchars($c['first'])?></td>
                <td><?=htmlspecialchars($c['last'])?></td>
                <td><?=htmlspecialchars($c['birthday'])?></td>
                <td><?=htmlspecialchars($c['address'])?></td>
                <td><?=htmlspecialchars($c['contact'])?></td>
                <td><?=htmlspecialchars($c['email'])?></td>
                <td class="actions-cell">
                    <button class="actions-btn" title="More actions"></button>
                    <div class="actions-menu">
                        <button class="menu-item block-btn" onclick="toggleBlock(<?=$c['id']?>, <?=$c['blocked']?>)"><?= $c['blocked'] ? 'Unblock' : 'Block' ?></button>
                        <a href="orders.php?user_id=<?=$c['id']?>" class="menu-item orders-btn">Orders</a>
                        <button class="menu-item edit-btn" onclick="openEditModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['first'],ENT_QUOTES) ?>', '<?= htmlspecialchars($c['last'],ENT_QUOTES) ?>', '<?= htmlspecialchars($c['email'],ENT_QUOTES) ?>', '<?= htmlspecialchars($c['address'],ENT_QUOTES) ?>', '<?= htmlspecialchars($c['contact'],ENT_QUOTES) ?>', '<?= htmlspecialchars($c['birthday'],ENT_QUOTES) ?>')">Edit</button>
                        <button class="menu-item delete-btn" onclick="deleteCustomer(<?=$c['id']?>)">Delete</button>
                    </div>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</main>
<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Edit Customer</h3>
        <form method="post">
            <input type="hidden" name="edit_user" id="edit_user_id">
            <div class="form-group"><input name="firstname" id="edit_first" placeholder="First name" required></div>
            <div class="form-group"><input name="lastname" id="edit_last" placeholder="Last name" required></div>
            <div class="form-group"><input name="email" id="edit_email" placeholder="Email" required></div>
            <div class="form-group"><input name="address" id="edit_address" placeholder="Address"></div>
            <div class="form-group"><input name="contact" id="edit_contact" placeholder="Contact"></div>
            <div class="form-group"><input name="birthday" id="edit_birthday" type="date"></div>
            <div class="button-group">
                <button type="button" id="cancelEdit" class="button">Cancel</button>
                <button type="submit" class="button add-new">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
function openEditModal(id, first, last, email, address, contact, birthday) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_first').value = first;
    document.getElementById('edit_last').value = last;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_contact').value = contact;
    document.getElementById('edit_birthday').value = birthday;
    document.getElementById('editModal').style.display = 'block';
}

function toggleBlock(id, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const form = document.createElement('form');
    form.method = 'post';
    form.style.display = 'none';
    
    const input1 = document.createElement('input');
    input1.type = 'hidden';
    input1.name = 'block_user';
    input1.value = id;
    
    const input2 = document.createElement('input');
    input2.type = 'hidden';
    input2.name = 'set_blocked';
    input2.value = newStatus;
    
    form.appendChild(input1);
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
}

function deleteCustomer(id) {
    if (confirm('Are you sure you want to delete this customer?')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.style.display = 'none';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_user';
        input.value = id;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

document.getElementById('cancelEdit').onclick = function() {
    document.getElementById('editModal').style.display = 'none';
};

window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        document.getElementById('editModal').style.display = 'none';
    }
}

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