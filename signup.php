<?php
session_start();

// CONNECT TO DATABASE
$connection = new mysqli("localhost", "root", "", "onlinestore");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));
    $firstname = htmlspecialchars(trim($_POST['firstname']??''));
    $lastname = htmlspecialchars(trim($_POST['lastname']??''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $contact = htmlspecialchars(trim($_POST['contact'] ?? ''));
    $birthday = htmlspecialchars(trim($_POST['birthday'] ?? ''));

    $region = htmlspecialchars(trim($_POST['region'] ?? ''));
    $province = htmlspecialchars(trim($_POST['province'] ?? ''));
    $city = htmlspecialchars(trim($_POST['city'] ?? ''));
    $barangay = htmlspecialchars(trim($_POST['barangay'] ?? ''));

    $hashed_password_for_storage = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, firstname, lastname, password, email, address, contact, birthday, region, province, city, barangay)
            VALUES ('$username', '$firstname','$lastname','$hashed_password_for_storage', '$email', '$address', '$contact', '$birthday', '$region', '$province', '$city', '$barangay')";

    if (mysqli_query($connection, $sql)) {
        $successMessage = "Customer stored successfully!";

        $_SESSION['active_login_username'] = $username;
        $_SESSION['active_login_hashed_password'] = $hashed_password_for_storage;

        $_SESSION['temp_user_data_details'] = [
            'username' => $username,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'address' => $address,
            'contact' => $contact,
            'birthday' => $birthday,
            'region' => $region,
            'province' => $province,
            'city' => $city,
            'barangay' => $barangay,
            'profile_image' => 'images/default_profile.png'
        ];

        $_SESSION['signup_success_redirect'] = true;
        $_SESSION['registered_username'] = $username;

        header("Location: signup_result.php");
        exit();
    } else {
        $errorMessage = "ERROR: Could not insert record. " . mysqli_error($connection);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container <?php if(basename($_SERVER['PHP_SELF']) == 'signup.php') echo 'login-main-transparent'; ?>">
        <h2>Create an Account</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form method="post" action="signup.php" class="auth-form">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" placeholder="Enter your username" required>

            <label for="firstname">Firstname:</label>
            <input type="text" name="firstname" id="firstname" placeholder="Enter your firstname" required>

            <label for="lastname">Lastname:</label>
            <input type="text" name="lastname" id="lastname" placeholder="Enter your lastname" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required>

            <!-- Region Dropdowns -->
            <label for="region">Region:</label>
            <select id="region" name="region" required>
                <option value="">Select Region</option>
            </select>

            <label for="province">Province:</label>
            <select id="province" name="province" required>
                <option value="">Select Province</option>
            </select>

            <label for="city">City/Municipality:</label>
            <select id="city" name="city" required>
                <option value="">Select City/Municipality</option>
            </select>

            <label for="barangay">Barangay:</label>
            <select id="barangay" name="barangay" required>
                <option value="">Select Barangay</option>
            </select>

            <label for="address">Street/House No.:</label>
            <input type="text" name="address" id="address" placeholder="e.g., #123 Mabini St." required>

            <label for="contact">Contact Number:</label>
            <input type="tel" name="contact" id="contact" pattern="[0-9]{11}" placeholder="e.g., 09123456789" required>

            <label for="birthday">Birthday:</label>
            <input type="date" name="birthday" id="birthday" required>

            <button type="submit" id="btnCreateAccount">Create Account</button>
            
            <div class="form-footer">
                 <a href="login.php">Back to Login</a>
            </div>
        </form>
    </main>

    <footer>
        <p>Join our community and discover unique Filipino products.</p>
    </footer>

    <!-- Philippine Address Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="ph-json/ph-address-selector.js"></script>
    <script>
        $(document).ready(function () {
            phAddressInit({
                region: "#region",
                province: "#province",
                city: "#city",
                barangay: "#barangay",
                dataPath: "ph-address"
            });
        });

        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>
