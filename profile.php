<?php
session_start();
require_once "database/DBController.php";

// Only allow logged-in users
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"] ?? "";
    $last_name = $_POST["last_name"] ?? "";
    $username = $_POST["username"] ?? "";
    // Only allow password change if provided
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($password && $password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if username is taken by another user
        $sql = "SELECT user_id FROM user WHERE username = ? AND user_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            if ($password) {
                $hashed_password = md5($password); // For demo only
                $sql = "UPDATE user SET first_name=?, last_name=?, username=?, password=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $first_name, $last_name, $username, $hashed_password, $_SESSION["user_id"]);
            } else {
                $sql = "UPDATE user SET first_name=?, last_name=?, username=? WHERE user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $first_name, $last_name, $username, $_SESSION["user_id"]);
            }
            if ($stmt->execute()) {
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["username"] = $username;
                $success = "Profile updated successfully.";
            } else {
                $error = "Update failed. Please try again.";
            }
        }
    }
}

// Get current user info
$sql = "SELECT * FROM user WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Edit Profile</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php endif; ?>
        <form method="post" action="profile.php">
            <div>
                <label>First Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>
            <br>
            <div>
                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
            <br>
            <div>
                <label>Username:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <br>
            <div>
                <label>New Password:</label>
                <input type="password" name="password">
            </div>
            <br>
            <div>
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password">
            </div>
            <br>
            <button type="submit">Update Profile</button>
        </form>
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
