<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0);

if ($booking_id <= 0) {
    header("Location: my-bookings.php");
    exit();
}

// Check for payment status
$payment_status = isset($_GET['payment']) ? $_GET['payment'] : null;

$page_title = "Detail Pesanan - EzRent";
include 'header.php';

require_once '../../php/config/database.php';

$booking = null;
$error = null;

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
            v.jenis as vehicle_type,
            v.plat_nomor as vehicle_plate,
            v.harga_per_hari as daily_rate,
            v.images as vehicle_image,
            v.warna as vehicle_color,
            v.transmisi as transmission,
            v.bahan_bakar as fuel_type,
            v.kapasitas as capacity,
            u.nama_lengkap as user_name,
            u.email as user_email,
            u.nomor_telepon as user_phone
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $error = "Pesanan tidak ditemukan atau Anda tidak memiliki akses.";
    }
} catch (PDOException $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}

function getStatusInfo($status) {
    $statusConfig = [
        'pending' => ['label' => 'MENUNGGU KONFIRMASI', 'class' => 'status-pending'],
        'confirmed' => ['label' => 'DIKONFIRMASI', 'class' => 'status-confirmed'],
        'active' => ['label' => 'SEDANG BERJALAN', 'class' => 'status-active'],
        'completed' => ['label' => 'SELESAI', 'class' => 'status-completed'],
        'cancelled' => ['label' => 'DIBATALKAN', 'class' => 'status-cancelled']
    ];
    return $statusConfig[$status] ?? ['label' => strtoupper($status), 'class' => 'status-default'];
}

function getVehicleType($type) {
    $types = ['mobil' => 'Automobile', 'motor' => 'Motorcycle', 'sepeda_listrik' => 'E-Bike', 'sepeda' => 'Bicycle'];
    return $types[$type] ?? 'Vehicle';
}

if ($booking) {
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $duration = $start->diff($end)->days + 1;
    $created = new DateTime($booking['created_at']);
    
    $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $statusInfo = getStatusInfo($booking['status']);
    $paymentStatus = $booking['status'] === 'cancelled' ? 'refunded' : ($booking['status'] === 'pending' ? 'unpaid' : 'paid');
}
?>

<style>
/* Override body from header.php */
body {
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%) !important;
    color: #fff !important;
}

:root {
    --primary: #c41e3a;
    --primary-dark: #a01830;
    --gold: #d4af37;
    --dark: #0a0a0a;
    --darker: #050505;
}

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

.animate-in {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }

/* Hero Section */
.detail-hero {
    background: var(--dark);
    min-height: 280px;
    padding-top: 80px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.detail-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(ellipse at 20% 50%, rgba(196, 30, 58, 0.15) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 50%, rgba(196, 30, 58, 0.1) 0%, transparent 50%),
        linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.5) 100%);
    pointer-events: none;
}

.detail-hero::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.02'/%3E%3C/svg%3E");
    pointer-events: none;
}

/* Floating Elements */
.floating-element {
    position: absolute;
    opacity: 0.03;
    pointer-events: none;
}

.float-1 {
    top: 10%;
    right: 10%;
    width: 300px;
    height: 300px;
    border: 1px solid #fff;
    border-radius: 50%;
    animation: float 15s ease-in-out infinite;
}

.float-2 {
    bottom: -50px;
    left: 5%;
    width: 200px;
    height: 200px;
    border: 1px solid var(--primary);
    transform: rotate(45deg);
    animation: float 20s ease-in-out infinite reverse;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3rem 20px;
    position: relative;
    z-index: 2;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
}

.breadcrumb a {
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb a:hover { color: var(--primary); }
.breadcrumb .separator { color: rgba(255,255,255,0.2); }
.breadcrumb .current { color: rgba(255,255,255,0.8); }

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
    flex-wrap: wrap;
}

.hero-title {
    color: #fff;
    font-size: 2.5rem;
    font-weight: 200;
    letter-spacing: -0.02em;
    margin-bottom: 0.5rem;
}

.hero-title strong {
    font-weight: 700;
}

.order-code {
    display: inline-block;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
    color: #fff;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    margin-top: 1rem;
}

/* Status Badges */
.status-badge {
    padding: 0.75rem 2rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    position: relative;
    overflow: hidden;
}

.status-badge::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 2s linear infinite;
}

.status-pending { background: #1a1500; color: #d4af37; border: 1px solid rgba(212, 175, 55, 0.3); }
.status-confirmed { background: #001a33; color: #4d94ff; border: 1px solid rgba(77, 148, 255, 0.3); }
.status-active { background: #001a0d; color: #00cc66; border: 1px solid rgba(0, 204, 102, 0.3); }
.status-completed { background: #1a1a1a; color: #999; border: 1px solid rgba(153, 153, 153, 0.3); }
.status-cancelled { background: #1a0000; color: #ff4d4d; border: 1px solid rgba(255, 77, 77, 0.3); }

/* Main Content */
.detail-content {
    background: linear-gradient(180deg, #0f0f0f 0%, #080808 100%);
    min-height: 70vh;
    padding: 4rem 20px;
    position: relative;
}

.detail-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--primary), transparent);
}

.content-container {
    max-width: 1200px;
    margin: 0 auto;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
}

@media (max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; }
}

/* Cards */
.card {
    background: linear-gradient(145deg, rgba(20,20,20,0.9) 0%, rgba(10,10,10,0.95) 100%);
    border: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
}

.card-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-title {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
}

.card-body { padding: 2rem; }

/* Vehicle Card */
.vehicle-showcase {
    display: flex;
    gap: 2rem;
    align-items: center;
}

@media (max-width: 600px) {
    .vehicle-showcase { flex-direction: column; text-align: center; }
}

.vehicle-image-wrapper {
    width: 180px;
    height: 120px;
    background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex-shrink: 0;
}

.vehicle-image-wrapper::before {
    content: '';
    position: absolute;
    inset: 0;
    border: 1px solid rgba(255,255,255,0.05);
}

.vehicle-type-label {
    font-size: 0.65rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--primary);
    position: absolute;
    top: 10px;
    left: 10px;
}

.vehicle-silhouette {
    font-size: 3rem;
    opacity: 0.3;
}

.vehicle-info h3 {
    font-size: 1.5rem;
    font-weight: 300;
    color: #fff;
    margin-bottom: 0.25rem;
    letter-spacing: -0.01em;
}

.vehicle-brand {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
    margin-bottom: 1rem;
}

.vehicle-plate {
    display: inline-block;
    background: var(--dark);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    color: #fff;
}

.vehicle-specs {
    display: flex;
    gap: 1.5rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.06);
    flex-wrap: wrap;
}

.spec-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.spec-label {
    font-size: 0.65rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
}

.spec-value {
    font-size: 0.9rem;
    color: #fff;
    font-weight: 500;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 10px;
    bottom: 10px;
    width: 1px;
    background: linear-gradient(180deg, var(--primary), rgba(255,255,255,0.1));
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
}

.timeline-item:last-child { padding-bottom: 0; }

.timeline-dot {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 12px;
    height: 12px;
    background: var(--dark);
    border: 2px solid var(--primary);
    border-radius: 50%;
    transition: all 0.3s;
}

.timeline-item.completed .timeline-dot {
    background: var(--primary);
    box-shadow: 0 0 15px rgba(196, 30, 58, 0.5);
}

.timeline-item.pending .timeline-dot {
    border-color: rgba(255,255,255,0.2);
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 500;
    color: #fff;
    margin-bottom: 0.25rem;
}

.timeline-desc {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.4);
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    background: rgba(255,255,255,0.06);
}

.info-item {
    background: rgba(10,10,10,0.8);
    padding: 1.5rem;
}

.info-label {
    font-size: 0.65rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
    margin-bottom: 0.5rem;
}

.info-value {
    font-size: 1.1rem;
    font-weight: 500;
    color: #fff;
}

.info-sub {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.4);
    margin-top: 0.25rem;
}

/* Sidebar */
.detail-sidebar {
    position:sticky;
    top: 100px;
}

/* Price Summary */
.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.price-row:last-of-type { border-bottom: none; }

.price-row.total {
    margin-top: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--primary);
    border-bottom: none;
}

.price-label { color: rgba(255,255,255,0.5); font-size: 0.9rem; }
.price-value { color: #fff; font-weight: 600; font-size: 0.95rem; }
.price-row.total .price-label { color: #fff; font-weight: 500; }
.price-row.total .price-value { color: var(--primary); font-size: 1.5rem; font-weight: 700; }

/* Payment Status */
.payment-status {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    margin-top: 1.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
}

.payment-status.paid {
    background: rgba(0, 204, 102, 0.1);
    border: 1px solid rgba(0, 204, 102, 0.3);
    color: #00cc66;
}

.payment-status.unpaid {
    background: rgba(255, 77, 77, 0.1);
    border: 1px solid rgba(255, 77, 77, 0.3);
    color: #ff4d4d;
}

/* Action Buttons */
.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 1rem 1.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    margin-bottom: 0.75rem;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.5s;
}

.action-btn:hover::before { left: 100%; }

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(196, 30, 58, 0.3);
}

.btn-secondary {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.4);
}

.btn-danger {
    background: rgba(255, 77, 77, 0.1);
    border: 1px solid rgba(255, 77, 77, 0.3);
    color: #ff4d4d;
}

.btn-danger:hover {
    background: rgba(255, 77, 77, 0.2);
}

/* Support Box */
.support-box {
    background: linear-gradient(135deg, rgba(196, 30, 58, 0.1) 0%, transparent 100%);
    border: 1px solid rgba(196, 30, 58, 0.2);
    padding: 1.5rem;
    text-align: center;
    margin-top: 1rem;
}

.support-box p {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 0.75rem;
}

.support-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.8rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    transition: color 0.3s;
}

.support-link:hover { color: #ff6b7a; }

/* Error State */
.error-container {
    max-width: 500px;
    margin: 0 auto;
    text-align: center;
    padding: 5rem 2rem;
}

.error-icon {
    width: 80px;
    height: 80px;
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2rem;
    color: rgba(255,255,255,0.3);
}

.error-title {
    font-size: 1.5rem;
    font-weight: 300;
    color: #fff;
    margin-bottom: 0.75rem;
}

.error-text {
    color: rgba(255,255,255,0.5);
    margin-bottom: 2rem;
}

/* Customer Info */
.customer-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: rgba(255,255,255,0.06);
}

@media (max-width: 600px) {
    .customer-grid { grid-template-columns: 1fr; }
}

.customer-item {
    background: rgba(10,10,10,0.8);
    padding: 1.25rem;
}

/* Notes */
.notes-content {
    background: rgba(212, 175, 55, 0.05);
    border-left: 2px solid var(--gold);
    padding: 1.25rem;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
    line-height: 1.7;
}

.notes-empty {
    color: rgba(255,255,255,0.3);
    font-style: italic;
}

@media print {
    .detail-hero, .sidebar-card { display: none !important; }
    .detail-grid { grid-template-columns: 1fr !important; }
    .card { border: 1px solid #ddd !important; background: #fff !important; }
}
</style>

<?php if ($error): ?>
<section class="detail-content">
    <div class="error-container animate-in">
        <div class="error-icon">!</div>
        <h2 class="error-title">Pesanan Tidak Ditemukan</h2>
        <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
        <a href="my-bookings.php" class="action-btn btn-secondary">Kembali ke Pesanan</a>
    </div>
</section>
<?php else: ?>

<section class="detail-hero">
    <div class="floating-element float-1"></div>
    <div class="floating-element float-2"></div>
    
    <div class="hero-container">
        <nav class="breadcrumb animate-in">
            <a href="dashboard.php">Dashboard</a>
            <span class="separator">/</span>
            <a href="my-bookings.php">Pesanan</a>
            <span class="separator">/</span>
            <span class="current">Detail</span>
        </nav>
        
        <div class="hero-content">
            <div class="animate-in delay-1">
                <h1 class="hero-title">Detail <strong>Pesanan</strong></h1>
                <div class="order-code">#EZR-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            <div class="animate-in delay-2">
                <div class="status-badge <?php echo $statusInfo['class']; ?>">
                    <?php echo $statusInfo['label']; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($payment_status === 'success'): ?>
<div class="payment-notification success animate-in" style="
    position: relative;
    max-width: 1200px;
    margin: 0 auto 0;
    padding: 1.5rem 20px;
">
    <div style="
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(34, 197, 94, 0.05) 100%);
        border: 1px solid rgba(34, 197, 94, 0.4);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    ">
        <div style="
            width: 50px;
            height: 50px;
            background: rgba(34, 197, 94, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        ">✓</div>
        <div>
            <h4 style="color: #22c55e; margin: 0 0 0.25rem 0; font-size: 1.1rem;">Pembayaran Berhasil!</h4>
            <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;">Terima kasih! Pembayaran Anda telah dikonfirmasi. Admin akan segera memproses pesanan Anda.</p>
        </div>
    </div>
</div>
<?php elseif ($payment_status === 'pending'): ?>
<div class="payment-notification pending animate-in" style="
    position: relative;
    max-width: 1200px;
    margin: 0 auto 0;
    padding: 1.5rem 20px;
">
    <div style="
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.15) 0%, rgba(234, 179, 8, 0.05) 100%);
        border: 1px solid rgba(234, 179, 8, 0.4);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    ">
        <div style="
            width: 50px;
            height: 50px;
            background: rgba(234, 179, 8, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        ">⏳</div>
        <div>
            <h4 style="color: #eab308; margin: 0 0 0.25rem 0; font-size: 1.1rem;">Menunggu Pembayaran</h4>
            <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;">Silakan selesaikan pembayaran Anda sesuai instruksi. Status akan diperbarui otomatis setelah pembayaran dikonfirmasi.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<section class="detail-content">
    <div class="content-container">
        <div class="detail-grid">
            <div class="detail-main">
                <!-- Vehicle Card -->
                <div class="card animate-in delay-1">
                    <div class="card-header">
                        <span class="card-title">Kendaraan</span>
                        <span class="card-title" style="color: var(--primary);"><?php echo getVehicleType($booking['vehicle_type']); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="vehicle-showcase">
                            <div class="vehicle-image-wrapper">
                                <span class="vehicle-type-label"><?php echo getVehicleType($booking['vehicle_type']); ?></span>
                                <div class="vehicle-silhouette">▣</div>
                            </div>
                            <div class="vehicle-info">
                                <h3><?php echo htmlspecialchars($booking['vehicle_name']); ?></h3>
                                <p class="vehicle-brand"><?php echo htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']); ?></p>
                                <span class="vehicle-plate"><?php echo htmlspecialchars($booking['vehicle_plate']); ?></span>
                            </div>
                        </div>
                        
                        <div class="vehicle-specs">
                            <?php if (!empty($booking['vehicle_color'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Warna</span>
                                <span class="spec-value"><?php echo htmlspecialchars($booking['vehicle_color']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($booking['transmission'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Transmisi</span>
                                <span class="spec-value"><?php echo htmlspecialchars($booking['transmission']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($booking['fuel_type'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Bahan Bakar</span>
                                <span class="spec-value"><?php echo htmlspecialchars($booking['fuel_type']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($booking['capacity'])): ?>
                            <div class="spec-item">
                                <span class="spec-label">Kapasitas</span>
                                <span class="spec-value"><?php echo htmlspecialchars($booking['capacity']); ?> Penumpang</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="card animate-in delay-2">
                    <div class="card-header">
                        <span class="card-title">Status Pesanan</span>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Pesanan Dibuat</h4>
                                    <p class="timeline-desc"><?php echo $created->format('d M Y, H:i'); ?> WIB</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo in_array($booking['status'], ['confirmed', 'active', 'completed']) ? 'completed' : ($booking['status'] === 'cancelled' ? 'pending' : ''); ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Konfirmasi Admin</h4>
                                    <p class="timeline-desc"><?php echo $booking['status'] === 'pending' ? 'Menunggu konfirmasi' : ($booking['status'] === 'cancelled' ? 'Dibatalkan' : 'Dikonfirmasi'); ?></p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo in_array($booking['status'], ['active', 'completed']) ? 'completed' : 'pending'; ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Pengambilan Kendaraan</h4>
                                    <p class="timeline-desc"><?php echo $start->format('d M Y'); ?> — <?php echo htmlspecialchars($booking['pickup_location'] ?? 'Lokasi pickup'); ?></p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $booking['status'] === 'completed' ? 'completed' : 'pending'; ?>">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Pengembalian Kendaraan</h4>
                                    <p class="timeline-desc"><?php echo $end->format('d M Y'); ?> — <?php echo htmlspecialchars($booking['return_location'] ?? $booking['pickup_location'] ?? 'Lokasi return'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rental Details Card -->
                <div class="card animate-in delay-3">
                    <div class="card-header">
                        <span class="card-title">Detail Penyewaan</span>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Tanggal Mulai</div>
                            <div class="info-value"><?php echo $start->format('d M Y'); ?></div>
                            <div class="info-sub"><?php echo $dayNames[$start->format('l')] ?? $start->format('l'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tanggal Selesai</div>
                            <div class="info-value"><?php echo $end->format('d M Y'); ?></div>
                            <div class="info-sub"><?php echo $dayNames[$end->format('l')] ?? $end->format('l'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Durasi</div>
                            <div class="info-value"><?php echo $duration; ?> Hari</div>
                            <div class="info-sub"><?php echo $duration * 24; ?> Jam</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Kode Booking</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['kode_booking'] ?? 'EZR-' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT)); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Lokasi Pengambilan</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['pickup_location'] ?? '-'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Lokasi Pengembalian</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['return_location'] ?? $booking['pickup_location'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Customer Info Card -->
                <div class="card animate-in delay-4">
                    <div class="card-header">
                        <span class="card-title">Informasi Penyewa</span>
                    </div>
                    <div class="customer-grid">
                        <div class="customer-item">
                            <div class="info-label">Nama</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['user_name'] ?? $_SESSION['user_name'] ?? '-'); ?></div>
                        </div>
                        <div class="customer-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['user_email'] ?? '-'); ?></div>
                        </div>
                        <div class="customer-item">
                            <div class="info-label">Telepon</div>
                            <div class="info-value"><?php echo htmlspecialchars($booking['user_phone'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <?php if (!empty($booking['notes'])): ?>
                <div class="card animate-in delay-4">
                    <div class="card-header">
                        <span class="card-title">Catatan</span>
                    </div>
                    <div class="card-body">
                        <div class="notes-content"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="detail-sidebar">
                <div class="card sidebar-card animate-in delay-2">
                    <div class="card-header">
                        <span class="card-title">Ringkasan Biaya</span>
                    </div>
                    <div class="card-body">
                        <div class="price-row">
                            <span class="price-label">Tarif per Hari</span>
                            <span class="price-value">Rp <?php echo number_format($booking['daily_rate'] ?? ($booking['total_price'] / $duration), 0, ',', '.'); ?></span>
                        </div>
                        <div class="price-row">
                            <span class="price-label">Durasi Sewa</span>
                            <span class="price-value"><?php echo $duration; ?> Hari</span>
                        </div>
                        
                        <?php if (!empty($booking['discount_code']) && $booking['discount_amount'] > 0): ?>
                        <div class="price-row">
                            <span class="price-label">Subtotal</span>
                            <span class="price-value">Rp <?php echo number_format($booking['total_price'] + $booking['discount_amount'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="price-row" style="color: #22c55e;">
                            <span class="price-label">
                                <i class="fas fa-tag"></i> Diskon (<?php echo strtoupper($booking['discount_code']); ?>)
                            </span>
                            <span class="price-value">-Rp <?php echo number_format($booking['discount_amount'], 0, ',', '.'); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="price-row total">
                            <span class="price-label">Total Bayar</span>
                            <span class="price-value">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="payment-status <?php echo $paymentStatus === 'paid' ? 'paid' : 'unpaid'; ?>">
                            <?php echo $paymentStatus === 'paid' ? 'Pembayaran Lunas' : 'Menunggu Pembayaran'; ?>
                        </div>
                    </div>
                </div>

                <div class="card animate-in delay-3">
                    <div class="card-header">
                        <span class="card-title">Aksi</span>
                    </div>
                    <div class="card-body">
                        <?php if ($booking['status'] === 'pending'): ?>
                            <a href="payment-gateway.php?booking_id=<?php echo $booking['id']; ?>" class="action-btn btn-primary">Bayar Sekarang</a>
                            <a href="cancel-booking.php?id=<?php echo $booking['id']; ?>" class="action-btn btn-danger" onclick="return confirm('Yakin ingin membatalkan pesanan?')">Batalkan Pesanan</a>
                        <?php elseif (in_array($booking['status'], ['confirmed', 'active', 'completed'])): ?>
                            <a href="invoice.php?booking_id=<?php echo $booking['id']; ?>" class="action-btn btn-primary">Lihat Invoice</a>
                            <?php if ($booking['status'] === 'completed'): ?>
                            <a href="review.php?booking_id=<?php echo $booking['id']; ?>" class="action-btn btn-secondary">Beri Ulasan</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <a href="my-bookings.php" class="action-btn btn-secondary">Kembali ke Pesanan</a>
                        
                        <div class="support-box">
                            <p>Ada pertanyaan tentang pesanan?</p>
                            <a href="contact.php" class="support-link">Hubungi Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-in').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
});
</script>

<?php include '../../php/includes/footer.php'; ?>
