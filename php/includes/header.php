<?php
// Header untuk halaman PUBLIC (belum login)
// Session sudah di-start di halaman yang memanggil header ini

// Tentukan base path berdasarkan lokasi file
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/php/pages/') !== false) {
    $base_path = '../';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo isset($page_title) ? $page_title : 'EzRent - Sewa Kendaraan Mudah'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #fff;
            color: #000;
            line-height: 1.6;
            min-height: 50vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* Respect device safe area (notch) */
        body { padding-top: env(safe-area-inset-top); }
        header { padding-top: env(safe-area-inset-top); }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: transparent;
            border-bottom: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        header.scrolled {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: baseline;
            gap: 0;
            letter-spacing: -0.03em;
            transition: opacity 0.3s ease;
        }

        .logo:hover {
            opacity: 0.9;
        }

        .logo-ez {
            font-weight: 300;
            color: #ffffff;
        }

        .logo-rent {
            font-weight: 700;
            color: #ffffff;
        }

        .logo-accent {
            color: #d50000;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 300;
            font-size: 0.85rem;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .nav-links a:hover {
            color: white;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 1px;
            background: white;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.active {
            color: white;
        }

        .nav-links a.active::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .btn-login {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 300;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
        }

        .btn-login:hover {
            color: white;
        }

        .btn-register {
            background: white;
            color: black;
            text-decoration: none;
            font-weight: 500;
            padding: 0.6rem 1.75rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            border: 2px solid white;
        }

        .btn-register:hover {
            background: transparent;
            color: white;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
        }

        /* Responsive */
        @media (max-width: 950px) {
            .mobile-menu-btn {
                display: block;
            }

            header {
                background: #0a0a0a !important;
                box-shadow: none !important;
            }

            .nav-links {
                display: none;
                margin-top: 25px;
                margin-left: 25px;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: rgba(0, 0, 0, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                padding: 2rem 1rem;
                gap: 1.5rem;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                padding: 0.75rem 0;
            }

            /* On mobile: show a compact login button in the header (hide register) */
            .auth-buttons { display: flex !important; gap: 0.5rem; align-items: center; }
            .auth-buttons .btn-login { display: inline-block; padding: 0.35rem 0.8rem; font-size: 0.82rem; }
            .auth-buttons .btn-register { display: none !important; }

            .logo {
                font-size: 1.5rem;
    
            }
            .nav-links a {
                font-size: 1rem;
        }
    }
        @media (max-width: 950px) {
                    .navbar {
                    justify-content: center !important;
                    position: relative;
                }
                .logo {
                    position: absolute;
                    left: 50%;
                    transform: translateX(-50%);
                    margin: 0 !important;
                    text-align: center;
                }
                .mobile-menu-btn {
                    position: absolute;
                    left: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                }
                .auth-buttons {
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                }
        }
    </style>
</head>
<body>
    
    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>

                <a href="<?php echo $base_path; ?>index.php" class="logo">
                    <span class="logo-ez">Ez</span><span class="logo-rent">Rent</span><span class="logo-accent">.</span>
                </a>

                <ul class="nav-links" id="navLinks">
                    <li><a href="<?php echo $base_path; ?>index.php">Beranda</a></li>
                    <li><a href="<?php echo $base_path; ?>vehicles.php">Kendaraan</a></li>
                    <li><a href="<?php echo $base_path; ?>about.php">Tentang Kami</a></li>
                    <li><a href="<?php echo $base_path; ?>contact.php">Kontak</a></li>
                </ul>

                <div class="auth-buttons">
                    <!-- Tampilan untuk guest (belum login) -->
                    <a href="<?php echo $base_path; ?>login.php" class="btn-login">Login</a>
                    <a href="<?php echo $base_path; ?>register.php" class="btn-register">Daftar</a>
                </div>
            </nav>
        </div>
    </header>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            if (mobileBtn && !navLinks.contains(event.target) && !mobileBtn.contains(event.target)) {
                navLinks.classList.remove('active');
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