<?php
session_start();
include '../db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["role"]) || $_SESSION["user"]["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Get statistics
$stats = [
    'products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'],
    'cart_items' => $conn->query("SELECT COUNT(*) as count FROM cart")->fetch_assoc()['count']
];

// Get recent products
$recent_products = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Concorde</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
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
            background: rgba(10, 17, 40, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1.2rem 0;
            transition: all 0.3s ease;
        }

        .admin-header.scrolled {
            padding: 0.8rem 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
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

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--white);
            text-decoration: none;
            letter-spacing: 1px;
            position: relative;
            padding-left: 32px;
        }

        .logo::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 15px var(--accent-light);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(91, 192, 190, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(91, 192, 190, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(91, 192, 190, 0);
            }
        }

        .admin-nav {
            display: flex;
            gap: 1.8rem;
            align-items: center;
        }

        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            color: var(--accent);
            transform: translateY(-3px);
        }

        .nav-link i {
            font-size: 1.2rem;
        }

        .main-content {
            padding: 7rem 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-title {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-title i {
            color: var(--accent);
            font-size: 1.4rem;
        }

        .stat-value {
            color: var(--text-dark);
            font-size: 2.5rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
        }

        .recent-products {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            color: var(--text-dark);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .section-title i {
            color: var(--accent);
        }

        .product-list {
            display: grid;
            gap: 1.5rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-radius: 10px;
            background: #f8f9fa;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-sm);
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 1.5rem;
        }

        .product-info {
            flex: 1;
        }

        .product-info h3 {
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .product-info p {
            color: var(--text-light);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-3px);
        }

        .btn-secondary {
            background: var(--secondary);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: var(--primary-light);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <a href="dashboard.php" class="logo">Concorde</a>
                </div>
                <nav class="admin-nav">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <a href="add_product.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </a>
                    <a href="manage_products.php" class="nav-link">
                        <i class="fas fa-box"></i> Products
                    </a>
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-box"></i>
                        Total Products
                    </div>
                    <div class="stat-value"><?php echo $stats['products']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-users"></i>
                        Total Customers
                    </div>
                    <div class="stat-value"><?php echo $stats['users']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-shopping-cart"></i>
                        Items in Cart
                    </div>
                    <div class="stat-value"><?php echo $stats['cart_items']; ?></div>
                </div>
            </div>

            <div class="recent-products">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Recent Products
                </h2>
                <div class="product-list">
                    <?php while($product = $recent_products->fetch_assoc()): ?>
                        <div class="product-item">
                            <img src="../<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-image">
                            <div class="product-info">
                                <h3><?php echo $product['name']; ?></h3>
                                <p>
                                    <i class="fas fa-rupee-sign"></i>
                                    <?php echo number_format($product['price'], 2); ?> 
                                    <span style="margin-left: 1rem;">
                                        <i class="fas fa-box"></i>
                                        Stock: <?php echo $product['stock']; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="action-buttons">
                    <a href="add_product.php" class="action-btn btn-primary">
                        <i class="fas fa-plus-circle"></i>
                        Add New Product
                    </a>
                    <a href="manage_products.php" class="action-btn btn-secondary">
                        <i class="fas fa-cog"></i>
                        Manage Products
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Add scroll effect to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.admin-header');
            if (window.scrollY > 0) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html> 