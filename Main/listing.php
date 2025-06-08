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
    $catalog = trim($_POST['catalog'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $image_path = 'uploads/default.jpg'; // Default image path

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_file_type = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($image_file_type, $allowed_types)) {
            $target_file = $target_dir . uniqid() . '.' . $image_file_type;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = "Failed to upload image. Please try again. Error: " . $_FILES['image']['error'];
                error_log("Image upload failed: " . $_FILES['image']['error']);
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            error_log("Unsupported image type: " . $image_file_type);
        }
    }

    if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || empty($serial_number)) {
        $error = "All fields (name, description, price, stock, serial number) are required, and price/stock must be valid.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image_path, catalog, serial_number, user_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssss", $name, $description, $price, $stock, $image_path, $catalog, $serial_number, $_SESSION['user']);
        if ($stmt->execute()) {
            $success = "Listing created successfully!";
            $last_inserted_id = $conn->insert_id;
        } else {
            $error = "Failed to create listing. Please try again. Error: " . $conn->error;
            error_log("Insert failed: " . $conn->error);
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
            if ($stmt->affected_rows > 0) {
                $success = "Listing deleted successfully!";
            } else {
                $error = "No matching listing found to delete.";
            }
            // Redirect to refresh the page with updated data
            header("Location: listing.php");
            exit();
        } else {
            $error = "Failed to delete listing. Error: " . $conn->error;
            error_log("Delete failed: " . $conn->error);
        }
        $stmt->close();
    } else {
        $error = "Serial number is required to delete a listing.";
    }
}

// Fetch user's listings
$user_email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT product_id, name, price, image_path, serial_number FROM products WHERE user_email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$listings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch newly added listing if applicable
$new_listing = null;
if ($last_inserted_id) {
    $result = $conn->query("SELECT serial_number, name, price, image_path FROM products WHERE product_id = " . $conn->real_escape_string($last_inserted_id));
    $new_listing = $result->fetch_assoc();
}

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
        <?php if ($new_listing) { ?>
            <div class="bg-white p-4 rounded-lg shadow mb-4">
                <h3 class="text-lg font-semibold">Newly Added Listing</h3>
                <p>Name: <?php echo htmlspecialchars($new_listing['name']); ?></p>
                <p>Price: R<?php echo number_format($new_listing['price'], 2); ?></p>
                <p>Serial Number: <?php echo htmlspecialchars($new_listing['serial_number']); ?></p>
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
        <form method="POST" action="listing.php" enctype="multipart/form-data" class="space-y-4">
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
                <label for="image" class="block text-gray-700 font-medium mb-1">Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label for="catalog" class="block text-gray-700 font-medium mb-1">Catalog</label>
                <input type="text" id="catalog" name="catalog" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>
            <div>
                <label for="serial_number" class="block text-gray-700 font-medium mb-1">Serial Number</label>
                <input type="text" id="serial_number" name="serial_number" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
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
                        <th class="py-3 px-4 text-left">Serial Number</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listings)) { ?>
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">No listings found.</td>
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
                                <td class="py-3 px-4"><?php echo htmlspecialchars($listing['serial_number']); ?></td>
                                <td class="py-3 px-4">
                                    <form method="POST" action="listing.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                        <input type="hidden" name="serial_number_to_delete" value="<?php echo htmlspecialchars($listing['serial_number']); ?>">
                                        <button type="submit" name="delete_listing" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
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