<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: /Main/index.php");
    exit();
}

// Database connection (optional, included for consistency)
$conn = new mysqli("localhost", "root", "", "ubuntu_tech");
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
    <title>User Manual - Ubuntu Technologies</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        .manual-nav {
            position: sticky;
            top: 1rem;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }
        .manual-nav a {
            display: block;
            padding: 0.5rem 1rem;
            color: #4b5563;
            border-left: 4px solid transparent;
        }
        .manual-nav a:hover {
            background-color: #f3f4f6;
            border-left-color: #2563eb;
        }
        .manual-section {
            scroll-margin-top: 1rem;
        }
        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }
            .manual-nav {
                position: static;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Main Content -->
        <div class="sm:ml-64 p-6 flex-1">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">User Manual</h1>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Navigation Menu -->
                <div class="md:col-span-1">
                    <div class="bg-white p-4 rounded-lg shadow manual-nav">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Contents</h2>
                        <a href="#introduction" class="hover:bg-gray-100">1. Introduction</a>
                        <a href="#accessing" class="hover:bg-gray-100">2. Accessing the Website</a>
                        <a href="#products" class="hover:bg-gray-100">3. Products</a>
                        <a href="#menus" class="hover:bg-gray-100">4. Changing Menus</a>
                        <a href="#shipping" class="hover:bg-gray-100">5. Shipping Options</a>
                        <a href="#orders" class="hover:bg-gray-100">6. Orders</a>
                        <a href="#updating" class="hover:bg-gray-100">7. Updating a Page</a>
                        <a href="#payments" class="hover:bg-gray-100">8. Payments</a>
                        <a href="#traffic" class="hover:bg-gray-100">9. Checking Web Traffic</a>
                        <a href="#appendix" class="hover:bg-gray-100">10. Appendix</a>
                    </div>
                </div>
                <!-- Manual Content -->
                <div class="md:col-span-3 bg-white p-6 rounded-lg shadow">
                    <section id="introduction" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Introduction</h2>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">1.1 Background</h3>
                        <p class="text-gray-600 mb-4">Ubuntu Technologies is a South African C2C marketplace designed to empower local trade by connecting buyers and sellers.</p>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">1.2 About This Website</h3>
                        <p class="text-gray-600">The Ubuntu Technologies website (<a href="https://47.129.252.206/dashboard/Main/home.php" class="text-blue-600 hover:underline">https://47.129.252.206/dashboard/Main/home.php</a>) is built using HTML, CSS, JavaScript, and PHP, with Tailwind CSS for responsive design. It was initially developed locally using XAMPP, which includes MariaDB, PHP, and Perl. The database is managed via phpMyAdmin with SQL queries. The site is hosted on an AWS EC2 instance, configured via remote desktop, with files transferred to the instance for hosting.</p>
                    </section>

                    <section id="accessing" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. Accessing the Website</h2>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">2.1 Accessing the Website</h3>
                        <ul class="list-disc list-inside text-gray-600 mb-4">
                            <li><a href="https://47.129.252.206/dashboard/Main/home.php" class="text-blue-600 hover:underline">Home</a>: Main page for users and admins.</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/index.php" class="text-blue-600 hover:underline">Login</a>: Enter credentials for access.</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/signup.php" class="text-blue-600 hover:underline">Sign Up</a>: Create an account for new users.</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/admin_dashboard.php" class="text-blue-600 hover:underline">Admin Dashboard</a>: Manage the website (admin only).</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/products.php" class="text-blue-600 hover:underline">Products</a>: View the product catalog.</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/listing.php" class="text-blue-600 hover:underline">My Listings</a>: Create and manage listings.</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/orders.php" class="text-blue-600 hover:underline">Orders</a>: View successful orders.</li>
                            <li><a href="https://47.129.shetext-blue-600 hover:underline">Contact</a>: Contact support for issues.</li>
                            <li><a href="https://47.129.252.206/dashboard/Main/cart.php" class="text-blue-600 hover:underline">Cart</a>: View cart before purchasing.</li>
                        </ul>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">2.2 The Admin Area</h3>
                        <p class="text-gray-600">Admin Login: <strong>admin@ubuntutech.com</strong>, Password: <strong>admin123</strong>. The admin area allows viewing website performance, managing users, and handling products (add/delete).</p>
                    </section>

                    <section id="products" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. Products</h2>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">3.1 Adding and Removing Products</h3>
                        <p class="text-gray-600 mb-2">Products can be added via:</p>
                        <ul class="list-disc list-inside text-gray-600 mb-4">
                            <li><strong>Admin</strong>: Create listings with product details (description, image, stock, price, catalog) or delete products.</li>
                            <li><strong>Seller</strong>: Log in, go to My Listings, create a listing to update the database, or delete it if needed.</li>
                        </ul>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">3.2 Updating Products</h3>
                        <p class="text-gray-600">Currently, products/listings cannot be edited. Delete the existing product/listing and create a new one to update information.</p>
                    </section>

                    <section id="menus" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Changing Menus</h2>
                        <p class="text-gray-600">The website dynamically adjusts to different screen sizes. On mobile devices, the navigation menu collapses and can be expanded for usability.</p>
                    </section>

                    <section id="shipping" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Shipping Options</h2>
                        <p class="text-gray-600">Free delivery is offered for carts valued at R700 or more. Below this, a R65 courier fee applies. Shipping is available only in South Africa and is based on cart value, not weight or quantity.</p>
                    </section>

                    <section id="orders" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">6. Orders</h2>
                        <p class="text-gray-600">After finalizing your cart, enter banking details in the payment tab. If funds are available, the order is recorded and viewable on the Orders page, showing delivery status. The courier receives your name, email, and phone number, with a PIN sent for delivery verification.</p>
                    </section>

                    <section id="updating" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">7. Updating a Page</h2>
                        <p class="text-gray-600">Page modifications require developer access to program files, database, and the EC2 instance. Changes must follow the website’s theme, schema, and use PHP, HTML, CSS (Tailwind), and JavaScript, linking to existing files.</p>
                    </section>

                    <section id="payments" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">8. Payments</h2>
                        <p class="text-gray-600">Accepted payment methods: MasterCard and PayPal. Enter debit/credit card details as required.</p>
                    </section>

                    <section id="traffic" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">9. Checking Web Traffic</h2>
                        <p class="text-gray-600">Admins can view limited traffic statistics (monthly visitors, total users, products) in the admin panel. For detailed analysis, use tools like Google Analytics or Semrush.</p>
                    </section>

                    <section id="appendix" class="manual-section mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">10. Appendix: Setting Up Ubuntu Technologies Website</h2>
                        <ol class="list-decimal list-inside text-gray-600 mb-4">
                            <li><strong>Domain Registration</strong>:
                                <ul class="list-disc list-inside ml-6">
                                    <li>Choose a domain (e.g., <a href="https://47.129.252.206/dashboard/Main/home.php" class="text-blue-600 hover:underline">https://47.129.252.206/dashboard/Main/home.php</a>).</li>
                                    <li>Register via GoDaddy, Namecheap, or Google Domains.</li>
                                    <li>Configure DNS to point to hosting provider’s nameservers.</li>
                                </ul>
                            </li>
                            <li><strong>Server Setup</strong>:
                                <ul class="list-disc list-inside ml-6">
                                    <li>Choose a hosting provider (e.g., AWS).</li>
                                    <li>Create an EC2 instance (Amazon Machine Language - Microsoft Windows Server 2025).</li>
                                    <li>Configure firewall for HTTP/HTTPS.</li>
                                    <li>Save key pair securely and connect via RDP client.</li>
                                    <li>Transfer website files to the instance.</li>
                                </ul>
                            </li>
                            <li><strong>Website Development</strong>:
                                <ul class="list-disc list-inside ml-6">
                                    <li>Create a static website using PHP, HTML, CSS, and JavaScript.</li>
                                    <li>File structure: index.html (Homepage), css/style.css (Styling), js/script.js (Interactivity).</li>
                                    <li>Test locally using XAMPP.</li>
                                </ul>
                            </li>
                            <li><strong>Deployment</strong>:
                                <ul class="list-disc list-inside ml-6">
                                    <li>Copy files to the EC2 instance and run XAMPP for hosting.</li>
                                    <li>Verify accessibility via the domain.</li>
                                </ul>
                            </li>
                            <li><strong>Security and Maintenance</strong>:
                                <ul class="list-disc list-inside ml-6">
                                    <li>Regularly update server and dependencies.</li>
                                    <li>Schedule backups of files and database.</li>
                                </ul>
                            </li>
                        </ol>
                        <h3 class="text-xl font-medium text-gray-700 mb-2">Additional Resources</h3>
                        <ul class="list-disc list-inside text-gray-600">
                            <li><a href="https://httpd.apache.org/docs/" class="text-blue-600 hover:underline">Apache Documentation</a></li>
                            <li><a href="https://letsencrypt.org/" class="text-blue-600 hover:underline">Let's Encrypt</a></li>
                            <li><a href="https://ubuntu.com/server/docs" class="text-blue-600 hover:underline">Ubuntu Server Guide</a></li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-600 text-white py-8 px-6 w-full">
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
</body>
</html>