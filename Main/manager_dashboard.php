<?php
session_start();


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ubuntu_tech"; // Updated to match existing database; change to "Ubuntu_database" if created

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $delete_message = "Product deleted successfully!";
    } catch(PDOException $e) {
        $delete_message = "Error deleting product: " . $e->getMessage();
    }
}

// Handle listing deletion
if (isset($_POST['delete_listing'])) {
    $listing_id = $_POST['listing_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
        $stmt->execute([$listing_id]);
        $delete_message = "Listing deleted successfully!";
    } catch(PDOException $e) {
        $delete_message = "Error deleting listing: " . $e->getMessage();
    }
}

// Fetch products
$stmt = $conn->prepare("SELECT p.id, p.name, p.description, p.price, u.username 
                       FROM products p 
                       JOIN users u ON p.created_by = u.id");


// Fetch listings
$stmt = $conn->prepare("SELECT l.id, l.title, l.description, u.username 
                       FROM listings l 
                       JOIN users u ON l.created_by = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        .panel {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .delete-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-btn:hover {
            background-color: #cc0000;
        }
        .message {
            color: green;
            margin-bottom: 20px;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Manager Dashboard</h1>
    
    <?php if (isset($delete_message)): ?>
        <p class="<?php echo strpos($delete_message, 'Error') === false ? 'message' : 'error'; ?>">
            <?php echo htmlspecialchars($delete_message); ?>
        </p>
    <?php endif; ?>

    <div class="panel">
        <h2>Manage Products</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6">No products found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['username']); ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete_product" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <div class="panel">
        <h2>Manage Listings</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
            <?php if (empty($listings)): ?>
                <tr>
                    <td colspan="5">No listings found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($listing['id']); ?></td>
                        <td><?php echo htmlspecialchars($listing['title']); ?></td>
                        <td><?php echo htmlspecialchars($listing['description']); ?></td>
                        <td><?php echo htmlspecialchars($listing['username']); ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <button type="submit" name="delete_listing" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>

<?php
$conn = null;
?>