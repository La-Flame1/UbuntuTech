<?php
session_start();

// Check for logout
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: home.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "ubuntu_tech");
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Check logs.");
}

// Fetch featured products (top 3 by product_id as placeholder)
$featured_query = "SELECT product_id, name, price, image_path, description FROM products ORDER BY product_id DESC LIMIT 3";
$featured_result = $conn->query($featured_query);
$featured_products = $featured_result->fetch_all(MYSQLI_ASSOC);

// Handle view details or add to cart requests
$selected_product = null;
$message = isset($_GET['message']) ? $_GET['message'] : '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $product_id = $_GET['id'];

    if (!isset($_SESSION['user_id'])) {
        // Store the intended action and product ID in session
        $_SESSION['pending_action'] = $action;
        $_SESSION['pending_product_id'] = $product_id;
        header("Location: login.php");
        exit();
    } else {
        if ($action === 'view') {
            // Fetch detailed product info
            try {
                $stmt = $conn->prepare("SELECT id, name, description, price FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $message = "Error fetching product details: " . $e->getMessage();
            }
        } elseif ($action === 'add') {
            // Process add to cart (simple example)
            try {
                $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $stmt->bind_result($product_name);
                $stmt->fetch();
                $message = "Added " . htmlspecialchars($product_name) . " to cart!";
                // In a real app, update a cart table here
                $stmt->close();
            } catch(PDOException $e) {
                $message = "Error adding to cart: " . $e->getMessage();
            }
            $selected_product = null; // Reset to close details view
        }
    }
}

// Handle post-login action resumption
if (isset($_SESSION['pending_action']) && isset($_SESSION['pending_product_id']) && isset($_SESSION['user_id'])) {
    $action = $_SESSION['pending_action'];
    $product_id = $_SESSION['pending_product_id'];
    unset($_SESSION['pending_action'], $_SESSION['pending_product_id']); // Clear after use

    if ($action === 'view') {
        try {
            $stmt = $conn->prepare("SELECT id, name, description, price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
        try {
            $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->bind_result($product_name);
            $stmt->fetch();
            $message = "Added " . htmlspecialchars($product_name) . " to cart!";
            $stmt->close();
        } catch(Exception $e) {
            $message = "Error adding to cart: " . $e->getMessage();
        }
        } catch(PDOException $e) {
            $message = "Error adding to cart: " . $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Ubuntu Technologies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .theme-blue { background: linear-gradient(90deg, #0152e9, #0137a0); }
        .theme-blue-hover { background-color: #0137a0; }
        .sidebar {
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
            width: 240px;
        }
        .email-text {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .nav-text {
            transition: opacity 0.3s ease-in-out, width 0.3s ease-in-out;
            opacity: 1;
            width: auto;
        }
        .user-info-section {
            transition: opacity 0.3s ease-in-out, height 0.3s ease-in-out, padding 0.3s ease-in-out;
            overflow: hidden;
        }

        @media (max-width: 640px) {
            .sidebar {
                transform: translateX(-100%);
                width: 60px;
                position: fixed;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .nav-item.collapsed {
                padding-left: 8px;
                width: 60px;
            }
            .nav-item.expanded {
                padding-left: 16px;
            }
            #toggle-btn {
                top: 16px;
            }
            .sidebar.open ~ #toggle-btn {
                display: none;
            }
            .sidebar:not(.open) .nav-text {
                opacity: 0;
                width: 0;
            }
            .sidebar:not(.open) .email-text {
                max-width: 40px;
            }
            .sidebar.open .email-text {
                max-width: calc(100% - 20px);
            }
            .sidebar:not(.open) .user-info-section {
                opacity: 0;
                height: 0;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                border-bottom: none;
            }
            .sidebar.open .user-info-section {
                opacity: 1;
                height: auto;
                padding-top: 1rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e5e7eb;
            }
        }

        @media (min-width: 641px) {
            .sidebar {
                transform: translateX(0);
                width: 240px;
            }
            .email-text {
                max-width: 200px;
            }
            .nav-text {
                opacity: 1;
                width: auto;
            }
            .user-info-section {
                opacity: 1;
                height: auto;
                padding-top: 1rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e5e7eb;
            }
        }

        @keyframes dash {
            to {
                stroke-dashoffset: 0;
            }
        }

        .route-line {
            stroke-dasharray: 1000;
            stroke-dashoffset: 1000;
            animation: dash 10s linear forwards infinite;
            animation-delay: var(--delay);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            display: flex;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .modal-image {
            flex: 0 0 50%;
            height: 100%;
            overflow: hidden;
        }
        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        .modal-details {
            flex: 0 0 50%;
            padding-left: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .close-modal {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .close-modal:hover {
            color: #000;
        }

        /* Footer Styles */
        footer {
            width: 100%;
            margin-left: 0;
            position: relative;
            bottom: 0;
            left: 0;
        }
        .footer-content {
            min-height: calc(100vh - 64px);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Navigation Sidebar -->
        <div id="sidebar" class="sidebar fixed top-0 left-0 h-screen bg-white shadow-lg flex flex-col z-50">
            <!-- Logo -->
            <div class="p-4">
                <img src="images/logo1.png" alt="Ubuntu Technologies Logo" class="w-full h-auto">
            </div>
            <!-- User Info Tab (Visible if logged in) -->
            <?php if (isset($_SESSION['user']) && isset($_SESSION['username'])) { ?>
                <div class="p-4 border-b border-gray-200 user-info-section">
                    <div class="bg-gray-100 p-3 rounded-lg">
                        <p class="text-sm font-medium text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                        <p class="text-xs text-gray-600 email-text"><?php echo htmlspecialchars($_SESSION['user']); ?></p>
                    </div>
                </div>
            <?php } ?>
            <!-- Navigation Links -->
            <nav id="nav-links" class="flex-1">
                <a href="home.php" class="nav-item block py-3 px-4 text-gray-700 hover:bg-gray-100 font-semibold flex items-center transition-all duration-300 collapsed" data-full-text="Home">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="nav-text">Home</span>
                </a>
                <a href="products.php" class="nav-item block py-3 px-4 text-gray-700 hover:bg-gray-100 font-semibold flex items-center transition-all duration-300 collapsed" data-full-text="Products">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="nav-text">Products</span>
                </a>
                <a href="listing.php" class="nav-item block py-3 px-4 text-gray-700 hover:bg-gray-100 font-semibold flex items-center transition-all duration-300 collapsed" data-full-text="My Listings">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h1m0 0h6m-6 0a2 2 0 100 4 2 2 0 000-4zm6 0h2a2 2 0 002-2V7a2 2 0 00-2-2h-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"></path></svg>
                    <span class="nav-text">My Listings</span>
                </a>
                <a href="orders.php" class="nav-item block py-3 px-4 text-gray-700 hover:bg-gray-100 font-semibold flex items-center transition-all duration-300 collapsed" data-full-text="Orders">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span class="nav-text">Orders</span>
                </a>
                <a href="contact.php" class="nav-item block py-3 px-4 text-gray-700 hover:bg-gray-100 font-semibold flex items-center transition-all duration-300 collapsed" data-full-text="Contact Us">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="nav-text">Contact Us</span>
                </a>
                </a>
                <?php if (isset($_SESSION['user']) && $_SESSION['user'] === 'manager@ubuntutech.com') { ?>
                    <a href="manager_panel.php" class="nav-item block py-3 px-4 text-gray-700 hover:bg-gray-100 font-semibold flex items-center transition-all duration-300" data-full-text="Manager Panel">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m10-10V7a4 4 0 00-8 0v4"></path></svg>
                        <span class="nav-text">Manager Panel</span>
                    </a>
                <?php } ?>
            </nav>
            <!-- Login/Signup Buttons or Logout (Conditional) -->
            <div class="p-4">
                <?php if (isset($_SESSION['user'])) { ?>
                    <a href="?logout=true" class="block w-full py-2 px-4 text-center text-white bg-red-600 hover:bg-red-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">Logout</a>
                <?php } else { ?>
                    <a href="index.php" class="block w-full py-2 px-4 text-center text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:opacity-50 mb-2" id="login-btn">Login</a>
                    <a href="signup.php" class="block w-full py-2 px-4 text-center text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:opacity-50" id="signup-btn">Sign Up</a>
                <?php } ?>
            </div>
        </div>

        <!-- Toggle Button for Mobile -->
        <button id="toggle-btn" class="sm:hidden fixed left-4 z-50 p-2 bg-gray-200 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Main Content -->
        <div class="flex-1 sm:ml-64 p-6 bg-gray-50 min-h-screen">
            <!-- Hero Section -->
            <div class="bg-blue-600 text-white rounded-xl shadow-lg p-8 mb-8 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4 animate-fade-in-down">
                    Ubuntu Technologies: Empowering Local Trade
                </h1>
                <p class="text-lg md:text-xl font-light mb-6 animate-fade-in-up">
                    South Africa's Premier <span class="font-semibold">C2C Marketplace</span> – Connect, Buy, and Sell Locally.
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="products.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-3 px-8 rounded-full shadow-md transition-all duration-300 transform hover:scale-105">
                        Explore Products Now!
                    </a>
                    <a href="listing.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-3 px-8 rounded-full shadow-md transition-all duration-300 transform hover:scale-105">
                        Create Listing
                    </a>
                </div>
            </div>

            <!-- What We Do & Our Mission -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-handshake text-blue-600 text-3xl mr-3"></i> What We Do
                    </h2>
                    <p class="text-gray-700 leading-relaxed">
                        Ubuntu Technologies offers a secure online marketplace for South Africans to buy and sell goods directly. From local crafts to electronics, our platform connects individuals, fostering a thriving C2C community with easy listing and purchasing options.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bullseye text-blue-600 text-3xl mr-3"></i> Our Mission
                    </h2>
                    <p class="text-gray-700 leading-relaxed">
                        We aim to empower local trade by connecting South African communities. Through trust, transparency, and accessibility, we support entrepreneurs and buyers, enhancing economic growth across the nation.
                    </p>
                </div>
            </div>

            <!-- User Satisfaction / Ratings -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8 text-center">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Why Our Users Love Us</h2>
                <div class="flex justify-center items-center mb-4">
                    <i class="fas fa-star text-yellow-400 text-3xl mx-1"></i>
                    <i class="fas fa-star text-yellow-400 text-3xl mx-1"></i>
                    <i class="fas fa-star text-yellow-400 text-3xl mx-1"></i>
                    <i class="fas fa-star text-yellow-400 text-3xl mx-1"></i>
                    <i class="fas fa-star-half-alt text-yellow-400 text-3xl mx-1"></i>
                    <span class="ml-3 text-2xl font-bold text-gray-700">4.8/5.0</span>
                </div>
                <p class="text-gray-600 mb-6">
                    Based on thousands of satisfied customers across South Africa.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-100 p-4 rounded-lg shadow-inner">
                        <p class="text-gray-800 italic">"Great platform for local trades! Easy to use."</p>
                        <p class="text-sm text-gray-600 mt-2">- Thabo S.</p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg shadow-inner">
                        <p class="text-gray-800 italic">"Sold my items quickly with excellent support."</p>
                        <p class="text-sm text-gray-600 mt-2">- Naledi P.</p>
                    </div>
                    <div class="bg-gray-100 p-4 rounded-lg shadow-inner">
                        <p class="text-gray-800 italic">"Trustworthy and efficient marketplace."</p>
                        <p class="text-sm text-gray-600 mt-2">- Kwame M.</p>
                    </div>
                </div>
            </div>

            <!-- South Africa Coverage Map -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Extensive Reach Across South Africa</h2>
                <p class="text-gray-600 text-center mb-6">
                    Connecting buyers and sellers across various municipalities.
                </p>
                <div class="w-full flex justify-center p-4">
                    <svg viewBox="0 0 600 500" class="w-full h-auto max-w-2xl bg-gray-50 rounded-lg shadow-inner border border-gray-200">
                        <!-- Simplified South Africa Map Outline -->
                        <path fill="#e0e0e0" stroke="#a0a0a0" stroke-width="1" d="M150 50 L100 100 L80 150 L120 200 L150 250 L200 300 L250 350 L300 400 L350 450 L400 400 L450 350 L500 300 L550 250 L500 200 L450 150 L400 100 L350 50 Z"/>
                        <!-- Major Cities -->
                        <circle cx="300" cy="100" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Pretoria</title></circle>
                        <circle cx="280" cy="120" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Johannesburg</title></circle>
                        <circle cx="450" cy="250" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Durban</title></circle>
                        <circle cx="150" cy="400" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Cape Town</title></circle>
                        <circle cx="350" cy="420" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Port Elizabeth</title></circle>
                        <circle cx="250" cy="300" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Bloemfontein</title></circle>
                        <circle cx="400" cy="80" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Polokwane</title></circle>
                        <circle cx="200" cy="250" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Kimberley</title></circle>
                        <circle cx="480" cy="200" r="8" fill="#0152e9" stroke="#fff" stroke-width="1.5"><title>Mbombela</title></circle>
                        <!-- Connecting Lines -->
                        <g stroke="#0152e9" stroke-width="3" fill="none" stroke-linecap="round">
                            <path class="route-line" style="--delay:0s;" d="M300 100 L280 120" />
                            <path class="route-line" style="--delay:0.5s;" d="M280 120 L450 250" />
                            <path class="route-line" style="--delay:1s;" d="M450 250 L150 400" />
                            <path class="route-line" style="--delay:1.5s;" d="M150 400 L350 420" />
                            <path class="route-line" style="--delay:2s;" d="M250 300 L200 250" />
                            <path class="route-line" style="--delay:2.5s;" d="M400 80 L300 100" />
                            <path class="route-line" style="--delay:3s;" d="M480 200 L450 250" />
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Featured Products (Moved to Bottom) -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Featured Products</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php if (empty($featured_products)) { ?>
                        <p class="text-gray-500 text-center col-span-full">No featured products available.</p>
                    <?php } else { ?>
                        <?php foreach ($featured_products as $index => $product) { ?>
                            <div class="bg-gray-100 p-4 rounded-lg shadow-inner flex flex-col items-center">
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-32 h-32 object-cover rounded-lg mb-2">
                                <h3 class="text-lg font-semibold text-gray-800 text-center"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-gray-700 font-medium text-center">R<?php echo number_format($product['price'], 2); ?></p>
                                <button id="viewDetailsBtn_<?php echo $index; ?>" class="mt-2 bg-blue-600 text-white py-1 px-3 rounded-lg hover:bg-blue-700" data-product='<?php echo json_encode($product); ?>'>View Details</button>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>

            <!-- Footer -->
            <footer class="theme-blue text-white py-8 px-6 w-full">
                <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <p class="text-lg font-semibold">Ubuntu Technologies</p>
                        <p class="text-sm">© <?php echo date('Y'); ?> Ubuntu Technologies. All rights reserved.</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="https://facebook.com" target="_blank" class="hover:text-gray-300"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com" target="_blank" class="hover:text-gray-300"><i class="fab fa-twitter"></i></a>
                        <a href="https://instagram.com" target="_blank" class="hover:text-gray-300"><i class="fab fa-instagram"></i></a>
                        <a href="contact.php" class="inline-block bg-white text-blue-600 hover:bg-gray-100 font-bold py-2 px-4 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105">
                            Contact Us
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modal Template -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">×</span>
            <div class="modal-image">
                <img id="modalImage" src="" alt="Product Image">
            </div>
            <div class="modal-details">
                <div>
                    <h3 id="modalName" class="text-xl font-semibold text-gray-800 mb-2"></h3>
                    <p id="modalDelivery" class="text-gray-600 mb-2">Delivery: 3-7 business days (depending on location)</p>
                    <p id="modalPrice" class="text-gray-700 font-medium mb-2"></p>
                    <p id="modalDescription" class="text-gray-600 mb-4"></p>
                </div>
                <form id="addToCartForm" method="POST" action="home.php" class="flex items-center">
                    <input type="hidden" id="modalProductId" name="product_id">
                    <label for="modalQuantity" class="mr-2 text-gray-700">Qty:</label>
                    <input type="number" id="modalQuantity" name="quantity" min="1" value="1" class="w-16 px-2 py-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <button type="submit" name="add_to_cart" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const navItems = document.querySelectorAll('.nav-item');
        const emailTextElement = document.querySelector('.email-text');
        const userInfoSection = document.querySelector('.user-info-section');
        const modal = document.getElementById('productModal');
        let currentProduct = null;

        function updateSidebarState(isOpen) {
            if (isOpen) {
                sidebar.classList.add('open');
            } else {
                sidebar.classList.remove('open');
            }

            navItems.forEach(item => {
                const navText = item.querySelector('.nav-text');
                if (isOpen) {
                    item.classList.remove('collapsed');
                    item.classList.add('expanded');
                    navText.style.opacity = '1';
                    navText.style.width = 'auto';
                } else {
                    item.classList.add('collapsed');
                    item.classList.remove('expanded');
                    navText.style.opacity = '0';
                    navText.style.width = '0';
                }
            });

            if (userInfoSection) {
                if (isOpen) {
                    userInfoSection.style.opacity = '1';
                    userInfoSection.style.height = 'auto';
                    userInfoSection.style.paddingTop = '1rem';
                    userInfoSection.style.paddingBottom = '1rem';
                    userInfoSection.style.borderBottom = '1px solid #e5e7eb';
                    if (emailTextElement) {
                        emailTextElement.style.maxWidth = window.innerWidth <= 640 ? 'calc(100% - 20px)' : '200px';
                    }
                } else {
                    userInfoSection.style.opacity = '0';
                    userInfoSection.style.height = '0';
                    userInfoSection.style.paddingTop = '0px';
                    userInfoSection.style.paddingBottom = '0px';
                    userInfoSection.style.borderBottom = 'none';
                    if (emailTextElement) {
                        emailTextElement.style.maxWidth = '40px';
                    }
                }
            }

            if (window.innerWidth <= 640) {
                if (isOpen) {
                    const navLinks = document.getElementById('nav-links');
                    let maxTextWidth = 0;
                    navItems.forEach(item => {
                        const text = item.getAttribute('data-full-text');
                        const tempSpan = document.createElement('span');
                        tempSpan.style.fontSize = '1rem';
                        tempSpan.style.fontWeight = '600';
                        tempSpan.style.visibility = 'hidden';
                        tempSpan.style.position = 'absolute';
                        tempSpan.textContent = text;
                        document.body.appendChild(tempSpan);
                        maxTextWidth = Math.max(maxTextWidth, tempSpan.offsetWidth);
                        document.body.removeChild(tempSpan);
                    });
                    const padding = 80;
                    sidebar.style.width = `${maxTextWidth + padding}px`;
                } else {
                    sidebar.style.width = '60px';
                }
                toggleBtn.style.display = isOpen ? 'none' : 'block';
            } else {
                sidebar.style.width = '240px';
                toggleBtn.style.display = 'none';
            }
        }

        toggleBtn.addEventListener('click', () => {
            updateSidebarState(!sidebar.classList.contains('open'));
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 640 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                updateSidebarState(false);
            }
        });

        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (window.innerWidth <= 640 && sidebar.classList.contains('open')) {
                    setTimeout(() => {
                        updateSidebarState(false);
                    }, 100);
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 640) {
                updateSidebarState(true);
            } else {
                updateSidebarState(false);
            }
        });

        adjustSidebarInitial();

        function adjustSidebarInitial() {
            if (window.innerWidth > 640) {
                updateSidebarState(true);
            } else {
                updateSidebarState(false);
            }
        }

        // Modal Functions
        function openModal(product) {
            try {
                currentProduct = product;
                document.getElementById('modalImage').src = product.image_path;
                document.getElementById('modalName').textContent = product.name;
                document.getElementById('modalPrice').textContent = `Price: R${parseFloat(product.price).toFixed(2)}`;
                document.getElementById('modalDescription').textContent = product.description || 'No description available.';
                document.getElementById('modalProductId').value = product.product_id;
                modal.style.display = 'block';
                console.log('Modal opened with product:', product);
            } catch (error) {
                console.error('Error opening modal:', error);
            }
        }

        function closeModal() {
            modal.style.display = 'none';
            console.log('Modal closed');
        }

        // Handle Add to Cart
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!currentProduct) return;

            const formData = new FormData(this);
            fetch('home.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModal();
                    location.reload();
                } else {
                    if (data.message === 'Please log in to add to cart.') {
                        alert(data.message);
                        window.location.href = 'index.php?redirect=login.php';
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target == modal) {
                closeModal();
            }
        });

        // Attach event listeners to View Details buttons
        document.querySelectorAll('[id^="viewDetailsBtn_"]').forEach(button => {
            button.addEventListener('click', () => {
                try {
                    const product = JSON.parse(button.getAttribute('data-product'));
                    openModal(product);
                } catch (error) {
                    console.error('Error parsing product data:', error);
                }
            });
        });
    </script>
</body>
</html>