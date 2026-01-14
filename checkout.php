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
    $pdo = new PDO($dsn, $username, $password);
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

<html>
<head>
    <title>Checkout</title>
</head>
<body>

<h2>Shopping Cart</h2>

<?php if (!empty($cart)): ?>
    <table border="1">
        <tr>
            <th>Product Name</th>
            <th>Price Each</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>

        <?php
        foreach ($cart as $item) {
            $subtotal = $item['Price'] * $item['Amount'];
            $total += $subtotal;

            echo "<tr>";
            echo "<td>" . $item['Prod_name'] . "</td>";
            echo "<td>$" . number_format($item['Price'], 2) . "</td>";
            echo "<td>" . $item['Amount'] . "</td>";
            echo "<td>$" . number_format($subtotal, 2) . "</td>";
            echo "</tr>";
        }
        ?>

    </table>

    <h3>Total: <?php echo "$" . number_format($total, 2); ?></h3>

    <form method="POST" action="shipping_billing.php">
        <input type="submit" value="Proceed to Checkout">
    </form>
    <br>
    <a href="store.php"><button type="button">Continue Shopping</button></a>


<?php else: ?>

    <p>Your cart is empty!</p>
    <a href="store.php"><button type="button">Back to Store</button></a>
<?php endif; ?>

</body>
</html>
