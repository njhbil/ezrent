<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel - EzRent'; ?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/responsive.css" rel="stylesheet">
    <link href="../../assets/css/admin-premium.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            min-height: 100%;
            width: 100%;
            overflow-x: hidden;
            max-width: 100vw;
        }
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }
        
        /* Global fix for horizontal overflow */
        .d-flex { 
            max-width: 100%; 
        }
        
        /* Chart responsive */
        canvas {
            max-width: 100% !important;
            height: auto !important;
        }
        
        /* Sidebar - Premium Modern Design */
        .sidebar {
            display: flex;
            flex-direction: column;
            max-height: 100vh;
            background: linear-gradient(180deg, #0f0f0f 0%, #1a1a1a 100%);
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            z-index: 100;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255,255,255,0.05);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        /* Animated Background Pattern */
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(213, 0, 0, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(255, 82, 82, 0.08) 0%, transparent 40%);
            opacity: 0.6;
            pointer-events: none;
            z-index: 0;
            animation: bgPulse 8s ease-in-out infinite;
        }
        
        @keyframes bgPulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 0.8; }
        }
        
        /* Sidebar Header with Enhanced Logo */
        .sidebar-header {
            padding: 2.5rem 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: relative;
            overflow: hidden;
            z-index: 1;
            background: rgba(0, 0, 0, 0.2);
        }
        
        /* Animated Top Border */
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, #ff5252, #d50000, transparent);
            animation: borderSlide 3s ease-in-out infinite;
        }
        
        @keyframes borderSlide {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* safe area for notched devices */
        body { padding-top: env(safe-area-inset-top); }
        .sidebar { padding-top: calc(env(safe-area-inset-top) + 1rem); }
        
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .logo-text .brand {
            font-size: 2.2rem;
            color: #fff;
            letter-spacing: -0.05em;
            display: flex;
            align-items: baseline;
            font-weight: 300;
            position: relative;
        }
        
        .logo-text .brand .ez { 
            font-weight: 300;
            background: linear-gradient(135deg, #fff 0%, #e0e0e0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo-text .brand .rent { 
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #f5f5f5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo-text .brand .dot { 
            color: #d50000; 
            font-size: 2.5rem;
            filter: drop-shadow(0 0 10px rgba(213, 0, 0, 0.8));
            animation: dotPulse 2s ease-in-out infinite;
        }
        
        @keyframes dotPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }
        
        .logo-text .tagline {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.25em;
            margin-top: 0.5rem;
            font-weight: 600;
        }
        
        /* Admin Profile Section - Premium Card */
        .admin-profile {
            padding: 1.5rem;
            margin: 1.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            position: relative;
            z-index: 1;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        .admin-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .admin-profile:hover::before {
            left: 100%;
        }
        
        .admin-profile:hover {
            background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.04) 100%);
            border-color: rgba(255,255,255,0.15);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .admin-avatar {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #d50000 0%, #ff5252 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 1.3rem;
            box-shadow: 0 8px 24px rgba(213, 0, 0, 0.4);
            position: relative;
            transition: all 0.3s ease;
        }
        
        .admin-avatar::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid #1a1a1a;
            border-radius: 50%;
            animation: statusPulse 2s ease-in-out infinite;
        }
        
        @keyframes statusPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            50% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        }
        
        .admin-profile:hover .admin-avatar {
            transform: scale(1.05) rotate(5deg);
        }
        
        .admin-info {
            flex: 1;
        }
        
        .admin-name {
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            letter-spacing: -0.01em;
        }
        
        .admin-role {
            color: #10b981;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
        }
        
        .admin-role::before {
            content: '●';
            color: #10b981;
            font-size: 0.5rem;
            animation: onlinePulse 2s ease-in-out infinite;
        }
        
        @keyframes onlinePulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        
        /* Navigation - Premium iOS Style */
        .sidebar-nav { 
            padding: 0.5rem 0;
            position: relative;
            z-index: 1;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            padding-bottom: 1.5rem;
            max-height: calc(100vh - 280px);
        }
        
        /* Custom Scrollbar */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(213, 0, 0, 0.3);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(213, 0, 0, 0.5);
        }
        
        .nav-section-title {
            padding: 1.5rem 1.5rem 0.75rem;
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-weight: 700;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            margin: 0.25rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(213, 0, 0, 0.15), rgba(255, 82, 82, 0.1));
            transition: width 0.4s ease;
            z-index: -1;
        }
        
        .nav-link:hover::before {
            width: 100%;
        }
        
        .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover i {
            transform: scale(1.15);
            color: #ff5252;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, rgba(213, 0, 0, 0.15) 0%, rgba(255, 82, 82, 0.1) 100%);
            color: #fff;
            border: 1px solid rgba(213, 0, 0, 0.3);
            box-shadow: 0 4px 12px rgba(213, 0, 0, 0.2);
            transform: translateX(0);
        }
        
        .nav-link.active i {
            color: #ff5252;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: linear-gradient(180deg, #d50000, #ff5252);
            border-radius: 0 4px 4px 0;
        }
        
        .badge-count {
            margin-left: auto;
            background: linear-gradient(135deg, #d50000, #ff5252);
            color: #fff;
            font-size: 0.7rem;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(213, 0, 0, 0.4);
            animation: badgePulse 2s ease-in-out infinite;
        }
        
        @keyframes badgePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .nav-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            margin: 1rem 1.5rem;
        }
        
        .nav-link.logout {
            color: #ff5252;
            margin-top: 0.5rem;
        }
        
        .nav-link.logout:hover {
            background: rgba(255, 82, 82, 0.1);
            border-color: rgba(255, 82, 82, 0.3);
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 0; /* ensure dropdowns/modal appear above main content */
        }
        
        /* Top Header - Enhanced */
        .top-header {
            background: #fff;
            padding: 1.25rem 2rem;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        /* Ensure dropdowns and interactive items are clickable above other layers */
        .dropdown-item, .dropdown-toggle {
            pointer-events: auto !important;
            z-index: 1020; /* keep toggle itself in normal flow */
        }
        /* Render dropdown menus in a high layer to avoid being blocked by overlays/stacking contexts */
        .dropdown-menu {
            position: fixed !important;
            pointer-events: auto !important;
            z-index: 12060 !important; /* above sidebar overlay (99) and below modals (11060) */
            transform-origin: top right !important;
            min-width: 10rem;
        }

        /* Allow dropdown menus inside tables to overflow their container */
        .table-responsive {
            overflow: visible !important;
        }

        /* Ensure Bootstrap modals/backdrops are always on top and clickable */
        .modal-backdrop {
            z-index: 11050 !important;
            pointer-events: auto !important;
        }
        .modal {
            z-index: 11060 !important;
        }
        .modal .modal-content {
            pointer-events: auto !important;
        }
        
        .page-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #666;
        }
        
        .breadcrumb a {
            color: #d50000;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb a:hover {
            color: #ff5252;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .current-time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.85rem;
            padding: 0.6rem 1rem;
            background: #f5f5f5;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .current-time i {
            color: #d50000;
        }
        
        .header-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            color: #666;
        }
        
        .header-btn:hover {
            background: #f5f5f5;
            border-color: #d50000;
            color: #d50000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #d50000;
            border-radius: 50%;
            border: 2px solid #fff;
            animation: notificationPulse 2s ease-in-out infinite;
        }
        
        @keyframes notificationPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(213, 0, 0, 0.7); }
            50% { box-shadow: 0 0 0 4px rgba(213, 0, 0, 0); }
        }
        
        .header-btn-logout {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: linear-gradient(135deg, #d50000, #ff5252);
            color: #fff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(213, 0, 0, 0.3);
        }
        
        .header-btn-logout:hover {
            background: linear-gradient(135deg, #b30000, #d50000);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(213, 0, 0, 0.4);
            color: #fff;
        }
        
        /* Content Area */
        .content-area {
            padding: 2rem;
            background: transparent;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 99;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* When a Bootstrap modal is open, hide the sidebar overlay so it doesn't block modal interactions */
        body.modal-open .sidebar-overlay {
            display: none !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        
        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .mobile-toggle {
                display: flex !important;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                border-radius: 12px;
                border: 1px solid rgba(0,0,0,0.08);
                background: #fff;
                cursor: pointer;
                transition: all 0.3s ease;
                color: #666;
            }
            
            .mobile-toggle:hover {
                background: #f5f5f5;
                color: #d50000;
                border-color: #d50000;
            }
            
            .top-header {
                padding: 1rem 1.25rem;
            }
            
            .page-info h1 {
                font-size: 1.25rem;
            }
            
            .current-time {
                display: none;
            }
            
            .header-btn-logout span {
                display: none;
            }
            
            .header-btn-logout {
                width: 42px;
                height: 42px;
                padding: 0;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .content-area {
                padding: 1rem;
            }
            
            .top-header {
                padding: 0.875rem 1rem;
            }
            
            .page-info h1 {
                font-size: 1.1rem;
            }
            
            .breadcrumb {
                font-size: 0.75rem;
            }
            
            .header-actions {
                gap: 0.5rem;
            }
            
            .header-btn {
                width: 38px;
                height: 38px;
            }
        }
        
        .mobile-toggle { display: none; }
    </style>
</head>
<body>

<?php
// Get notification counts
$pendingCount = 0;
$newMsgCount = 0;
try {
    require_once '../../php/config/database.php';
    $pendingCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $newMsgCount = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'new'")->fetchColumn();
} catch (Exception $e) {}
?>

<div class="d-flex" style="max-width: 100vw; overflow-x: hidden;">
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <!-- Logo Section -->
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-text">
                    <span class="brand">
                        <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
                    </span>
                    <span class="tagline">Admin Panel</span>
                </div>
            </div>
        </div>
        
        <!-- Admin Profile -->
        <div class="admin-profile">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($_SESSION['nama_lengkap'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="admin-info">
                <div class="admin-name"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Administrator'); ?></div>
                <div class="admin-role">Online • Admin</div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="sidebar-nav">
            <div class="nav-section-title">Menu Utama</div>
            
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'bookings.php') ? 'active' : ''; ?>" href="bookings.php">
                <i class="fas fa-calendar-check"></i>
                <span>Pesanan</span>
                <?php if ($pendingCount > 0): ?>
                <span class="badge-count"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'vehicles.php') ? 'active' : ''; ?>" href="vehicles.php">
                <i class="fas fa-car"></i>
                <span>Kendaraan</span>
            </a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>" href="users.php">
                <i class="fas fa-users"></i>
                <span>Pengguna</span>
            </a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'discounts.php') ? 'active' : ''; ?>" href="discounts.php">
                <i class="fas fa-tags"></i>
                <span>Kode Diskon</span>
            </a>
            
            <div class="nav-section-title">Komunikasi</div>
            
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'messages.php') ? 'active' : ''; ?>" href="messages.php">
                <i class="fas fa-envelope"></i>
                <span>Pesan</span>
                <?php if ($newMsgCount > 0): ?>
                <span class="badge-count"><?php echo $newMsgCount; ?></span>
                <?php endif; ?>
            </a>
            
            <div class="nav-section-title">Laporan & Pengaturan</div>
            
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                <i class="fas fa-chart-line"></i>
                <span>Laporan</span>
            </a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
            
            <div class="nav-divider"></div>
            
            <a class="nav-link logout" href="../../php/auth/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <button class="mobile-toggle" onclick="toggleSidebar()" title="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-info">
                <h1><?php 
                    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
                    $pageTitles = [
                        'dashboard' => 'Dashboard',
                        'bookings' => 'Manajemen Pesanan',
                        'vehicles' => 'Manajemen Kendaraan',
                        'users' => 'Manajemen Pengguna',
                        'discounts' => 'Kode Diskon',
                        'messages' => 'Pesan Pelanggan',
                        'reports' => 'Laporan',
                        'settings' => 'Pengaturan',
                        'edit-vehicle' => 'Edit Kendaraan'
                    ];
                    echo $pageTitles[$currentPage] ?? 'Admin Panel';
                ?></h1>
                <div class="breadcrumb">
                    <a href="dashboard.php"><i class="fas fa-home"></i></a>
                    <span>/</span>
                    <span><?php echo $pageTitles[$currentPage] ?? 'Page'; ?></span>
                </div>
            </div>
            
            <div class="header-actions">
                <div class="current-time">
                    <i class="far fa-clock"></i>
                    <span id="currentTime"></span>
                </div>
                <button class="header-btn" title="Notifikasi">
                    <i class="fas fa-bell"></i>
                    <?php if ($pendingCount > 0 || $newMsgCount > 0): ?>
                    <span class="notification-dot"></span>
                    <?php endif; ?>
                </button>
                <a href="settings.php" class="header-btn" title="Pengaturan">
                    <i class="fas fa-cog"></i>
                </a>
                <a href="../../php/auth/logout.php" class="header-btn-logout" title="Logout" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">

<script>
function updateTime() {
    const now = new Date();
    const options = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
    document.getElementById('currentTime').textContent = now.toLocaleDateString('id-ID', options);
}
updateTime();
setInterval(updateTime, 60000);

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

        // Ensure sidebar overlay does not block Bootstrap modals or dropdowns
document.addEventListener('DOMContentLoaded', function() {
    var overlay = document.getElementById('sidebarOverlay');
    if (!overlay) return;

    // Listen for any Bootstrap modal show/hide events
    // Use document-level event listeners to catch all modal events
    document.addEventListener('show.bs.modal', function() {
        overlay.__prevDisplay = overlay.style.display;
        overlay.__prevShow = overlay.classList.contains('show');
        overlay.style.display = 'none';
        overlay.classList.remove('show');
        overlay.style.pointerEvents = 'none';
    });

    document.addEventListener('hidden.bs.modal', function() {
        // restore only if sidebar still open
        setTimeout(function() {
            if (document.getElementById('sidebar') && document.getElementById('sidebar').classList.contains('show')) {
                overlay.style.display = overlay.__prevDisplay || 'block';
                if (overlay.__prevShow) overlay.classList.add('show');
                overlay.style.pointerEvents = '';
            } else {
                overlay.style.display = overlay.__prevDisplay || '';
                overlay.classList.remove('show');
                overlay.style.pointerEvents = '';
            }
        }, 50);
    });

            // Move dropdown menus to document.body while open so they are never trapped
            document.addEventListener('show.bs.dropdown', function(e) {
                try {
                    overlay.__prevDisplay = overlay.style.display;
                    overlay.__prevShow = overlay.classList.contains('show');
                    overlay.style.display = 'none';
                    overlay.classList.remove('show');
                    overlay.style.pointerEvents = 'none';
                } catch (err) {}

                var toggle = e.target && (e.target.matches('.dropdown-toggle') ? e.target : e.target.querySelector('.dropdown-toggle'));
                var menu = null;
                if (toggle) {
                    // dropdown-menu is usually the next sibling of the toggle's parent or inside the same .dropdown
                    var parent = toggle.closest('.dropdown');
                    if (parent) menu = parent.querySelector('.dropdown-menu');
                }
                if (!menu && e.target && e.target.querySelector) menu = e.target.querySelector('.dropdown-menu');

                if (menu) {
                    // store original place so we can restore later
                    menu.__origParent = menu.parentNode;
                    menu.__origNext = menu.nextSibling;
                    // append to body so it's in top-level stacking context
                    document.body.appendChild(menu);
                    // ensure it's on top and clickable
                    menu.style.position = 'fixed';
                    menu.style.zIndex = '200000';
                    menu.style.minWidth = menu.offsetWidth + 'px';

                    // position it near the toggle
                    try {
                        var rect = toggle.getBoundingClientRect();
                        menu.style.top = (rect.bottom) + 'px';
                        // align to right edge of toggle for dropdown-menu-end, otherwise left
                        if (menu.classList.contains('dropdown-menu-end')) {
                            menu.style.left = (rect.right - menu.offsetWidth) + 'px';
                        } else {
                            menu.style.left = rect.left + 'px';
                        }
                    } catch (err) {}
                }
            });

            document.addEventListener('hidden.bs.dropdown', function(e) {
                try {
                    // restore overlay only when no modal is open
                    if (!document.body.classList.contains('modal-open')) {
                        overlay.style.display = overlay.__prevDisplay || '';
                        if (overlay.__prevShow) overlay.classList.add('show');
                        overlay.style.pointerEvents = overlay.__prevPointer || '';
                    }
                } catch (err) {}

                // restore menu to original location
                var toggle = e.target && (e.target.matches('.dropdown-toggle') ? e.target : e.target.querySelector('.dropdown-toggle'));
                var parent = toggle && toggle.closest('.dropdown');
                var menu = parent && parent.querySelector('.dropdown-menu');
                // if menu was moved, it has __origParent property
                if (menu && menu.__origParent) {
                    // remove inline styles
                    menu.style.position = '';
                    menu.style.zIndex = '';
                    menu.style.top = '';
                    menu.style.left = '';
                    menu.style.minWidth = '';
                    // move back
                    if (menu.__origNext) menu.__origParent.insertBefore(menu, menu.__origNext);
                    else menu.__origParent.appendChild(menu);
                    delete menu.__origParent;
                    delete menu.__origNext;
                }
            });
});
</script>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>