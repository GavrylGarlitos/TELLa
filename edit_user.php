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

$message = null;
$id = intval($_GET['id'] ?? 0);
if ($id < 1) die('Invalid user id.');

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_user'])){
 $img = storeImg($_FILES['profile_image']);
 if(!$img) $img = $_POST['existing_image'];
 $st = $mysqli->prepare('UPDATE users SET username=?, firstname=?, lastname=?, email=?, contact=?, birthday=?, address=?, profile_image=? WHERE id=?');
 $st->bind_param('ssssssssi', $_POST['username'],$_POST['firstname'],$_POST['lastname'],$_POST['email'],$_POST['contact'],$_POST['birthday'],$_POST['address'],$img,$id);
 $st->execute(); $st->close();
 header('Location: admin.php?msg=User updated successfully!');
 exit();
}

$st = $mysqli->prepare('SELECT username,firstname,lastname,email,contact,birthday,address,profile_image FROM users WHERE id=?');
$st->bind_param('i',$id); $st->execute();
$r = $st->get_result(); $user = $r->fetch_assoc();
$st->close();
if(!$user) die('User not found.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<link rel="stylesheet" href="style.css">
<style>
.container {
 max-width: 600px;
 margin: 40px auto;
 padding: 20px;
 background: #fff;
 border-radius: 8px;
 box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.container h2 {
 margin-bottom: 20px;
 color: #5D4037;
 border-bottom: 2px solid #D7CCC8;
 padding-bottom: 8px;
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
 margin-top: 20px;
}
.button img-preview {
 margin-top: 15px;
}
</style>
</head>
<body>
<div class="container">
<h2>Edit User</h2>
<form method="post" enctype="multipart/form-data">
 <div class="form-group">
  <input name="username" value="<?=htmlspecialchars($user['username'])?>" placeholder="Username" required>
 </div>
 <div class="form-group">
  <input name="firstname" value="<?=htmlspecialchars($user['firstname'])?>" placeholder="First name" required>
 </div>
 <div class="form-group">
  <input name="lastname" value="<?=htmlspecialchars($user['lastname'])?>" placeholder="Last name" required>
 </div>
 <div class="form-group">
  <input type="email" name="email" value="<?=htmlspecialchars($user['email'])?>" placeholder="Email" required>
 </div>
 <div class="form-group">
  <input name="address" value="<?=htmlspecialchars($user['address'])?>" placeholder="Address">
 </div>
 <div class="form-group">
  <input name="contact" value="<?=htmlspecialchars($user['contact'])?>" placeholder="Contact">
 </div>
 <div class="form-group">
  <input type="date" name="birthday" value="<?=htmlspecialchars($user['birthday'])?>">
 </div>
 <div class="form-group">
  <input type="file" name="profile_image" accept="image/*">
 </div>
 <input type="hidden" name="existing_image" value="<?=htmlspecialchars($user['profile_image'])?>">
 <div style="margin:10px 0;">
     <img src="<?=htmlspecialchars($user['profile_image'])?>" width="80" style="border-radius:50%; border:2px solid #D7CCC8;">
 </div>
 <div class="button-group">
  <button type="submit" name="update_user" class="button add-new">Save Changes</button>
  <a href="admin.php" class="button">Cancel</a>
 </div>
</form>
</div>
</body>
</html>
