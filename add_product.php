<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // File upload handling
    $target_dir = "uploads/";

    // Ensure the folder exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

    // Check file size
    if ($_FILES["image"]["size"] > $maxFileSize) {
        die("Error: File size exceeds 5MB limit.");
    }

    // Move uploaded file
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        die("Error uploading file!");
    }

    // Insert into DB using prepared statement to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $name, $description, $price, $target_file);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!');</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #0A1128;
            --primary-light: #1C2541;
            --secondary: #3A506B;
            --accent: #5BC0BE;
            --accent-hover: #3A9190;
            --accent-light: #6FFFE9;
            --gray-light: #F5F5F5;
            --gray-dark: #333F58;
            --white: #FFFFFF;
            --text-dark: #0A1128;
            --text-light: #6B7C93;
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-md: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--white);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
            padding: 2rem;
        }

        h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--gray-light);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }

        input[type="file"] {
            margin-bottom: 1rem;
        }

        button {
            width: 100%;
            padding: 1rem;
            background: var(--accent);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        button:hover {
            background: var(--accent-hover);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="logo-container">
                    <a href="#" class="logo">Concorde</a>
                </div>
                <div class="icons-container">
                    <a href="admin_dashboard.php" class="nav-icon">Dashboard</a>
                    <a href="products.php" class="nav-icon">Products</a>
                    <a href="logout.php" class="nav-icon">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <h2>Add Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required><br>
        <textarea name="description" placeholder="Product Description" required></textarea><br>
        <input type="number" name="price" placeholder="Price" required><br>
        <input type="file" name="image" required><br>
        <button type="submit">Add Product</button>
    </form>
</body>
</html> 
