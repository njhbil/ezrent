<?php 
// Start session dan cek login SEBELUM output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login atau bukan user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
// Koneksi ke database
require_once '../../php/config/database.php';

// Check untuk success message
$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;
$bookingCode = isset($_GET['kode']) ? htmlspecialchars($_GET['kode']) : '';

$page_title = "Pesanan Saya - EzRent";
include 'header.php';

// Inisialisasi variabel default
$bookings = [];
$totalBookings = 0;
$pendingCount = 0;
$confirmedCount = 0;
$activeCount = 0;
$completedCount = 0;
$cancelledCount = 0;
$totalSpent = 0;
$error = null;

try {
    // Query untuk mengambil data pesanan user dengan detail lengkap
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
            v.jenis as vehicle_type,
            v.plat_nomor as vehicle_plate,
            v.harga_per_hari as daily_rate,
            v.images as vehicle_image
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung statistik
    $totalBookings = count($bookings);
    $pendingCount = count(array_filter($bookings, function($b) { return $b['status'] === 'pending'; }));
    $confirmedCount = count(array_filter($bookings, function($b) { return $b['status'] === 'confirmed'; }));
    $activeCount = count(array_filter($bookings, function($b) { return $b['status'] === 'active'; }));
    $completedCount = count(array_filter($bookings, function($b) { return $b['status'] === 'completed'; }));
    $cancelledCount = count(array_filter($bookings, function($b) { return $b['status'] === 'cancelled'; }));
    
    // Hitung total pengeluaran
    $totalSpent = array_reduce($bookings, function($sum, $b) { 
        return $b['status'] !== 'cancelled' ? $sum + $b['total_price'] : $sum; 
    }, 0);
    
} catch (PDOException $e) {
    $error = "Terjadi kesalahan saat mengambil data pesanan: " . $e->getMessage();
}

// Fungsi untuk mendapatkan badge status
function getStatusBadge($status) {
    $statusConfig = [
        'pending' => ['label' => 'Menunggu Konfirmasi', 'icon' => '⏳', 'class' => 'status-pending'],
        'confirmed' => ['label' => 'Dikonfirmasi', 'icon' => '✓', 'class' => 'status-confirmed'],
        'ready' => ['label' => 'Siap Diambil', 'icon' => '🚗', 'class' => 'status-ready'],
        'active' => ['label' => 'Sedang Dipinjam', 'icon' => '🔑', 'class' => 'status-active'],
        'completed' => ['label' => 'Selesai', 'icon' => '✅', 'class' => 'status-completed'],
        'cancelled' => ['label' => 'Dibatalkan', 'icon' => '✕', 'class' => 'status-cancelled']
    ];
    
    $config = $statusConfig[$status] ?? ['label' => $status, 'icon' => '•', 'class' => 'status-default'];
    
    return '<span class="status-badge ' . $config['class'] . '">' . $config['icon'] . ' ' . $config['label'] . '</span>';
}

// Fungsi untuk mendapatkan icon kendaraan
function getVehicleIcon($type) {
    $icons = [
        'mobil' => '',
        'motor' => '',
        'sepeda_listrik' => '',
        'sepeda' => ''
    ];
    return $icons[$type] ?? '';
}

// Fungsi untuk mendapatkan payment status
function getPaymentStatus($status) {
    $config = [
        'unpaid' => ['label' => 'Belum Bayar', 'class' => 'payment-unpaid'],
        'paid' => ['label' => 'Lunas', 'class' => 'payment-paid'],
        'partial' => ['label' => 'DP', 'class' => 'payment-partial'],
        'refunded' => ['label' => 'Refund', 'class' => 'payment-refunded']
    ];
    $c = $config[$status] ?? $config['unpaid'];
    return '<span class="payment-badge ' . $c['class'] . '">' . $c['label'] . '</span>';
}
?>

<style>
    /* Animations */
    .fade-in {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .fade-in.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    .stagger-1 { transition-delay: 0.1s; }
    .stagger-2 { transition-delay: 0.2s; }
    .stagger-3 { transition-delay: 0.3s; }
    .stagger-4 { transition-delay: 0.4s; }

    /* Hero Section */
    .bookings-hero {
        /* use a full-cover hero image (match other pages) with subtle dark overlay */
        background: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.35)), url('../assets/images/vehicles/ez-1.jpg') center/cover no-repeat;
        padding: 8rem 20px 5rem;
        width: 100%;
        position: relative;
        overflow: hidden;
        color: #fff;
    }
    
    /* Particle Canvas */
    .particle-canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
    }
    
    .bookings-hero::before {
        /* keep pattern off for a cleaner hero — subtle overlay handled by the gradient above */
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: none;
        pointer-events: none;
    }
    
    .hero-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .hero-content {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .hero-badge {
        display: inline-block;
        background: rgba(213,0,0,0.2);
        border: 1px solid rgba(213,0,0,0.4);
        color: #ff6b6b;
        padding: 0.5rem 1.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin-bottom: 1.5rem;
    }
    
    .hero-title {
        color: #fff;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 300;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }
    
    .hero-title strong {
        font-weight: 700;
    }
    
    .hero-title .accent {
        color: #d50000;
    }
    
    .hero-subtitle {
        color: rgba(255,255,255,0.7);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .stat-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        background: rgba(255,255,255,0.08);
        border-color: rgba(213,0,0,0.3);
        transform: translateY(-3px);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #d50000;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.6);
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    /* Content Section */
    .bookings-content {
        padding: 4rem 20px;
        background: #f8f9fa;
        width: 100%;
        min-height: 50vh;
        position: relative;
    }
    
    /* Texture Pattern Background */
    .bookings-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: 
            radial-gradient(circle at 25px 25px, rgba(0,0,0,0.02) 2px, transparent 0),
            radial-gradient(circle at 75px 75px, rgba(0,0,0,0.02) 2px, transparent 0);
        background-size: 100px 100px;
        pointer-events: none;
    }
    
    .bookings-content::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            linear-gradient(90deg, transparent 0%, rgba(213,0,0,0.02) 50%, transparent 100%),
            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23d50000' fill-opacity='0.015' fill-rule='evenodd'/%3E%3C/svg%3E");
        pointer-events: none;
    }
    
    .content-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* Filter Tabs */
    .filter-section {
        background: #fff;
        border: none;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        position: relative;
        z-index: 2;
    }
    
    .filter-tabs {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .filter-tab {
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        color: #6b7280;
        border: 2px solid #e5e7eb;
        padding: 0.75rem 1.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 50px;
    }
    
    .filter-tab:hover {
        border-color: #000;
        color: #000;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .filter-tab.active {
        background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
        color: #fff;
        border-color: #000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    
    .filter-count {
        background: rgba(0,0,0,0.08);
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
    }
    
    .filter-tab.active .filter-count {
        background: rgba(213,0,0,0.8);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: #fff;
        border: 1px solid #e5e7eb;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }
    
    .empty-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #000;
        margin-bottom: 0.5rem;
    }
    
    .empty-text {
        color: #6b7280;
        margin-bottom: 2rem;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #d50000;
        color: #fff;
        padding: 0.875rem 2rem;
        border: 2px solid #d50000;
        font-weight: 600;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: transparent;
        color: #d50000;
    }

    /* Booking Card - Premium Design */
    .booking-card {
        background: #fff;
        border: none;
        margin-bottom: 1.5rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .booking-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(180deg, #d50000 0%, #ff6b6b 50%, #d50000 100%);
        border-radius: 16px 0 0 16px;
    }
    
    .booking-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(213,0,0,0.03) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .booking-card:hover {
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        transform: translateY(-8px) scale(1.01);
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.75rem 2rem;
        background: linear-gradient(135deg, #fafafa 0%, #fff 100%);
        border-bottom: 1px solid rgba(0,0,0,0.05);
        gap: 1rem;
        flex-wrap: wrap;
        position: relative;
    }
    
    .booking-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 2rem;
        right: 2rem;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(213,0,0,0.2), transparent);
    }
    
    .booking-vehicle {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    
    .vehicle-icon {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        box-shadow: 
            0 8px 25px rgba(0,0,0,0.1),
            inset 0 1px 0 rgba(255,255,255,0.5);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
    }
    
    .vehicle-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 16px;
        transition: transform 0.4s ease;
    }
    
    .booking-card:hover .vehicle-icon img {
        transform: scale(1.08);
    }
    
    .vehicle-icon::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: shimmerIcon 3s infinite;
        z-index: 10;
    }
    
    @keyframes shimmerIcon {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    .vehicle-info h3 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #000;
        margin-bottom: 0.4rem;
        letter-spacing: -0.02em;
    }
    
    .vehicle-meta {
        font-size: 0.9rem;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .vehicle-meta::before {
        content: '•';
        color: #d50000;
    }
    
    .booking-id {
        font-size: 0.8rem;
        color: #6b7280;
        text-align: right;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        padding: 1rem 1.5rem;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .booking-id strong {
        display: block;
        font-size: 1.1rem;
        color: #000;
        margin-top: 0.35rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-radius: 50px;
        position: relative;
        overflow: hidden;
    }
    
    .status-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, transparent 100%);
    }
    
    .status-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #b45309;
        border: 2px solid #fcd34d;
        box-shadow: 0 4px 15px rgba(251,191,36,0.3);
    }
    
    .status-confirmed {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
        border: 2px solid #93c5fd;
        box-shadow: 0 4px 15px rgba(59,130,246,0.3);
    }
    
    .status-ready {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
        border: 2px solid #fbbf24;
        box-shadow: 0 4px 15px rgba(245,158,11,0.3);
    }
    
    .status-active {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #047857;
        border: 2px solid #6ee7b7;
        box-shadow: 0 4px 15px rgba(16,185,129,0.3);
    }
    
    .status-completed {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #1f2937;
        border: 2px solid #d1d5db;
        box-shadow: 0 4px 15px rgba(107,114,128,0.2);
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #b91c1c;
        border: 2px solid #fca5a5;
        box-shadow: 0 4px 15px rgba(239,68,68,0.3);
    }

    /* Payment Badges */
    .payment-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        font-weight: 600;
    }
    
    .payment-unpaid {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .payment-paid {
        background: #d1fae5;
        color: #059669;
    }
    
    .payment-partial {
        background: #fef3c7;
        color: #d97706;
    }

    /* Booking Body */
    .booking-body {
        padding: 2rem;
        position: relative;
        background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
    }
    
    .booking-details-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 992px) {
        .booking-details-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .booking-details-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .detail-item {
        position: relative;
        padding: 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.3s ease;
    }
    
    .detail-item:hover {
        background: #fff;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    
    .detail-label {
        font-size: 0.65rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .detail-value {
        font-size: 1rem;
        font-weight: 700;
        color: #000;
    }
    
    .detail-value.price {
        color: #d50000;
        font-size: 1.2rem;
        text-shadow: 0 2px 10px rgba(213,0,0,0.2);
    }
    
    .detail-sub {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.15rem;
    }

    /* Timeline */
    .booking-timeline {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        margin-bottom: 1.5rem;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.04);
        position: relative;
        overflow: hidden;
    }
    
    .booking-timeline::before {
        content: '→';
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        color: rgba(213,0,0,0.2);
        font-weight: bold;
    }
    
    .timeline-point {
        text-align: center;
        flex: 1;
        padding: 0.75rem;
        background: #fff;
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    
    .timeline-point:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    
    .timeline-date {
        font-size: 1rem;
        font-weight: 700;
        color: #000;
    }
    
    .timeline-label {
        font-size: 0.65rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 0.25rem;
    }
    
    .timeline-line {
        flex: 2;
        height: 2px;
        background: linear-gradient(90deg, #d50000 0%, #000 100%);
        position: relative;
    }
    
    .timeline-line::before {
        content: '→';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #f8f9fa;
        padding: 0 0.5rem;
        color: #d50000;
        font-size: 1rem;
    }

    /* Booking Footer */
    .booking-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 2rem;
        background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
        border-top: 1px solid rgba(0,0,0,0.05);
        flex-wrap: wrap;
        gap: 1rem;
        border-radius: 0 0 16px 16px;
    }
    
    .booking-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .meta-item {
        font-size: 0.8rem;
        color: #6b7280;
    }
    
    .booking-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        font-size: 0.8rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 8px;
        text-decoration: none;
    }
    
    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .btn-detail {
        background: linear-gradient(135deg, #000 0%, #1a1a1a 100%);
        color: #fff;
    }
    
    .btn-detail:hover {
        background: linear-gradient(135deg, #333 0%, #1a1a1a 100%);
    }
    
    .btn-cancel {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
        border: 1px solid rgba(220,38,38,0.2);
    }
    
    .btn-cancel:hover {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
    }
    

    
    .btn-review {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
        border: 1px solid rgba(5,150,105,0.2);
    }
    
    .btn-review:hover {
        background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
    }
    
    .btn-pay {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-pay:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #fff;
        transform: translateY(-2px);
    }
    
    .btn-pay:hover {
        background: linear-gradient(135deg, #b50000 0%, #a00000 100%);
    }
    
    .btn-invoice {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #374151;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    .btn-invoice:hover {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
    }

    /* No Results */
    .no-results {
        display: none;
        text-align: center;
        padding: 4rem 2rem;
        background: #fff;
        border: 1px solid #e5e7eb;
    }

    /* Help Section - Enhanced */
    .help-section {
        padding: 5rem 20px;
        background: linear-gradient(135deg, #000 0%, #1a1a1a 50%, #000 100%);
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .help-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    
    .help-section::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(213,0,0,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .help-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .help-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .help-badge {
        display: inline-block;
        background: rgba(213,0,0,0.2);
        border: 1px solid rgba(213,0,0,0.4);
        color: #ff6b6b;
        padding: 0.5rem 1.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin-bottom: 1.5rem;
    }
    
    .help-title {
        font-size: 2.5rem;
        font-weight: 300;
        color: #fff;
        margin-bottom: 1rem;
        line-height: 1.2;
    }
    
    .help-title strong {
        font-weight: 700;
    }
    
    .help-title .accent {
        color: #d50000;
    }
    
    .help-subtitle {
        color: rgba(255,255,255,0.6);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }
    
    .help-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    @media (max-width: 992px) {
        .help-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .help-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .help-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.08);
        padding: 2rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .help-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #d50000, transparent);
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .help-card:hover {
        background: rgba(255,255,255,0.06);
        border-color: rgba(213,0,0,0.3);
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    
    .help-card:hover::before {
        opacity: 1;
    }
    
    .help-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, rgba(213,0,0,0.2) 0%, rgba(213,0,0,0.1) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1.25rem;
        transition: all 0.3s;
    }
    
    .help-card:hover .help-icon {
        background: rgba(213,0,0,0.3);
        transform: scale(1.1);
    }
    
    .help-card h3 {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    .help-card p {
        color: rgba(255,255,255,0.6);
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .help-card .help-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #d50000;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s;
    }
    
    .help-card .help-link:hover {
        color: #ff5252;
        gap: 0.75rem;
    }
    
    /* CTA Banner */
    .help-cta {
        background: linear-gradient(90deg, rgba(213,0,0,0.15) 0%, rgba(213,0,0,0.05) 100%);
        border: 1px solid rgba(213,0,0,0.3);
        padding: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2rem;
        flex-wrap: wrap;
    }
    
    .cta-content h3 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .cta-content p {
        color: rgba(255,255,255,0.7);
        font-size: 0.95rem;
        margin: 0;
    }
    
    .cta-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .cta-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.75rem;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .cta-btn-primary {
        background: #d50000;
        color: #fff;
        border: 2px solid #d50000;
    }
    
    .cta-btn-primary:hover {
        background: transparent;
        color: #d50000;
    }
    
    .cta-btn-outline {
        background: transparent;
        color: #fff;
        border: 2px solid rgba(255,255,255,0.3);
    }
    
    .cta-btn-outline:hover {
        background: rgba(255,255,255,0.1);
        border-color: #fff;
    }
</style>

<?php if ($showSuccess): ?>
<!-- Success Modal -->
<div id="successModal" class="modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div style="background: #111; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 3rem; text-align: center; max-width: 450px; margin: 20px; animation: modalPop 0.3s ease;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2.5rem; color: #fff;"><svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
        <h2 style="color: #fff; font-size: 1.5rem; font-weight: 600; margin-bottom: 0.75rem;">Pemesanan Berhasil!</h2>
        <p style="color: rgba(255,255,255,0.7); margin-bottom: 1.5rem;">Kode booking Anda:</p>
        <div style="background: rgba(213,0,0,0.1); border: 1px dashed #d50000; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <span style="color: #d50000; font-size: 1.25rem; font-weight: 700; letter-spacing: 0.05em;"><?php echo $bookingCode; ?></span>
        </div>
        <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem; margin-bottom: 1.5rem;">Silakan selesaikan pembayaran dan tunggu konfirmasi dari admin.</p>
        <button onclick="closeSuccessModal()" style="background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%); color: #fff; border: none; padding: 0.875rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%;">Lihat Pesanan Saya</button>
    </div>
</div>
<style>
    @keyframes modalPop {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>
<script>
function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
    // Remove query params
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>
<?php endif; ?>

<!-- Hero Section -->
<section class="bookings-hero">
    <canvas id="particleCanvas" class="particle-canvas"></canvas>
    <div class="hero-container">
        <div class="hero-content fade-in">
            <h1 class="hero-title">Pesanan <span class="accent"><strong>Saya</strong></span></h1>
            <p class="hero-subtitle">Kelola dan lacak semua pesanan kendaraan Anda di satu tempat</p>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card fade-in stagger-1">
                <div class="stat-number"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card fade-in stagger-2">
                <div class="stat-number"><?php echo $activeCount + $pendingCount + $confirmedCount; ?></div>
                <div class="stat-label">Aktif / Proses</div>
            </div>
            <div class="stat-card fade-in stagger-3">
                <div class="stat-number"><?php echo $completedCount; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-card fade-in stagger-4">
                <div class="stat-number">Rp <?php echo number_format($totalSpent / 1000000, 1); ?>jt</div>
                <div class="stat-label">Total Transaksi</div>
            </div>
        </div>
    </div>
</section>

<!-- Bookings Content -->
<section class="bookings-content">
    <div class="content-container">
        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 1.5rem; margin-bottom: 2rem; text-align: center; border: 1px solid #fca5a5;">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-section fade-in">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">
                    Semua <span class="filter-count"><?php echo $totalBookings; ?></span>
                </button>
                <button class="filter-tab" data-filter="pending">
                    Menunggu <span class="filter-count"><?php echo $pendingCount; ?></span>
                </button>
                <button class="filter-tab" data-filter="confirmed">
                    Dikonfirmasi <span class="filter-count"><?php echo $confirmedCount; ?></span>
                </button>
                <button class="filter-tab" data-filter="active">
                    Berjalan <span class="filter-count"><?php echo $activeCount; ?></span>
                </button>
                <button class="filter-tab" data-filter="completed">
                    Selesai <span class="filter-count"><?php echo $completedCount; ?></span>
                </button>
                <button class="filter-tab" data-filter="cancelled">
                    Dibatalkan <span class="filter-count"><?php echo $cancelledCount; ?></span>
                </button>
            </div>
        </div>

        <?php if (empty($bookings)): ?>
            <!-- Empty State -->
            <div class="empty-state fade-in">
                <div class="empty-icon"></div>
                <h3 class="empty-title">Belum Ada Pesanan</h3>
                <p class="empty-text">Anda belum memiliki pesanan kendaraan. Mulai sewa kendaraan pertama Anda sekarang!</p>
                <a href="vehicles.php" class="btn-primary">
                    Sewa Kendaraan Sekarang
                </a>
            </div>
        <?php else: ?>
            <!-- Bookings List -->
            <div id="bookingsList">
                <?php foreach ($bookings as $index => $booking): 
                    // Calculate duration
                    $start = new DateTime($booking['start_date']);
                    $end = new DateTime($booking['end_date']);
                    $duration = $start->diff($end)->days + 1;
                    
                    // Format dates
                    $startFormatted = $start->format('d M Y');
                    $endFormatted = $end->format('d M Y');
                    $startDay = $start->format('l');
                    $endDay = $end->format('l');
                    
                    // Indonesian day names
                    $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                    $startDay = $dayNames[$startDay] ?? $startDay;
                    $endDay = $dayNames[$endDay] ?? $endDay;
                    
                    // Payment status (simulated - you can add this column to DB)
                    $paymentStatus = $booking['status'] === 'cancelled' ? 'refunded' : 
                                   ($booking['status'] === 'pending' ? 'unpaid' : 'paid');
                    
                    // Get vehicle image
                    $vehicleImages = json_decode($booking['vehicle_image'], true);
                    $vehicleImg = isset($vehicleImages[0]) ? $vehicleImages[0] : 'default.jpg';
                ?>
                    <div class="booking-card fade-in" data-status="<?php echo $booking['status']; ?>" style="transition-delay: <?php echo $index * 0.1; ?>s;">
                        
                        <!-- Header -->
                        <div class="booking-header">
                            <div class="booking-vehicle">
                                <div class="vehicle-icon">
                                    <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($vehicleImg); ?>" alt="<?php echo htmlspecialchars($booking['vehicle_name']); ?>">
                                </div>
                                <div class="vehicle-info">
                                    <h3><?php echo htmlspecialchars($booking['vehicle_name']); ?></h3>
                                    <div class="vehicle-meta">
                                        <?php echo htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']); ?> • 
                                        <strong><?php echo htmlspecialchars($booking['vehicle_plate']); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                                <?php echo getStatusBadge($booking['status']); ?>
                                <div class="booking-id">
                                    No. Pesanan
                                    <strong>#EZR-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="booking-body">
                            <!-- Timeline -->
                            <div class="booking-timeline">
                                <div class="timeline-point">
                                    <div class="timeline-date"><?php echo $start->format('d M'); ?></div>
                                    <div class="timeline-label">Mulai Sewa</div>
                                </div>
                                <div class="timeline-line"></div>
                                <div class="timeline-point">
                                    <div class="timeline-date"><?php echo $end->format('d M'); ?></div>
                                    <div class="timeline-label">Selesai</div>
                                </div>
                            </div>
                            
                            <!-- Details Grid -->
                            <div class="booking-details-grid">
                                <div class="detail-item">
                                    <div class="detail-label">Tanggal Mulai</div>
                                    <div class="detail-value"><?php echo $startFormatted; ?></div>
                                    <div class="detail-sub"><?php echo $startDay; ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Tanggal Selesai</div>
                                    <div class="detail-value"><?php echo $endFormatted; ?></div>
                                    <div class="detail-sub"><?php echo $endDay; ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Durasi Sewa</div>
                                    <div class="detail-value"><?php echo $duration; ?> Hari</div>
                                    <div class="detail-sub"><?php echo $duration * 24; ?> jam</div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Tarif per Hari</div>
                                    <div class="detail-value">Rp <?php echo number_format($booking['daily_rate'] ?? ($booking['total_price'] / $duration), 0, ',', '.'); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Total Biaya</div>
                                    <div class="detail-value price">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></div>
                                    <div class="detail-sub"><?php echo getPaymentStatus($paymentStatus); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="booking-footer">
                            <div class="booking-meta">
                                <div class="meta-item">
                                    Dipesan: <?php echo date('d M Y, H:i', strtotime($booking['created_at'])); ?>
                                </div>
                                <?php if (!empty($booking['pickup_location'])): ?>
                                <div class="meta-item">
                                    <?php echo htmlspecialchars($booking['pickup_location']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="booking-actions">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a href="payment-gateway.php?booking_id=<?php echo $booking['id']; ?>" class="btn-action btn-pay">
                                        Lanjutkan Pembayaran
                                    </a>
                                    <button class="btn-action btn-cancel" data-booking-id="<?php echo $booking['id']; ?>">
                                        Batalkan
                                    </button>
                                <?php elseif ($booking['status'] === 'confirmed'): ?>
                                    <button class="btn-action btn-invoice" onclick="window.location.href='invoice.php?booking_id=<?php echo $booking['id']; ?>'">
                                        Invoice
                                    </button>
                                <?php elseif ($booking['status'] === 'ready'): ?>
                                    <span class="btn-action btn-ready-info" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; cursor: default;">
                                        🚗 Siap Diambil
                                    </span>
                                    <button class="btn-action btn-invoice" onclick="window.location.href='invoice.php?booking_id=<?php echo $booking['id']; ?>'">
                                        Invoice
                                    </button>
                                <?php elseif ($booking['status'] === 'active'): ?>
                                    <button class="btn-action btn-invoice" onclick="window.location.href='invoice.php?booking_id=<?php echo $booking['id']; ?>'">
                                        Invoice
                                    </button>
                                <?php elseif ($booking['status'] === 'completed'): ?>
                                    <button class="btn-action btn-review" data-booking-id="<?php echo $booking['id']; ?>">
                                        Beri Ulasan
                                    </button>
                                    <button class="btn-action btn-invoice" onclick="window.location.href='invoice.php?booking_id=<?php echo $booking['id']; ?>'">
                                        Invoice
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn-action btn-detail" onclick="window.location.href='booking-detail.php?id=<?php echo $booking['id']; ?>'">
                                    Lihat Detail
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- No Results -->
            <div class="no-results" id="noResults">
                <div class="empty-icon"></div>
                <h3 class="empty-title">Tidak Ada Hasil</h3>
                <p class="empty-text">Tidak ada pesanan yang sesuai dengan filter yang dipilih.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Help Section -->
<section class="help-section">
    <div class="help-container">
        <div class="help-header">
            <h2 class="help-title fade-in">Butuh <strong>Bantuan</strong>?</h2>
            <p class="help-subtitle fade-in">Tim customer service kami siap membantu kapan saja. Hubungi kami melalui berbagai channel yang tersedia.</p>
        </div>
        
        <div class="help-grid">
            <div class="help-card fade-in stagger-1">
                <div class="help-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
                <h3>Telepon</h3>
                <p>Hubungi hotline kami untuk bantuan langsung</p>
                <a href="tel:02112345678" class="help-link">(021) 1234-5678 →</a>
            </div>
            <div class="help-card fade-in stagger-2">
                <div class="help-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></div>
                <h3>WhatsApp</h3>
                <p>Chat cepat via WhatsApp untuk respon instan</p>
                <a href="https://wa.me/6281234567890" class="help-link" target="_blank">0812-3456-7890 →</a>
            </div>
            <div class="help-card fade-in stagger-3">
                <div class="help-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></div>
                <h3>Email</h3>
                <p>Kirim email untuk pertanyaan detail</p>
                <a href="mailto:support@ezrent.com" class="help-link">support@ezrent.com →</a>
            </div>
            <div class="help-card fade-in stagger-4">
                <div class="help-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg></div>
                <h3>FAQ & Bantuan</h3>
                <p>Temukan jawaban pertanyaan umum</p>
                <a href="help.php" class="help-link">Pusat Bantuan →</a>
            </div>
        </div>
        
        <div class="help-cta fade-in">
            <div class="cta-content">
                <h3>Belum Menemukan Kendaraan yang Cocok?</h3>
                <p>Jelajahi koleksi lengkap kendaraan kami dengan berbagai pilihan dan harga terbaik</p>
            </div>
            <div class="cta-buttons">
                <a href="vehicles.php" class="cta-btn cta-btn-primary">Lihat Kendaraan</a>
                <a href="contact.php" class="cta-btn cta-btn-outline">Hubungi Kami</a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll Animation
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });

    // Filter functionality
    const filterTabs = document.querySelectorAll('.filter-tab');
    const bookingCards = document.querySelectorAll('.booking-card');
    const bookingsList = document.getElementById('bookingsList');
    const noResults = document.getElementById('noResults');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active state
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            let visibleCount = 0;
            
            bookingCards.forEach(card => {
                const status = card.dataset.status;
                const show = filter === 'all' || status === filter;
                
                card.style.display = show ? 'block' : 'none';
                if (show) visibleCount++;
            });
            
            // Show/hide no results
            if (noResults) {
                if (visibleCount === 0 && bookingsList) {
                    bookingsList.style.display = 'none';
                    noResults.style.display = 'block';
                } else if (bookingsList) {
                    bookingsList.style.display = 'block';
                    noResults.style.display = 'none';
                }
            }
        });
    });

    // Cancel booking
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?\n\nPembatalan kurang dari 24 jam sebelum waktu sewa akan dikenakan biaya 50%.')) {
                window.location.href = 'cancel-booking.php?id=' + bookingId;
            }
        });
    });



    // Review booking
    document.querySelectorAll('.btn-review').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            alert('Fitur ulasan pesanan akan segera tersedia!\n\nTerima kasih atas pengalaman Anda bersama EzRent.');
        });
    });

    // Counter animation for stats
    const animateCounters = () => {
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const text = stat.textContent;
            const hasRp = text.includes('Rp');
            const hasJt = text.includes('jt');
            const numericValue = parseFloat(text.replace(/[^0-9.]/g, ''));
            
            if (isNaN(numericValue)) return;
            
            let current = 0;
            const duration = 1500;
            const increment = numericValue / (duration / 16);
            
            const updateCounter = () => {
                current += increment;
                if (current < numericValue) {
                    if (hasRp && hasJt) {
                        stat.textContent = 'Rp ' + current.toFixed(1) + 'jt';
                    } else {
                        stat.textContent = Math.floor(current);
                    }
                    requestAnimationFrame(updateCounter);
                } else {
                    stat.textContent = text;
                }
            };
            
            const statObserver = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    updateCounter();
                    statObserver.disconnect();
                }
            }, { threshold: 0.5 });
            
            statObserver.observe(stat);
        });
    };
    
    animateCounters();
    
    // Particle Animation
    const canvas = document.getElementById('particleCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        const particleCount = 80;
        
        function resizeCanvas() {
            const hero = document.querySelector('.bookings-hero');
            canvas.width = hero.offsetWidth;
            canvas.height = hero.offsetHeight;
        }
        
        function createParticles() {
            particles = [];
            for (let i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    radius: Math.random() * 2 + 1,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5,
                    alpha: Math.random() * 0.5 + 0.2
                });
            }
        }
        
        function drawParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach((p, i) => {
                // Update position
                p.x += p.vx;
                p.y += p.vy;
                
                // Wrap around edges
                if (p.x < 0) p.x = canvas.width;
                if (p.x > canvas.width) p.x = 0;
                if (p.y < 0) p.y = canvas.height;
                if (p.y > canvas.height) p.y = 0;
                
                // Draw particle
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(213, 0, 0, ${p.alpha})`;
                ctx.fill();
                
                // Draw connections
                particles.forEach((p2, j) => {
                    if (i !== j) {
                        const dx = p.x - p2.x;
                        const dy = p.y - p2.y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        
                        if (dist < 120) {
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.strokeStyle = `rgba(213, 0, 0, ${0.1 * (1 - dist / 120)})`;
                            ctx.lineWidth = 0.5;
                            ctx.stroke();
                        }
                    }
                });
            });
            
            requestAnimationFrame(drawParticles);
        }
        
        resizeCanvas();
        createParticles();
        drawParticles();
        
        window.addEventListener('resize', () => {
            resizeCanvas();
            createParticles();
        });
    }
});
</script>

<?php include '../../php/includes/footer.php'; ?>
