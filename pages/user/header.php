<?php
// Header untuk halaman USER (sudah login)
// Session dan cek login sudah dilakukan di halaman yang memanggil header ini

// Ambil data user dari session
$user_name = $_SESSION['nama_lengkap'] ?? '';
$base_path = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard User - EzRent'; ?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #fafafa;
            color: #000;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header - Transparent overlay on hero */
        header {
            background: transparent;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        /* For pages without hero, add padding to body */
        body.no-hero header {
            background: #000;
            position: sticky;
        }

        /* Safe-area padding for notch devices */
        body { padding-top: env(safe-area-inset-top); }
        header { padding-top: env(safe-area-inset-top); }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
        }

        .logo {
            font-size: 1.75rem;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: baseline;
            letter-spacing: -0.03em;
            transition: opacity 0.3s;
        }

        .logo:hover { opacity: 0.9; }
        .logo-ez { font-weight: 300; color: #fff; }
        .logo-rent { font-weight: 700; color: #fff; }
        .logo-accent { color: #d50000; }

        .nav-links {
            display: flex;
            list-style: none;
            margin-top: 20px ;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-weight: 300;
            font-size: 0.9rem;
            padding: 0.5 rem 0;
            position: relative;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .nav-links a:hover {
            color: #fff;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 1px;
            background: #fff;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a.active {
            color: #fff;
        }

        .auth-buttons {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .user-greeting {
            color: rgba(255,255,255,0.7);
            font-weight: 300;
            font-size: 0.9rem;
        }

        .btn-logout {
            background: transparent;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border: 2px solid #fff;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.8rem;
        }

        .btn-logout:hover {
            background: #fff;
            color: #000;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #fff;
        }

        /* Main Content Area */
        main.main-content {
            flex: 1;
            padding: 0;
            width: 100%;
        }

        /* Container wrapper for pages that need it */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 20px;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 300;
            color: #000;
            letter-spacing: -0.02em;
        }

        .page-title strong {
            font-weight: 600;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1rem;
            font-weight: 300;
            margin-top: 0.5rem;
        }

        /* Card Styles */
        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0;
            box-shadow: none;
            transition: all 0.3s ease;
        }

        .card:hover {
            border-color: #000;
        }

        .card-header {
            background: #000;
            color: #fff;
            padding: 1rem 1.5rem;
            font-weight: 500;
            border-radius: 0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Stat Cards */
        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: #000;
            transform: translateY(-3px);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            background: #fafafa;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .stat-card .stat-value {
            font-size: 2.25rem;
            font-weight: 600;
            color: #000;
            letter-spacing: -0.02em;
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 0.25rem;
        }

        /* Tables */
        .table { margin: 0; }
        .table thead th {
            background: #fafafa;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
            color: #6b7280;
            padding: 1rem 1.25rem;
        }
        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.95rem;
        }
        .table tbody tr:hover { background: #fafafa; }

        /* Buttons */
        .btn-primary {
            background: #000;
            border: 2px solid #000;
            color: #fff;
            border-radius: 0;
            padding: 0.75rem 2rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: transparent;
            color: #000;
            border-color: #000;
        }

        .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            color: #6b7280;
            border-radius: 0;
            padding: 0.75rem 2rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
        }

        .btn-outline-secondary:hover {
            background: #000;
            color: #fff;
            border-color: #000;
        }

        .btn-danger {
            background: #d50000;
            border: 2px solid #d50000;
            border-radius: 0;
        }

        .btn-danger:hover {
            background: transparent;
            color: #d50000;
        }

        /* Badge */
        .badge {
            font-weight: 500;
            padding: 0.4rem 0.85rem;
            border-radius: 0;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .bg-success { background: #000 !important; }
        .bg-warning { background: #f59e0b !important; color: #000 !important; }
        .bg-danger { background: #d50000 !important; }
        .bg-info { background: #6b7280 !important; }
        .bg-secondary { background: #e5e7eb !important; color: #000 !important; }

        /* Form */
        .form-control, .form-select {
            border-radius: 0;
            border: 2px solid #e5e7eb;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #000;
            box-shadow: none;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        /* Modal */
        .modal-content {
            border-radius: 0;
            border: none;
        }

        .modal-header {
            background: #000;
            color: #fff;
            border-radius: 0;
            padding: 1.25rem 1.5rem;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .modal-title {
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.95rem;
        }

        /* Alert */
        .alert {
            border-radius: 0;
            border: none;
            padding: 1rem 1.25rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #166534;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #d50000;
        }

        .alert-warning {
            background: #fffbeb;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state h4 {
            font-weight: 500;
            color: #000;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            font-weight: 300;
        }

        /* Responsive */
        @media (max-width: 880px) {
            .mobile-menu-btn {
                display: block;
            }

            .nav-links {
                display: none;
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

            .auth-buttons {
                display: none;
            }

            .page-title {
                font-size: 1.5rem;
            }

            main {
                padding: 2rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation untuk User -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <span class="logo-ez">Ez</span><span class="logo-rent">Rent</span><span class="logo-accent">.</span>
                </a>

                <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>

                <ul class="nav-links" id="navLinks">
                    <li><a href="dashboard.php">Beranda</a></li>
                    <li><a href="vehicles.php">Kendaraan</a></li>
                    <li><a href="my-bookings.php">Pesanan Saya</a></li>
                    <li><a href="contact.php">Kontak</a></li>
                </ul>

                <div class="auth-buttons">
                    <a href="profile.php" class="user-greeting" style="text-decoration: none;">
                        <?php echo htmlspecialchars($user_name); ?>
                    </a>
                    <a href="../../php/auth/logout.php" class="btn-logout" onclick="return confirmLogoutSimple()">
                        Logout
                    </a>
                </div>

                <script>
                function confirmLogoutSimple() {
                    return confirm('Apakah Anda yakin ingin logout?');
                }
                </script>
            </nav>
        </div>
    </header>

    <main class="main-content">

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById('navLinks');
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            if (!navLinks.contains(event.target) && !mobileBtn.contains(event.target)) {
                navLinks.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>