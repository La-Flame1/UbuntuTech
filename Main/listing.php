<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "Ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Handle form submission
$success = $error = '';
$last_inserted_id = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_listing'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image_path = trim($_POST['image_path'] ?? 'images/default-product.jpg');
    $catalog = trim($_POST['catalog'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');

    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $error = "All fields (name, description, price, stock) are required, and price/stock must be valid.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_path, catalog, serial_number, user_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssss", $name, $description, $price, $stock, $image_path, $catalog, $serial_number, $_SESSION['user']);
        if ($stmt->execute()) {
            $success = "Listing created successfully!";
            $last_inserted_id = $conn->insert_id;
        } else {
            $error = "Failed to create listing. Please try again.";
        }
        $stmt->close();
    }
}

// Handle delete action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_listing'])) {
    $serial_number_to_delete = trim($_POST['serial_number_to_delete'] ?? '');

    if (!empty($serial_number_to_delete)) {
        $stmt = $conn->prepare("DELETE FROM products WHERE serial_number = ? AND user_email = ?");
        $stmt->bind_param("ss", $serial_number_to_delete, $_SESSION['user']);
        if ($stmt->execute()) {
            $success = "Listing deleted successfully!";
            // Optionally refresh listings after deletion
            header("Refresh:0");
        } else {
            $error = "Failed to delete listing. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Serial number is required to delete a listing.";
    }
}


// Fetch user's listings
$user_email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT product_id, name, price, image_path FROM products WHERE user_email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$listings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<?php include 'nav.php'; ?>

<!-- Main Content -->
<div class="sm:ml-64 p-6 flex-1">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Listings</h1>

    <?php if (isset($success)) { ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
        </div>
        <?php if ($last_inserted_id) {
            $new_listing = $conn->query("SELECT serial_number, name, price, image_path FROM products WHERE serial_number = $last_inserted_number")->fetch_assoc();
        ?>
            <div class="bg-white p-4 rounded-lg shadow mb-4">
                <h3 class="text-lg font-semibold">Newly Added Listing</h3>
                <p>Name: <?php echo htmlspecialchars($new_listing['name']); ?></p>
                <p>Price: R<?php echo number_format($new_listing['price'], 2); ?></p>
                <img src="<?php echo htmlspecialchars($new_listing['image_path']); ?>" alt="<?php echo htmlspecialchars($new_listing['name']); ?>" class="w-16 h-16 object-cover rounded-lg">
            </div>
        <?php } ?>
    <?php } elseif (isset($error)) { ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php } ?>

    <!-- Create New Listing Form -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Create a Listing</h2>
        <form method="POST" action="listing.php" class="space-y-4">
            <div>
                
                <label for="name" class="block text-gray-700 font-medium mb-1">Product Name</label>
                <input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            </div>
            <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea id="description" name="description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" rows="4" required></textarea>
            </div>
            <div>
                <label for="price" class="block text-gray-700 font-medium mb-1">Price (R)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            </div>
            <div>
                <label for="stock" class="block text-gray-700 font-medium mb-1">Stock</label>
                <input type="number" id="stock" name="stock" min="0" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            </div>
            <div>
                <label for="image_path" class="block text-gray-700 font-medium mb-1">Image Path</label>
                <input type="text" id="image_path" name="image_path" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="images/default-product.jpg">
            </div>
            <div>
                <label for="catalog" class="block text-gray-700 font-medium mb-1">Catalog</label>
                <input type="text" id="catalog" name="catalog" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <button type="submit" name="submit_listing" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Submit Listing</button>
        </form>
    </div>

    <!-- My Listings Table -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">My Listings</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Product</th>
                        <th class="py-3 px-4 text-left">Price</th>
                        <th class="py-3 px-4 text-left">Image</th>
                        <th class="py-3 px-4 text-left">Actions</th> 
                    </tr>
                    
                </thead>
                <tbody>
                    <?php if (empty($listings)) { ?>
                        <tr>
                            <td colspan="4" class="py-4 text-center text-gray-500">No listings found.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($listings as $listing) { ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($listing['product_id']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($listing['name']); ?></td>
                                <td class="py-3 px-4">R<?php echo number_format($listing['price'], 2); ?></td>
                                <td class="py-3 px-4">
                                    <img src="<?php echo htmlspecialchars($listing['image_path']); ?>" alt="<?php echo htmlspecialchars($listing['name']); ?>" class="w-16 h-16 object-cover rounded-lg">
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>