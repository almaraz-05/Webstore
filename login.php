<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'secrets.php';

try {
    $dsn = "mysql:host=courses;dbname=z1963386";
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Username and password are required";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ?");
            $stmt->bindParam(1, $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['Password'])) {
                    $_SESSION['user_id'] = $user['UserID'];
                    $_SESSION['username'] = $user['Username'];
                    header("Location: store.php");
                    exit;
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "User not found";
            }
        }
    }
} catch (PDOException $e) {
    $error = "Connection to database failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>
<div class="container login-container">
    <h1>Login</h1>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter Username" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter Password" required>

        <input type="submit" value="Login">

        <p><a href="register.php">Register</a></p>
    </form>
</div>
</body></html>
