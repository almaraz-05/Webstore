<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping and Billing</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <h2>Shipping and Billing Information</h2>

    <form method="POST" action="confirm_order.php">
        <h3>Shipping Address</h3>

        <label for="street">Street Address:</label>
        <input type="text" id="street" name="street" required>

        <label for="city">City:</label>
        <input type="text" id="city" name="city" required>

        <label for="state">State:</label>
        <input type="text" id="state" name="state" required>

        <label for="postal">Postal Code:</label>
        <input type="text" id="postal" name="postal" required>

        <h3>Billing Info</h3>

        <label for="cardname">Name on Card:</label>
        <input type="text" id="cardname" name="cardname" required>

        <label for="cardnumber">Card Number:</label>
        <input type="text" id="cardnumber" name="cardnumber" required>

        <label for="expdate">Expiration Date (MM/YY):</label>
        <input type="text" id="expdate" name="expdate" required>

        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" required>

        <input type="submit" value="Place Order">
    </form>
</div>

</body>
</html>
