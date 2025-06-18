<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: /Main/index.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Ubuntu Technologies</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .sidebar { transition: transform 0.3s ease-in-out; }
        .order-item { max-height: 150px; overflow-y: auto; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="sm:ml-64 p-6 flex-1">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">My Orders</h1>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <p class="text-sm">Order placed successfully!</p>
            </div>
        <?php } ?>

        <?php if (empty($orders)) { ?>
            <div class="bg-white p-6 rounded-lg shadow text-center">
                <p class="text-gray-600">You have no orders yet.</p>
                <a href="/Main/products.php" class="mt-4 inline-block bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700">Browse Products</a>
            </div>
        <?php } else { ?>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Order History</h2>
                <div class="space-y-4">
                    <?php foreach ($orders as $order) { ?>
                        <div class="border border-gray-200 p-4 rounded-lg">
                            <p class="text-gray-700"><strong>Order ID:</strong> #<?php echo htmlspecialchars($order['order_id']); ?></p>
                            <p class="text-gray-700"><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                            <p class="text-gray-700"><strong>Total Value:</strong> R<?php echo number_format($order['total_value'], 2); ?></p>
                            <p class="text-gray-700"><strong>Delivery Date:</strong> <?php echo htmlspecialchars($order['delivery_date']); ?></p>
                            <p class="text-gray-700"><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                            <p class="text-gray-700"><strong>Coupon Code:</strong> <?php echo htmlspecialchars($order['coupon_code'] ?? 'None'); ?></p>
                            <p class="text-gray-700"><strong>Order Items:</strong></p>
                            <div class="order-item bg-gray-100 p-2 mt-2 text-sm">
                                <?php
                                $order_items = json_decode($order['order_items'], true);
                                if (is_array($order_items)) {
                                    foreach ($order_items as $item) {
                                        echo '<p>' . htmlspecialchars($item['name']) . ' (Qty: ' . $item['quantity'] . ') - R' . number_format($item['subtotal'], 2) . '</p>';
                                    }
                                } else {
                                    echo '<p>Unable to display items.</p>';
                                }
                                ?>
                            </div>
                            <p class="text-gray-700 mt-2"><strong>Notes:</strong> <?php echo htmlspecialchars($order['description'] ?? 'None'); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <footer class="bg-blue-600 text-white py-8 px-6 w-full mt-8">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-lg font-semibold">Ubuntu Technologies</p>
                    <p class="text-sm">© <?php echo date('Y'); ?> Ubuntu Technologies. All rights reserved.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="https://facebook.com" target="_blank" class="hover:text-gray-300"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://twitter.com" target="_blank" class="hover:text-gray-300"><i class="fab fa-twitter"></i></a>
                    <a href="https://instagram.com" target="_blank" class="hover:text-gray-300"><i class="fab fa-instagram"></i></a>
                    <a href="/Main/contact.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">Contact Us</a>
                    <a href="/Main/manual.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">User Manual</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>