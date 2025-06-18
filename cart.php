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

$conn->close();
?>

<?php include 'nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Ubuntu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            .form-input {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="sm:ml-64 p-6 flex-1 container mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Your Cart</h1>
        <div class="bg-white p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Cart Items</h2>
            <?php if (empty($_SESSION['cart'])) { ?>
                <p class="text-gray-700">Your cart is empty.</p>
                <a href="/Main/products.php" class="mt-4 w-full py-3 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition duration-300 inline-block">Continue Shopping</a>
            <?php } else { ?>
                <ul class="list-disc pl-5 mb-4">
                    <?php foreach ($_SESSION['cart'] as $item) { ?>
                        <li><?php echo htmlspecialchars($item['name']); ?> (Qty: <?php echo $item['quantity']; ?>) - R<?php echo number_format($item['subtotal'], 2); ?><br>
                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($item['description']); ?></span>
                        </li>
                    <?php } ?>
                </ul>
                <p class="mb-4">Total: R<span id="total-value"><?php echo number_format(array_sum(array_column($_SESSION['cart'], 'subtotal')), 2); ?></span></p>
                <form id="cartForm" method="POST" action="payment.php">
                    <div class="mb-4">
                        <label for="delivery_date" class="block text-gray-700">Delivery Date</label>
                        <input type="date" id="delivery_date" name="delivery_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 form-input" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" name="checkout" class="bg-green-100 border border-green-400 text-green-700 w-full py-3 rounded-lg hover:bg-green-200 transition duration-300 mb-2">Checkout</button>
                    <a href="products.php" class="w-full py-3 bg-red-600 text-white text-center rounded-lg hover:bg-red-700 transition duration-300 block">Cancel</a>
                </form>
            <?php } ?>
        </div>
    </div>

    <script>
        // Client-side validation and debugging
        document.getElementById('cartForm')?.addEventListener('submit', function(e) {
            const deliveryDate = new Date(document.getElementById('delivery_date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            console.log('Cart form submission attempted');
            console.log('Form Action:', document.getElementById('cartForm').action);
            console.log('Delivery Date:', document.getElementById('delivery_date').value);

            if (deliveryDate < today) {
                e.preventDefault();
                alert('Delivery date cannot be in the past.');
                console.log('Submission prevented: Invalid delivery date');
            } else {
                console.log('Form valid, submitting to payment.php');
            }
        });
    </script>
</body>
</html>