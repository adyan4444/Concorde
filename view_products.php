<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Products</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        .delete-btn {
            color: red;
            cursor: pointer;
        }
        .edit-btn {
            color: blue;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Product List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Image</th>
            <th>Action</th> <!-- ✅ Fixed Action Header -->
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td>₹<?php echo number_format($row['price'], 2); ?></td>
                <td><img src="<?php echo $row['image']; ?>" alt="Product Image"></td>
                <td>
                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="edit-btn">Edit</a> |
                    <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="delete-btn">Delete</a>
                </td> <!-- ✅ Fixed Edit/Delete Buttons inside Row -->
            </tr>
        <?php } ?>

    </table>
</body>
</html>
