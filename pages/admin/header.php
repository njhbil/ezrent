<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel - EzRent'; ?></title>
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/responsive.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            min-height: 100%;
            width: 100%;
        }
        body { 
            background: #fafafa; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Sidebar - iOS/Glassmorphism Style */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, rgba(15,15,15,0.97) 0%, rgba(20,20,20,0.98) 100%);
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            z-index: 100;
            transition: all 0.3s ease;
            border-right: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        /* Background decoration */
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(213,0,0,0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(100,100,255,0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        /* Sidebar Header with Logo */
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d50000, #ff5252, #d50000);
            background-size: 200% 100%;
            animation: gradientMove 3s ease infinite;
        }
        
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* safe area for notched devices */
        body { padding-top: env(safe-area-inset-top); }
        .sidebar { padding-top: calc(env(safe-area-inset-top) + 1rem); }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        
        .logo-text .brand {
            font-size: 1.85rem;
            color: #fff;
            letter-spacing: -0.03em;
            display: flex;
            align-items: baseline;
        }
        
        .logo-text .brand .ez { font-weight: 300; }
        .logo-text .brand .rent { font-weight: 700; }
        .logo-text .brand .dot { color: #d50000; font-size: 2rem; }
        
        .logo-text .tagline {
            font-size: 0.6rem;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-top: 0.15rem;
        }
        
        /* Admin Profile Section - iOS Card Style */
        .admin-profile {
            padding: 1.25rem;
            margin: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .admin-profile:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-2px);
        }
        
        .admin-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #d50000 0%, #ff5252 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #fff;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(213,0,0,0.3);
        }
        
        .admin-info {
            flex: 1;
        }
        
        .admin-name {
            color: #fff;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .admin-role {
            color: #d50000;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .admin-role::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Navigation - iOS Style */
        .sidebar-nav { 
            padding: 0.5rem 0;
            position: relative;
            z-index: 1;
        }
        
        .nav-section-title {
            padding: 0.75rem 1.5rem 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.65rem;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-weight: 600;
        }
        
        .nav-section-title:first-child {
            margin-top: 0;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.75rem 1rem;
            margin: 0.15rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 450;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }
        
        .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.08);
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, rgba(213,0,0,0.15) 0%, rgba(213,0,0,0.08) 100%);
            color: #fff;
            border-color: rgba(213,0,0,0.2);
            box-shadow: 0 4px 15px rgba(213,0,0,0.1);
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: #d50000;
            border-radius: 0 3px 3px 0;
        }
        
        .nav-link i { 
            width: 22px; 
            text-align: center; 
            font-size: 1.05rem;
            position: relative;
            z-index: 1;
            opacity: 0.85;
        }
        
        .nav-link.active i {
            color: #ff5252;
            opacity: 1;
        }
        
        .nav-link span {
            position: relative;
            z-index: 1;
        }
        
        .nav-link .badge-count {
            margin-left: auto;
            background: linear-gradient(135deg, #d50000 0%, #ff5252 100%);
            color: #fff;
            font-size: 0.65rem;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(213,0,0,0.3);
        }
        
        .nav-divider { 
            height: 1px; 
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            margin: 1rem 1rem;
        }
        
        .nav-link.logout { 
            color: #fff;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            margin: 1rem;
            border-radius: 12px;
            padding: 0.875rem 1.25rem !important;
            justify-content: center;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(213,0,0,0.25);
            border: none;
        }
        .nav-link.logout:hover { 
            background: linear-gradient(135deg, #ff5252 0%, #d50000 100%);
            transform: translateY(-2px) translateX(0) !important;
            box-shadow: 0 6px 20px rgba(213,0,0,0.35);
        }
        .nav-link.logout i {
            margin-right: 0.5rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 0;
            background: #fafafa;
        }
        
        /* Top Header Bar */
        .top-header {
            background: #fff;
            padding: 1.5rem 2.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .page-info h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #000;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }
        
        .page-info .breadcrumb {
            font-size: 0.8rem;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
            background: none;
        }
        
        .page-info .breadcrumb a {
            color: #d50000;
            text-decoration: none;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-btn {
            width: 42px;
            height: 42px;
            border: 1px solid #e5e7eb;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }
        
        .header-btn:hover {
            border-color: #000;
            color: #000;
        }
        
        .header-btn .notification-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #d50000;
            border-radius: 50%;
        }
        
        .header-btn-logout {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
        }
        
        .header-btn-logout:hover {
            background: linear-gradient(135deg, #b71c1c 0%, #8b0000 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(213,0,0,0.3);
        }
        
        .header-btn-logout i {
            font-size: 0.9rem;
        }
        
        .current-time {
            font-size: 0.85rem;
            color: #6b7280;
            padding: 0.5rem 1rem;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
        }
        
        .current-time i {
            margin-right: 0.5rem;
            color: #d50000;
        }
        
        /* Content Area */
        .content-area {
            padding: 2.5rem;
            width: 100%;
            min-width: 0;
        }
        
        .content-area .row {
            margin: 0;
            width: 100%;
        }
        
        /* Cards - Elegant Minimalist Design */
        .card {
            background: #fff;
            border: none;
            border-radius: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: #d50000;
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        
        .card-header {
            background: #fafafa;
            color: #1a1a1a;
            padding: 1rem 1.5rem;
            font-weight: 600;
            border-radius: 0;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        
        .card-header i {
            color: #d50000;
            font-size: 0.9rem;
        }
        
        .card-body {
            padding: 1.5rem;
            background: #fff;
        }
        
        /* Stats Cards - Clean Minimal Design */
        .stat-card {
            background: #fff;
            border: none;
            padding: 1.75rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 0;
            border-left: 3px solid #d50000;
        }
        
        .stat-card:hover {
            background: #fafafa;
        }
        
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            background: #f5f5f5;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #d50000;
        }
        
        .stat-card .stat-value {
            font-size: 2.25rem;
            font-weight: 600;
            color: #1a1a1a;
            letter-spacing: -0.02em;
            line-height: 1;
        }
        
        .stat-card .stat-label {
            font-size: 0.75rem;
            color: #888;
            margin-top: 0.35rem;
            font-weight: 400;
        }
        
        .stat-card .stat-change {
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #eee;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 500;
        }
        
        .stat-card .stat-change.up {
            color: #10b981;
        }
        
        .stat-card .stat-change.down {
            color: #d50000;
        }
        
        /* Tables */
        .table { margin: 0; }
        .table thead th {
            background: #fafafa;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.7rem;
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
            font-size: 0.9rem;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover { 
            background: linear-gradient(90deg, rgba(213,0,0,0.02), transparent);
        }
        
        .table img { border-radius: 0; }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            border: 2px solid transparent;
            color: #fff;
            border-radius: 0;
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: transparent;
            color: #d50000;
            border-color: #d50000;
        }
        
        .btn-outline-secondary {
            border: 2px solid #e5e7eb;
            color: #6b7280;
            border-radius: 0;
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.8rem;
            background: transparent;
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

        .btn-success {
            background: #10b981;
            border: 2px solid #10b981;
            border-radius: 0;
        }

        .btn-success:hover {
            background: transparent;
            color: #10b981;
        }

        .btn-warning {
            background: #f59e0b;
            border: 2px solid #f59e0b;
            border-radius: 0;
            color: #000;
        }

        .btn-warning:hover {
            background: transparent;
            color: #f59e0b;
        }

        .btn-info {
            background: #3b82f6;
            border: 2px solid #3b82f6;
            border-radius: 0;
        }

        .btn-info:hover {
            background: transparent;
            color: #3b82f6;
        }

        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.7rem;
        }

        .btn-outline-primary {
            border: 2px solid #d50000;
            color: #d50000;
            border-radius: 0;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: #d50000;
            color: #fff;
        }
        
        /* Badge */
        .badge {
            font-weight: 500;
            padding: 0.4rem 0.85rem;
            border-radius: 0;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .badge-success, .bg-success { background: #10b981 !important; }
        .badge-warning, .bg-warning { background: #f59e0b !important; color: #000 !important; }
        .badge-danger, .bg-danger { background: #d50000 !important; }
        .badge-info, .bg-info { background: #3b82f6 !important; }
        .bg-secondary { background: #6b7280 !important; }
        .bg-primary { background: #d50000 !important; }
        
        /* Modal */
        .modal-content { border-radius: 0; border: none; }
        .modal-header { 
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            color: #fff; 
            border-radius: 0;
            padding: 1.25rem 1.5rem;
        }
        .modal-header .btn-close { filter: invert(1); }
        .modal-title {
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .modal-title i {
            color: #d50000;
        }
        
        /* Forms */
        .form-control, .form-select {
            border-radius: 0;
            border: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #d50000;
            box-shadow: 0 0 0 3px rgba(213,0,0,0.1);
        }
        
        .form-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        /* Alert */
        .alert {
            border-radius: 0;
            border: none;
            padding: 1rem 1.25rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #166534;
            border-left: 4px solid #10b981;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #991b1b;
            border-left: 4px solid #d50000;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        
        /* Progress Bar */
        .progress {
            border-radius: 0;
            height: 8px;
            background: #e5e7eb;
        }

        .progress-bar {
            border-radius: 0;
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #ccc;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        /* Pagination */
        .pagination .page-link {
            border-radius: 0;
            border: 1px solid #e5e7eb;
            color: #374151;
            padding: 0.5rem 0.875rem;
        }

        .pagination .page-link:hover {
            background: #000;
            border-color: #000;
            color: #fff;
        }

        .pagination .page-item.active .page-link {
            background: #d50000;
            border-color: #d50000;
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .sidebar { 
                transform: translateX(-100%); 
                width: 280px;
            }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .top-header {
                padding: 1rem 1.5rem;
            }
            .content-area {
                padding: 1.5rem;
            }
        }
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

<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar">
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
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'test-payment.php') ? 'active' : ''; ?>" href="test-payment.php">
                <i class="fas fa-flask"></i>
                <span>Test Pembayaran</span>
                <span class="badge-count" style="background: #ffc107; color: #000;">SANDBOX</span>
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
            <div class="page-info">
                <h1><?php 
                    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
                    $pageTitles = [
                        'dashboard' => 'Dashboard',
                        'bookings' => 'Manajemen Pesanan',
                        'vehicles' => 'Manajemen Kendaraan',
                        'users' => 'Manajemen Pengguna',
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
// Update current time
function updateTime() {
    const now = new Date();
    const options = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
    document.getElementById('currentTime').textContent = now.toLocaleDateString('id-ID', options);
}
updateTime();
setInterval(updateTime, 60000);
</script>
