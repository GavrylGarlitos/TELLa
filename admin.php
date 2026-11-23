<?php

session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') die('Access denied');
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die('Database connection failed: '.$mysqli->connect_error);

function storeImg($f){
 if($f['error']===UPLOAD_ERR_OK && is_uploaded_file($f['tmp_name'])){
  $e = strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
  if(!in_array($e,['jpg','jpeg','png','gif','webp'])) return null;
  $n = 'uploads/'.uniqid('user_',true).'.'.$e;
  return move_uploaded_file($f['tmp_name'],$n) ? $n : null;
 }
 return null;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_user'])){
 $img = storeImg($_FILES['profile_image']);
 if(!$img){
  $i1 = trim($_POST['firstname'])[0]??''; $i2 = trim($_POST['lastname'])[0]??'';
  $txt = strtoupper($i1.$i2 ?: 'U');
  $img = 'https://placehold.co/40x40/cccccc/000000?text='.$txt;
 }
 $p = password_hash($_POST['password'],PASSWORD_DEFAULT);
 $st = $mysqli->prepare('INSERT INTO users(username,firstname,lastname,password,email,contact,birthday,address,profile_image) VALUES (?,?,?,?,?,?,?,?,?)');
 $st->bind_param('sssssssss',$_POST['username'],$_POST['firstname'],$_POST['lastname'],$p,$_POST['email'],$_POST['contact'],$_POST['birthday'],$_POST['address'],$img);
 $st->execute(); $st->close();
 $message = 'User added successfully!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
 $uid = intval($_POST['delete_user']);
 $st = $mysqli->prepare('DELETE FROM users WHERE id=?');
 $st->bind_param('i', $uid);
 $st->execute();
 $st->close();
 $message = 'User deleted successfully!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
 $userIds = $_POST['selected_users'] ?? [];
 if (!empty($userIds)) {
  $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
  $st = $mysqli->prepare("DELETE FROM users WHERE id IN ($placeholders)");
  $st->bind_param(str_repeat('i', count($userIds)), ...$userIds);
  $st->execute();
  $st->close();
  $message = count($userIds) . ' users deleted successfully!';
 }
}

$users = [];
$r = $mysqli->query('SELECT id,username,firstname,lastname,address,contact,birthday,email,profile_image FROM users');
while($r && $row = $r->fetch_assoc())
 $users[] = ['id'=>$row['id'],'image'=>$row['profile_image'],'username'=>$row['username'],'first'=>$row['firstname'],'last'=>$row['lastname'],'birthday'=>$row['birthday'],'address'=>$row['address'],'contact'=>$row['contact'],'email'=>$row['email']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin – User Management</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
    .admin-header-bar nav a:hover {
        color: #D2B48C;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: #fff;
        border-radius: 8px;
        overflow: visible;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .users-table th {
        background: #5D4037;
        color: #fff;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }
    .users-table td {
        padding: 12px;
        border-bottom: 1px solid #eee;
        vertical-align: top;
    }
    .users-table tr:hover {
        background: #f8f9fa;
    }
    .users-table img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #D7CCC8;
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
    .button:hover {
        background: #4A2C20;
    }
    .button.add-new {
        background: #687A61;
    }
    .button.delete-selected {
        background: #dc3545;
        margin-left: 10px;
    }
    .button.delete-selected:hover {
        background: #c82333;
    }
    .success-message {
        background: #DFF2BF;
        color: #4F8A10;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        border-left: 4px solid #4F8A10;
    }
    .admin-toolbar {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        align-items: center;
    }
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
    .actions-menu .menu-item.edit-btn {
        color: #28a745;
    }
    .actions-menu .menu-item.edit-btn:hover {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #1e7e34;
    }
    .actions-menu .menu-item.edit-btn::before {
        content: '✏️';
        font-size: 14px;
    }
    .actions-menu .menu-item.delete-btn {
        color: #dc3545;
    }
    .actions-menu .menu-item.delete-btn:hover {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }
    .actions-menu .menu-item.delete-btn::before {
        content: '🗑️';
        font-size: 14px;
    }
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
    #deleteSelectedBtn {
        display: none;
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
    <h2>User Management</h2>
    
    <?php if (isset($message)): ?>
        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <div class="admin-toolbar">
        <button type="button" id="selectAllBtn" class="button">ALL (<?=count($users)?>)</button>
        <button type="button" id="openAddModal" class="button add-new"><i class="fas fa-plus"></i> ADD NEW</button>
        <button type="button" id="deleteSelectedBtn" class="button delete-selected"><i class="fas fa-trash"></i> DELETE SELECTED</button>
    </div>

    <form id="bulkDeleteForm" method="post" style="display: none;">
        <input type="hidden" name="delete_selected" value="1">
    </form>

    <table class="users-table">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
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
        <?php foreach($users as $u):?>
            <tr data-id="<?=$u['id']?>">
                <td><input type="checkbox" class="user-checkbox" value="<?=$u['id']?>"></td>
                <td><img src="<?=htmlspecialchars($u['image'])?>"></td>
                <td><?=htmlspecialchars($u['username'])?></td>
                <td><?=htmlspecialchars($u['first'])?></td>
                <td><?=htmlspecialchars($u['last'])?></td>
                <td><?=htmlspecialchars($u['birthday'])?></td>
                <td><?=htmlspecialchars($u['address'])?></td>
                <td><?=htmlspecialchars($u['contact'])?></td>
                <td><?=htmlspecialchars($u['email'])?></td>
                <td class="actions-cell">
                    <button class="actions-btn" title="More actions"></button>
                    <div class="actions-menu">
                        <button class="menu-item edit-btn" data-user-id="<?=$u['id']?>">Edit User</button>


                        <button class="menu-item delete-btn" data-user-id="<?=$u['id']?>">Delete User</button>
                    </div>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</main>

<div id="addUserModal" class="modal">
    <div class="modal-content">
        <h3>Add New User</h3>
        <form enctype="multipart/form-data" method="post">
            <div class="form-group">
                <input name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input name="firstname" placeholder="First name" required>
            </div>
            <div class="form-group">
                <input name="lastname" placeholder="Last name" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input name="address" placeholder="Address">
            </div>
            <div class="form-group">
                <input name="contact" placeholder="Contact number">
            </div>
            <div class="form-group">
                <input type="date" name="birthday">
            </div>
            <div class="form-group">
                <input type="file" name="profile_image" accept="image/*">
            </div>
            <input type="hidden" name="add_user" value="1">
            <div class="button-group">
                <button type="button" id="cancelAdd" class="button">Cancel</button>
                <button type="submit" class="button add-new">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
const selectAllCheckbox = document.getElementById('selectAll');
const selectAllBtn = document.getElementById('selectAllBtn');
const userCheckboxes = document.querySelectorAll('.user-checkbox');
const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
const bulkDeleteForm = document.getElementById('bulkDeleteForm');

// Handle select all checkbox
selectAllCheckbox.addEventListener('change', function() {
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateDeleteButtonVisibility();
});

// Handle select all button
selectAllBtn.addEventListener('click', function() {
    const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
    selectAllCheckbox.checked = !allChecked;
    updateDeleteButtonVisibility();
});

// Handle individual checkbox changes
userCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        selectAllCheckbox.checked = checkedCount === userCheckboxes.length;
        selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < userCheckboxes.length;
        updateDeleteButtonVisibility();
    });
});

// Update delete button visibility
function updateDeleteButtonVisibility() {
    const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
    deleteSelectedBtn.style.display = checkedCount > 0 ? 'inline-block' : 'none';
}

// Handle bulk delete
deleteSelectedBtn.addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    const count = checkedBoxes.length;
    if (confirm(`Are you sure you want to delete ${count} selected user(s)?`)) {
        // Clear any existing hidden inputs
        const existingInputs = bulkDeleteForm.querySelectorAll('input[name="selected_users[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected user IDs to the form
        checkedBoxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_users[]';
            input.value = checkbox.value;
            bulkDeleteForm.appendChild(input);
        });
        
        bulkDeleteForm.submit();
    }
});

// Modal functionality
const addModal = document.getElementById('addUserModal');
document.getElementById('openAddModal').onclick = () => addModal.style.display = 'block';
document.getElementById('cancelAdd').onclick = () => addModal.style.display = 'none';

window.onclick = function(event) {
    if (event.target == addModal) {
        addModal.style.display = 'none';
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

// Handle edit clicks
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const userId = this.getAttribute('data-user-id');
        window.location.href = 'edit_user.php?id=' + userId;
    });
});


// Handle delete clicks
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const userId = this.getAttribute('data-user-id');
        if (confirm('Are you sure you want to delete this user?')) {
            // Create and submit a form for single user deletion
            const form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_user';
            input.value = userId;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        // Close the dropdown
        this.closest('.actions-menu').classList.remove('show');
    });
});
</script>
</body>
</html>