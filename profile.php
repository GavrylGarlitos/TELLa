<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_data'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; 

// Connect to database
$mysqli = new mysqli('localhost', 'root', '', 'onlinestore');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Handle profile image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_image"])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = basename($_FILES["profile_image"]["name"]);
    $safeFileName = preg_replace("/[^A-Za-z0-9_.\-]/", '', $fileName);
    if (empty($safeFileName)) $safeFileName = "uploaded_image.jpg";

    $targetFile = $targetDir . uniqid() . "_" . $safeFileName; 
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $check = false;
    if (isset($_FILES["profile_image"]["tmp_name"]) && is_uploaded_file($_FILES["profile_image"]["tmp_name"])) {
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    }

    if ($check !== false) {
        if ($_FILES["profile_image"]["size"] <= 2000000) {
            if (in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile)) {
                    // Update existing profile_image in the database
                    $stmt = $mysqli->prepare("UPDATE users SET profile_image=? WHERE username=?");
                    $stmt->bind_param("ss", $targetFile, $username);
                    if ($stmt->execute()) {
                        $_SESSION['upload_success'] = "Profile image updated!";
                    } else {
                        $_SESSION['upload_error'] = "Database update failed.";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['upload_error'] = "Sorry, there was an error uploading your file.";
                }
            } else {
                $_SESSION['upload_error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $_SESSION['upload_error'] = "File is too large. Maximum 2MB.";
        }
    } else {
        $_SESSION['upload_error'] = "File is not an image or was not uploaded correctly.";
    }

    header("Location: profile.php");
    exit();
}

// Always fetch fresh user data from DB
$stmt = $mysqli->prepare("SELECT username, email, address, contact, birthday, profile_image FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['user_data'] = $user;
} else {
    // fallback if something goes wrong
    $user = $_SESSION['user_data'];
}
$stmt->close();
$mysqli->close();

// Use existing profile_image or fallback
$profileImage = $user['profile_image'] ?? 'images/default_profile.png';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'header.php'; // Include the common site header. ?>
    
    <main class="container profile-container">
        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Image" class="profile-image">

        <h2><?= htmlspecialchars($user['username']) ?></h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($user['contact']) ?></p>
        <p><strong>Birthday:</strong> <?= htmlspecialchars($user['birthday']) ?></p>

        <form method="POST" enctype="multipart/form-data" action="profile.php">
            <label for="profile_image">Change Profile Image:</label><br>
            <input type="file" name="profile_image" id="profile_image" accept="image/*" required>
            <br><button type="submit" class="button">Upload Image</button>
        </form>

        <?php // Display upload success or error messages. ?>
        <?php if (isset($_SESSION['upload_success'])): ?>
            <div class="upload-status success-message"><?= htmlspecialchars($_SESSION['upload_success']) ?></div>
            <?php unset($_SESSION['upload_success']); // Clear the message after displaying it. ?>
        <?php elseif (isset($_SESSION['upload_error'])): ?>
            <div class="upload-status error-message"><?= htmlspecialchars($_SESSION['upload_error']) ?></div>
            <?php unset($_SESSION['upload_error']); // Clear the message. ?>
        <?php endif; ?>

        <div class="button-group">
            <a href="cart.php" class="button">Your Cart</a>
             <a href="home.php" class="button">Back to Home</a>
        </div>
    </main>

    <footer>
        <p>Manage your account settings.</p>
    </footer>

    <script>
        // JavaScript function for logout confirmation.
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>
