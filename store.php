<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'secrets.php';

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Redirect to register.php if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}
$dsn = "mysql:host=courses;dbname=z1963386";

try {
    $pdo = new PDO($dsn, $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Process form submission if any
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'] ?: $_POST['product_dropdown'];
    $quantity = intval($_POST['quantity']);
    $action = $_POST['action'];

    if (!$product_id || $quantity <= 0) {
        echo "<p style='color:red;'>Invalid product or quantity.</p>";
    } else {
        if ($action === "add") {
            // Check if product already in cart
            $stmt = $pdo->prepare("SELECT Amount FROM Shop_Cart WHERE UserID = ? AND ProdID = ?");
            $stmt->execute([$user_id, $product_id]);
            $existing = $stmt->fetchColumn();

            if ($existing !== false) {
                // Update quantity
                $new_qty = $existing + $quantity;
                $update = $pdo->prepare("UPDATE Shop_Cart SET Amount = ? WHERE UserID = ? AND ProdID = ?");
                $update->execute([$new_qty, $user_id, $product_id]);
            } else {
                // Insert new item
                $insert = $pdo->prepare("INSERT INTO Shop_Cart (UserID, ProdID, Amount) VALUES (?, ?, ?)");
                $insert->execute([$user_id, $product_id, $quantity]);
            }

            // Redirect to cart page after adding
            header("Location: checkout.php");
            exit();

        } elseif ($action === "remove") {
            // Remove item from cart
            $delete = $pdo->prepare("DELETE FROM Shop_Cart WHERE UserID = ? AND ProdID = ?");
            $delete->execute([$user_id, $product_id]);

            // Redirect to cart page after removing
            header("Location: checkout.php");
            exit();

        } else {
            echo "<p style='color:red;'>Invalid action.</p>";
        }
    }
}

// Now fetch inventory for display
$sql = "SELECT Items.ProdID, Items.Prod_Name, Items.Price, Stock.Quantity
        FROM Items
        JOIN Stock ON Items.ProdID = Stock.ProdID";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
  <body>

<div style="text-align: right;">
    <form method="post" style="display: inline;">
        <button type="submit" name="logout">Sign Out</button>
    </form>
</div>


    <h2>Items Currently Available</h2>

    <?php if (!empty($products)) : ?>
        <table border="1" cellpadding="5">
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Price ($)</th>
                <th>Quantity Available</th>
            </tr>
            <?php foreach ($products as $row) : ?>
                <tr>
                    <td><?= htmlspecialchars($row["ProdID"]) ?></td>
                    <td><?= htmlspecialchars($row["Prod_Name"]) ?></td>
                    <td>$<?= number_format($row["Price"], 2) ?></td>
                    <td><?= htmlspecialchars($row["Quantity"]) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <p>We are out of stock in everything!</p>
    <?php endif; ?>

    <br><br>
    <h3>Add or Remove Items from Your Cart</h3>

    <form method="post" action="">
        <label for="product_dropdown">Select a Product:</label><br>
        <select name="product_dropdown">
            <option value="">Choose a product</option>
            <?php foreach ($products as $row) : ?>
                <option value="<?= htmlspecialchars($row["ProdID"]) ?>">
                    <?= htmlspecialchars($row["Prod_Name"]) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="product_id">OR Enter item ID:</label><br>
        <input type="number" name="product_id" min="1"><br><br>

        <label for="quantity">Quantity:</label><br>
        <input type="number" name="quantity" value="1" min="1"><br><br>

        <button type="submit" name="action" value="add">Add to Cart</button>
        <button type="submit" name="action" value="remove">Remove from Cart</button>
    </form>

    <br><br>
    <!-- Manual link to view cart if they want -->
    <a href="checkout.php"><button type="button">View Your Shopping Cart</button></a>

  </body>
</html>
