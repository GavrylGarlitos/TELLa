<?php
/* DB CONNECTION */
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die('Connection failed: ' . $mysqli->connect_error);


/* HANDLE PASSWORD UPDATE */
$show_popup = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $retype_password = $_POST['retype_password'] ?? '';


    // Validate inputs
    if (empty($username) || empty($new_password) || empty($retype_password)) {
        $error_message = 'All fields are required.';
    } elseif ($new_password !== $retype_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);


        // Update the password in the database
        $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param('ss', $hashed_password, $username);
        $result = $stmt->execute();


        if ($result) {
             $show_popup = true;
        } else {
            $error_message = 'Failed to update password. Please try again.';
        }


        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Popup Modal Styles */
        .popup-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.6);
            display: <?= $show_popup ? 'flex' : 'none' ?>;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }


        .popup-container {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            max-width: 300px;
        }


        .popup-container h3 {
            margin-top: 0;
        }


        .popup-container button {
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #6D4C41;
            color: white;
            border: none;
            border-radius: 5px;
        }


        .popup-container button:hover {
            background-color: #6D4C41;
        }
    </style>
</head>
<body>
    <!-- POPUP MODAL -->
    <div class="popup-overlay">
        <div class="popup-container">
            <h3>Password Updated</h3>
            <button onclick="window.location.href='login.php'">Login</button>
        </div>
    </div>


    <div class="container auth-form-container">
        <h2>Reset Password</h2>
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>


        <form method="post" action="forgot_password.php" class="auth-form">


            <div class="label-container">
                <label for="username">Enter Username:</label>
            </div>
            <div class="input-container">
                <input type="text" id="username" name="username" placeholder="Username">
            </div>






            <div class="label-container">
                <label for="new_password">Enter New Password:</label>
            </div>
             <div class="input-container">
                <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
            </div>






            <div class="label-container">
                <label for="retype_password">Re-Type New Password:</label>
            </div>
            <div class="input-container">
                <input type="password" name="retype_password" id="retype_password" placeholder="Re-Type New Password" required>
            </div>






            <div class="form-group">
                <button type="submit" class="button">Update Password</button>
            </div>
            <div class="form-group">
                <button type="button" onclick="window.location.href='login.php'" class="button accent">Back</button>
            </div>
        </form>
    </div>
</body>
</html>


