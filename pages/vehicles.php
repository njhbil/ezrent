<?php 
$page_title = "Kendaraan Tersedia - EzRent";

// Koneksi Database
require_once '../php/config/database.php';

// Cek status login
session_start();
$is_logged_in = isset($_SESSION['user_id']);

// Ambil data motor dari database
$stmt_motor = $pdo->prepare("SELECT * FROM vehicles WHERE jenis = 'motor' ORDER BY id ASC");
$stmt_motor->execute();
$motors = $stmt_motor->fetchAll(PDO::FETCH_ASSOC);

// Ambil data mobil dari database
$stmt_mobil = $pdo->prepare("SELECT * FROM vehicles WHERE jenis = 'mobil' ORDER BY id ASC");
$stmt_mobil->execute();
$mobils = $stmt_mobil->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #fff; color: #000; }
        
        /* Hero */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1583121274602-3e2820c69888?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            z-index: 1;
            animation: heroZoom 20s ease-in-out infinite alternate;
        }
        /* Particle Canvas (hero animated dots) */
        .particle-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 3;
            pointer-events: none;
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
        .hero-content {
            position: relative;
            z-index: 10;
            padding: 8rem 2rem 4rem;
            max-width: 800px;
        }
        .hero-content .hero-badge {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards 0.3s;
        }
        .hero-content h1 {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards 0.5s;
        }
        .hero-content p {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards 0.7s;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .hero-badge {
            display: inline-block;
            background: rgba(213, 0, 0, 0.2);
            border: 1px solid rgba(213, 0, 0, 0.5);
            color: #ff5252;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .hero h1 {
            color: #fff;
            font-size: 4rem;
            font-weight: 280;
            margin-bottom: 1rem;
        }
        .hero h1 strong { font-weight: 700; color: #d50000; }
        .hero p {
            color: rgba(255,255,255,0.8);
            font-size: 1.5rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Filter Section */
        .filter-section {
            background: #0a0a0a;
            padding: 3rem 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .filter-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .filter-header {
            text-align: center;
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        .filter-header.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .filter-header h2 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .filter-header p {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }
        .filter-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.2s;
        }
        .filter-buttons.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .filter-btn {
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 30px;
            padding: 0.75rem 2rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .filter-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(213, 0, 0, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s, height 0.4s;
        }
        .filter-btn:hover::before {
            width: 200px;
            height: 200px;
        }
        .filter-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(213, 0, 0, 0.3);
        }
        .filter-btn:hover, .filter-btn.active {
            background: #d50000;
            border-color: #d50000;
            color: #fff;
        }
        .search-box {
            max-width: 500px;
            margin: 2rem auto 0;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50px;
            background: rgba(255,255,255,0.05);
            font-size: 1rem;
            color: #fff;
            transition: all 0.3s ease;
        }
        .search-input::placeholder { color: rgba(255,255,255,0.5); }
        .search-input:focus {
            outline: none;
            border-color: #d50000;
            background: rgba(255,255,255,0.08);
        }
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.5);
        }
        
        /* Vehicles Section */
        .vehicles-section {
            background: #0a0a0a;
            padding: 4rem 20px;
            min-height: 60vh;
        }
        .vehicles-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .category-title {
            font-size: 1.5rem;
            font-weight: 300;
            color: #fff;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            position: relative;
            display: inline-block;
            padding-bottom: 0.5rem;
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s ease;
        }
        .category-title.visible {
            opacity: 1;
            transform: translateX(0);
        }
        .category-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #d50000;
            transition: width 0.6s ease 0.3s;
        }
        .category-title.visible::after {
            width: 40px;
        }
        @keyframes pulseLine {
            0%, 100% { box-shadow: 0 0 0 rgba(213,0,0,0); }
            50% { box-shadow: 0 0 10px rgba(213,0,0,0.5); }
        }
        .category-title.visible::after {
            animation: pulseLine 2s infinite;
        }
        .category-count {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
            margin-left: 0.5rem;
        }
        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 4rem;
        }
        .vehicle-card {
            background: #111;
            border: 1px solid rgba(255,255,255,0.1);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border-radius: 12px;
            opacity: 0;
            transform: translateY(50px);
        }
        .vehicle-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .vehicle-card:nth-child(1) { transition-delay: 0.05s; }
        .vehicle-card:nth-child(2) { transition-delay: 0.1s; }
        .vehicle-card:nth-child(3) { transition-delay: 0.15s; }
        .vehicle-card:nth-child(4) { transition-delay: 0.2s; }
        .vehicle-card:nth-child(5) { transition-delay: 0.25s; }
        .vehicle-card:nth-child(6) { transition-delay: 0.3s; }
        .vehicle-card:nth-child(7) { transition-delay: 0.35s; }
        .vehicle-card:nth-child(8) { transition-delay: 0.4s; }
        .vehicle-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 0;
            background: linear-gradient(180deg, #d50000 0%, #ff5252 100%);
            transition: height 0.3s ease;
            z-index: 10;
        }
        .vehicle-card:hover::before { height: 100%; }
        .vehicle-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            transition: left 0.5s ease;
            z-index: 5;
            pointer-events: none;
        }
        .vehicle-card:hover::after {
            left: 100%;
        }
        .vehicle-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), 0 0 30px rgba(213,0,0,0.15);
            border-color: rgba(213,0,0,0.4);
        }
        .vehicle-image {
            position: relative;
            aspect-ratio: 16/10;
            overflow: hidden;
        }
        .vehicle-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        @media (max-width: 768px) {
        .vehicle-image {
         aspect-ratio: 4/3 !important;   /* Lebih proporsional */
        min-height: 160px;
        max-height: 220px;

        }
        }
        .vehicle-card:hover .vehicle-image img {
            transform: scale(1.1);
        }
        .vehicle-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.4rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 20px;
        }
        .status-tersedia { background: rgba(34, 197, 94, 0.9); color: #fff; }
        .status-disewa { background: rgba(239, 68, 68, 0.9); color: #fff; }
        .vehicle-brand {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(0,0,0,0.7);
            color: #fff;
            padding: 0.3rem 0.8rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 4px;
        }
        .vehicle-info { padding: 1.5rem; }
        .vehicle-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .vehicle-model {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
            margin-bottom: 1rem;
        }
        .vehicle-specs {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .spec-item {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.6);
            display: flex;
            align-items: center;
            gap: 0.3rem;
            background: rgba(255,255,255,0.05);
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
        }
        .vehicle-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
        }
        .price span {
            font-size: 0.8rem;
            font-weight: 400;
            color: rgba(255,255,255,0.6);
        }
        .btn-book {
            width: 100px;
            background: #d50000;
            color: #fff;
            border: none;
            padding: 0.7rem 1.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            border-radius: 6px;
            position: relative;
            overflow: hidden;
        }
        .btn-book::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.4s ease;
        }
        .btn-book:hover::before {
            left: 100%;
        }
        .btn-book:hover {
            background: #ff1744;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(213, 0, 0, 0.4);
            color: #fff;
        }
        .btn-book:active {
            transform: translateY(-1px);
        }
        .btn-book.disabled {
            background: #444;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-book.disabled::before {
            display: none;
        }
        
        /* Footer */
        footer {
            background: #000;
            color: #fff;
            padding: 3rem 20px;
            text-align: center;
        }
        footer p {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
        }
        
        /* Scroll Progress */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: rgba(255,255,255,0.1);
            z-index: 1000;
        }
        .scroll-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #d50000 0%, #ff5252 100%);
            width: 0%;
            transition: width 0.1s linear;
        }
        
        /* Mobile menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .nav-links, .auth-buttons {
            transition: opacity 0.3s ease;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .nav-links, .auth-buttons { display: none; }
            .mobile-menu-btn { display: block; }
            .vehicles-grid { grid-template-columns: 1fr; }
            .filter-buttons { gap: 0.5rem; }
            .filter-btn { padding: 0.6rem 1.25rem; font-size: 0.8rem; }
            .hero h1 { font-size: 3rem !important;  margin: 20px !important; } 
            .hero p { font-size: 1.2rem !important; margin: 20px !important;}    }
    </style> 
</head>
<body class="page-vehicles">
    <?php include '../php/includes/header.php'; ?>
    
    <!-- Scroll Progress -->
    <div class="scroll-progress">
        <div class="scroll-progress-bar" id="scrollProgress"></div>
    </div>
    
    <!-- Hero -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Pilih <strong>Kendaraan Anda</strong></h1>
            <p>Koleksi kendaraan EzRent berkualitas premium dengan harga terbaik. Semua kendaraan terawat dengan standar tertinggi.</p>
        </div>
    </section>
    <canvas id="particleCanvas" class="particle-canvas"></canvas>
    
    <!-- Filter Section -->
    <section class="filter-section">
        <div class="filter-container">
            <div class="filter-header">
                <h2>Filter Kendaraan</h2>
                <p>Temukan kendaraan yang sesuai dengan kebutuhan Anda</p>
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">Semua (<?php echo count($motors) + count($mobils); ?>)</button>
                <button class="filter-btn" data-filter="motor">Motor (<?php echo count($motors); ?>)</button>
                <button class="filter-btn" data-filter="mobil">Mobil (<?php echo count($mobils); ?>)</button>
                <button class="filter-btn" data-filter="tersedia">Tersedia</button>
            </div>
            
            <div class="search-box">
                <span class="search-icon"></span>
                <input type="text" class="search-input" placeholder="Cari kendaraan..." id="searchInput">
            </div>
        </div>
    </section>
    
    <!-- Vehicles Section -->
    <section class="vehicles-section">
        <div class="vehicles-container">
            
            <!-- Motor Section -->
            <div class="vehicle-category" data-category="motor">
                <h3 class="category-title">Motor <span class="category-count">(<?php echo count($motors); ?> unit)</span></h3>
                <div class="vehicles-grid">
                    <?php foreach ($motors as $motor): 
                        $images = json_decode($motor['images'], true);
                        $image = isset($images[0]) ? $images[0] : 'default.jpg';
                    ?>
                    <div class="vehicle-card" data-name="<?php echo strtolower($motor['nama']); ?>" data-status="<?php echo $motor['status']; ?>">
                        <div class="vehicle-image">
                            <img src="../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($motor['nama']); ?>"
                                 onerror="this.src='../assets/images/vehicles/default.jpg'">
                            <span class="vehicle-brand"><?php echo htmlspecialchars($motor['merek']); ?></span>
                            <span class="vehicle-status <?php echo $motor['status'] === 'tersedia' ? 'status-tersedia' : 'status-disewa'; ?>">
                                <?php echo $motor['status'] === 'tersedia' ? 'Tersedia' : 'Disewa'; ?>
                            </span>
                        </div>
                        <div class="vehicle-info">
                            <h4 class="vehicle-name"><?php echo htmlspecialchars($motor['nama']); ?></h4>
                            <p class="vehicle-model"><?php echo htmlspecialchars($motor['model']); ?></p>
                            <div class="vehicle-specs">
                                <span class="spec-item"><?php echo $motor['tahun']; ?></span>
                                <span class="spec-item"><?php echo htmlspecialchars($motor['warna']); ?></span>
                                <span class="spec-item"><?php echo ucfirst($motor['transmisi']); ?></span>
                            </div>
                            <div class="vehicle-price">
                                <div class="price">Rp <?php echo number_format($motor['harga_per_hari'], 0, ',', '.'); ?><span>/hari</span></div>
                                <?php if ($is_logged_in): ?>
                                    <?php if ($motor['status'] === 'tersedia'): ?>
                                        <a href="user/booking-process.php?vehicle_id=<?php echo $motor['id']; ?>" class="btn-book">Pesan</a>
                                    <?php else: ?>
                                        <a href="#" class="btn-book disabled" aria-disabled="true" title="Kendaraan tidak tersedia">Pesan</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php?redirect=vehicles" class="btn-book">Login</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Mobil Section -->
            <div class="vehicle-category" data-category="mobil">
                <h3 class="category-title">Mobil <span class="category-count">(<?php echo count($mobils); ?> unit)</span></h3>
                <div class="vehicles-grid">
                    <?php foreach ($mobils as $mobil): 
                        $images = json_decode($mobil['images'], true);
                        $image = isset($images[0]) ? $images[0] : 'default.jpg';
                    ?>
                    <div class="vehicle-card" data-name="<?php echo strtolower($mobil['nama']); ?>" data-status="<?php echo $mobil['status']; ?>">
                        <div class="vehicle-image">
                            <img src="../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($mobil['nama']); ?>"
                                 onerror="this.src='../assets/images/vehicles/default.jpg'">
                            <span class="vehicle-brand"><?php echo htmlspecialchars($mobil['merek']); ?></span>
                            <span class="vehicle-status <?php echo $mobil['status'] === 'tersedia' ? 'status-tersedia' : 'status-disewa'; ?>">
                                <?php echo $mobil['status'] === 'tersedia' ? 'Tersedia' : 'Disewa'; ?>
                            </span>
                        </div>
                        <div class="vehicle-dots" aria-hidden="true">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    </div>
                        <div class="vehicle-info">
                            <h4 class="vehicle-name"><?php echo htmlspecialchars($mobil['nama']); ?></h4>
                            <p class="vehicle-model"><?php echo htmlspecialchars($mobil['model']); ?></p>
                            <div class="vehicle-specs">
                                <span class="spec-item"><?php echo $mobil['tahun']; ?></span>
                                <span class="spec-item"><?php echo $mobil['kapasitas']; ?> Kursi</span>
                                <span class="spec-item"><?php echo ucfirst($mobil['transmisi']); ?></span>
                                <span class="spec-item"><?php echo ucfirst($mobil['bahan_bakar']); ?></span>
                            </div>
                            <div class="vehicle-price">
                                <div class="price">Rp <?php echo number_format($mobil['harga_per_hari'], 0, ',', '.'); ?><span>/hari</span></div>
                                <?php if ($is_logged_in): ?>
                                    <?php if ($mobil['status'] === 'tersedia'): ?>
                                        <a href="user/booking-process.php?vehicle_id=<?php echo $mobil['id']; ?>" class="btn-book">Pesan</a>
                                    <?php else: ?>
                                        <a href="#" class="btn-book disabled" aria-disabled="true" title="Kendaraan tidak tersedia">Pesan</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php?redirect=vehicles" class="btn-book">Login</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
    </section>
    
    <footer>
        <p>© 2024 EzRent. All rights reserved.</p>
    </footer>
    
    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            // Scroll progress bar
            const scrollProgress = document.getElementById('scrollProgress');
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;
            scrollProgress.style.width = scrollPercent + '%';
        });
        
        // Scroll Reveal Animation
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
        
        // Observe elements for animation
        document.querySelectorAll('.vehicle-card, .category-title, .filter-header, .filter-buttons').forEach(el => {
            observer.observe(el);
        });
        
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const vehicleCards = document.querySelectorAll('.vehicle-card');
            const categories = document.querySelectorAll('.vehicle-category');
            const searchInput = document.getElementById('searchInput');
            
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    
                    if (filter === 'all') {
                        categories.forEach(cat => cat.style.display = 'block');
                        vehicleCards.forEach(card => card.style.display = 'block');
                    } else if (filter === 'motor' || filter === 'mobil') {
                        categories.forEach(cat => {
                            cat.style.display = cat.dataset.category === filter ? 'block' : 'none';
                        });
                        vehicleCards.forEach(card => card.style.display = 'block');
                    } else if (filter === 'tersedia') {
                        categories.forEach(cat => cat.style.display = 'block');
                        vehicleCards.forEach(card => {
                            card.style.display = card.dataset.status === 'tersedia' ? 'block' : 'none';
                        });
                    }
                });
            });
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                vehicleCards.forEach(card => {
                    const name = card.dataset.name;
                    if (name.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
    <script>
        (function() {
            const canvas = document.getElementById('particleCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            let particles = [];
            let w = 0, h = 0;

            function resize() {
                const dpr = window.devicePixelRatio || 1;
                w = canvas.clientWidth || window.innerWidth;
                h = canvas.clientHeight || Math.max(window.innerHeight * 0.8, 400);
                canvas.width = Math.floor(w * dpr);
                canvas.height = Math.floor(h * dpr);
                canvas.style.width = w + 'px';
                canvas.style.height = h + 'px';
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            }

            function rand(min, max) { return Math.random() * (max - min) + min; }

            function createParticles(count) {
                particles = [];
                for (let i = 0; i < count; i++) {
                    particles.push({
                        x: rand(0, w),
                        y: rand(0, h),
                        vx: rand(-0.15, 0.15),
                        vy: rand(-0.15, 0.15),
                        r: rand(1.2, 3.2),
                        alpha: rand(0.4, 0.95),
                        drift: rand(0.001, 0.01)
                    });
                }
            }

            function update() {
                for (let p of particles) {
                    p.x += p.vx;
                    p.y += p.vy + Math.sin(Date.now() * p.drift) * 0.15;

                    if (p.x < -10) p.x = w + 10;
                    if (p.x > w + 10) p.x = -10;
                    if (p.y < -10) p.y = h + 10;
                    if (p.y > h + 10) p.y = -10;
                }
            }

            function draw() {
                ctx.clearRect(0, 0, w, h);

                for (let p of particles) {
                    ctx.beginPath();
                    ctx.fillStyle = `rgba(213,20,20,${p.alpha})`;
                    ctx.shadowColor = 'rgba(213,20,20,0.6)';
                    ctx.shadowBlur = 6;
                    ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.shadowBlur = 0;
                }
            }

            let rafId;
            function loop() {
                update();
                draw();
                rafId = requestAnimationFrame(loop);
            }

            function start() {
                resize();
                const base = Math.max(28, Math.floor((w * h) / 30000));
                const count = window.innerWidth < 768 ? Math.max(18, Math.floor(base / 2)) : base;
                createParticles(count);
                if (rafId) cancelAnimationFrame(rafId);
                loop();
            }

            window.addEventListener('resize', function() {
                // debounce
                clearTimeout(window.__particle_resize);
                window.__particle_resize = setTimeout(start, 150);
            });

            // Start when DOM ready
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                start();
            } else {
                document.addEventListener('DOMContentLoaded', start);
            }
        })();
    </script>
</body>
</html>