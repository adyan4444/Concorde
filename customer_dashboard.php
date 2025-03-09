<?php
session_start();
include 'db_connect.php';

// Clear and consistent login status check
$is_logged_in = isset($_SESSION["user"]) && !empty($_SESSION["user"]);
$products = [];
$db_connected = false;
$cart_count = 0;

// Check database connection
if (isset($conn)) {
    if (!$conn->connect_error) {
        $db_connected = true;
        
        // Get cart count if user is logged in
        if ($is_logged_in && isset($_SESSION["user"]["id"])) {
            $user_id = $_SESSION["user"]["id"];
            $cart_query = "SELECT COUNT(*) as cart_count FROM cart WHERE user_id = ?";
            $stmt = $conn->prepare($cart_query);
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $cart_count = $result->fetch_assoc()['cart_count'];
                }
                $stmt->close();
            }
        }
        
        // Fetch products
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concorde - Premium Automotive Fluids</title>
    
    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Your existing CSS styles here */
        /* All the CSS from the original file */
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
            --gradient-dark: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --gradient-accent: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            --success: #36B37E;
            --warning: #FFAB00;
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 6px 16px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 12px 24px rgba(0, 0, 0, 0.16);
            --shadow-hover: 0 18px 32px rgba(10, 17, 40, 0.2);
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
        }

        /* Header Styles */
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
            max-width: 1400px;
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

        .nav-icon .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .status-indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: red;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            background: var(--primary);
            padding-top: 80px;
        }

        .engine-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.6;
            z-index: 0;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(10, 17, 40, 0.9) 0%, rgba(10, 17, 40, 0.7) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
            padding: 0 2rem;
        }

        .hero h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards;
        }

        .hero h1 span {
            display: block;
            color: var(--accent);
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--gray-light);
            margin-bottom: 2.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease 0.2s forwards;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease 0.4s forwards;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: var(--transition-smooth);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--white);
            border: none;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .btn-outline {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--accent);
        }

        .btn-outline:hover {
            background: rgba(91, 192, 190, 0.1);
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .btn i {
            font-size: 1.2rem;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: var(--white);
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .section-title p {
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
        }

        .features-grid {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
        }

        .feature-card {
            position: relative;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            background: var(--gradient-dark);
            color: var(--white);
            box-shadow: var(--shadow-md);
            transition: var(--transition-smooth);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(91, 192, 190, 0.1);
            opacity: 0;
            transition: var(--transition-smooth);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--accent);
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Categories Section */
        .categories {
            padding: 6rem 0;
            background: var(--gray-light);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .category-card {
            position: relative;
            height: 350px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition-smooth);
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .category-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-smooth);
        }

        .category-card:hover img {
            transform: scale(1.05);
        }

        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 2rem;
            background: linear-gradient(transparent, rgba(10, 17, 40, 0.95));
            color: var(--white);
            transition: var(--transition-smooth);
        }

        .category-card:hover .category-overlay {
            background: linear-gradient(transparent, rgba(91, 192, 190, 0.95));
        }

        .category-overlay h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .category-overlay p {
            font-size: 1rem;
        }

        /* Products Section */
        .products {
            padding: 6rem 0;
            background: var(--white);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
        }

        .product-card {
            position: relative;
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent);
            color: var(--white);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .product-image {
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition-smooth);
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-details {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .product-description {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .product-price {
            color: var(--accent);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .product-form {
            margin-top: auto;
        }

        .product-quantity {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 1rem;
            background: var(--accent);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition-smooth);
        }

        .add-to-cart-btn:hover {
            background: var(--accent-hover);
        }

        /* Company Details Section */
        .company-details {
            padding: 6rem 0;
            background: var(--primary);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .company-details::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('assets/images/pattern.png');
            opacity: 0.05;
            pointer-events: none;
        }

        .company-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
        }

        .company-card {
            position: relative;
            z-index: 1;
        }

        .company-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--accent);
        }

        .company-card p {
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .stat-item {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .contact-list {
            list-style: none;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(91, 192, 190, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 1.2rem;
        }

        .contact-text {
            font-size: 1rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.2rem;
            transition: var(--transition-smooth);
        }

        .social-link:hover {
            background: var(--accent);
            transform: translateY(-5px);
        }

        /* Footer */
        .footer {
            background: var(--primary-light);
            padding: 2rem 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-accent);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            z-index: 100;
            transition: var(--transition-smooth);
        }

        .fab:hover {
            transform: scale(1.1) rotate(10deg);
        }

        /* Media Queries */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 3.5rem;
            }

            .features-grid {
                gap: 2rem;
            }

            .feature-card {
                width: 220px;
                height: 220px;
                padding: 1.5rem;
            }

            .feature-icon {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1.5rem;
            }

            .hero h1 {
                font-size: 2.8rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .features-grid {
                gap: 1.5rem;
            }

            .feature-card {
                width: 180px;
                height: 180px;
                padding: 1.2rem;
            }

            .feature-icon {
                font-size: 2rem;
                margin-bottom: 0.8rem;
            }

            .feature-card h3 {
                font-size: 1.1rem;
            }

            .feature-card p {
                font-size: 0.8rem;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn {
                width: 100%;
            }
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: var(--white);
            min-width: 160px;
            box-shadow: var(--shadow-md);
            z-index: 1;
            right: 0;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            color: var(--text-dark);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            background-color: transparent;
        }

        .dropdown-content a:hover {
            background-color: var(--gray-light);
        }

        .login-prompt {
            width: 100%;
            text-align: center;
        }

        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            background: var(--gray-light);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div id="top"></div>
    
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-container">
                <div class="logo-container">
                    <a href="#" class="logo">Concorde</a>
                </div>
                <div class="icons-container">
                    <a href="#" class="nav-icon">
                        <i class="fas fa-home"></i>
                    </a>
                    <a href="cart.php" class="nav-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <a href="#" class="nav-icon">
                            <i class="fas fa-user"></i>
                            <span class="status-indicator" style="background: <?php echo $is_logged_in ? 'green' : 'red'; ?>;"></span>
                        </a>
                        <div class="dropdown-content">
                            <?php if ($is_logged_in): ?>
                                <a href="logout.php">Logout</a>
                            <?php else: ?>
                                <a href="login.php">Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Database Connection Status -->
    <?php if ($db_connected): ?>
        <!-- Connection successful, no need to show message -->
    <?php else: ?>
        <div style="margin-top: 100px; padding: 20px; background-color: #f8d7da; color: #721c24; text-align: center;">
            <p>Database connection failed. Please try again later.</p>
        </div>
    <?php endif; ?>

    <!-- Hero Section with Engine Animation -->
    <section class="hero">
        <video class="engine-video" autoplay loop muted playsinline>
            <source src="assets/videos/engine-video.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Engineering <span>Excellence</span></h1>
            <p>Discover premium automotive fluids engineered to maximize performance, extend engine life, and deliver unparalleled protection for your vehicle.</p>
            <div class="hero-buttons">
                <a href="#products" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Shop Now
                </a>
                <a href="#company" class="btn btn-outline">
                    <i class="fas fa-info-circle"></i> Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Circular Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Concorde</h2>
                <p>Our advanced formulations deliver exceptional results where it matters most</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-temperature-high"></i>
                    </div>
                    <h3>Thermal Stability</h3>
                    <p>Superior performance at extreme temperatures</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Engine Protection</h3>
                    <p>Advanced wear prevention technology</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3>Power Maximizer</h3>
                    <p>Enhanced performance for peak engine output</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-flask"></i>
                    </div>
                    <h3>Lab Certified</h3>
                    <p>Rigorously tested for guaranteed quality</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories" id="categories">
        <div class="container">
            <div class="section-title">
                <h2>Product Categories</h2>
                <p>Explore our comprehensive range of premium automotive fluids</p>
            </div>
            <div class="categories-grid">
                <div class="category-card">
                    <img src="assets/images/engine-oil-category.jpg" alt="Engine Oils">
                    <div class="category-overlay">
                        <h3>Synthetic Engine Oils</h3>
                        <p>Advanced molecular engineering for superior protection
                    </div>
                </div>
                <div class="category-card">
                    <img src="assets/images/coolant-category.jpg" alt="Coolants">
                    <div class="category-overlay">
                        <h3>Performance Coolants</h3>
                        <p>Ultimate temperature control solutions</p>
                    </div>
                </div>
                <div class="category-card">
                    <img src="assets/images/transmission-category.jpg" alt="Transmission Fluids">
                    <div class="category-overlay">
                        <h3>Transmission Fluids</h3>
                        <p>Smooth gear transitions and optimal power transfer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <?php
    


// Initialize variables



// Check if user is logged in
if (!isset($_SESSION["user"])) {
    // Display login prompt via JavaScript
    echo '<script>
        document.querySelector(".status-indicator").style.background = "red";
        document.querySelector(".dropdown-content").innerHTML = "<a href=\"login.php\">Login</a>";
    </script>';
} else {
    // Check database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }else {
        $db_connected = true;
        // Fetch products
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content remains the same -->
</head>
<body>
    <!-- Header content remains the same -->

    <!-- Products Section -->
    <section class="products" id="products">
        <div class="container">
            <div class="section-title">
                <h2>Featured Products</h2>
                <p>Experience the pinnacle of automotive fluid engineering</p>
            </div>
            <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <span class="product-badge"><?= htmlspecialchars($product['tag'] ?? '') ?></span>
                            <div class="product-image">
                                <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="product-details">
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                <p class="product-price">₹<?= number_format($product['price'], 2) ?></p>
                                <?php if ($is_logged_in): ?>
                                    <form class="product-form" onsubmit="add-To-Cart(event, <?= $product['id'] ?>)">
    <input type="number" name="quantity" value="1" min="1" class="product-quantity" id="quantity-<?= $product['id'] ?>">
    <button type="submit" class="add-to-cart-btn">
        <i class="fas fa-cart-plus"></i> Add to Cart
    </button>
</form>

<script>
function loadCart() {
    fetch('fetch_cart.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('cart-container').innerHTML = data;
    })
    .catch(error => console.error('Error:', error));
}

// Load cart items when the page loads
document.addEventListener("DOMContentLoaded", loadCart);
function addToCart(event, productId) {
    event.preventDefault(); // Prevent page reload

    let quantity = document.getElementById('quantity-' + productId).value;

    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Show success message
        loadCart(); // Update the cart UI
    })
    .catch(error => console.error('Error:', error));
}

</script>

                                <?php else: ?>
                                    <div class="login-prompt">
                                        <a href="login.php" class="btn btn-primary">Login to Purchase</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No products currently available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <!-- Company Details Section -->
    <section class="company-details" id="company">
        <div class="container">
            <div class="section-title">
                <h2>About Us</h2>
                <p>Learn more about our commitment to excellence</p>
            </div>
            <div class="company-container">
                <div class="company-card">
                    <h3>Our Mission</h3>
                    <p>To deliver premium automotive fluids that maximize performance, extend engine life, and deliver unparalleled protection for your vehicle.</p>
                </div>
                <div class="company-card">
                    <h3>Our Values</h3>
                    <p>Quality, Innovation, Sustainability, and Customer Satisfaction.</p>
                </div>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <h3 class="stat-number">100+</h3>
                    <p class="stat-label">Products</p>
                </div>
                <div class="stat-item">
                    <h3 class="stat-number">20+</h3>
                    <p class="stat-label">Years in Business</p>
                </div>
            </div>
            <div class="contact-list">
                <div class="contact-item">
                    <i class="contact-icon fas fa-phone"></i>
                    <p class="contact-text">+91 123-456-7890</p>
                </div>
                <div class="contact-item">
                    <i class="contact-icon fas fa-envelope"></i>
                    <p class="contact-text">info@concorde.com</p>
                </div>
                <div class="contact-item">
                    <i class="contact-icon fas fa-map-marker-alt"></i>
                    <p class="contact-text">123 Main Street, City, State, ZIP</p>
                </div>
            </div>
            <div class="social-links">
                <a href="#" class="social-link">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-link">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Concorde. All rights reserved.</p>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <a href="#top" class="fab">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Logout function
        function logout() {
            <?php session_destroy(); ?>
            document.querySelector('.status-indicator').style.background = 'red';
            document.querySelector('.dropdown-content').innerHTML = '<a href="login.php">Login</a>';
        }
    </script>
</body>
</html>