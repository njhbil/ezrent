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
    // Include any payment row so we can reflect actual payment status
    $stmt = $pdo->prepare("\n        SELECT \n            b.*,\n            p.status as payment_row_status,\n            v.nama as vehicle_name,\n            v.merek as vehicle_brand,\n            v.model as vehicle_model,\n            v.jenis as vehicle_type,\n            v.plat_nomor as vehicle_plate,\n            v.harga_per_hari as daily_rate,\n            v.images as vehicle_image\n        FROM bookings b\n        JOIN vehicles v ON b.vehicle_id = v.id\n        LEFT JOIN payments p ON p.booking_id = b.id\n        WHERE b.user_id = ?\n        ORDER BY b.created_at DESC\n    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Normalize payment_status for each booking using booking.payment_status first,
    // then fall back to any payments row status (p.status).
    foreach ($bookings as &$bk) {
        $ps = isset($bk['payment_status']) ? $bk['payment_status'] : '';
        $prow = isset($bk['payment_row_status']) ? $bk['payment_row_status'] : '';

        if (!empty($ps)) {
            $ps = strtolower($ps);
            if (in_array($ps, ['paid','completed'], true)) $bk['payment_status'] = 'paid';
            elseif ($ps === 'pending') $bk['payment_status'] = 'pending';
            elseif (in_array($ps, ['failed','deny','cancel','expired'], true)) $bk['payment_status'] = 'failed';
            elseif ($ps === 'refunded') $bk['payment_status'] = 'refunded';
            else $bk['payment_status'] = $ps;
        } elseif (!empty($prow)) {
            $prow = strtolower($prow);
            if (in_array($prow, ['completed','capture','settlement'], true)) $bk['payment_status'] = 'paid';
            elseif ($prow === 'pending') $bk['payment_status'] = 'pending';
            elseif (in_array($prow, ['deny','cancel','expire','failed'], true)) $bk['payment_status'] = 'failed';
            elseif ($prow === 'refund') $bk['payment_status'] = 'refunded';
            else $bk['payment_status'] = 'unpaid';
        } else {
            $bk['payment_status'] = 'unpaid';
        }
    }
    
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
        'pending' => ['label' => 'Menunggu Konfirmasi', 'class' => 'status-pending'],
        'confirmed' => ['label' => 'Dikonfirmasi', 'class' => 'status-confirmed'],
        'ready' => ['label' => 'Siap Diambil', 'class' => 'status-ready'],
        'active' => ['label' => 'Sedang Dipinjam', 'class' => 'status-active'],
        'completed' => ['label' => 'Selesai', 'class' => 'status-completed'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'status-cancelled']
    ];
    
    $config = $statusConfig[$status] ?? ['label' => $status, 'class' => 'status-default'];
    
    return '<span class="status-badge ' . $config['class'] . '">' . $config['label'] . '</span>';
}

// Safely format dates to avoid warnings on missing/invalid values
function safe_date_format($date, $format = 'd M Y') {
    if (empty($date)) return '-';
    $ts = strtotime($date);
    if ($ts === false) return '-';
    return date($format, $ts);
}

// Safely compute days between two dates
function safe_duration_days($start, $end) {
    if (empty($start) || empty($end)) return '-';
    try {
        $s = new DateTime($start);
        $e = new DateTime($end);
        $diff = $s->diff($e);
        return $diff->days;
    } catch (Exception $ex) {
        return '-';
    }
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
        // Show a simple "Sudah Bayar" label when payment is completed/paid
        'paid' => ['label' => 'Sudah Bayar', 'class' => 'payment-paid'],
        'completed' => ['label' => 'Sudah Bayar', 'class' => 'payment-paid'],
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

    /* Hero Section - Enhanced Design */
    .bookings-hero {
        background: 
            linear-gradient(135deg, rgba(16, 20, 31, 0.92) 0%, rgba(16, 20, 31, 0.85) 50%, rgba(213, 0, 0, 0.15) 100%),
            url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
        padding: 10rem 20px 4rem;
        width: 100%;
        position: relative;
        overflow: hidden;
        color: #fff;
        min-height: 50vh;
        display: flex;
        align-items: center;
    }
    
    /* Animated gradient overlay */
    .bookings-hero::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: 
            radial-gradient(circle at 30% 50%, rgba(213, 0, 0, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 70% 50%, rgba(255, 107, 107, 0.1) 0%, transparent 50%);
        animation: gradientShift 20s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes gradientShift {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(-10%, 10%) rotate(5deg); }
    }
    
    /* Decorative elements */
    .hero-decoration {
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.15;
        pointer-events: none;
    }
    
    .hero-decoration-1 {
        top: -100px;
        left: -100px;
        background: linear-gradient(135deg, #d50000 0%, #ff6b6b 100%);
    }
    
    .hero-decoration-2 {
        bottom: -150px;
        right: -150px;
        background: linear-gradient(135deg, #ff6b6b 0%, #d50000 100%);
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
    
    .hero-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .hero-content {
        text-align: center;
        margin-bottom: 4rem;
    }
    
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.25), rgba(255, 107, 107, 0.2));
        border: 1px solid rgba(213, 0, 0, 0.5);
        backdrop-filter: blur(10px);
        color: #fff;
        padding: 0.6rem 1.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(213, 0, 0, 0.2);
        animation: badgePulse 3s ease-in-out infinite;
    }
    
    @keyframes badgePulse {
        0%, 100% { transform: translateY(0); box-shadow: 0 8px 32px rgba(213, 0, 0, 0.2); }
        50% { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(213, 0, 0, 0.3); }
    }
    
    .hero-badge::before {
        content: '';
        font-size: 1rem;
    }
    
    .hero-title {
        color: #fff;
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 300;
        margin-bottom: 1.5rem;
        letter-spacing: -0.03em;
        line-height: 1.2;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    .hero-title strong {
        font-weight: 700;
        background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .hero-title .accent {
        background: linear-gradient(135deg, #ff6b6b 0%, #d50000 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        display: inline-block;
    }
    
    .hero-title .accent::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #d50000, transparent);
        border-radius: 2px;
    }
    
    .hero-subtitle {
        color: rgba(255, 255, 255, 0.85);
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.7;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    /* Stats Section - Separated & Elegant */
    .stats-section {
        padding: 0 20px;
        margin-top: -60px;
        position: relative;
        z-index: 10;
        margin-bottom: 4rem;
    }
    
    .stats-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
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
        background: #fff;
        border-radius: 20px;
        padding: 2.5rem 2rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #d50000, #ff6b6b);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        border-color: rgba(213, 0, 0, 0.2);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        position: relative;
        transition: all 0.4s ease;
    }
    
    .stat-icon svg {
        width: 30px;
        height: 30px;
        stroke: #d50000;
        stroke-width: 2;
        transition: all 0.4s ease;
    }
    
    .stat-card:hover .stat-icon {
        background: linear-gradient(135deg, #d50000, #ff4444);
        transform: scale(1.1) rotate(5deg);
    }
    
    .stat-card:hover .stat-icon svg {
        stroke: #fff;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
        line-height: 1;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 600;
    }

    /* Main Content */
    .bookings-content {
        padding: 4rem 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Success Alert - Enhanced */
    .success-alert {
        background: linear-gradient(135deg, rgba(76, 175, 80, 0.15), rgba(129, 199, 132, 0.1));
        border: 2px solid rgba(76, 175, 80, 0.3);
        border-radius: 16px;
        padding: 1.8rem;
        margin-bottom: 2.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        box-shadow: 0 8px 32px rgba(76, 175, 80, 0.15);
        backdrop-filter: blur(10px);
        animation: slideInDown 0.6s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .success-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #4CAF50, #66BB6A);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
        box-shadow: 0 8px 24px rgba(76, 175, 80, 0.3);
        animation: iconPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    
    @keyframes iconPop {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    .success-content h3 {
        color: #2E7D32;
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    
    .success-content p {
        color: #4CAF50;
        margin-bottom: 0.8rem;
        line-height: 1.6;
    }
    
    .booking-code {
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        background: rgba(255, 255, 255, 0.9);
        padding: 0.7rem 1.3rem;
        border-radius: 10px;
        font-weight: 700;
        color: #1B5E20;
        font-size: 1.1rem;
        border: 2px solid rgba(76, 175, 80, 0.3);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);
    }

    /* Filter Tabs - Enhanced */
    .filter-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .filter-tabs {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .filter-tab {
        padding: 0.9rem 2rem;
        border: 2px solid #e0e0e0;
        background: #fff;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
        color: #666;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.95rem;
        position: relative;
        overflow: hidden;
    }
    
    .filter-tab::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(213, 0, 0, 0.1), transparent);
        transition: left 0.5s ease;
    }
    
    .filter-tab:hover::before {
        left: 100%;
    }
    
    .filter-tab:hover {
        border-color: #d50000;
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(213, 0, 0, 0.15);
    }
    
    .filter-tab.active {
        background: linear-gradient(135deg, #d50000, #ff4444);
        color: #fff;
        border-color: #d50000;
        box-shadow: 0 8px 24px rgba(213, 0, 0, 0.3);
        transform: translateY(-3px);
    }
    
    .filter-count {
        background: rgba(255, 255, 255, 0.25);
        padding: 0.25rem 0.7rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
    }
    
    .filter-tab.active .filter-count {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Booking Cards - Enhanced */
    .bookings-list {
        display: grid;
        gap: 2rem;
    }
    
    .booking-card {
        background: #fff;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.06);
        position: relative;
    }
    
    .booking-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: linear-gradient(180deg, #d50000, #ff4444);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .booking-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }
    
    .booking-card:hover::before {
        opacity: 1;
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 2rem 2rem 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .booking-id {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .booking-number {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .booking-number::before {
        content: '';
        font-size: 1.5rem;
    }
    
    .booking-date {
        color: #666;
        font-size: 0.9rem;
        margin-top: 0.3rem;
    }
    
    .booking-body {
        padding: 2rem;
    }
    
    .vehicle-info {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .vehicle-image {
        width: 140px;
        height: 105px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        position: relative;
    }
    
    .vehicle-image::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.1), transparent);
        pointer-events: none;
    }
    
    .vehicle-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    
    .booking-card:hover .vehicle-image img {
        transform: scale(1.1);
    }
    
    .vehicle-details h3 {
        font-size: 1.4rem;
        color: #1a1a1a;
        margin-bottom: 0.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    
    .vehicle-type {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: linear-gradient(135deg, #f0f0f0, #e8e8e8);
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #555;
    }
    
    .vehicle-meta {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: #666;
        font-size: 0.95rem;
    }
    
    .meta-icon {
        color: #d50000;
        font-size: 1.2rem;
    }

    /* Rental Details - Enhanced */
    .rental-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .detail-label {
        color: #888;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 600;
    }
    
    .detail-value {
        color: #1a1a1a;
        font-size: 1.1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .price-highlight {
        color: #d50000;
        font-size: 1.5rem;
        font-weight: 800;
    }

    /* Status Badges - Enhanced */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.3rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .status-pending {
        background: linear-gradient(135deg, #fff3e0, #ffe0b2);
        color: #e65100;
        border: 2px solid #ffb74d;
    }
    
    .status-confirmed {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        color: #0d47a1;
        border: 2px solid #64b5f6;
    }
    
    .status-ready {
        background: linear-gradient(135deg, #f3e5f5, #e1bee7);
        color: #4a148c;
        border: 2px solid #ba68c8;
    }
    
    .status-active {
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        color: #1b5e20;
        border: 2px solid #81c784;
    }
    
    .status-completed {
        background: linear-gradient(135deg, #e0f2f1, #b2dfdb);
        color: #004d40;
        border: 2px solid #4db6ac;
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #ffebee, #ffcdd2);
        color: #b71c1c;
        border: 2px solid #ef5350;
    }
    
    .payment-badge {
        display: inline-flex;
        padding: 0.5rem 1.1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .payment-unpaid {
        background: linear-gradient(135deg, #fff3e0, #ffe0b2);
        color: #e65100;
    }
    
    .payment-paid {
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        color: #1b5e20;
    }
    
    .payment-partial {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        color: #0d47a1;
    }
    
    .payment-refunded {
        background: linear-gradient(135deg, #f3e5f5, #e1bee7);
        color: #4a148c;
    }

    /* Action Buttons - Enhanced */
    .booking-actions {
        display: flex;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 2px dashed rgba(0, 0, 0, 0.08);
        flex-wrap: wrap;
    }
    
    .btn {
        padding: 0.9rem 2rem;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.95rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #d50000, #ff4444);
        color: #fff;
        box-shadow: 0 6px 20px rgba(213, 0, 0, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(213, 0, 0, 0.4);
    }
    
    .btn-outline {
        background: #fff;
        color: #1a1a1a;
        border: 2px solid #e0e0e0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .btn-outline:hover {
        border-color: #d50000;
        color: #d50000;
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(213, 0, 0, 0.15);
    }
    
    .btn-cancel {
        background: linear-gradient(135deg, #ffebee, #ffcdd2);
        color: #c62828;
        border: 2px solid #ef5350;
    }
    
    .btn-cancel:hover {
        background: linear-gradient(135deg, #ffcdd2, #ef9a9a);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(198, 40, 40, 0.2);
    }

    /* Empty State - Enhanced */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 24px;
        border: 2px dashed #e0e0e0;
        margin: 2rem 0;
    }
    
    .empty-icon {
        font-size: 6rem;
        margin-bottom: 2rem;
        opacity: 0.5;
        animation: emptyFloat 3s ease-in-out infinite;
    }
    
    @keyframes emptyFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .empty-state h3 {
        font-size: 2rem;
        color: #1a1a1a;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .empty-state p {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 2.5rem;
        line-height: 1.6;
    }

    /* Help Section - Enhanced */
    .help-section {
        background: linear-gradient(135deg, #10141f 0%, #1a1f2e 100%);
        padding: 5rem 20px;
        margin-top: 5rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    
    .help-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: 
            radial-gradient(circle at 20% 50%, rgba(213, 0, 0, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 50%, rgba(255, 107, 107, 0.1) 0%, transparent 50%);
        animation: helpBgShift 20s ease-in-out infinite;
        pointer-events: none;
    }
    
    @keyframes helpBgShift {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(10%, -10%); }
    }
    
    .help-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .help-header {
        text-align: center;
        margin-bottom: 4rem;
    }
    
    .help-header h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .help-header p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.2rem;
    }
    
    .help-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
    }
    
    .help-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 2.5rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
    }
    
    .help-card:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(213, 0, 0, 0.5);
        box-shadow: 0 20px 60px rgba(213, 0, 0, 0.2);
    }
    
    .help-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #d50000, #ff4444);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #fff;
        box-shadow: 0 10px 30px rgba(213, 0, 0, 0.3);
        transition: transform 0.4s ease;
    }
    
    .help-card:hover .help-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .help-card h3 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .help-card p {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }
    
    .help-link {
        color: #ff6b6b;
        text-decoration: none;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .help-link:hover {
        color: #fff;
        gap: 1rem;
    }
    
    .help-cta {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 24px;
        padding: 3rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 2rem;
        flex-wrap: wrap;
    }
    
    .cta-content h3 {
        font-size: 1.8rem;
        margin-bottom: 0.7rem;
        font-weight: 700;
    }
    
    .cta-content p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.1rem;
    }
    
    .cta-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .cta-btn {
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
    }
    
    .cta-btn-primary {
        background: linear-gradient(135deg, #d50000, #ff4444);
        color: #fff;
        box-shadow: 0 8px 24px rgba(213, 0, 0, 0.3);
    }
    
    .cta-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 36px rgba(213, 0, 0, 0.4);
    }
    
    .cta-btn-outline {
        background: transparent;
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .cta-btn-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #fff;
        transform: translateY(-3px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .bookings-hero {
            padding: 8rem 20px 3rem;
            min-height: 45vh;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1rem;
        }
        
        .stats-section {
            margin-top: -40px;
            padding: 0 15px;
        }
        
        .stats-grid {
            gap: 1rem;
        }
        
        .stat-card {
            padding: 2rem 1.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .vehicle-info {
            grid-template-columns: 1fr;
        }
        
        .vehicle-image {
            width: 100%;
            height: 200px;
        }
        
        .rental-details {
            grid-template-columns: 1fr;
        }
        
        .filter-tabs {
            flex-direction: column;
        }
        
        .filter-tab {
            justify-content: center;
        }
        
        .help-cta {
            flex-direction: column;
            text-align: center;
        }
        
        .cta-buttons {
            justify-content: center;
        }
        /* Booking specific responsive tweaks */
        .booking-header {
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
        }
        .booking-number { font-size: 1.05rem; }
        .booking-date { font-size: 0.85rem; }
        .booking-actions { flex-direction: column; gap: 0.75rem; }
        .vehicle-image { height: 180px; }
    }
</style>

<!-- Hero Section -->
<section class="bookings-hero">
    <div class="hero-decoration hero-decoration-1"></div>
    <div class="hero-decoration hero-decoration-2"></div>
    <canvas id="particleCanvas" class="particle-canvas"></canvas>
    
    <div class="hero-container">
        <div class="hero-content fade-in">
            <h1 class="hero-title">
                Kelola <strong>Pesanan Anda</strong><br>
                dengan <span class="accent">Mudah</span>
            </h1>
            <p class="hero-subtitle">
                Pantau status sewa kendaraan, kelola pembayaran, dan nikmati layanan rental terbaik dengan EzRent
            </p>
        </div>
    </div>
</section>

<!-- Stats Section - Separated -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-card fade-in stagger-1">
                <div class="stat-icon">
                    <svg fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $totalBookings; ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card fade-in stagger-2">
                <div class="stat-icon">
                    <svg fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $completedCount; ?></div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-card fade-in stagger-3">
                <div class="stat-icon">
                    <svg fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="stat-number"><?php echo $activeCount; ?></div>
                <div class="stat-label">Aktif</div>
            </div>
            <div class="stat-card fade-in stagger-4">
                <div class="stat-icon">
                    <svg fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-number">Rp <?php echo number_format($totalSpent / 1000000, 1); ?>jt</div>
                <div class="stat-label">Total Transaksi</div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="bookings-content">
    
    <?php if ($showSuccess && $bookingCode): ?>
    <div class="success-alert fade-in">
        <div class="success-icon">Sukses</div>
        <div class="success-content">
            <h3>Pesanan Berhasil Dibuat!</h3>
            <p>Terima kasih telah memesan di EzRent. Pesanan Anda sedang diproses dan akan segera dikonfirmasi.</p>
            <div class="booking-code">
                <span>Kode Booking: <strong><?php echo $bookingCode; ?></strong></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger fade-in">
        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="filter-container fade-in">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">
                <span>Semua</span>
                <span class="filter-count"><?php echo $totalBookings; ?></span>
            </button>
            <button class="filter-tab" data-filter="pending">
                <span>Menunggu</span>
                <span class="filter-count"><?php echo $pendingCount; ?></span>
            </button>
            <button class="filter-tab" data-filter="confirmed">
                <span>Dikonfirmasi</span>
                <span class="filter-count"><?php echo $confirmedCount; ?></span>
            </button>
            <button class="filter-tab" data-filter="active">
                <span>Aktif</span>
                <span class="filter-count"><?php echo $activeCount; ?></span>
            </button>
            <button class="filter-tab" data-filter="completed">
                <span>Selesai</span>
                <span class="filter-count"><?php echo $completedCount; ?></span>
            </button>
            <button class="filter-tab" data-filter="cancelled">
                <span>Dibatalkan</span>
                <span class="filter-count"><?php echo $cancelledCount; ?></span>
            </button>
        </div>
    </div>

    <!-- Bookings List -->
    <?php if (empty($bookings)): ?>
    <div class="empty-state fade-in">
        <div class="empty-icon"></div>
        <h3>Belum Ada Pesanan</h3>
        <p>Anda belum memiliki pesanan rental kendaraan.<br>Mulai petualangan Anda dengan menyewa kendaraan pilihan!</p>
        <a href="vehicles.php" class="btn btn-primary">
            <span>Lihat Kendaraan Tersedia</span>
        </a>
    </div>
    <?php else: ?>
    <div class="bookings-list" id="bookingsList">
        <?php foreach ($bookings as $booking): 
            $images = !empty($booking['vehicle_image']) ? json_decode($booking['vehicle_image'], true) : [];
            $mainImage = !empty($images) ? $images[0] : 'default-vehicle.jpg';
            // Ensure correct relative path from pages/user/ to assets and fallback if missing on disk
            $candidatePath = __DIR__ . '/../../assets/images/vehicles/' . $mainImage;
            if (!file_exists($candidatePath) || empty($mainImage)) {
                $mainImage = 'default-vehicle.jpg';
            }
        ?>
        <div class="booking-card fade-in" data-status="<?php echo $booking['status']; ?>">
            <div class="booking-header">
                <div class="booking-id">
                    <div>
                        <div class="booking-number">Kode Pesanan: <strong><?php echo htmlspecialchars($booking['kode_booking'] ?? ('EZR-' . str_pad($booking['id'] ?? 0, 6, '0', STR_PAD_LEFT))); ?></strong></div>
                        <div class="booking-date">Dibuat: <?php echo !empty($booking['created_at']) ? date('d M Y, H:i', strtotime($booking['created_at'])) : '-'; ?></div>
                    </div>
                </div>
                <div>
                    <?php echo getStatusBadge($booking['status']); ?>
                </div>
            </div>
            
            <div class="booking-body">
                <div class="vehicle-info">
                    <div class="vehicle-image">
                        <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($mainImage); ?>" 
                             alt="<?php echo htmlspecialchars($booking['vehicle_name']); ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
                    </div>
                    <div class="vehicle-details">
                        <h3>
                            <?php echo getVehicleIcon($booking['vehicle_type']); ?>
                            <?php
                            $vehicleName = !empty($booking['vehicle_name']) ? $booking['vehicle_name'] : 'Nama kendaraan tidak tersedia';
                            echo htmlspecialchars($vehicleName);
                            ?>
                        </h3>
                        <div class="vehicle-type">
                            <?php 
                            $typeNames = [
                                'mobil' => 'Mobil',
                                'motor' => 'Motor',
                                'sepeda_listrik' => 'Sepeda Listrik',
                                'sepeda' => 'Sepeda'
                            ];
                            echo $typeNames[$booking['vehicle_type']] ?? $booking['vehicle_type'];
                            ?>
                        </div>
                        <div class="vehicle-meta">
                            <div class="meta-item">
                                <span class="meta-icon"></span>
                                <span><?php echo htmlspecialchars($booking['vehicle_brand']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon"></span>
                                <span><?php echo htmlspecialchars($booking['vehicle_plate']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="rental-details">
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Mulai</div>
                        <div class="detail-value"><?php echo safe_date_format($booking['start_date']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Selesai</div>
                        <div class="detail-value"><?php echo safe_date_format($booking['end_date']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Durasi</div>
                        <div class="detail-value">
                            <?php 
                            if (isset($booking['duration']) && $booking['duration'] !== '') {
                                echo htmlspecialchars($booking['duration']);
                            } else {
                                echo safe_duration_days($booking['start_date'], $booking['end_date']);
                            }
                            ?> Hari
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Total Harga</div>
                        <div class="detail-value price-highlight">Rp <?php echo number_format($booking['total_price'] ?? 0, 0, ',', '.'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status Pembayaran</div>
                        <div class="detail-value"><?php echo getPaymentStatus($booking['payment_status'] ?? 'unpaid'); ?></div>
                    </div>
                </div>
                
                <div class="booking-actions">
                    <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">
                        <span>Detail Pesanan</span>
                    </a>
                    
                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                    <button class="btn btn-cancel btn-cancel" data-booking-id="<?php echo $booking['id']; ?>">
                        <span>Batalkan</span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($booking['status'] === 'completed'): ?>
                    <button class="btn btn-outline btn-review" data-booking-id="<?php echo $booking['id']; ?>">
                        <span>Beri Ulasan</span>
                    </button>
                    <?php endif; ?>
                    
                    <?php if (isset($booking['payment_status']) && $booking['payment_status'] === 'unpaid' && $booking['status'] !== 'cancelled'): ?>
                    <a href="payment.php?booking=<?php echo $booking['id']; ?>" class="btn btn-primary">
                        <span>Bayar Sekarang</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div id="noResults" class="empty-state" style="display: none;">
        <div class="empty-icon"></div>
        <h3>Tidak Ada Hasil</h3>
        <p>Tidak ada pesanan dengan filter yang dipilih.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Help Section -->
<section class="help-section">
    <div class="help-container">
        <div class="help-header fade-in">
            <h2>Butuh Bantuan?</h2>
            <p>Tim kami siap membantu Anda 24/7</p>
        </div>
        
        <div class="help-grid">
            <div class="help-card fade-in stagger-1">
                <div class="help-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 0 1 2-2h3.28a1 1 0 0 1 .948.684l1.498 4.493a1 1 0 0 1-.502 1.21l-2.257 1.13a11.042 11.042 0 0 0 5.516 5.516l1.13-2.257a1 1 0 0 1 1.21-.502l4.493 1.498a1 1 0 0 1 .684.949V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg></div>
                <h3>Hubungi Kami</h3>
                <p>Layanan pelanggan 24/7</p>
                <a href="tel:+6281234567890" class="help-link">+62 812-3456-7890 →</a>
            </div>
            <div class="help-card fade-in stagger-2">
                <div class="help-icon"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></div>
                <h3>Live Chat</h3>
                <p>Chat langsung dengan tim support</p>
                <a href="#" class="help-link">Mulai Chat →</a>
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