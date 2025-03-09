<?php
session_start();
include '../db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["role"]) || $_SESSION["user"]["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $success = "Product deleted successfully";
    } else {
        $error = "Error deleting product";
    }
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Concorde Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #0A1128;
            --primary-light: #1C2541;
            --secondary: #3A506B;
            --accent: #5BC0BE;
            --accent-hover: #3A9190;
            --accent-light: #6FFFE9;
            --white: #FFFFFF;
            --text-dark: #0A1128;
            --text-light: #6B7C93;
            --danger: #dc3545;
            --danger-hover: #bd2130;
            --gradient-dark: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .admin-header {
            background: var(--primary);
            color: var(--white);
            padding: 1rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--white);
            text-decoration: none;
        }

        .admin-nav {
            display: flex;
            gap: 2rem;
        }

        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .main-content {
            padding: 2rem 0;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            color: var(--text-dark);
            font-size: 1.5rem;
        }

        .add-product-btn {
            background: var(--accent);
            color: var(--white);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .add-product-btn:hover {
            background: var(--accent-hover);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-details {
            padding: 1.5rem;
        }

        .product-title {
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .product-info {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .product-price {
            color: var(--accent);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-actions {
            display: flex;
            gap: 1rem;
        }

        .edit-btn, .delete-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            flex: 1;
            text-align: center;
            transition: all 0.3s ease;
        }

        .edit-btn {
            background: var(--accent);
            color: var(--white);
        }

        .edit-btn:hover {
            background: var(--accent-hover);
        }

        .delete-btn {
            background: var(--danger);
            color: var(--white);
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
        }

        .delete-btn:hover {
            background: var(--danger-hover);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">Concorde Admin</a>
                <nav class="admin-nav">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="add_product.php" class="nav-link">Add Product</a>
                    <a href="manage_products.php" class="nav-link">Manage Products</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Manage Products</h1>
                <a href="add_product.php" class="add-product-btn">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="products-grid">
                <?php while($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                        <div class="product-details">
                            <h2 class="product-title"><?php echo $product['name']; ?></h2>
                            <p class="product-info">Stock: <?php echo $product['stock']; ?></p>
                            <p class="product-price">₹<?php echo number_format($product['price'], 2); ?></p>
                            <div class="card-actions">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</body>
</html> 