<?php
session_start();

// Allow access for users with role 'admin'
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin'])) {
    header("Location: /UbuntuTech/Main/index.php");
    error_log("Unauthorized access attempt by user: " . ($_SESSION['user'] ?? 'Unknown'));
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "Ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location:home.php");
    exit();
}

// Handle user management
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_user') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        if ($stmt === false) {
            error_log("Prepare failed (add_user): " . $conn->error);
        } else {
            $stmt->bind_param("sss", $username, $email, $password);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($_POST['action'] == 'delete_user') {
        $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
        if ($user_id) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt === false) {
                error_log("Prepare failed (delete_user): " . $conn->error);
            } else {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Handle product management and image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_action'])) {
    if ($_POST['product_action'] == 'add_product') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $serial_number = isset($_POST['serial_number']) ? $_POST['serial_number'] : null;
        $catalog = $_POST['catalog'] ?? null;
        $layout_option = $_POST['layout_option'] ?? 'default';
        $image_path = null;

        // Handle image upload if provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['png', 'jpeg', 'jpg', 'pdf'];

            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file;
                } else {
                    error_log("Failed to upload file: " . $_FILES["product_image"]["error"]);
                }
            } else {
                error_log("Unsupported file type: $imageFileType");
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, serial_number, catalog, image_path, layout_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Prepare failed (add_product): " . $conn->error);
            die("Failed to prepare statement. Check logs.");
        }
        $stmt->bind_param("ssdsssss", $name, $description, $price, $stock, $serial_number, $catalog, $image_path, $layout_option);
        $stmt->execute();
        $stmt->close();
    } elseif ($_POST['product_action'] == 'delete_product') {
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
        if ($product_id) {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            if ($stmt === false) {
                error_log("Prepare failed (delete_product): " . $conn->error);
            } else {
                $stmt->bind_param("i", $product_id);
                if ($stmt->execute()) {
                    // Optionally redirect to avoid re-submission on refresh
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
                $stmt->close();
            }
        }
    }
}

// Fetch data
$users_result = $conn->query("SELECT * FROM users");
$products_result = $conn->query("SELECT * FROM products");

// Simple performance metrics
$performance = [
    'total_users' => $users_result ? $users_result->num_rows : 0,
    'total_products' => $products_result ? $products_result->num_rows : 0,
    'page_views' => 1500
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Dashboard</title>
    <style>
        .layout-grid img { width: 100%; height: auto; }
        .layout-list img { max-width: 200px; height: auto; display: block; margin: 0 auto; }
        .layout-carousel img { width: 300px; height: auto; display: inline-block; margin: 0 10px; }
        .truncate-lines {
            display: -webkit-box;
            -webkit-line-clamp: 5; /* Limit to 5 lines */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="fixed left-0 h-screen bg-white shadow-lg flex flex-col z-50 w-64 p-4" style="top: 24px;">
            <h2 class="text-xl font-bold mb-4">Admin Panel</h2>
            <div class="mb-4 p-2 bg-gray-100 rounded-lg">
                <p class="text-sm font-medium text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</p>
                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($_SESSION['user'] ?? 'No email'); ?></p>
            </div>
            <nav class="flex-1">
                <a href="#performance" class="block py-2 px-4 text-gray-700 hover:bg-gray-100 rounded">Performance</a>
                <a href="#users" class="block py-2 px-4 text-gray-700 hover:bg-gray-100 rounded mt-2">Manage Users</a>
                <a href="#products" class="block py-2 px-4 text-gray-700 hover:bg-gray-100 rounded mt-2">Manage Products</a>
            </nav>
            <a href="?logout=true" class="mt-8 py-2 px-4 text-white bg-red-600 hover:bg-red-700 rounded-lg text-center" style="margin-bottom: 20px;">Logout</a>
        </div>

        <div class="ml-64 p-6 flex-1" style="margin-top: 24px;">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>

            <section id="performance" class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">Website Performance</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-medium">Total Users</h3>
                        <p class="text-2xl"><?php echo $performance['total_users']; ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-medium">Total Products</h3>
                        <p class="text-2xl"><?php echo $performance['total_products']; ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-medium">Page Views</h3>
                        <p class="text-2xl"><?php echo $performance['page_views']; ?></p>
                    </div>
                </div>
            </section>

            <section id="users" class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">Manage Users</h2>
                <form method="POST" class="mb-4 space-y-4">
                    <input type="hidden" name="action" value="add_user">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <button type="submit" class="py-2 px-4 text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Add User</button>
                </form>
                <table class="w-full bg-white shadow-md rounded-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 text-left">ID</th>
                            <th class="p-2 text-left">Username</th>
                            <th class="p-2 text-left">Email</th>
                            <th class="p-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()) { ?>
                            <tr class="border-b">
                                <td class="p-2"><?php echo $user['id']; ?></td>
                                <td class="p-2"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="p-2"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="p-2">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <section id="products" class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">Manage Products</h2>
                <form method="POST" enctype="multipart/form-data" class="mb-4 space-y-4">
                    <input type="hidden" name="product_action" value="add_product">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                        <input type="text" name="serial_number" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price (R)</label>
                        <input type="number" step="0.01" name="price" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catalog</label>
                        <input type="text" name="catalog" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg" placeholder="e.g., Electronics, Clothing">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Image</label>
                        <input type="file" name="product_image" accept=".png,.jpeg,.jpg,.pdf" class="mt-1 block w-full p-2 border border-gray-300 rounded-lg">
                    </div>
                    <button type="submit" class="py-2 px-4 text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Add Product</button>
                </form>
                <table class="w-full bg-white shadow-md rounded-lg">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 text-left">Serial Number</th>
                            <th class="p-2 text-left">Name</th>
                            <th class="p-2 text-left">Description</th>
                            <th class="p-2 text-left">Price</th>
                            <th class="p-2 text-left">Stock</th>
                            <th class="p-2 text-left">Catalog</th>
                            <th class="p-2 text-left">Image</th>
                            <th class="p-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $products_result->data_seek(0); while ($product = $products_result->fetch_assoc()) { ?>
                            <tr class="border-b <?php echo 'layout-' . htmlspecialchars($product['layout_option']); ?>">
                                <td class="p-2"><?php echo htmlspecialchars($product['serial_number']); ?></td>
                                <td class="p-2"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="p-2 truncate-lines"><?php echo htmlspecialchars($product['description']); ?></td>
                                <td class="p-2">R<?php echo number_format($product['price'], 2); ?></td>
                                <td class="p-2"><?php echo $product['stock']; ?></td>
                                <td class="p-2"><?php echo htmlspecialchars($product['catalog'] ?? 'N/A'); ?></td>
                                <td class="p-2"><?php echo $product['image_path'] ? '<a href="' . htmlspecialchars($product['image_path']) . '" target="_blank">View</a>' : 'No Image'; ?></td>
                                <td class="p-2">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?php echo isset($product['product_id']) ? htmlspecialchars($product['product_id']) : ''; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('tr').forEach(row => {
            const layout = row.getAttribute('class')?.split('layout-')[1];
            if (layout) {
                row.classList.add('layout-' + layout);
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>