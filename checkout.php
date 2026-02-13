<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'secrets.php';

if (isset($_SESSION['user_id'])) {
    $userid = $_SESSION['user_id'];
} else {
    header("Location: login.php");
    exit();
}


$dsn = "mysql:host=courses;dbname=z1963386";

try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        SELECT Items.Prod_name, Items.Price, Shop_Cart.Amount
        FROM Shop_Cart
        JOIN Items ON Shop_Cart.ProdID = Items.ProdID
        WHERE Shop_Cart.UserID = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userid]);
    $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Shopping Cart</h2>

    <?php if (!empty($cart)): ?>

        <div class="card">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Price Each</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;

                    foreach ($cart as $item) {
                        $subtotal = $item['Price'] * $item['Amount'];
                        $total += $subtotal;

                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($item['Prod_name']) . "</td>";
                        echo "<td>$" . number_format($item['Price'], 2) . "</td>";
                        echo "<td>" . (int)$item['Amount'] . "</td>";
                        echo "<td>$" . number_format($subtotal, 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div class="total-row">
                <span class="total-label">Total:</span>
                <span class="total-value">$<?php echo number_format($total, 2); ?></span>
            </div>

            <div class="actions">
                <form method="POST" action="shipping_billing.php">
                    <input type="submit" value="Proceed to Checkout" class="btn btn-primary">
                </form>

                <a href="store.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>

    <?php else: ?>

        <div class="card">
            <p class="muted">Your cart is empty.</p>
            <a href="store.php" class="btn btn-primary">Back to Store</a>
        </div>

    <?php endif; ?>
</div>

</body>
</html>
