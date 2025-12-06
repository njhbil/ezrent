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

$user_name = $_SESSION['nama_lengkap'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

// Koneksi Database
require_once '../../php/config/database.php';

$page_title = "Kendaraan - EzRent";

// Ambil data motor dari database (ID 1-20)
$stmt_motor = $pdo->prepare("SELECT * FROM vehicles WHERE jenis = 'motor' ORDER BY id ASC");
$stmt_motor->execute();
$motors = $stmt_motor->fetchAll(PDO::FETCH_ASSOC);

// Ambil data mobil dari database (ID 21+)
$stmt_mobil = $pdo->prepare("SELECT * FROM vehicles WHERE jenis = 'mobil' ORDER BY id ASC");
$stmt_mobil->execute();
$mobils = $stmt_mobil->fetchAll(PDO::FETCH_ASSOC);

include 'header.php'; 
?>

<style>
    /* Hero Section */
    .hero {
        position: relative;
        min-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
        width: 100%;
        margin-top: -76px;
    }
    
    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('https://images.unsplash.com/photo-1583121274602-3e2820c69888?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        z-index: 1;
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
    
    /* Particle Canvas */
    .particle-canvas {
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
        padding: 8rem 2rem 4rem;
        max-width: 800px;
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
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 300;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.3s;
    }
    
    .hero h1 strong {
        font-weight: 700;
        color: #d50000;
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
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s ease forwards 0.1s;
    }
    
    .hero p {
        color: rgba(255,255,255,0.8);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.8;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.5s;
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
    
    @keyframes heroZoom {
        0% { transform: scale(1); }
        100% { transform: scale(1.1); }
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Scroll Reveal */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Vehicle Card Animation */
    .vehicle-card {
        background: #111;
        border: 1px solid rgba(255,255,255,0.1);
        overflow: hidden;
        transition: all 0.4s ease, opacity 0.6s ease, transform 0.6s ease;
        position: relative;
        border-radius: 12px;
        opacity: 0;
        transform: translateY(30px);
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

    /* Filter Section */
    .filter-section {
        background: #0a0a0a;
        padding: 3rem 20px;
        width: 100%;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .filter-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .filter-header {
        text-align: center;
        margin-bottom: 2rem;
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
        transition: all 0.3s ease;
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
    
    .search-input::placeholder {
        color: rgba(255,255,255,0.5);
    }
    
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
        width: 100%;
        min-height: 60vh;
    }
    
    .vehicles-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .category-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 2px;
        background: #d50000;
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
    
    .vehicle-card:hover::before {
        height: 100%;
    }
    
    .vehicle-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        border-color: rgba(213,0,0,0.3);
    }
    
    .vehicle-card.booked {
        opacity: 0.7;
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
    
    .status-tersedia {
        background: rgba(34, 197, 94, 0.9);
        color: #fff;
    }
    
    .status-disewa {
        background: rgba(239, 68, 68, 0.9);
        color: #fff;
    }
    
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
    
    .vehicle-info {
        padding: 1.5rem;
    }
    
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
        gap: 1rem;
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
        background: #d50000;
        color: #fff;
        border: none;
        padding: 0.7rem 1.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        border-radius: 6px;
    }
    
    .btn-book:hover {
        background: #b71c1c;
        transform: translateY(-2px);
        color: #fff;
    }
    
    .btn-book:disabled, .btn-book.disabled {
        background: #444;
        cursor: not-allowed;
        transform: none;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: rgba(255,255,255,0.6);
    }
    
    .empty-state h3 {
        color: #fff;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .hero h1 { font-size: 1.75rem; }
        .vehicles-grid { grid-template-columns: 1fr; }
        .filter-buttons { gap: 0.5rem; }
        .filter-btn { padding: 0.6rem 1.25rem; font-size: 0.8rem; }
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <canvas id="particleCanvas" class="particle-canvas"></canvas>
    <div class="hero-content">
        <h1>Pilih <strong>Kendaraan Anda</strong></h1>
        <p>Koleksi kendaraan EzRent berkualitas premium dengan harga terbaik. Semua kendaraan terawat dengan standar tertinggi.</p>
    </div>
</section>

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
                <?php if (empty($motors)): ?>
                <div class="empty-state">
                    <h3>Belum ada motor tersedia</h3>
                    <p>Silakan cek kembali nanti</p>
                </div>
                <?php else: ?>
                <?php foreach ($motors as $motor): 
                    // Parse images JSON
                    $images = json_decode($motor['images'], true);
                    $image = isset($images[0]) ? $images[0] : 'default.jpg';
                ?>
                <div class="vehicle-card <?php echo $motor['status'] !== 'tersedia' ? 'booked' : ''; ?>" 
                     data-name="<?php echo strtolower($motor['nama']); ?>" 
                     data-status="<?php echo $motor['status']; ?>">
                    <div class="vehicle-image">
                        <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" 
                             alt="<?php echo htmlspecialchars($motor['nama']); ?>"
                             onerror="this.src='../../assets/images/vehicles/default.jpg'">
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
                            <?php if ($motor['status'] === 'tersedia'): ?>
                            <a href="booking-process.php?vehicle_id=<?php echo $motor['id']; ?>" class="btn-book">Pesan</a>
                            <?php else: ?>
                            <button class="btn-book disabled" disabled>Tidak Tersedia</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobil Section -->
        <div class="vehicle-category" data-category="mobil">
            <h3 class="category-title">Mobil <span class="category-count">(<?php echo count($mobils); ?> unit)</span></h3>
            <div class="vehicles-grid">
                <?php if (empty($mobils)): ?>
                <div class="empty-state">
                    <h3>Belum ada mobil tersedia</h3>
                    <p>Silakan cek kembali nanti</p>
                </div>
                <?php else: ?>
                <?php foreach ($mobils as $mobil): 
                    // Parse images JSON
                    $images = json_decode($mobil['images'], true);
                    $image = isset($images[0]) ? $images[0] : 'default.jpg';
                ?>
                <div class="vehicle-card <?php echo $mobil['status'] !== 'tersedia' ? 'booked' : ''; ?>" 
                     data-name="<?php echo strtolower($mobil['nama']); ?>" 
                     data-status="<?php echo $mobil['status']; ?>">
                    <div class="vehicle-image">
                        <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" 
                             alt="<?php echo htmlspecialchars($mobil['nama']); ?>"
                             onerror="this.src='../../assets/images/vehicles/default.jpg'">
                        <span class="vehicle-brand"><?php echo htmlspecialchars($mobil['merek']); ?></span>
                        <span class="vehicle-status <?php echo $mobil['status'] === 'tersedia' ? 'status-tersedia' : 'status-disewa'; ?>">
                            <?php echo $mobil['status'] === 'tersedia' ? 'Tersedia' : 'Disewa'; ?>
                        </span>
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
                            <?php if ($mobil['status'] === 'tersedia'): ?>
                            <a href="booking-process.php?vehicle_id=<?php echo $mobil['id']; ?>" class="btn-book">Pesan</a>
                            <?php else: ?>
                            <button class="btn-book disabled" disabled>Tidak Tersedia</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const vehicleCards = document.querySelectorAll('.vehicle-card');
    const categories = document.querySelectorAll('.vehicle-category');
    const searchInput = document.getElementById('searchInput');
    
    // Filter functionality
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
    
    document.querySelectorAll('.vehicle-card, .category-title, .reveal').forEach(el => {
        scrollObserver.observe(el);
    });
    
    // Parallax effect on hero
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        const heroBg = document.querySelector('.hero-bg');
        
        if (hero && scrolled < hero.offsetHeight) {
            heroBg.style.transform = `scale(${1 + scrolled * 0.0003}) translateY(${scrolled * 0.3}px)`;
        }
    });
    
    // Particle Animation
    const canvas = document.getElementById('particleCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        const particleCount = 80;
        
        function resizeCanvas() {
            const hero = document.querySelector('.hero');
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
