<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ubuntu_tech";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Fetch help desk queries
$stmt = $conn->prepare("SELECT * FROM help_desk ORDER BY created_at DESC");
$stmt->execute();
$queries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle query resolution
if (isset($_POST['resolve_query'])) {
    $query_id = $_POST['query_id'] ?? '';
    $resolution_notes = $_POST['resolution_notes'] ?? '';
    $email = $_POST['user_email'] ?? '';
    $subject = "Resolution of Your Query - Ubuntu Technologies";
    $message = "Dear " . (strtok($email, '@') ?: 'User') . ",\n\nThank you for contacting Ubuntu Technologies. Your query has been resolved. Details:\n\n" . $resolution_notes . "\n\nBest regards,\nUbuntu Technologies Support\nhttps://47.129.252.206/dashboard/Main/";
    $headers = "From: support@ubuntutech.com";

    try {
        $stmt = $conn->prepare("UPDATE help_desk SET resolution_notes = ?, is_resolved = TRUE, resolved_at = NOW() WHERE query_id = ?");
        $stmt->execute([$resolution_notes, $query_id]);
        if (mail($email, $subject, $message, $headers)) {
            $resolve_message = "Query resolved and email sent successfully!";
        } else {
            $resolve_message = "Query resolved, but email failed to send.";
        }
    } catch(PDOException $e) {
        $resolve_message = "Error resolving query: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles not fully covered by Tailwind utilities */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .panel {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            max-width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 1rem;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .delete-btn, .logout-btn {
            background-color: #dc2626;
            color: #ffffff;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .delete-btn:hover, .logout-btn:hover {
            background-color: #b91c1c;
        }
        .resolve-btn {
            background-color: #10b981;
            color: #ffffff;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .resolve-btn:hover {
            background-color: #059669;
        }
        .message {
            color: #15803d;
            margin-bottom: 1rem;
        }
        .error {
            color: #b91c1c;
            margin-bottom: 1rem;
        }
        .status-resolved {
            background-color: #d1fae5;
            color: #065f46;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }
        .status-unresolved {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }
        textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
        }
        .header {
            margin-bottom: 1.5rem;
        }
        .action-form {
            display: inline-block;
            margin-top: 0.5rem;
        }
        .action-form button {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="ml-16 sm:ml-64 p-6">
    <div class="header flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manager Dashboard</h1>
        <form method="post" class="inline-block">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>

    <?php if (isset($resolve_message)): ?>
        <p class="<?php echo strpos($resolve_message, 'Error') === false ? 'message' : 'error'; ?>">
            <?php echo htmlspecialchars($resolve_message); ?>
        </p>
    <?php endif; ?>

    <div class="panel p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Manage Contact Us Queries</h2>
        <div class="table-container max-w-full overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">ID</th>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">User Email</th>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">Issue Type</th>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">Description</th>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">Created At</th>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">Status</th>
                        <th class="bg-gray-100 font-semibold border border-gray-200 p-4 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($queries)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-gray-600 border border-gray-200 p-4">No queries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($queries as $query): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-200 p-4"><?php echo htmlspecialchars($query['id'] ?? 'N/A'); ?></td>
                                <td class="border border-gray-200 p-4"><?php echo htmlspecialchars($query['user_email'] ?? 'N/A'); ?></td>
                                <td class="border border-gray-200 p-4"><?php echo htmlspecialchars($query['issue_type'] ?? 'N/A'); ?></td>
                                <td class="border border-gray-200 p-4"><?php echo htmlspecialchars($query['description'] ?? 'N/A'); ?></td>
                                <td class="border border-gray-200 p-4"><?php echo date('Y-m-d H:i', strtotime($query['created_at'] ?? 'now')); ?></td>
                                <td class="border border-gray-200 p-4">
                                    <span class="<?php echo (isset($query['is_resolved']) && $query['is_resolved']) ? 'status-resolved' : 'status-unresolved'; ?>">
                                        <?php echo (isset($query['is_resolved']) && $query['is_resolved']) ? 'Resolved' : 'Unresolved'; ?>
                                    </span>
                                </td>
                                <td class="border border-gray-200 p-4">
                                    <?php if (!isset($query['is_resolved']) || !$query['is_resolved']): ?>
                                        <form method="post" class="action-form inline-block">
                                            <input type="hidden" name="query_id" value="<?php echo htmlspecialchars($query['id'] ?? ''); ?>">
                                            <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($query['user_email'] ?? ''); ?>">
                                            <button type="button" class="resolve-btn" onclick="document.getElementById('resolveForm<?php echo htmlspecialchars($query['id'] ?? ''); ?>').style.display='block'; this.style.display='none';">Resolve</button>
                                            <div id="resolveForm<?php echo htmlspecialchars($query['id'] ?? ''); ?>" style="display:none; margin-top:0.5rem;">
                                                <textarea name="resolution_notes" rows="4" placeholder="Enter resolution notes..." required class="w-full p-2 border rounded"></textarea>
                                                <div class="action-form mt-2">
                                                    <button type="submit" name="resolve_query" class="resolve-btn">Submit Resolution</button>
                                                    <button type="button" class="delete-btn" onclick="this.parentElement.parentElement.style.display='none'; document.querySelector('[onclick^=document.getElementById]').style.display='inline-block';">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-500">Action Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn = null;
?>