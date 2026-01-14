<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userid = $_SESSION['user_id'];
?>

<html>
<head>
    <title>Shipping and Billing</title>
</head>
<body>

<h2>Shipping and Billing Information</h2>

<form method="POST" action="confirm_order.php">
    <h3>Shipping Address</h3>
    <label>Street Address:</label><br>
    <input type="text" name="street" required><br><br>

    <label>City:</label><br>
    <input type="text" name="city" required><br><br>

    <label>State:</label><br>
    <input type="text" name="state" required><br><br>

    <label>Postal Code:</label><br>
    <input type="text" name="postal" required><br><br>

    <h3>Billing Info</h3>
    <label>Name on Card:</label><br>
    <input type="text" name="cardname" required><br><br>

    <label>Card Number:</label><br>
    <input type="text" name="cardnumber" required><br><br>

    <label>Expiration Date (MM/YY):</label><br>
    <input type="text" name="expdate" required><br><br>

    <label>CVV:</label><br>
    <input type="text" name="cvv" required><br><br>

    <input type="submit" value="Place Order">
</form>

</body>
</html>

