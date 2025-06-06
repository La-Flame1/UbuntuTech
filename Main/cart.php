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

// Handle checkout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkout'])) {
    $cart_items = $_SESSION['cart'];
    $total_value = array_sum(array_column($cart_items, 'subtotal'));
    $data = [
        'cart_items' => array_column($cart_items, 'name'),
        'description' => $_POST['description'] ?? '',
        'total_value' => $total_value,
        'delivery_date' => $_POST['delivery_date'] ?? date('Y-m-d', strtotime('+7 days'))
    ];
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'action' => 'checkout',
                'cart_items' => json_encode($data['cart_items']),
                'description' => $data['description'],
                'total_value' => $total_value,
                'delivery_date' => $data['delivery_date']
            ])
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents('http://localhost/UbuntuTech/Main/order.php', false, $context);
    $_SESSION['cart'] = []; // Clear cart after checkout
    exit();
}

$conn->close();
?>

<?php include 'nav.php'; ?>

<div class="sm:ml-64 p-6 flex-1">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Cart</h1>
    <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Cart Items</h2>
        <?php if (empty($_SESSION['cart'])) { ?>
            <p class="text-gray-700">Your cart is empty.</p>
        <?php } else { ?>
            <ul class="list-disc pl-5 mb-4">
                <?php foreach ($_SESSION['cart'] as $item) { ?>
                    <li><?php echo htmlspecialchars($item['name']); ?> (Qty: <?php echo $item['quantity']; ?>) - $<?php echo number_format($item['subtotal'], 2); ?><br>
                        <span class="text-sm text-gray-600"><?php echo htmlspecialchars($item['description']); ?></span>
                    </li>
                <?php } ?>
            </ul>
            <p class="mb-4">Total: $<span id="total-value"><?php echo number_format(array_sum(array_column($_SESSION['cart'], 'subtotal')), 2); ?></span></p>
            <form method="POST" action="cart.php">
                
                <div class="mb-4">
                    <label for="delivery_date" class="block text-gray-700">Delivery Date</label>
                    <input type="date" id="delivery_date" name="delivery_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <button class="w-full py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-300 mb-2">Checkout</button>
                <a href="products.php" class="w-full py-3 bg-red-600 text-white text-center rounded-lg hover:bg-red-700 transition duration-300 block">Cancel</a>
            </form>
        <?php } ?>
    </div>
</div>
</body>
</html>