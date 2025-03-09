<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user"]["id"])) {
    echo "<p>Please login to view your cart.</p>";
    exit;
}

$user_id = $_SESSION["user"]["id"];
$sql = "SELECT p.name, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['name']} - Qty: {$row['quantity']} - ₹" . number_format($row['price'] * $row['quantity'], 2) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Your cart is empty.</p>";
}
?>
