<?php 
// Start session di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Kontak - EzRent";
include 'header.php'; 

// Koneksi database
require_once '../../php/config/database.php';

$success_message = '';
$error_message = '';
$user_name = $_SESSION['nama_lengkap'] ?? 'Pengguna';
$user_email = $_SESSION['email'] ?? '';

// Handle form submission untuk user yang sudah login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($subject) || empty($message)) {
        $error_message = "Harap lengkapi semua field yang wajib diisi.";
    } elseif (strlen($subject) > 200) {
        $error_message = "Subjek pesan maksimal 200 karakter.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (user_id, subject, message, status, created_at) 
                VALUES (?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'], 
                $subject, 
                $message
            ]);
            
            $success_message = "Pesan Anda telah berhasil dikirim. Kami akan merespons dalam 1x24 jam.";
            $_POST = [];
            
        } catch (PDOException $e) {
            error_log("Message Send Error: " . $e->getMessage());
            $error_message = "Terjadi kesalahan saat menyimpan pesan.";
        }
    }
}

// Ambil riwayat pesan user
$user_messages = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, subject, message, status, created_at, replied_at
        FROM messages 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch Messages Error: " . $e->getMessage());
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    /* Hero Section */
    .hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        z-index: 1;
        animation: heroZoom 20s ease-in-out infinite alternate;
    }
    @keyframes heroZoom {
        0% { transform: scale(1); }
        100% { transform: scale(1.1); }
    }
    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.7) 100%);
        z-index: 2;
    }
    .hero-particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 3;
        pointer-events: none;
    }
    .hero-content {
        position: relative;
        z-index: 10;
        padding: 2rem;
        max-width: 900px;
        text-align: center;
    }
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 0.5rem 1.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        opacity: 0;
        transform: translateY(-20px);
        animation: fadeInDown 0.6s ease forwards 0.2s;
    }
    .hero h1 {
        color: #fff;
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 300;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
        text-shadow: 0 4px 30px rgba(0,0,0,0.5);
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.4s;
    }
    .hero h1 strong {
        font-weight: 700;
        background: linear-gradient(135deg, #fff 0%, #e0e0e0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero p {
        color: rgba(255,255,255,0.85);
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto 2rem;
        line-height: 1.8;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.6s;
    }
    
    .scroll-indicator {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        animation: bounce 2s infinite;
    }
    .scroll-indicator .mouse {
        width: 24px;
        height: 40px;
        border: 2px solid rgba(255,255,255,0.5);
        border-radius: 12px;
        position: relative;
    }
    .scroll-indicator .mouse::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 8px;
        background: #d50000;
        border-radius: 2px;
        animation: scrollWheel 1.5s infinite;
    }
    .scroll-indicator span {
        color: rgba(255,255,255,0.5);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.2em;
    }
    
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInDown {
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes bounce {
        0%, 100% { transform: translateX(-50%) translateY(0); }
        50% { transform: translateX(-50%) translateY(-10px); }
    }
    @keyframes scrollWheel {
        0% { opacity: 1; transform: translateX(-50%) translateY(0); }
        100% { opacity: 0; transform: translateX(-50%) translateY(10px); }
    }
    
    /* Contact Section - Light */
    .contact-section {
        padding: 6rem 2rem;
        background: #fafafa;
        position: relative;
        overflow: hidden;
    }
    .contact-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='50' cy='50' r='1' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
        background-size: 30px 30px;
        pointer-events: none;
    }
    .contact-container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    .section-header {
        text-align: center;
        margin-bottom: 4rem;
    }
    .section-badge {
        display: inline-block;
        background: rgba(213,0,0,0.1);
        border: 1px solid rgba(213,0,0,0.2);
        color: #d50000;
        padding: 0.5rem 1.5rem;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        margin-bottom: 1.5rem;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    .section-badge.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .section-header h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 300;
        margin-bottom: 1rem;
        color: #000;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease 0.1s;
    }
    .section-header h2.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .section-header h2 strong {
        font-weight: 700;
        background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .section-header p {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease 0.2s;
    }
    .section-header p.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .section-line {
        width: 80px;
        height: 3px;
        background: linear-gradient(90deg, transparent, #d50000, transparent);
        margin: 1.5rem auto 0;
    }
    
    .contact-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }
    
    /* Contact Cards */
    .contact-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        padding: 2.5rem 2rem;
        text-align: center;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        opacity: 0;
        transform: translateY(40px);
    }
    .contact-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .contact-card:nth-child(1) { transition-delay: 0.1s; }
    .contact-card:nth-child(2) { transition-delay: 0.2s; }
    .contact-card:nth-child(3) { transition-delay: 0.3s; }
    .contact-card:nth-child(4) { transition-delay: 0.4s; }
    
    .contact-card::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #d50000 0%, #ff5252 100%);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    .contact-card:hover::before {
        transform: scaleX(1);
    }
    .contact-card:hover {
        transform: translateY(-10px);
        border-color: #d50000;
        box-shadow: 0 25px 60px rgba(213,0,0,0.15);
    }
    .contact-card .card-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border: 2px solid #e5e7eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .contact-card .card-icon svg {
        width: 28px;
        height: 28px;
        color: #d50000;
        transition: all 0.4s ease;
    }
    .contact-card:hover .card-icon {
        background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
        border-color: #d50000;
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 15px 40px rgba(213,0,0,0.3);
    }
    .contact-card:hover .card-icon svg {
        color: #fff;
    }
    .contact-card h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #000;
        margin-bottom: 0.75rem;
    }
    .contact-card .card-value {
        color: #d50000;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .contact-card .card-desc {
        color: #6b7280;
        font-size: 0.85rem;
    }
    
    /* Form Section - Dark */
    .form-section {
        padding: 6rem 2rem;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
        position: relative;
        overflow: hidden;
    }
    .form-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .form-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        position: relative;
        z-index: 2;
    }
    .form-info {
        opacity: 0;
        transform: translateX(-40px);
        transition: all 0.8s ease;
    }
    .form-info.visible {
        opacity: 1;
        transform: translateX(0);
    }
    .form-info h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 300;
        color: #fff;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }
    .form-info h2 strong {
        font-weight: 700;
        color: #d50000;
    }
    .form-info p {
        color: rgba(255,255,255,0.7);
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.8;
    }
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .info-item .icon {
        width: 50px;
        height: 50px;
        background: rgba(213,0,0,0.1);
        border: 1px solid rgba(213,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .info-item .icon svg {
        width: 24px;
        height: 24px;
        color: #d50000;
    }
    .info-item .content h4 {
        color: #fff;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .info-item .content span {
        color: rgba(255,255,255,0.6);
        font-size: 0.9rem;
    }
    
    /* Form Card */
    .form-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 3rem;
        opacity: 0;
        transform: translateX(40px);
        transition: all 0.8s ease 0.2s;
    }
    .form-card.visible {
        opacity: 1;
        transform: translateX(0);
    }
    .form-card h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 0.5rem;
    }
    .form-card .form-subtitle {
        color: rgba(255,255,255,0.6);
        margin-bottom: 2rem;
        font-size: 0.95rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        color: rgba(255,255,255,0.8);
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 1rem 1.25rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.15);
        color: #fff;
        font-size: 1rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #d50000;
        background: rgba(255,255,255,0.08);
        box-shadow: 0 0 20px rgba(213,0,0,0.15);
    }
    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255,255,255,0.4);
    }
    .form-group select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='rgba(255,255,255,0.5)'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.5rem;
    }
    .form-group select option {
        background: #1a1a1a;
        color: #fff;
    }
    .form-group textarea {
        resize: vertical;
        min-height: 150px;
    }
    
    .btn-submit {
        width: 100%;
        padding: 1.25rem 2rem;
        background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
        color: #fff;
        border: none;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        cursor: pointer;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }
    .btn-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    .btn-submit:hover::before {
        left: 100%;
    }
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(213,0,0,0.4);
    }
    
    /* Alert Messages */
    .alert {
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
        font-size: 0.95rem;
    }
    .alert-success {
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.3);
        color: #10b981;
    }
    .alert-error {
        background: rgba(239,68,68,0.1);
        border: 1px solid rgba(239,68,68,0.3);
        color: #ef4444;
    }
    
    /* Recent Messages Section */
    .messages-section {
        padding: 6rem 2rem;
        background: #fff;
    }
    .messages-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .messages-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .messages-header h2 {
        font-size: 2rem;
        font-weight: 300;
        color: #000;
        margin-bottom: 0.5rem;
    }
    .messages-header h2 strong {
        font-weight: 700;
    }
    .messages-header p {
        color: #6b7280;
    }
    
    .message-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .message-item {
        background: #fafafa;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
    }
    .message-item.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .message-item:nth-child(1) { transition-delay: 0.1s; }
    .message-item:nth-child(2) { transition-delay: 0.2s; }
    .message-item:nth-child(3) { transition-delay: 0.3s; }
    .message-item:nth-child(4) { transition-delay: 0.4s; }
    .message-item:nth-child(5) { transition-delay: 0.5s; }
    
    .message-item:hover {
        border-color: #d50000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .message-subject {
        font-weight: 600;
        color: #000;
    }
    .message-status {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: 20px;
    }
    .message-status.new {
        background: #fef3c7;
        color: #d97706;
    }
    .message-status.replied {
        background: #d1fae5;
        color: #059669;
    }
    .message-status.closed {
        background: #e5e7eb;
        color: #6b7280;
    }
    .message-excerpt {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .message-date {
        font-size: 0.8rem;
        color: #9ca3af;
    }
    
    .empty-messages {
        text-align: center;
        padding: 3rem;
        color: #6b7280;
    }
    .empty-messages svg {
        width: 60px;
        height: 60px;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .contact-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .form-container {
            grid-template-columns: 1fr;
            gap: 3rem;
        }
    }
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
        .form-card {
            padding: 2rem;
        }
    }
</style>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-particles" id="hero-particles"></div>
        <div class="hero-content">
            <div class="hero-badge">Contact Us</div>
            <h1>Hubungi <strong>Kami</strong></h1>
            <p>Halo <strong><?php echo htmlspecialchars($user_name); ?></strong>! Kami siap membantu Anda 24/7. Kirim pesan atau hubungi langsung melalui channel yang tersedia.</p>
        </div>
        <div class="scroll-indicator">
            <div class="mouse"></div>
            <span>Scroll</span>
        </div>
    </section>
    
    <!-- Contact Info Cards -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="section-header">
                <div class="section-badge">Kontak</div>
                <h2>Cara Menghubungi <strong>Kami</strong></h2>
                <p>Pilih cara yang paling nyaman untuk Anda</p>
                <div class="section-line"></div>
            </div>
            
            <div class="contact-grid">
                <div class="contact-card">
                    <div class="card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3>Alamat Kantor</h3>
                    <div class="card-value">Jakarta Selatan</div>
                    <div class="card-desc">Jl. Raya Rental No. 123</div>
                </div>
                
                <div class="contact-card">
                    <div class="card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3>Telepon</h3>
                    <div class="card-value">(021) 1234-5678</div>
                    <div class="card-desc">Senin - Minggu, 24 Jam</div>
                </div>
                
                <div class="contact-card">
                    <div class="card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3>WhatsApp</h3>
                    <div class="card-value">0812-3456-7890</div>
                    <div class="card-desc">Chat cepat & responsif</div>
                </div>
                
                <div class="contact-card">
                    <div class="card-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3>Email</h3>
                    <div class="card-value">hello@ezrent.id</div>
                    <div class="card-desc">Respon dalam 24 jam</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Form Section -->
    <section class="form-section">
        <div class="form-container">
            <div class="form-info">
                <h2>Kirim Pesan <strong>Langsung</strong></h2>
                <p>Ada pertanyaan atau butuh bantuan? Isi form di samping dan tim kami akan segera menghubungi Anda kembali.</p>
                
                <div class="info-list">
                    <div class="info-item">
                        <div class="icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="content">
                            <h4>Respon Cepat</h4>
                            <span>Balasan dalam 1x24 jam</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="content">
                            <h4>Privasi Terjaga</h4>
                            <span>Data Anda aman bersama kami</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="content">
                            <h4>Dukungan 24/7</h4>
                            <span>Siap membantu kapan saja</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-card">
                <h3>Kirim Pesan</h3>
                <p class="form-subtitle">Ada yang bisa kami bantu, <?php echo htmlspecialchars($user_name); ?>?</p>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Subjek Pesan *</label>
                        <select name="subject" required>
                            <option value="">Pilih subjek pesan</option>
                            <option value="Pertanyaan Umum">Pertanyaan Umum</option>
                            <option value="Bantuan Pemesanan">Bantuan Pemesanan</option>
                            <option value="Penyewaan Kendaraan">Penyewaan Kendaraan</option>
                            <option value="Keluhan Layanan">Keluhan Layanan</option>
                            <option value="Saran & Feedback">Saran & Feedback</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Pesan Anda *</label>
                        <textarea name="message" placeholder="Tulis pesan Anda di sini..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Recent Messages -->
    <?php if (!empty($user_messages)): ?>
    <section class="messages-section">
        <div class="messages-container">
            <div class="messages-header">
                <h2>Riwayat <strong>Pesan</strong></h2>
                <p>Pesan terakhir yang Anda kirim</p>
            </div>
            
            <div class="message-list">
                <?php foreach ($user_messages as $msg): ?>
                <div class="message-item">
                    <div class="message-header">
                        <span class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></span>
                        <span class="message-status <?php echo $msg['status']; ?>">
                            <?php 
                            echo $msg['status'] === 'new' ? 'Baru' : 
                                ($msg['status'] === 'replied' ? 'Dibalas' : 'Ditutup'); 
                            ?>
                        </span>
                    </div>
                    <p class="message-excerpt"><?php echo htmlspecialchars($msg['message']); ?></p>
                    <span class="message-date"><?php echo date('d M Y, H:i', strtotime($msg['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include '../../php/includes/footer.php'; ?>

<script>
// Scroll Reveal Animation
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.section-badge, .section-header h2, .section-header p, .contact-card, .form-info, .form-card, .message-item').forEach(el => {
    scrollObserver.observe(el);
});

// Hero Parallax Effect
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    const heroBg = document.querySelector('.hero-bg');
    
    if (hero && scrolled < hero.offsetHeight) {
        heroBg.style.transform = `scale(${1 + scrolled * 0.0003}) translateY(${scrolled * 0.3}px)`;
    }
});

// Floating Particles
function createParticles() {
    const container = document.getElementById('hero-particles');
    if (!container) return;
    
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: ${Math.random() * 6 + 2}px;
            height: ${Math.random() * 6 + 2}px;
            background: rgba(255,255,255,${Math.random() * 0.3 + 0.1});
            border-radius: 50%;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            animation: float ${Math.random() * 10 + 10}s infinite ease-in-out;
            animation-delay: ${Math.random() * 5}s;
        `;
        container.appendChild(particle);
    }
}

const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        50% { transform: translateY(-100px) translateX(50px); }
    }
`;
document.head.appendChild(style);
createParticles();

// Card 3D Hover Effect
document.querySelectorAll('.contact-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 15;
        const rotateY = (centerX - x) / 15;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    });
});
</script>
</body>
</html>