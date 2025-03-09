<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user data
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION["user"] = [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ];
            
            // Redirect to customer dashboard
            header("Location: customer_dashboard.php");
            exit();
        } else {
            echo "Invalid credentials!";
        }
    } else {
        echo "User not found!";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Concorde - Premium Automotive Fluids</title>
    
    <!-- Fonts and Icons -->
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
            --gradient-dark: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --gradient-accent: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            --success: #36B37E;
            --warning: #FFAB00;
            --danger: #FF5630;
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
            background: var(--gradient-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Background Animation */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/images/pattern.png');
            background-size: cover;
            opacity: 0.05;
            z-index: -1;
        }

        /* Floating Particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            display: block;
            background: var(--accent-light);
            border-radius: 50%;
            opacity: 0.2;
            animation: float 15s infinite ease-in-out;
        }

        .particle:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .particle:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            left: 80%;
            animation-delay: 2s;
        }

        .particle:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 40%;
            left: 60%;
            animation-delay: 4s;
        }

        .particle:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 20%;
            animation-delay: 6s;
        }

        .particle:nth-child(5) {
            width: 50px;
            height: 50px;
            top: 20%;
            left: 80%;
            animation-delay: 8s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
            100% {
                transform: translateY(0) rotate(360deg);
            }
        }

        .login-container {
            max-width: 480px;
            width: 100%;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.8s ease forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: var(--gradient-dark);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: var(--white);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .logo-container {
            position: relative;
            display: inline-block;
        }

        .logo {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
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

        .login-header h1 {
            color: var(--white);
            margin-top: 1rem;
            font-size: 1.8rem;
            opacity: 0.9;
        }

        .login-body {
            padding: 2.5rem;
        }

        .login-welcome {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-welcome h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .login-welcome p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.8rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            padding-left: 3rem;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            color: var(--text-dark);
            background-color: #F8FAFC;
            transition: var(--transition-smooth);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(91, 192, 190, 0.1);
        }

        .form-icon {
            position: absolute;
            top: 2.8rem;
            left: 1rem;
            color: var(--text-light);
            font-size: 1.2rem;
        }

        .form-control:focus + .form-icon {
            color: var(--accent);
        }

        .alert {
            padding: 1rem 1.2rem;
            border-radius: 10px;
            margin-bottom: 1.8rem;
            background: rgba(255, 86, 48, 0.1);
            border-left: 4px solid var(--danger);
            color: #9F2B1E;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .alert i {
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }

        .login-btn {
            width: 100%;
            padding: 1.2rem;
            background: var(--gradient-accent);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 12px rgba(91, 192, 190, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: skewX(-30deg);
            transition: var(--transition-smooth);
        }

        .login-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(91, 192, 190, 0.4);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .register-link {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .register-link a:hover {
            color: var(--accent-hover);
        }

        .return-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .return-link a {
            color: var(--text-light);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: var(--transition-smooth);
        }

        .return-link a:hover {
            color: var(--accent);
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
                border-radius: 15px;
            }

            .login-header {
                padding: 2rem 1.5rem;
            }

            .login-body {
                padding: 2rem 1.5rem;
            }

            .logo {
                font-size: 2rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <a href="index.php" class="logo">Concorde</a>
            </div>
            <h1>Premium Automotive Fluids</h1>
        </div>
        
        <div class="login-body">
            <div class="login-welcome">
                <h2>Welcome Back</h2>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

    <form method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="Your email">
                    <i class="fas fa-envelope form-icon"></i>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Your password">
                    <i class="fas fa-lock form-icon"></i>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
    </form>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
            </div>
            
            <div class="return-link">
                <a href="customer_dashboard.php">
                    <i class="fas fa-arrow-left"></i> Return to home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Focus the email field on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>