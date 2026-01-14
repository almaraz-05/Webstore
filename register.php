<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$dsn = "mysql:host=courses;dbname=z1963386";
$username = "z1963386";
$password = "2000Dec22";

try {
    $pdo = new PDO($dsn, $username, $password);
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

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <style>
        body {font-family: Arial, sans-serif; margin: 20px; line-height: 1.6;}
        form { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; width: 400px;}
        label { display: block; margin-bottom: 5px; }
        input { padding: 5px; margin-bottom: 10px; width: 100%; }
        input[type="submit"] { background-color: #4CAF50; color: white; border: none; cursor: pointer; width: auto; }
        input[type="submit"]:hover { background-color: #45a049; }
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; }
    </style>
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
