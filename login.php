<?php
/*  DB CONNECTION  */
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) die('Connection failed: ' . $mysqli->connect_error);




/*  SESSION & FEEDBACK */
session_start();




$show_signup_success_message        = false;
$registered_username_for_login_form = '';
if (isset($_SESSION['signup_success_redirect']) && $_SESSION['signup_success_redirect']) {
    $show_signup_success_message = true;
    $registered_username_for_login_form = $_SESSION['registered_username'] ?? '';
    unset($_SESSION['signup_success_redirect'], $_SESSION['registered_username']);
}




/*  LOGIN HANDLER - REPLACE THIS ENTIRE SECTION  */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_attempt = trim($_POST['username'] ?? '');
    $password_attempt = $_POST['password'] ?? '';




    /* helper: set session + redirect */
    $login_and_go = function ($username, $role, $extra = []) {
        $_SESSION['username']  = $username;
        $_SESSION['role']      = $role;
        $_SESSION['user_data'] = array_merge(['username' => $username], $extra);
        header('Location: ' . ($role === 'admin' ? 'admin.php' : 'home.php'));
        exit();
    };




    /* Check admin_users table */
    $stmt = $mysqli->prepare(
        'SELECT password, email, address, contact, birthday, profile_image
           FROM admin_users
          WHERE username = ?
          LIMIT 1'
    );
    $stmt->bind_param('s', $username_attempt);
    $stmt->execute();
    $stmt->store_result();




    if ($stmt->num_rows === 1) {
        $stmt->bind_result($stored_password, $email, $address, $contact, $birthday, $profile_image);
        $stmt->fetch();
       
        // For admin users, check if password is hashed or plain text
        if (password_verify($password_attempt, $stored_password) || $password_attempt === $stored_password) {
            $login_and_go($username_attempt, 'admin', [
                'email'         => $email         ?? 'admin@example.com',
                'address'       => $address       ?? 'N/A',
                'contact'       => $contact       ?? 'N/A',
                'birthday'      => $birthday      ?? 'N/A',
                'profile_image' => $profile_image ?? 'images/default_profile.png'
            ]);
        }
    }
    $stmt->close();




    /* Check users table */
    $stmt = $mysqli->prepare(
        'SELECT password, email, address, contact, birthday, profile_image, blocked
           FROM users
          WHERE username = ?
          LIMIT 1'
    );
    $stmt->bind_param('s', $username_attempt);
    $stmt->execute();
    $stmt->store_result();


    if ($stmt->num_rows === 1) {
        $stmt->bind_result($stored_password, $email, $address, $contact, $birthday, $profile_image, $blocked);
        $stmt->fetch();
        if ($blocked) {
            $error_message = 'Your account is blocked. Please contact support.';
        } else if (password_verify($password_attempt, $stored_password)) {
            $login_and_go($username_attempt, 'user', [
                'email'         => $email         ?? 'user@example.com',
                'address'       => $address       ?? 'N/A',
                'contact'       => $contact       ?? 'N/A',
                'birthday'      => $birthday      ?? 'N/A',
                'profile_image' => $profile_image ?? 'images/default_profile.png'
            ]);
        }
    }
    $stmt->close();


    /* If no match anywhere */
    if (!isset($error_message)) {
        $error_message = 'Invalid username or password.';
    }
}
?>












<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>




    <main class="container <?php if(basename($_SERVER['PHP_SELF']) == 'login.php') echo 'login-main-transparent'; ?>">
        <?php if ($show_signup_success_message): ?>
            <div class="success-message">
                Account for "<?= htmlspecialchars($registered_username_for_login_form) ?>" created successfully! Please log in.
            </div>
        <?php endif; ?>




        <h2>Login to Your Account</h2>
        <form method="post" action="login.php" class="auth-form">
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>




            <input type="text" name="username" id="username" value="<?= htmlspecialchars($registered_username_for_login_form) ?>" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>




            <button type="submit" id="btnLogin">Login</button>




            <div class="form-footer">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>




            <div class="login-separator">
                <span class="login-separator-line"></span>
                <span class="login-separator-text">or</span>
                <span class="login-separator-line"></span>
            </div>




            <div class="form-footer">
                Don't have an account? <a href="signup.php">Create Account</a>
            </div>
        </form>
    </main>




    <footer>
        <p>Discover unique finds and Filipino craftsmanship.</p>
    </footer>




    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>
