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
$success = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_query'])) {
    $user_email = $_SESSION['user'];
    $issue_type = $_POST['issue_type'] ?? '';
    $description = $_POST['description'] ?? '';

    // Validate inputs
    if (empty($issue_type) || empty($description)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO help_desk (user_email, issue_type, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_email, $issue_type, $description);
        if ($stmt->execute()) {
            $success = "Query submitted successfully!";
            // Redirect after 3 seconds using a relative path
            echo '<script>setTimeout(() => { window.location.href = "contact.php"; }, 3000);</script>';
        } else {
            $error = "Failed to submit query. Please try again.";
        }
        $stmt->close();
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
    <title>Contact Us - Ubuntu Technologies</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="sm:ml-64 p-6 flex-1">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Contact Us</h1>

        <?php if (!empty($success)) { ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php } elseif (!empty($error)) { ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php } ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Get in Touch</h2>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-gray-700 font-medium">Email Us</p>
                            <p class="text-gray-600">support@ubuntutech.com</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <div>
                            <p class="text-gray-700 font-medium">Call Us</p>
                            <p class="text-gray-600">+27 12 345 6789</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                        <div>
                            <p class="text-gray-700 font-medium">Live Chat</p>
                            <p class="text-gray-600">Chat with a consultant now</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.5v11m0 0l-3.5-3.5m3.5 3.5l3.5-3.5m-7-7h7"></path>
                        </svg>
                        <div>
                            <p class="text-gray-700 font-medium">User Manual</p>
                            <p class="text-gray-600"><a href="manual.php" class="text-blue-600 hover:underline">View our user manual for guidance</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Submit a Query</h2>
                <form method="POST" action="contact.php">
                    <div class="mb-4">
                        <label for="issue_type" class="block text-gray-700 font-medium mb-2">Type of Issue</label>
                        <select id="issue_type" name="issue_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="">Select an issue type</option>
                            <option value="Technical">Technical Support</option>
                            <option value="Billing">Billing Issue</option>
                            <option value="Product Inquiry">Product Inquiry</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Description</label>
                        <textarea id="description" name="description" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" rows="5" placeholder="Explain your issue in detail..."></textarea>
                    </div>
                    <button type="submit" name="submit_query" class="w-full py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">Submit Query</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>