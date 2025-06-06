<?php
session_start();

// Initialize failed attempts if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "Ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && (!isset($_POST['action']) || $_POST['action'] !== 'recover' && $_POST['action'] !== 'set_new_password')) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        $_SESSION['login_attempts']++;
        header("Location: index.php");
        exit();
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt === false) {
        error_log("Prepare failed (login): " . $conn->error);
        $_SESSION['error'] = "Login failed. Check logs.";
        $_SESSION['login_attempts']++;
        header("Location: index.php");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Invalid email or password.";
        $_SESSION['login_attempts']++;
        header("Location: index.php");
        exit();
    }

    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Invalid email or password.";
        $_SESSION['login_attempts']++;
        header("Location: index.php");
        exit();
    }

    // Credentials are valid, set session variables
    $_SESSION['user'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    $stmt->close();

    // Reset failed attempts on successful login
    $_SESSION['login_attempts'] = 0;

   // Redirect based on role
if ($user['role'] === 'employee') {
    header("Location: manager_dashboard.php");
} elseif ($user['role'] === 'admin') {
    header("Location: admin_dashboard.php");
} else {
    header("Location: home.php");
}
exit();
}

// Handle password recovery form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'recover') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';

    if (empty($username) || empty($email) || empty($phone_number)) {
        $_SESSION['recovery_error'] = "All fields are required for recovery.";
        header("Location: index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND phone_number = ?");
    $stmt->bind_param("sss", $username, $email, $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['show_new_password_prompt'] = true; // Trigger new password prompt
        // Store user details for the next step
        $_SESSION['recovery_user'] = ['username' => $username, 'email' => $email, 'phone_number' => $phone_number];
    } else {
        $_SESSION['recovery_error'] = "No user found with the provided details.";
    }
    header("Location: index.php");
    exit();
}

// Handle new password submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'set_new_password') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password) || $new_password !== $confirm_password) {
        $_SESSION['recovery_error'] = "Passwords do not match or are empty.";
        header("Location: index.php");
        exit();
    }

    // Retrieve user details from session
    $user = $_SESSION['recovery_user'] ?? null;
    if ($user) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ? AND email = ? AND phone_number = ?");
        $stmt->bind_param("ssss", $hashed_password, $user['username'], $user['email'], $user['phone_number']);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['recovery_user']);
        unset($_SESSION['show_new_password_prompt']);
        $_SESSION['recovery_message'] = "Password updated successfully. Please log in with your new password.";
        $_SESSION['login_attempts'] = 0; // Reset attempts after successful update
    } else {
        $_SESSION['recovery_error'] = "User session expired. Please try recovery again.";
    }
    header("Location: index.php");
    exit();
}

$conn->close();
?>