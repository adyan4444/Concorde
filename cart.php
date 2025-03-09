<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';


// Check if cart table exists
$table_check = $conn->query("SHOW TABLES LIKE 'cart'");
if ($table_check->num_rows == 0) {
    die("Cart table does not exist. Please run setup_database.php first.");
}

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity <= 0) {
        echo "<script>alert('Invalid quantity!');</script>";
    } else {
        // Check stock limit
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = (SELECT product_id FROM cart WHERE id = ?)");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $stmt->bind_result($stock);
        $stmt->fetch();
        $stmt->close();

        if ($quantity > $stock) {
            echo "<script>alert('Cannot order more than available stock!');</script>";
        } else {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            if ($stmt) {
                $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION["user"]["id"]);
                $stmt->execute();
                $stmt->close();
                echo "<script>alert('Quantity updated successfully!'); window.location.href='cart.php';</script>";
            } else {
                die("Error preparing statement: " . $conn->error);
            }
        }
    }
}


// Handle item removal
if (isset($_POST['remove_item'])) {
    $cart_id = $_POST['cart_id'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ii", $cart_id, $_SESSION["user"]["id"]);
    $stmt->execute();
    $stmt->close();
}

// Fetch cart items with error handling
$user_id = $_SESSION["user"]["id"];
$cart_query = "SELECT c.*, p.name, p.price, p.image, p.stock 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
               
$stmt = $conn->prepare($cart_query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Calculate totals
$subtotal = 0;
$shipping = 100; // Fixed shipping cost
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Concorde</title>
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

        .header {
            background: rgba(10, 17, 40, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            padding: 1.2rem 0;
            transition: all 0.3s ease;
        }

        .header.scrolled {
            padding: 0.8rem 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .nav-container {
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

        .icons-container {
            display: flex;
            gap: 1.8rem;
            align-items: center;
        }

        .nav-icon {
            color: var(--white);
            font-size: 1.4rem;
            position: relative;
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .nav-icon:hover {
            color: var(--accent);
            transform: translateY(-3px);
        }

        .main-content {
            padding: 6rem 0 2rem;
        }

        .page-title {
            color: var(--text-dark);
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }

        .cart-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }

        .cart-items {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 1.5rem;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            color: var(--text-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .item-price {
            color: var(--accent);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-input {
            width: 60px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
        }

        .remove-btn {
            background: var(--danger);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            transition: background-color 0.3s ease;
        }

        .remove-btn:hover {
            background: var(--danger-hover);
        }

        .cart-summary {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-title {
            color: var(--text-dark);
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: var(--accent);
            color: var(--white);
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 1.5rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .checkout-btn:hover {
            background: var(--accent-hover);
        }

        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .continue-shopping {
            display: inline-block;
            margin-top: 1rem;
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .continue-shopping:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="logo-container">
                    <a href="customer_dashboard.php" class="logo">Concorde</a>
                </div>
                <div class="icons-container">
                    <a href="customer_dashboard.php" class="nav-icon">
                        <i class="fas fa-home"></i>
                    </a>
                    <a href="cart.php" class="nav-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php
                        // Get cart count
                        $cart_count = $cart_items->num_rows;
if ($cart_count > 0) {
    echo '<span class="badge">' . htmlspecialchars($cart_count) . '</span>';
}

                        ?>
                    </a>
                    <a href="account.php" class="nav-icon">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Shopping Cart</h1>

            <?php if ($cart_items->num_rows > 0): ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <?php while ($item = $cart_items->fetch_assoc()):
                            $item_total = $item['price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                            <div class="cart-item">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                                <div class="item-details">
                                    <h3 class="item-name"><?php echo $item['name']; ?></h3>
                                    <p class="item-price">₹<?php echo number_format($item['price'], 2); ?></p>
                                    <form method="POST" class="quantity-controls">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="quantity-input" onchange="this.form.submit()">
                                        <button type="submit" name="remove_item" class="remove-btn">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="cart-summary">
                        <h2 class="summary-title">Order Summary</h2>
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Shipping</span>
                            <span>₹<?php echo number_format($shipping, 2); ?></span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span>₹<?php echo number_format($subtotal + $shipping, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="checkout-btn">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart fa-3x" style="color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any products yet.</p>
                    <a href="customer_dashboard.php" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>