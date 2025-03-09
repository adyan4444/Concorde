<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cart_id = $_POST["cart_id"];

    if (isset($_POST["increase"])) {
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE id = ?";
    } elseif (isset($_POST["decrease"])) {
        $sql = "UPDATE cart SET quantity = GREATEST(quantity - 1, 1) WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
}

// Redirect back to cart
header("Location: cart.php");
exit();
?>
