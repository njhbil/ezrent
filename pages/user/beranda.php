<?php 
$page_title = "Beranda - EzRent";
include 'header.php'; 
?>

<style>
    /* Hero Animations */
    .hero {
        position: relative;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
        padding: 8rem 0 6rem;
        overflow: hidden;
    }
    
    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.4;
        pointer-events: none;
        animation: patternFloat 20s linear infinite;
    }
    
    .hero::after {
        content: '';
        position: absolute;
        bottom: -50%;
        right: -20%;
        width: 80%;
        height: 100%;
        background: radial-gradient(ellipse, rgba(213, 0, 0, 0.15) 0%, transparent 60%);
        pointer-events: none;
    }
    
    @keyframes patternFloat {
        0% { transform: translate(0, 0); }
        50% { transform: translate(-10px, 10px); }
        100% { transform: translate(0, 0); }
    }
    
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(213, 0, 0, 0.15);
        border: 1px solid rgba(213, 0, 0, 0.3);
        color: #d50000;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s ease forwards 0.1s;
    }
    
    .hero h1 {
        font-size: 3.5rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        color: #ffffff;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.3s;
    }
    
    .hero p {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 2.5rem;
        line-height: 1.7;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.5s;
    }
    
    .welcome-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 2.5rem;
        border-radius: 16px;
        max-width: 650px;
        margin: 0 auto;
        opacity: 0;
        transform: translateY(30px) scale(0.95);
        animation: fadeInScale 0.8s ease forwards 0.7s;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInScale {
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .welcome-card h3 {
        color: #ffffff;
        margin-bottom: 0.75rem;
        font-size: 1.5rem;
    }
    
    .welcome-card .user-name {
        color: #d50000;
    }
    
    .welcome-card p {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 2rem;
        font-size: 1rem;
        animation: none;
        opacity: 1;
        transform: none;
    }
    
    .hero-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .hero-btn {
        padding: 0.875rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .hero-btn-primary {
        background: linear-gradient(135deg, #d50000 0%, #ff1744 100%);
        color: white;
        box-shadow: 0 4px 20px rgba(213, 0, 0, 0.4);
    }
    
    .hero-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(213, 0, 0, 0.5);
    }
    
    .hero-btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .hero-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-3px);
    }
    
    /* Scroll Reveal Animations */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    .reveal-scale {
        opacity: 0;
        transform: scale(0.9);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .reveal-scale.visible {
        opacity: 1;
        transform: scale(1);
    }
    
    .stagger-1 { transition-delay: 0.1s; }
    .stagger-2 { transition-delay: 0.2s; }
    .stagger-3 { transition-delay: 0.3s; }
    .stagger-4 { transition-delay: 0.4s; }
    
    /* Feature Cards */
    .feature-card {
        text-align: center;
        padding: 2.5rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 2px solid transparent;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .feature-card:hover {
        transform: translateY(-8px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.1);
    }
    
    .feature-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.1) 0%, rgba(213, 0, 0, 0.05) 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #d50000;
    }
    
    /* Quick Action Cards */
    .action-card {
        text-decoration: none;
        display: block;
    }
    
    .action-card-inner {
        text-align: center;
        padding: 3rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        height: 100%;
    }
    
    .action-card:hover .action-card-inner {
        transform: translateY(-8px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.15);
    }
    
    .action-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.1) 0%, rgba(213, 0, 0, 0.05) 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #d50000;
        transition: all 0.4s ease;
    }
    
    .action-card:hover .action-icon {
        background: linear-gradient(135deg, #d50000 0%, #ff1744 100%);
        color: white;
        transform: scale(1.1);
    }
    
    /* CTA Section */
    .cta-section {
        background: linear-gradient(135deg, #d50000 0%, #ff1744 50%, #d50000 100%);
        position: relative;
        overflow: hidden;
    }
    
    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        pointer-events: none;
    }
    
    /* Stats Section */
    .stat-card {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #d50000;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #64748b;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Section headers */
    .section-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .section-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0a0a0a;
        margin-bottom: 1rem;
    }
    
    .section-header p {
        font-size: 1.125rem;
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
    }
</style>

<main style="flex: 1;">
    <!-- Hero Section -->
    <section class="hero">
        <div class="container" style="position: relative; z-index: 1;">
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <div class="hero-badge">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Member Aktif
                </div>
                <h1>Selamat Datang di EzRent!</h1>
                <p>
                    Temukan berbagai pilihan kendaraan berkualitas untuk kebutuhan perjalanan Anda. 
                    Mulai dari mobil, motor, hingga sepeda listrik dengan harga terjangkau.
                </p>
                
                <!-- Tampilan untuk user yang sudah login -->
                <div class="welcome-card">
                    <h3>
                        Halo, <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>!
                    </h3>
                    <p>
                        Akses penuh telah aktif. Anda dapat menikmati semua fitur pemesanan kendaraan.
                    </p>
                    <div class="hero-buttons">
                        <a href="../vehicles.php" class="hero-btn hero-btn-primary">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                            Sewa Kendaraan
                        </a>
                        <a href="my-bookings.php" class="hero-btn hero-btn-secondary">
                            Lihat Pesanan
                        </a>
                        <a href="profile.php" class="hero-btn hero-btn-secondary">
                            Profil Saya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section style="padding: 5rem 0; background: #fafafa;">
        <div class="container">
            <div class="section-header reveal">
                <h2>Fitur Eksklusif Member</h2>
                <p>Nikmati berbagai keuntungan sebagai member EzRent</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <div class="feature-card reveal stagger-1">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Akses Penuh</h3>
                    <p style="color: #64748b; line-height: 1.6;">Akses semua kendaraan dan fitur pemesanan tanpa batas</p>
                </div>
                <div class="feature-card reveal stagger-2">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Harga Special</h3>
                    <p style="color: #64748b; line-height: 1.6;">Diskon khusus member untuk penyewaan jangka panjang</p>
                </div>
                <div class="feature-card reveal stagger-3">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    </div>
                    <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Proses Instan</h3>
                    <p style="color: #64748b; line-height: 1.6;">Pemesanan cepat dengan data yang sudah tersimpan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions Section -->
    <section style="padding: 5rem 0;">
        <div class="container">
            <div class="section-header reveal">
                <h2>Akses Cepat</h2>
                <p>Mulai jelajahi fitur-fitur yang tersedia untuk Anda</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <a href="../vehicles.php" class="action-card reveal stagger-1">
                    <div class="action-card-inner">
                        <div class="action-icon">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11"></path><path d="M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
                        </div>
                        <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Sewa Kendaraan</h3>
                        <p style="color: #64748b; line-height: 1.6;">Temukan kendaraan perfect untuk kebutuhan Anda</p>
                    </div>
                </a>
                <a href="my-bookings.php" class="action-card reveal stagger-2">
                    <div class="action-card-inner">
                        <div class="action-icon">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        </div>
                        <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Pesanan Saya</h3>
                        <p style="color: #64748b; line-height: 1.6;">Lihat dan kelola semua pesanan aktif Anda</p>
                    </div>
                </a>
                <a href="profile.php" class="action-card reveal stagger-3">
                    <div class="action-card-inner">
                        <div class="action-icon">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Profil Saya</h3>
                        <p style="color: #64748b; line-height: 1.6;">Kelola informasi akun dan data pribadi</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" style="padding: 5rem 0;">
        <div class="container" style="position: relative; z-index: 1;">
            <div style="text-align: center; color: white;" class="reveal">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                    Siap Memulai Perjalanan?
                </h2>
                <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.9;">
                    Jelajahi katalog kendaraan kami dan pesan sekarang juga!
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                    <a href="bookings.php" style="background: white; color: #d50000; text-decoration: none; padding: 0.875rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                        Lihat Kendaraan Tersedia
                    </a>
                    <a href="about.php" style="border: 2px solid white; color: white; text-decoration: none; padding: 0.875rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem;">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section style="padding: 5rem 0; background: #fafafa;">
        <div class="container">
            <div class="section-header reveal">
                <h2>EzRent dalam Angka</h2>
                <p>Bergabunglah dengan komunitas pengguna yang puas</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <div class="stat-card reveal stagger-1">
                    <div class="stat-number" data-target="500">0</div>
                    <div class="stat-label">Kendaraan</div>
                </div>
                <div class="stat-card reveal stagger-2">
                    <div class="stat-number" data-target="10000">0</div>
                    <div class="stat-label">Pelanggan</div>
                </div>
                <div class="stat-card reveal stagger-3">
                    <div class="stat-number" data-target="4.8" data-decimal="true">0</div>
                    <div class="stat-label">Rating</div>
                </div>
                <div class="stat-card reveal stagger-4">
                    <div class="stat-number" data-target="24">0</div>
                    <div class="stat-label">Jam Support</div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll Reveal Observer
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Counter animation for stat numbers
                if (entry.target.classList.contains('stat-card')) {
                    const statNumber = entry.target.querySelector('.stat-number');
                    if (statNumber && !statNumber.classList.contains('counted')) {
                        statNumber.classList.add('counted');
                        animateCounter(statNumber);
                    }
                }
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    document.querySelectorAll('.reveal, .reveal-scale').forEach(el => {
        scrollObserver.observe(el);
    });
    
    // Counter Animation
    function animateCounter(element) {
        const target = parseFloat(element.dataset.target);
        const isDecimal = element.dataset.decimal === 'true';
        const duration = 2000;
        const startTime = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = target * easeOut;
            
            if (isDecimal) {
                element.textContent = current.toFixed(1) + '/5';
            } else if (target >= 1000) {
                element.textContent = Math.floor(current).toLocaleString() + '+';
            } else {
                element.textContent = Math.floor(current) + '+';
            }
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }
    
    // Parallax effect on hero
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero && scrolled < hero.offsetHeight) {
            hero.style.transform = `translateY(${scrolled * 0.3}px)`;
        }
    });
});
</script>

<style>
    /* Responsive design */
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.5rem !important;
        }
        
        .hero p {
            font-size: 1rem;
        }
        
        .welcome-card {
            padding: 1.5rem;
        }
        
        .section-header h2 {
            font-size: 2rem;
        }
        
        .hero-buttons {
            flex-direction: column;
        }
        
        .hero-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<?php include '../../php/includes/footer.php'; ?>