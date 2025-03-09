<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION["user"]["id"])) {
    echo json_encode(["status" => "error", "message" => "Please log in to add items to the cart."]);
    exit;
}

$user_id = $_SESSION["user"]["id"];
$product_id = $_POST["product_id"];
$quantity = $_POST["quantity"];

// Check if the product is already in the cart
$sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity
    $sql = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
} else {
    // Insert new item
    $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Item added to cart!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add item to cart."]);
}
// Function to get cart count
function getCartCount($user_id, $conn) {
    $sql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row["total"] ?? 0;
}

?>
