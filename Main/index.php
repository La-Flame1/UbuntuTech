<?php
session_start();

// Retrieve error message, failed attempts, recovery messages, and new password prompt flag
$error = $_SESSION['error'] ?? null;
$failed_attempts = $_SESSION['login_attempts'] ?? 0;
$recovery_message = $_SESSION['recovery_message'] ?? null;
$recovery_error = $_SESSION['recovery_error'] ?? null;
$show_new_password_prompt = $_SESSION['show_new_password_prompt'] ?? false;

// Clear session variables after retrieval
unset($_SESSION['error']);
unset($_SESSION['recovery_message']);
unset($_SESSION['recovery_error']);
unset($_SESSION['show_new_password_prompt']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UbuntuTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-lg w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg">
        <h1 class="text-xl sm:text-2xl font-bold text-center text-gray-800 mb-6">Login to UbuntuTech</h1>
        <p class="text-center text-gray-600 text-sm mb-4">For Users, Employees, or Admins</p>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-gray-700 text-sm sm:text-base">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
            </div>
            <div>
                <label for="password" class="block text-gray-700 text-sm sm:text-base">Password</label>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
            </div>
            <button type="submit" class="w-full py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg text-sm sm:text-base">Login</button>
        </form>

        <!-- Password Recovery Option -->
        <?php if ($failed_attempts >= 2) { ?>
            <div class="mt-6">
                <h2 class="text-lg sm:text-xl font-semibold text-center text-gray-800 mb-4">Forgot Password?</h2>
                <!-- Recovery Success Message -->
                <?php if (isset($recovery_message)) { ?>
                    <div class="text-green-500 text-center text-sm sm:text-base mb-4"><?php echo htmlspecialchars($recovery_message); ?></div>
                <?php } ?>
                <!-- Recovery Error Message -->
                <?php if (isset($recovery_error)) { ?>
                    <div class="text-red-500 text-center text-sm sm:text-base mb-4"><?php echo htmlspecialchars($recovery_error); ?></div>
                <?php } ?>
                <form method="POST" action="login.php" class="space-y-4">
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

        <p class="mt-4 text-center text-sm sm:text-base">Don't have an account? <a href="signup.php" class="text-blue-600 hover:underline">Sign Up</a></p>
    </div>

    <!-- Error Modal -->
    <?php if (isset($error)) { ?>
        <div id="errorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Error</h3>
                <p class="text-red-500 text-center mb-4"><?php echo htmlspecialchars($error); ?></p>
                <button id="closeModal" class="w-full py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Close</button>
            </div>
        </div>
    <?php } ?>

    <!-- New Password Prompt Modal -->
    <?php if ($show_new_password_prompt) { ?>
        <div id="newPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Set New Password</h3>
                <form action="login.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="set_new_password">
                    <div>
                        <label for="new_password" class="block text-gray-700 text-sm sm:text-base">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-gray-700 text-sm sm:text-base">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm sm:text-base" required>
                    </div>
                    <button type="submit" class="w-full py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg text-sm sm:text-base">Update Password</button>
                </form>
                <button id="cancelNewPassword" class="mt-4 w-full py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg">Cancel</button>
            </div>
        </div>
    <?php } ?>

    <script>
        // Close error modal functionality
        const closeModalButton = document.getElementById('closeModal');
        const errorModal = document.getElementById('errorModal');

        if (closeModalButton && errorModal) {
            closeModalButton.addEventListener('click', () => {
                errorModal.style.display = 'none';
            });
        }

        // Close new password modal functionality
        const cancelNewPasswordButton = document.getElementById('cancelNewPassword');
        const newPasswordModal = document.getElementById('newPasswordModal');

        if (cancelNewPasswordButton && newPasswordModal) {
            cancelNewPasswordButton.addEventListener('click', () => {
                newPasswordModal.style.display = 'none';
                window.location.href = 'index.php';
            });
        }
    </script>
</body>
</html>