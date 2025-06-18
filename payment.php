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

// Initialize variables
$user_email = $_SESSION['user'];
$success = '';
$error = '';
$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    // Validate cart
    if (empty($_SESSION['cart'])) {
        $errors[] = "Your cart is empty.";
    } else {
        // Validate payment inputs
        $payment_method = $_POST['payment_method'] ?? '';
        $coupon_code = trim($_POST['coupon_code'] ?? '');
        $card_number = $_POST['card_number'] ?? '';
        $card_expiry = $_POST['card_expiry'] ?? '';
        $card_cvv = $_POST['card_cvv'] ?? '';

        if (empty($payment_method)) {
            $errors[] = "Please select a payment method.";
        } elseif (!in_array($payment_method, ['MasterCard', 'Visa'])) {
            $errors[] = "Invalid payment method.";
        }

        if (empty($card_number) || !preg_match('/^\d{16}$/', $card_number)) {
            $errors[] = "Invalid card number.";
        }
        if (empty($card_expiry) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $card_expiry)) {
            $errors[] = "Invalid expiry date (MM/YY).";
        }
        if (empty($card_cvv) || !preg_match('/^\d{3}$/', $card_cvv)) {
            $errors[] = "Invalid CVV.";
        }

        // Calculate total value and prepare order items
        $total_value = 0;
        $order_items = json_encode($_SESSION['cart']);
        foreach ($_SESSION['cart'] as $item) {
            $total_value += $item['subtotal'] * $item['quantity'];
        }

        // Optional description (e.g., order notes)
        $description = trim($_POST['order_notes'] ?? '');

        // Apply coupon code (placeholder logic)
        if (!empty($coupon_code)) {
            // Example: Assume 'DISCOUNT10' gives 10% off
            if ($coupon_code === 'DISCOUNT10') {
                $total_value *= 0.9;
            } else {
                $errors[] = "Invalid coupon code.";
            }
        }

        // Check minimum cart value for free shipping
        if ($total_value < 700) {
            $total_value += 65; // Add R65 shipping fee
        }

        if (empty($errors)) {
            // Insert order
            $delivery_date = date('Y-m-d', strtotime('+7 days'));
            $stmt = $conn->prepare("INSERT INTO orders (user_email, order_items, description, total_value, delivery_date, payment_method, coupon_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                $error = "Database query preparation failed. Please try again.";
            } else {
                $stmt->bind_param("sssdsds", $user_email, $order_items, $description, $total_value, $delivery_date, $payment_method, $coupon_code);
                if ($stmt->execute()) {
                    // Clear cart
                    $_SESSION['cart'] = [];
                    $success = "Payment processed successfully! Redirecting to your orders...";
                    // Use JavaScript for redirect to avoid header issues
                    echo '<script>setTimeout(() => { window.location.href = "orders.php?success=1"; }, 3000);</script>';
                } else {
                    error_log("Execute failed: " . $stmt->error);
                    $error = "Failed to process payment. Please try again.";
                }
                $stmt->close();
            }
        } else {
            $error = implode("<br>", $errors);
        }
    }
}

$conn->close();
?>

<?php include 'nav.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Ubuntu Technologies</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; }
        .sidebar { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="sm:ml-64 p-6 flex-1">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Payment</h1>

        <?php if (!empty($success)) { ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php } elseif (!empty($error)) { ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <p class="text-sm"><?php echo $error; ?></p>
            </div>
        <?php } ?>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Complete Your Purchase</h2>
            <form method="POST" action="payment.php">
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Cart Summary</label>
                    <?php if (empty($_SESSION['cart'])) { ?>
                        <p class="text-gray-600">Your cart is empty.</p>
                    <?php } else { ?>
                        <ul class="list-disc list-inside text-gray-600">
                            <?php
                            $cart_total = 0;
                            foreach ($_SESSION['cart'] as $item) {
                                $subtotal = $item['subtotal'] * $item['quantity'];
                                $cart_total += $subtotal;
                                echo '<li>' . htmlspecialchars($item['name']) . ' (Qty: ' . $item['quantity'] . ') - R' . number_format($subtotal, 2) . '</li>';
                            }
                            ?>
                        </ul>
                        <p class="text-gray-700 font-medium mt-2">Subtotal: R<?php echo number_format($cart_total, 2); ?></p>
                        <p class="text-gray-600 text-sm"><?php echo $cart_total < 700 ? 'Add R' . number_format(700 - $cart_total, 2) . ' more for free shipping!' : 'Free shipping applied!'; ?></p>
                    <?php } ?>
                </div>

                <div class="mb-4">
                    <label for="payment_method" class="block text-gray-700 font-medium mb-2">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Select payment method</option>
                        <option value="MasterCard">MasterCard</option>
                        <option value="Visa">Visa</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="card_number" class="block text-gray-700 font-medium mb-2">Card Number</label>
                    <input type="text" id="card_number" name="card_number" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="1234 5678 9012 3456" maxlength="16">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="card_expiry" class="block text-gray-700 font-medium mb-2">Expiry Date</label>
                        <input type="text" id="card_expiry" name="card_expiry" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div>
                        <label for="card_cvv" class="block text-gray-700 font-medium mb-2">CVV</label>
                        <input type="text" id="card_cvv" name="card_cvv" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="123" maxlength="3">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="coupon_code" class="block text-gray-700 font-medium mb-2">Coupon Code (Optional)</label>
                    <input type="text" id="coupon_code" name="coupon_code" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="Enter coupon code">
                </div>

                <div class="mb-4">
                    <label for="order_notes" class="block text-gray-700 font-medium mb-2">Order Notes (Optional)</label>
                    <textarea id="order_notes" name="order_notes" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" rows="3" placeholder="Any special instructions?"></textarea>
                </div>

                <button type="submit" name="process_payment" class="w-full py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">Process Payment</button>
            </form>
        </div>

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
                    <a href="contact.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">Contact Us</a>
                    <a href="manual.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">User Manual</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>