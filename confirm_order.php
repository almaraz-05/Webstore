<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'secrets.php';

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['user_id'];


$dsn = "mysql:host=courses;dbname=z1963386";

try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert into orders with random track num
    $stmt = $pdo->prepare("INSERT INTO Orders (UserID, Status, Tracking_Num) VALUES (?, 'Processing', CONCAT('TRK', FLOOR(RAND() * 100000)))");
    $stmt->execute([$userid]);
    $orderID = $pdo->lastInsertId();

    // Insert shipping info
    $stmt = $pdo->prepare("INSERT INTO Shipping_Info (UserID, Street_Address, City, State, Postal_Code) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $userid,
        $_POST['street'],
        $_POST['city'],
        $_POST['state'],
        $_POST['postal']
    ]);
    $shippingID = $pdo->lastInsertId();

    // Insert billing info
    $stmt = $pdo->prepare("INSERT INTO Billing_Info (UserID, Full_Name, Credit_card_no, Credit_card_ex, CVV) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $userid,
        $_POST['cardname'],
        $_POST['cardnumber'],
        $_POST['expdate'],
        $_POST['cvv']
    ]);

    // Calculate total
    $stmt = $pdo->prepare("SELECT Items.Price, Shop_Cart.Amount FROM Shop_Cart JOIN Items ON Shop_Cart.ProdID = Items.ProdID WHERE Shop_Cart.UserID = ?");
    $stmt->execute([$userid]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['Price'] * $item['Amount'];
    }

    // Insert order details
    $stmt = $pdo->prepare("INSERT INTO Order_Details (OrderID, Order_Date, Order_Total, ShippingID) VALUES (?, CURDATE(), ?, ?)");
    $stmt->execute([$orderID, $total, $shippingID]);

    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM Shop_Cart WHERE UserID = ?");
    $stmt->execute([$userid]);

    $success = true;

} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
    $success = false;
}
?>

<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f9f9f9;
        }
        .confirmation {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 30px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .confirmation h2 {
            color: #4CAF50;
        }
        .details {
            margin-top: 20px;
            padding: 10px;
            background: #f1f1f1;
        }
        .details p {
            margin: 8px 0;
        }
        .button {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

    </style>
</head>
<body>
    <div class="confirmation">
        <h2>Order Confirmation</h2>

        <?php if ($success): ?>
            <p>Thank you! Your order has been placed successfully.</p>

            <div class="details">
                <p><strong>Order ID:</strong> <?php echo $orderID; ?></p>
                <p><strong>Tracking Number:</strong> <?php
                    
                    $stmt = $pdo->prepare("SELECT Tracking_Num FROM Orders WHERE OrderID = ?");
                    $stmt->execute([$orderID]);
                    echo htmlspecialchars($stmt->fetchColumn());
                ?></p>
                <p><strong>Order Total:</strong> $<?php echo number_format($total, 2); ?></p>
                <p><strong>Order Date:</strong> <?php echo date("Y-m-d"); ?></p>
            </div>

            <a class="button" href="store.php">Return to Store</a>
        <?php else: ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
