<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'secrets.php';

// Database connection
$dsn = "mysql:host=courses;dbname=z1963386";

try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection to database failed: " . $e->getMessage());
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $formUsername = $_POST['username'];
    $formPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate input
    if (empty($fullName) || empty($email) || empty($formUsername) || empty($formPassword)) {
        $error = "Please fill all required fields";
    } elseif ($formPassword !== $confirmPassword) {
        $error = "Passwords do not match";
    } elseif (strlen($formPassword) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT UserID FROM User WHERE Username = ? OR Email = ?");
        $stmt->execute([$formUsername, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $error = "Username or Email already exists";
        } else {
            // Hash password
            $hashedPassword = password_hash($formPassword, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO User (Full_Name, Email, Username, Password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullName, $email, $formUsername, $hashedPassword]);

            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>

    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Create an Account</h2>

<?php if (!empty($error)): ?>
    <div class="alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="post" action="">
    <label for="full_name">Full Name</label>
    <input type="text" id="full_name" name="full_name" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="username">Username</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <label for="confirm_password">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <input type="submit" value="Register">
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

</body>
</html>
