<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'EzRent - Sewa Kendaraan Mudah'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --background: #ffffff;
            --border: #e5e7eb;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: var(--background);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-img {
            height: 40px;
            width: auto;
            transition: transform 0.3s ease;
        }

        .logo:hover .logo-img {
            transform: scale(1.05);
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: var(--primary);
        }

        .nav-links a.active::after {
            width: 100%;
        }

        /* Profile Dropdown Styles */
        .profile-menu {
            position: relative;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            color: var(--text-dark);
            font-weight: 500;
        }

        .profile-btn:hover {
            background: rgba(37, 99, 235, 0.05);
            color: var(--primary);
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1001;
        }

        .dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
            border-bottom: 1px solid var(--border);
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background: rgba(37, 99, 235, 0.05);
            color: var(--primary);
        }

        .dropdown-icon {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--background);
                flex-direction: column;
                padding: 1rem;
                border-bottom: 1px solid var(--border);
                box-shadow: var(--shadow-lg);
                transform: none;
                position: static;
            }

            .nav-links.active {
                display: flex;
            }

            .profile-menu {
                width: 100%;
            }

            .profile-btn {
                width: 100%;
                justify-content: center;
            }

            .dropdown-menu {
                position: static;
                width: 100%;
                box-shadow: none;
                border: none;
                border-top: 1px solid var(--border);
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
            }

            .dropdown-menu.active {
                display: block;
            }

            .logo-img {
                height: 35px;
            }
        }

        @media (max-width: 480px) {
            .logo-img {
                height: 30px;
            }
        }
    </style>
</head>
<body>
    <?php
    // Tentukan base path berdasarkan lokasi file
    $base_path = '';
    if (strpos($_SERVER['PHP_SELF'], '/php/pages/') !== false) {
        $base_path = '../';
    }
    
    // Data user (contoh - bisa diambil dari session)
    $user_name = "admin";
    $user_initials = "adm";
    ?>
    
    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="<?php echo $base_path; ?>index.php" class="logo">
                    <img src="<?php echo $base_path; ?>../assets/images/logo.jpg" alt="EzRent" class="logo-img">
                </a>

                <ul class="nav-links" id="navLinks">
                    <li><a href="<?php echo $base_path; ?>index.php">Beranda</a></li>
                    <li><a href="<?php echo $base_path; ?>vehicles.php">Kendaraan</a></li>
                    <li><a href="<?php echo $base_path; ?>about.php">Tentang Kami</a></li>
                    <li><a href="<?php echo $base_path; ?>contact.php">Kontak</a></li>
                </ul>

                <!-- Profile Dropdown Menu -->
                <div class="profile-menu">
                    <button class="profile-btn" onclick="toggleProfileMenu()">
                        <div class="profile-avatar"><?php echo $user_initials; ?></div>
                        <span><?php echo $user_name; ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="<?php echo $base_path; ?>profile.php" class="dropdown-item">
                            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Profil
                        </a>
                        <a href="<?php echo $base_path; ?>settings.php" class="dropdown-item">
                            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1Z"/>
                            </svg>
                            Pengaturan
                        </a>
                        <a href="<?php echo $base_path; ?>reports.php" class="dropdown-item">
                            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                                <path d="M16 13H8"/>
                                <path d="M16 17H8"/>
                                <path d="M10 9H8"/>
                            </svg>
                            Laporan
                        </a>
                        <a href="<?php echo $base_path; ?>logout.php" class="dropdown-item">
                            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16,17 21,12 16,7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>

                <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
            </nav>
        </div>
    </header>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        function toggleProfileMenu() {
            const dropdownMenu = document.getElementById('dropdownMenu');
            dropdownMenu.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const profileBtn = document.querySelector('.profile-btn');

            if (!navLinks.contains(event.target) && !mobileBtn.contains(event.target)) {
                navLinks.classList.remove('active');
            }

            if (!dropdownMenu.contains(event.target) && !profileBtn.contains(event.target)) {
                dropdownMenu.classList.remove('active');
            }
        });

        // Add active class to current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (linkPage === currentPage || (currentPage === '' && linkPage === 'index.php')) {
                    link.classList.add('active');
                }
            });
        });
    </script>