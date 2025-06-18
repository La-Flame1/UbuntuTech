<?php
// Check for logout (session should already be started in the main page)
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .theme-blue { background: linear-gradient(90deg, #0152e9); }
        .theme-blue-hover { background-color: (90deg, #0137a0); }
        .sidebar {
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
        }
        .email-text {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nav-text {
            transition: opacity 0.3s ease-in-out, width 0.3s ease-in-out;
        }
        @media (max-width: 640px) {
            .sidebar {
                transform: translateX(-100%);
                width: 60px;
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
                max-width: 100px;
            }
            /* Hiding the user info content when sidebar is collapsed on mobile */
        sidebar:not(.open) .user-info-content {
            opacity: 0;
            height: 0;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-bottom: none !important; /* Ensure border is gone */
            }

        /* For immediate hiding and showing without transition when state changes on mobile */
        .sidebar:not(.open) .user-info-section {
            display: none; /* Hide the entire section when collapsed */
        }
        .sidebar.open .user-info-section {
            display: block; /* Show the entire section when open */
        }
        }
        @media (min-width: 641px) {
            .email-text {
                max-width: 200px;
            }
            .nav-text {
                opacity: 1;
                width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Navigation Sidebar -->
        <div id="sidebar" class="sidebar fixed top-0 left-0 h-screen bg-white shadow-lg flex flex-col z-50">
            <!-- Logo -->
            <div class="p-4">
                <img src="uploads/logo.png" alt="Ubuntu Technologies Logo" class="w-full h-auto">
            </div>
            <!-- User Info Tab (Visible if logged in) -->
            <?php if (isset($_SESSION['user']) && isset($_SESSION['username'])) { ?>
                <div class="p-4 border-b border-gray-200 user-info-section">
                    <div class="bg-gray-100 p-3 rounded-lg user-info-content">
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

        <!-- Main Content Placeholder -->
        <div class="sm:ml-64 p-6 flex-1">
            <!-- Content will be included from other pages like home.php -->
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const navItems = document.querySelectorAll('.nav-item');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            const isOpen = sidebar.classList.contains('open');
            navItems.forEach(item => {
                item.classList.toggle('collapsed', !isOpen);
                item.classList.toggle('expanded', isOpen);
                const textSpan = item.querySelector('.nav-text');
                textSpan.style.opacity = isOpen ? '1' : '0';
                textSpan.style.width = isOpen ? 'auto' : '0';
            });

            if (isOpen && window.innerWidth <= 640) {
                const navLinks = document.getElementById('nav-links');
                const longestText = Array.from(navItems).reduce((longest, item) => {
                    const text = item.getAttribute('data-full-text');
                    return text.length > longest.length ? text : longest;
                }, '');
                const tempSpan = document.createElement('span');
                tempSpan.style.fontSize = '1rem';
                tempSpan.style.fontWeight = '600';
                tempSpan.style.visibility = 'hidden';
                tempSpan.textContent = longestText;
                document.body.appendChild(tempSpan);
                const textWidth = tempSpan.offsetWidth;
                document.body.removeChild(tempSpan);
                const padding = 80;
                sidebar.style.width = `${textWidth + padding}px`;
            } else if (window.innerWidth <= 640) {
                sidebar.style.width = '60px';
            }

            toggleBtn.style.display = isOpen ? 'none' : 'block';
        }

        toggleBtn.addEventListener('click', toggleSidebar);

        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                if (window.innerWidth <= 640 && !sidebar.classList.contains('open')) {
                    e.preventDefault();
                    toggleSidebar();
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 640 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                sidebar.classList.remove('open');
                navItems.forEach(item => {
                    item.classList.add('collapsed');
                    item.classList.remove('expanded');
                    item.querySelector('.nav-text').style.opacity = '0';
                    item.querySelector('.nav-text').style.width = '0';
                });
                sidebar.style.width = '60px';
                toggleBtn.style.display = 'block';
            }
        });

        function adjustSidebar() {
            if (window.innerWidth > 640) {
                sidebar.classList.remove('open');
                sidebar.style.width = '240px';
                navItems.forEach(item => {
                    item.classList.remove('collapsed', 'expanded');
                    item.querySelector('.nav-text').style.opacity = '1';
                    item.querySelector('.nav-text').style.width = 'auto';
                });
                toggleBtn.style.display = 'none';
            } else {
                sidebar.classList.remove('open');
                sidebar.style.width = '60px';
                navItems.forEach(item => {
                    item.classList.add('collapsed');
                    item.classList.remove('expanded');
                    item.querySelector('.nav-text').style.opacity = '0';
                    item.querySelector('.nav-text').style.width = '0';
                });
                toggleBtn.style.display = 'block';
            }
        }

        window.addEventListener('resize', adjustSidebar);
        adjustSidebar();
    </script>
</body>
</html>