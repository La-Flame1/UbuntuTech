<?php
session_start();

// Initialize failed attempts if not set
if (!isset($_SESSION['signup_attempts'])) {
    $_SESSION['signup_attempts'] = 0;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Handle signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'signup') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
        $_SESSION['signup_attempts']++;
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "Email already exists.";
            $_SESSION['signup_attempts']++;
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt === false) {
                error_log("Prepare failed (signup): " . $conn->error);
                $error = "Signup failed. Check logs.";
                $_SESSION['signup_attempts']++;
            } else {
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                $stmt->execute();
                $stmt->close();

                // Reset attempts on success
                $_SESSION['signup_attempts'] = 0;
                header("Location: /UbuntuTech/Main/index.php");
                exit();
            }
        }
    }
}

// Handle password recovery form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'recover') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $new_password = bin2hex(random_bytes(8)); // Generate a random 8-byte password

    if (empty($username) || empty($email) || empty($phone_number)) {
        $recovery_error = "All fields are required for recovery.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND phone_number = ?");
        $stmt->bind_param("sss", $username, $email, $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND email = ? AND phone_number = ?");
            $stmt->bind_param("ssss", $hashed_password, $username, $email, $phone_number);
            $stmt->execute();
            $stmt->close();
            $recovery_message = "Password reset successfully. Your new password is: $new_password. Please log in and change it.";
            $_SESSION['signup_attempts'] = 0; // Reset attempts after successful recovery
        } else {
            $recovery_error = "No user found with the provided details.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-lg w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg">
        <h1 class="text-xl sm:text-2xl font-bold text-center text-gray-800 mb-6">Sign Up</h1>
        
        <!-- Error Message -->
        <?php if (isset($error)) { ?>
            <div class="text-red-500 text-center text-sm sm:text-base mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <!-- Signup Form -->
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="signup">
            <div>
                <label for="username" class="block text-gray-700 text-sm sm:text-base">Username</label>
                <input type="text" id="username" name="username" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
            </div>
            <div>
                <label for="email" class="block text-gray-700 text-sm sm:text-base">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
            </div>
            <div>
                <label for="password" class="block text-gray-700 text-sm sm:text-base">Password</label>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
            </div>
            <button type="submit" class="w-full py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg text-sm sm:text-base">Sign Up</button>
        </form>

        <!-- Password Recovery Option -->
        <?php if ($_SESSION['signup_attempts'] >= 2) { ?>
            <div class="mt-6">
                <h2 class="text-lg sm:text-xl font-semibold text-center text-gray-800 mb-4">Forgot Password?</h2>
                <!-- Recovery Error Message -->
                <?php if (isset($recovery_error)) { ?>
                    <div class="text-red-500 text-center text-sm sm:text-base mb-4"><?php echo htmlspecialchars($recovery_error); ?></div>
                <?php } ?>
                <!-- Recovery Success Message -->
                <?php if (isset($recovery_message)) { ?>
                    <div class="text-green-500 text-center text-sm sm:text-base mb-4"><?php echo htmlspecialchars($recovery_message); ?></div>
                <?php } ?>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="recover">
                    <div>
                        <label for="recover_username" class="block text-gray-700 text-sm sm:text-base">Username</label>
                        <input type="text" id="recover_username" name="username" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
                    </div>
                    <div>
                        <label for="recover_email" class="block text-gray-700 text-sm sm:text-base">Email</label>
                        <input type="email" id="recover_email" name="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
                    </div>
                    <div>
                        <label for="recover_phone" class="block text-gray-700 text-sm sm:text-base">Phone Number</label>
                        <input type="tel" id="recover_phone" name="phone_number" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" placeholder="+1234567890" required>
                    </div>
                    <button type="submit" class="w-full py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg text-sm sm:text-base">Recover Password</button>
                </form>
            </div>
        <?php } ?>

        <p class="mt-4 text-center text-sm sm:text-base">Already have an account? <a href="index.php" class="text-blue-600 hover:underline">Login</a></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>