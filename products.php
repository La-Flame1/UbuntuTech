<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Database connection
$conn = new mysqli("localhost", "root", "", "Ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Handle Add to Cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity'])); // Ensure quantity is at least 1

    // Fetch product details
    $stmt = $conn->prepare("SELECT name, price, stock, description FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($product = $result->fetch_assoc()) {
        if ($product['stock'] >= $quantity) {
            // Add to cart
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $item['quantity'] += $quantity;
                    $item['subtotal'] = $item['quantity'] * $item['price'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $_SESSION['cart'][] = [
                    'product_id' => $product_id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'subtotal' => $quantity * $product['price'],
                    'description' => $product['description'] // Added description
                ];
            }
        } else {
            $error = "Not enough stock for {$product['name']}. Available: {$product['stock']}";
        }
    }
    $stmt->close();
}

// Handle Clear Cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch best sellers (top 3 by product_id as placeholder since sales_count is missing)
$best_sellers_query = "SELECT product_id, name, price, image_path FROM products ORDER BY product_id DESC LIMIT 3";
$best_sellers_result = $conn->query($best_sellers_query);
$best_sellers = $best_sellers_result->fetch_all(MYSQLI_ASSOC);

// Fetch all products with search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$products_query = "SELECT product_id, name, description, price, stock, image_path FROM products";
if ($search) {
    $search = $conn->real_escape_string($search);
    $products_query .= " WHERE name LIKE '%$search%'";
}
$products_query .= " ORDER BY name ASC";
$products_result = $conn->query($products_query);
$products = $products_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<?php include 'nav.php'; ?>

<!-- Main Content -->
<div class="sm:ml-64 p-6 flex-1">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Products</h1>

    <?php if (isset($error)) { ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php } ?>

    <!-- Search Bar -->
    <div class="mb-6">
        <form method="GET" action="products.php" class="flex items-center">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" class="w-full max-w-md px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <button type="submit" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-600">Search</button>
        </form>
    </div>

    <!-- Best Sellers (Horizontal Table) -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Best Sellers</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-4 text-left">Rank</th>
                        <th class="py-3 px-4 text-left">Product</th>
                        <th class="py-3 px-4 text-left">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($best_sellers)) { ?>
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">No best sellers yet.</td>
                        </tr>
                    <?php } else { ?>
                        <?php $rank = 1; foreach ($best_sellers as $product) { ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo $rank++; ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="py-3 px-4">R<?php echo number_format($product['price'], 2); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- All Products (Vertical Table) -->
    <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">All Products</h2>
        <div class="space-y-4">
            <?php if (empty($products)) { ?>
                <p class="text-gray-500">No products found.</p>
            <?php } else { ?>
                <?php foreach ($products as $product) { ?>
                    <div class="bg-white p-4 rounded-lg shadow flex flex-col md:flex-row items-center">
                        <!-- Image -->
                        <div class="w-full md:w-1/4">
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-32 object-contain rounded-lg">
                        </div>
                        <!-- Details -->
                        <div class="w-full md:w-3/4 md:pl-4 mt-4 md:mt-0">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="text-gray-700 font-medium">Price: R<?php echo number_format($product['price'], 2); ?></p>
                            <p class="text-gray-600">In Stock: <?php echo $product['stock']; ?></p>
                            <!-- Add to Cart Form -->
                            <form method="POST" action="products.php" class="mt-2 flex items-center">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <label for="quantity-<?php echo $product['product_id']; ?>" class="mr-2 text-gray-700">Qty:</label>
                                <input type="number" id="quantity-<?php echo $product['product_id']; ?>" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" class="w-16 px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <button type="submit" name="add_to_cart" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <!-- Sell Product Gradient Tab -->
    <div class="mb-6">
        <a href="listing.php" class="block w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4 rounded-lg text-center font-semibold hover:from-blue-600 hover:to-purple-700 transition duration-300">
            Want to sell a product? Create a listing!
        </a>
    </div>

    <!-- Cart Summary -->
<div class="fixed bottom-0 left-0 w-full z-50">
    <div class="bg-white text-black p-4 rounded-lg shadow-lg border border-blue-600 mx-4 mb-4">
        <div class="flex items-center mb-2">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <div>
                <p class="font-semibold">Cart (<?php echo count($_SESSION['cart']); ?> item/s)</p>

            </div>
        </div>
        <?php if (!empty($_SESSION['cart'])) { ?>
            <div class="mb-2">
                <?php foreach ($_SESSION['cart'] as $item) { ?>
                    <p class="text-sm"><?php echo htmlspecialchars($item['name']); ?>: <?php echo htmlspecialchars($item['description']); ?></p>
                <?php } ?>
            </div>
            <a href="cart.php" class="block w-full mt-2 py-2 text-center bg-white text-black border border-blue-600 rounded-lg hover:bg-gray-100 mb-2">View Cart</a>
            <form method="POST" action="products.php">
                <button type="submit" name="clear_cart" class="w-full py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Clear Cart</button>
            </form>
        <?php } ?>
    </div>
</div>
</div>
</body>
</html>