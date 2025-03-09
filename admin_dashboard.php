<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Concorde Lubricants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            background-attachment: fixed;
            color: white;
        }
        .abstract-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.transparenttextures.com/patterns/cubes.png');
            opacity: 0.2;
            z-index: -1;
        }
    </style>
</head>
<body class="relative">
    <div class="abstract-bg"></div>
    
    <!-- Navbar -->
    <header class="bg-white shadow-md p-4 fixed w-full top-0 left-0 z-10 border-b-2 border-gray-300 flex justify-between items-center">
        <h1 class="text-lg font-bold text-blue-600">🔷 Concorde Lubricants - Admin</h1>
        <nav>
            <ul class="flex space-x-6 relative">
                <li><a href="home.html" class="text-gray-600 hover:text-black flex flex-col items-center">
                    <img src="image/home.png" alt="Home" class="w-8 h-8">
                    <span>Home</span>
                </a></li>
                <li><a href="admin_logout.php" class="text-gray-600 hover:text-black flex flex-col items-center">
                    <img src="image/logout.png" alt="Logout" class="w-8 h-8">
                    <span>Logout</span>
                </a></li>
            </ul>
        </nav>
    </header>

    <main class="pt-24 container mx-auto px-6">
        <h2 class="text-3xl font-bold text-white mb-6">Admin Dashboard</h2>
        
        <!-- Dashboard Sections -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-lg text-black">
                <h3 class="text-xl font-semibold mb-4">Manage Products</h3>
                <p>View, add, and edit products in the store.</p>
                <a href="admin_edit.html" class="mt-4 block bg-blue-600 text-white py-3 rounded-lg text-center font-semibold hover:bg-blue-700 transition">Go to Products</a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg text-black">
                <h3 class="text-xl font-semibold mb-4">View Orders</h3>
                <p>Manage customer orders and track shipments.</p>
                <a href="orders.php" class="mt-4 block bg-green-600 text-white py-3 rounded-lg text-center font-semibold hover:bg-green-700 transition">Go to Orders</a>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg text-black">
                <h3 class="text-xl font-semibold mb-4">User Management</h3>
                <p>View and manage registered customers.</p>
                <a href="users.php" class="mt-4 block bg-yellow-600 text-white py-3 rounded-lg text-center font-semibold hover:bg-yellow-700 transition">Go to Users</a>
            </div>
        </div>
    </main>
</body>
</html>
