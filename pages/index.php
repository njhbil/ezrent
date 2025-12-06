<?php 
// Start session dan cek login SEBELUM output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $user_role = $_SESSION['role'] ?? '';
    
    if ($user_role === 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    } else {
        header("Location: user/dashboard.php");
        exit();
    }
}

// Koneksi database untuk ambil kendaraan
require_once '../php/config/database.php';

// Ambil 4 motor dan 4 mobil dari database
$motors = [];
$mobils = [];
try {
    $stmt = $pdo->prepare("SELECT id, nama, merek, harga_per_hari, images FROM vehicles WHERE jenis = 'motor' AND status = 'tersedia' ORDER BY id LIMIT 4");
    $stmt->execute();
    $motors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT id, nama, merek, harga_per_hari, images FROM vehicles WHERE jenis = 'mobil' AND status = 'tersedia' ORDER BY id LIMIT 4");
    $stmt->execute();
    $mobils = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle silently
}

$page_title = "EzRent - Sewa Kendaraan Mudah & Terpercaya";
include '../php/includes/header.php'; 
?>

<main>
    <!-- Video Hero Section -->
    <section class="video-hero" style="position: relative; height: 100vh !important; width: 100% !important; margin-top: 0;">
        <div class="video-container" style="position: absolute; top: 0; left: 0; width: 100% !important; height: 100% !important; z-index: 1;">
            <video autoplay muted loop playsinline class="hero-video" style="width: 100% !important; height: 100% !important; object-fit: cover;">
                <source src="../assets/video/Black and Blue Modern Simple Car Dealer Presentation.mp4" type="video/mp4">
            </video>
            <div class="video-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.5)); z-index: 2;"></div>
        </div>
        
        <div class="hero-content" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; display: flex; align-items: center; justify-content: flex-start; padding-left: 10%;">
            <div class="hero-text" style="text-align: left; color: white; max-width: 600px;">
                <h1 class="hero-title" style="font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 300; line-height: 1.2; margin-bottom: 2rem; letter-spacing: -0.02em;">
                    Kemewahan dan Kenyamanan<br>
                    <span style="font-weight: 600;">dalam Setiap Perjalanan</span>
                </h1>
                <a href="login.php" class="btn-hero btn-hero-secondary" style="display: inline-block; padding: 1rem 2.5rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; text-transform: uppercase; letter-spacing: 0.1em; background: transparent; color: white; border: 2px solid white;">Lebih Lanjut</a>
            </div>
        </div>
        
        <div class="scroll-indicator">
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" style="padding: 6rem 0; background: #fafafa; position: relative; overflow: hidden;">
        <!-- Texture pattern -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cpath d=&quot;M30 0L30 60M0 30L60 30&quot; stroke=&quot;rgba(0,0,0,0.03)&quot; stroke-width=&quot;1&quot; fill=&quot;none&quot;/%3E%3C/svg%3E'); pointer-events: none;"></div>
        <!-- Corner accents -->
        <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; border: 1px dashed rgba(213,0,0,0.1); border-radius: 50%; pointer-events: none;"></div>
        <div style="position: absolute; bottom: -80px; left: -80px; width: 250px; height: 250px; border: 1px dashed rgba(0,0,0,0.05); border-radius: 50%; pointer-events: none;"></div>
        
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 1;">
            <div style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 500; color: #000; margin-bottom: 1rem; letter-spacing: -0.02em;">Mengapa Memilih EzRent?</h2>
                <p style="font-size: 1.1rem; color: #6b7280; font-weight: 300;">Pengalaman sewa kendaraan yang tak tertandingi</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
                <div style="text-align: center; padding: 3rem 2rem; background: #fff; border: 1px solid #e5e7eb; transition: all 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; border: 2px solid #000; border-radius: 50%;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #000; margin-bottom: 1rem;">Terjamin & Aman</h3>
                    <p style="color: #6b7280; line-height: 1.7; font-weight: 300; font-size: 0.95rem;">Dukungan 24/7 dan perlindungan asuransi komprehensif untuk ketenangan Anda.</p>
                </div>
                <div style="text-align: center; padding: 3rem 2rem; background: #fff; border: 1px solid #e5e7eb; transition: all 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; border: 2px solid #000; border-radius: 50%;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.5">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #000; margin-bottom: 1rem;">Harga Transparan</h3>
                    <p style="color: #6b7280; line-height: 1.7; font-weight: 300; font-size: 0.95rem;">Harga kompetitif tanpa biaya tersembunyi. Apa yang Anda lihat adalah apa yang Anda bayar.</p>
                </div>
                <div style="text-align: center; padding: 3rem 2rem; background: #fff; border: 1px solid #e5e7eb; transition: all 0.3s ease;">
                    <div style="width: 70px; height: 70px; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; border: 2px solid #000; border-radius: 50%;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.5">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #000; margin-bottom: 1rem;">Proses Cepat</h3>
                    <p style="color: #6b7280; line-height: 1.7; font-weight: 300; font-size: 0.95rem;">Pemesanan online dalam hitungan menit. Tanpa antri, tanpa ribet.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Vehicles Preview Section -->
    <section class="vehicles-preview" style="position: relative; padding: 6rem 0; background: #0a0a0a; overflow: hidden;">
        <!-- Dot pattern texture -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Ccircle cx=&quot;20&quot; cy=&quot;20&quot; r=&quot;1&quot; fill=&quot;rgba(255,255,255,0.04)&quot;/%3E%3C/svg%3E'); pointer-events: none; z-index: 0;"></div>
        <!-- Background decorations -->
        <div class="section-bg-decor" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;">
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 200px; background: linear-gradient(180deg, rgba(255,255,255,0.02) 0%, transparent 100%);"></div>
            <div style="position: absolute; top: 20%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(213,0,0,0.06) 0%, transparent 70%); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: 10%; right: -5%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,255,255,0.02) 0%, transparent 70%); border-radius: 50%;"></div>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);"></div>
            <div style="position: absolute; top: 50%; left: 0; width: 100%; height: 1px; background: linear-gradient(90deg, transparent, rgba(213,0,0,0.1), transparent); transform: translateY(-50%);"></div>
        </div>
        <style>
            .vehicle-card {
                text-decoration: none;
                display: block;
                background: #111;
                overflow: hidden;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 20px rgba(255,255,255,0.05);
                opacity: 0;
                transform: translateY(40px);
                position: relative;
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
            .vehicle-card.visible {
                opacity: 1;
                transform: translateY(0);
            }
            .vehicle-card:hover {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 20px 50px rgba(255,255,255,0.15);
                background: #1a1a1a;
            }
            .vehicle-card:active {
                transform: translateY(-5px) scale(0.98);
            }
            .vehicle-card .card-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .vehicle-card:hover .card-img {
                transform: scale(1.1);
            }
            .vehicle-card .login-arrow {
                transition: all 0.3s ease;
            }
            .vehicle-card:hover .login-arrow {
                transform: translateX(5px);
                color: #d50000;
            }
            .section-title {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.8s ease;
            }
            .section-title.visible {
                opacity: 1;
                transform: translateY(0);
            }
            .category-label {
                position: relative;
                display: inline-block;
                opacity: 0;
                transform: translateX(-20px);
                transition: all 0.6s ease;
            }
            .category-label.visible {
                opacity: 1;
                transform: translateX(0);
            }
            .category-label::after {
                content: '';
                position: absolute;
                bottom: -8px;
                left: 0;
                width: 40px;
                height: 2px;
                background: #d50000;
            }
            .btn-view-all {
                display: inline-block;
                padding: 1rem 3rem;
                font-size: 0.9rem;
                font-weight: 500;
                text-decoration: none;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                background: #fff;
                color: #000;
                border: 2px solid #fff;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            .btn-view-all::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                transition: left 0.5s ease;
            }
            .btn-view-all:hover::before {
                left: 100%;
            }
            .btn-view-all:hover {
                background: transparent;
                color: #fff;
                transform: translateY(-3px);
                box-shadow: 0 10px 30px rgba(255,255,255,0.2);
            }
            .btn-view-all:active {
                transform: translateY(-1px);
            }
            .feature-card {
                opacity: 0;
                transform: translateY(40px);
                transition: all 0.6s ease;
            }
            .feature-card.visible {
                opacity: 1;
                transform: translateY(0);
            }
            .feature-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            }
            /* Scroll progress */
            .scroll-progress-fixed {
                position: fixed;
                top: 50%;
                right: 2rem;
                transform: translateY(-50%);
                z-index: 50;
                opacity: 0;
                transition: opacity 0.3s;
            }
            .scroll-progress-fixed.visible {
                opacity: 1;
            }
            .progress-bar-vertical {
                width: 3px;
                height: 80px;
                background: rgba(255,255,255,0.1);
                border-radius: 3px;
                overflow: hidden;
            }
            .progress-fill {
                width: 100%;
                height: 0%;
                background: linear-gradient(180deg, #d50000 0%, #ff5252 100%);
                border-radius: 3px;
                transition: height 0.1s;
            }
            .vehicle-card:nth-child(1) { transition-delay: 0.1s; }
            .vehicle-card:nth-child(2) { transition-delay: 0.2s; }
            .vehicle-card:nth-child(3) { transition-delay: 0.3s; }
            .vehicle-card:nth-child(4) { transition-delay: 0.4s; }
        </style>
        <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 2;">
            <div class="section-title" style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 500; color: #fff; margin-bottom: 1rem; letter-spacing: -0.02em;">Koleksi Kendaraan Kami</h2>
                <p style="font-size: 1.1rem; color: rgba(255,255,255,0.7); font-weight: 300;">Pilihan terbaik untuk setiap kebutuhan perjalanan</p>
            </div>

            <!-- Motor Section -->
            <div style="margin-bottom: 4rem;">
                <h3 class="category-label" style="font-size: 1.5rem; font-weight: 300; color: #fff; margin-bottom: 2rem; text-transform: uppercase; letter-spacing: 0.1em; padding-bottom: 0.5rem;">Motor</h3>
                <div class="vehicles-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                    <?php foreach ($motors as $motor): 
                        $images = json_decode($motor['images'], true);
                        $image = isset($images[0]) ? $images[0] : 'default.jpg';
                    ?>
                    <a href="login.php" class="vehicle-card">
                        <div style="aspect-ratio: 4/3; overflow: hidden;">
                            <img src="../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($motor['nama']); ?>" class="card-img">
                        </div>
                        <div style="padding: 1.25rem;">
                            <h4 style="color: #fff; font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($motor['nama']); ?></h4>
                            <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($motor['merek']); ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #fff; font-weight: 600;">Rp <?php echo number_format($motor['harga_per_hari'], 0, ',', '.'); ?><span style="font-weight: 300; font-size: 0.85rem;">/hari</span></span>
                                <span class="login-arrow" style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">Login →</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Mobil Section -->
            <div>
                <h3 class="category-label" style="font-size: 1.5rem; font-weight: 300; color: #fff; margin-bottom: 2rem; text-transform: uppercase; letter-spacing: 0.1em; padding-bottom: 0.5rem;">Mobil</h3>
                <div class="vehicles-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                    <?php foreach ($mobils as $mobil): 
                        $images = json_decode($mobil['images'], true);
                        $image = isset($images[0]) ? $images[0] : 'default.jpg';
                    ?>
                    <a href="login.php" class="vehicle-card">
                        <div style="aspect-ratio: 4/3; overflow: hidden;">
                            <img src="../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($mobil['nama']); ?>" class="card-img">
                        </div>
                        <div style="padding: 1.25rem;">
                            <h4 style="color: #fff; font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($mobil['nama']); ?></h4>
                            <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($mobil['merek']); ?></p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #fff; font-weight: 600;">Rp <?php echo number_format($mobil['harga_per_hari'], 0, ',', '.'); ?><span style="font-weight: 300; font-size: 0.85rem;">/hari</span></span>
                                <span class="login-arrow" style="color: rgba(255,255,255,0.5); font-size: 0.8rem;">Login →</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta-area" style="text-align: center; margin-top: 4rem; opacity: 0; transform: translateY(30px); transition: all 0.8s ease;">
                <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">Login untuk melihat semua kendaraan dan detail lengkap</p>
                <a href="login.php" class="btn-view-all">Lihat Semua Kendaraan</a>
            </div>
        </div>
    </section>
</main>

<!-- Scroll Progress Indicator -->
<div class="scroll-progress-fixed">
    <div class="progress-bar-vertical">
        <div class="progress-fill"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll animation observer
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

    // Observe elements
    document.querySelectorAll('.section-title, .category-label, .feature-card, .cta-area').forEach(el => {
        observer.observe(el);
    });

    // Stagger animation for vehicle cards
    const vehicleGrids = document.querySelectorAll('.vehicles-grid');
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const cards = entry.target.querySelectorAll('.vehicle-card');
                cards.forEach((card, index) => {
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, index * 100);
                });
            }
        });
    }, { threshold: 0.1 });

    vehicleGrids.forEach(grid => cardObserver.observe(grid));

    // Feature cards animation
    const featureCards = document.querySelectorAll('.features-section > .container > div:last-child > div');
    featureCards.forEach((card, index) => {
        card.classList.add('feature-card');
        card.style.transitionDelay = (index * 0.15) + 's';
        observer.observe(card);
    });

    // Scroll progress
    const progressFill = document.querySelector('.progress-fill');
    const scrollIndicator = document.querySelector('.scroll-progress-fixed');

    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrolled / maxScroll) * 100;

        // Update progress bar
        if (progressFill) {
            progressFill.style.height = scrollPercent + '%';
        }

        // Show/hide scroll indicator
        if (scrolled > 300) {
            scrollIndicator.classList.add('visible');
        } else {
            scrollIndicator.classList.remove('visible');
        }

        // Parallax for hero video
        const heroVideo = document.querySelector('.hero-video');
        if (heroVideo && scrolled < window.innerHeight) {
            heroVideo.style.transform = 'scale(' + (1 + scrolled * 0.0003) + ')';
        }
    });
});
</script>

<?php include '../php/includes/footer.php'; ?>
