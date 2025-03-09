<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get product ID
if (!isset($_GET['id'])) {
    die("Product ID not provided.");
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $product['image']; // Keep old image if new one is not uploaded

    // Handle Image Upload
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $maxFileSize = 5 * 1024 * 1024;

        if ($_FILES["image"]["size"] > $maxFileSize) {
            die("Error: File size exceeds 5MB limit.");
        }

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        } else {
            die("Error uploading file!");
        }
    }

    // Update Database
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?");
    $stmt->bind_param("ssdsi", $name, $description, $price, $image, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Product updated successfully!'); window.location='view_products.php';</script>";
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
</head>
<body>
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" value="<?php echo $product['name']; ?>" required><br>
        <textarea name="description" required><?php echo $product['description']; ?></textarea><br>
        <input type="number" name="price" value="<?php echo $product['price']; ?>" required><br>
        <img src="<?php echo $product['image']; ?>" width="80"><br>
        <input type="file" name="image"><br>
        <button type="submit">Update Product</button>
    </form>
</body>
</html>
