<?php
session_start();
require_once "database/DBController.php"; // Create this file for DB connection

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $hashed_password = md5($password); // For demo only
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION["username"] = $user["username"];
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["first_name"] = $user["first_name"];
        $_SESSION["last_name"] = $user["last_name"];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .login-container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if (isset($_SESSION["username"])): ?>
            <h2>Welcome, <?= htmlspecialchars($_SESSION["first_name"] . " " . $_SESSION["last_name"]) ?>!</h2>
            <p><strong>Username:</strong> <?= htmlspecialchars($_SESSION["username"]) ?></p>
            <a href="profile.php" class="profile-link">Edit Profile</a>
            <form method="post" action="logout.php" style="margin-top:15px;">
                <button type="submit">Logout</button>
            </form>
        <?php else: ?>
            <h2>Login</h2>
            <?php if ($error): ?>
                <p class="error"><?= $error ?></p>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div>
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <br>
                <div>
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <br>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>