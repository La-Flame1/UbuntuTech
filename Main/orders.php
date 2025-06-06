<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Handle new order from cart checkout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'checkout') {
    $user_email = $_SESSION['user'];
    $order_items = json_encode($_POST['cart_items'] ?? []);
    $description = $_POST['description'] ?? '';
    $total_value = floatval($_POST['total_value'] ?? 0);
    $delivery_date = $_POST['delivery_date'] ?? date('Y-m-d', strtotime('+7 days')); // Default to 7 days from now

    $stmt = $conn->prepare("INSERT INTO orders (user_email, order_items, description, total_value, delivery_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $user_email, $order_items, $description, $total_value, $delivery_date);
    $stmt->execute();
    $stmt->close();
    header("Location: order.php?success=1");
    exit();
}

// Fetch user's orders
$user_email = $_SESSION['user'];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<?php include 'nav.php'; ?>

<!-- Main Content -->
<div class="sm:ml-64 p-6 flex-1">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Orders</h1>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <p class="text-sm">Order placed successfully!</p>
        </div>
    <?php } ?>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
            <thead>
                <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                    <th class="py-3 px-4 text-left">Order ID</th>
                    <th class="py-3 px-4 text-left">Items</th>
                    <th class="py-3 px-4 text-left">Description</th>
                    <th class="py-3 px-4 text-left">Total Value</th>
                    <th class="py-3 px-4 text-left">Delivery Date</th>
                    <th class="py-3 px-4 text-left">Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)) { ?>
                    <tr>
                        <td colspan="6" class="py-4 text-center text-gray-500">No orders found.</td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($orders as $order) { ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars(json_decode($order['order_items'], true)[0] ?? 'N/A'); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($order['description'] ?? 'N/A'); ?></td>
                            <td class="py-3 px-4">$<?php echo number_format($order['total_value'], 2); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($order['delivery_date'] ?? 'N/A'); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($order['created_at']); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>