<?php 
$page_title = "Tentang Kami - EzRent";
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
        line-height: 1.7;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.5s;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
    
    .reveal-left {
        opacity: 0;
        transform: translateX(-40px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .reveal-left.visible {
        opacity: 1;
        transform: translateX(0);
    }
    
    .reveal-right {
        opacity: 0;
        transform: translateX(40px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .reveal-right.visible {
        opacity: 1;
        transform: translateX(0);
    }
    
    .stagger-1 { transition-delay: 0.1s; }
    .stagger-2 { transition-delay: 0.2s; }
    .stagger-3 { transition-delay: 0.3s; }
    .stagger-4 { transition-delay: 0.4s; }
    
    /* Section Headers */
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
    
    /* Value Cards */
    .value-card {
        text-align: center;
        padding: 2.5rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        border: 2px solid transparent;
    }
    
    .value-card:hover {
        transform: translateY(-8px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.1);
    }
    
    .value-icon {
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
    
    /* Stat Cards */
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
    
    /* Team Cards */
    .team-card {
        text-align: center;
        padding: 2rem 1.5rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        border: 2px solid transparent;
    }
    
    .team-card:hover {
        transform: translateY(-8px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.1);
    }
    
    .team-card:hover .team-photo {
        transform: scale(1.05);
        box-shadow: 0 8px 30px rgba(213, 0, 0, 0.2);
    }
    
    .team-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 0 auto 1.25rem;
        overflow: hidden;
        border: 3px solid #f8fafc;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
    }
    
    .team-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
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
    
    .cta-btn {
        background: white;
        color: #d50000;
        text-decoration: none;
        padding: 0.875rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }
</style>

<main style="flex: 1;">
    <!-- About Hero Section -->
    <section class="hero">
        <div class="container" style="position: relative; z-index: 1;">
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <div class="hero-badge">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Sejak 2020
                </div>
                <h1>Tentang EzRent</h1>
                <p>
                    Menyediakan solusi sewa kendaraan terbaik dengan komitmen terhadap kualitas dan kepuasan pelanggan
                </p>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section style="padding: 5rem 0;">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
                <!-- Vision -->
                <div class="value-card reveal-left stagger-1">
                    <div class="value-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                    </div>
                    <h2 style="color: #0a0a0a; margin-bottom: 1.25rem; font-size: 1.75rem; font-weight: 600;">Visi Kami</h2>
                    <p style="color: #64748b; line-height: 1.7; font-size: 1.05rem;">
                        Menjadi platform sewa kendaraan terdepan yang memberikan pengalaman terbaik bagi pelanggan 
                        dengan layanan yang cepat, aman, dan terjangkau.
                    </p>
                </div>
                
                <!-- Mission -->
                <div class="value-card reveal-right stagger-2">
                    <div class="value-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <h2 style="color: #0a0a0a; margin-bottom: 1.25rem; font-size: 1.75rem; font-weight: 600;">Misi Kami</h2>
                    <p style="color: #64748b; line-height: 1.7; font-size: 1.05rem;">
                        Menyediakan berbagai pilihan kendaraan berkualitas dengan proses pemesanan yang mudah, 
                        transparan, dan didukung oleh teknologi terkini.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- History & Values Section -->
    <section style="background: #fafafa; padding: 5rem 0;">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
                <!-- History -->
                <div class="value-card reveal-left stagger-1">
                    <div class="value-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <h2 style="color: #0a0a0a; margin-bottom: 1.25rem; font-size: 1.75rem; font-weight: 600;">Sejarah Kami</h2>
                    <p style="color: #64748b; line-height: 1.7; font-size: 1.05rem;">
                        EzRent didirikan pada tahun 2020 dengan tujuan memudahkan masyarakat dalam menyewa kendaraan 
                        untuk berbagai kebutuhan. Dari awal yang sederhana, kami telah berkembang menjadi platform 
                        terpercaya dengan ribuan pelanggan setia.
                    </p>
                </div>
                
                <!-- Values -->
                <div class="value-card reveal-right stagger-2">
                    <div class="value-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <h2 style="color: #0a0a0a; margin-bottom: 1.25rem; font-size: 1.75rem; font-weight: 600;">Nilai Kami</h2>
                    <p style="color: #64748b; line-height: 1.7; font-size: 1.05rem;">
                        Kepercayaan, kualitas, dan kepuasan pelanggan adalah nilai utama yang kami junjung 
                        dalam setiap layanan. Kami berkomitmen untuk memberikan pengalaman terbaik dalam 
                        setiap transaksi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section style="padding: 5rem 0;">
        <div class="container">
            <div class="section-header reveal">
                <h2>EzRent dalam Angka</h2>
                <p>Bukti komitmen kami dalam memberikan layanan terbaik</p>
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
                    <div class="stat-number" data-target="4">0</div>
                    <div class="stat-label">Tahun Pengalaman</div>
                </div>
                <div class="stat-card reveal stagger-4">
                    <div class="stat-number" data-target="24">0</div>
                    <div class="stat-label">Jam Support</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section style="background: #fafafa; padding: 5rem 0;">
        <div class="container">
            <div class="section-header reveal">
                <h2>Tim Kami</h2>
                <p>Dibackup oleh tim profesional yang berdedikasi untuk memberikan layanan terbaik</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                <!-- Anggota 1: CEO & Founder -->
                <div class="team-card reveal stagger-1">
                    <div class="team-photo">
                        <img src="../assets/images/team/budi-santoso.jpg" alt="Dimas Abdus Syukur">
                    </div>
                    <h3 style="margin-bottom: 0.5rem; color: #0a0a0a; font-size: 1.1rem; font-weight: 600;">Dimas Abdus Syukur</h3>
                    <p style="color: #d50000; font-weight: 500; margin-bottom: 0.75rem; font-size: 0.9rem;">CEO & Founder</p>
                    <p style="color: #64748b; line-height: 1.5; font-size: 0.85rem;">
                        Memimpin visi dan strategi perusahaan untuk pertumbuhan berkelanjutan
                    </p>
                </div>

                <!-- Anggota 2: Head of Operations -->
                <div class="team-card reveal stagger-2">
                    <div class="team-photo">
                        <img src="../assets/images/team/sari-dewi.jpg" alt="Nabil Akbar">
                    </div>
                    <h3 style="margin-bottom: 0.5rem; color: #0a0a0a; font-size: 1.1rem; font-weight: 600;">Nabil Akbar</h3>
                    <p style="color: #d50000; font-weight: 500; margin-bottom: 0.75rem; font-size: 0.9rem;">Head of Operations</p>
                    <p style="color: #64748b; line-height: 1.5; font-size: 0.85rem;">
                        Mengelola operasional harian dan memastikan kualitas layanan terbaik
                    </p>
                </div>

                <!-- Anggota 3: CTO -->
                <div class="team-card reveal stagger-3">
                    <div class="team-photo">
                        <img src="../assets/images/team/rizki-pratama.jpg" alt="Muhammad Fathur Arslan">
                    </div>
                    <h3 style="margin-bottom: 0.5rem; color: #0a0a0a; font-size: 1.1rem; font-weight: 600;">Muhammad Fathur Arslan</h3>
                    <p style="color: #d50000; font-weight: 500; margin-bottom: 0.75rem; font-size: 0.9rem;">CTO</p>
                    <p style="color: #64748b; line-height: 1.5; font-size: 0.85rem;">
                        Mengembangkan teknologi inovatif untuk pengalaman pengguna optimal
                    </p>
                </div>

                <!-- Anggota 4: Marketing Manager -->
                <div class="team-card reveal stagger-4">
                    <div class="team-photo">
                        <img src="../assets/images/team/andi-wijaya.jpg" alt="Muhammad Reza Yudistira">
                    </div>
                    <h3 style="margin-bottom: 0.5rem; color: #0a0a0a; font-size: 1.1rem; font-weight: 600;">Muhammad Reza Yudistira</h3>
                    <p style="color: #d50000; font-weight: 500; margin-bottom: 0.75rem; font-size: 0.9rem;">Marketing Manager</p>
                    <p style="color: #64748b; line-height: 1.5; font-size: 0.85rem;">
                        Membangun strategi pemasaran untuk menjangkau pelanggan potensial
                    </p>
                </div>
            </div>

            <!-- Team Stats -->
            <div style="margin-top: 3rem; text-align: center;" class="reveal">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; max-width: 800px; margin: 0 auto;">
                    <div class="stat-card">
                        <div style="font-size: 2rem; font-weight: 700; color: #d50000; margin-bottom: 0.5rem;">4</div>
                        <div style="color: #0a0a0a; font-weight: 600; font-size: 0.9rem;">Anggota Tim</div>
                    </div>
                    <div class="stat-card">
                        <div style="font-size: 2rem; font-weight: 700; color: #d50000; margin-bottom: 0.5rem;">25+</div>
                        <div style="color: #0a0a0a; font-weight: 600; font-size: 0.9rem;">Tahun Pengalaman</div>
                    </div>
                    <div class="stat-card">
                        <div style="font-size: 2rem; font-weight: 700; color: #d50000; margin-bottom: 0.5rem;">100%</div>
                        <div style="color: #0a0a0a; font-weight: 600; font-size: 0.9rem;">Dedikasi</div>
                    </div>
                    <div class="stat-card">
                        <div style="font-size: 2rem; font-weight: 700; color: #d50000; margin-bottom: 0.5rem;">24/7</div>
                        <div style="color: #0a0a0a; font-weight: 600; font-size: 0.9rem;">Support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" style="padding: 5rem 0;">
        <div class="container" style="position: relative; z-index: 1;">
            <div style="text-align: center; color: white;" class="reveal">
                <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">
                    Siap Bergabung dengan EzRent?
                </h2>
                <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.9;">
                    Mulai perjalanan Anda dengan kepercayaan dan kenyamanan
                </p>
                <a href="vehicles.php" class="cta-btn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    Lihat Kendaraan Tersedia
                </a>
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
    
    document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
        scrollObserver.observe(el);
    });
    
    // Counter Animation
    function animateCounter(element) {
        const target = parseFloat(element.dataset.target);
        if (!target) return;
        
        const duration = 2000;
        const startTime = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = target * easeOut;
            
            if (target >= 1000) {
                element.textContent = Math.floor(current).toLocaleString() + '+';
            } else if (target === 24) {
                element.textContent = Math.floor(current) + '/7';
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
    @media (max-width: 1024px) {
        .team-grid,
        section > .container > div[style*="grid-template-columns: repeat(4"] {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.5rem !important;
        }
        
        .hero p {
            font-size: 1rem;
        }
        
        section > .container > div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        
        section > .container > div[style*="grid-template-columns: repeat(4"] {
            grid-template-columns: 1fr !important;
        }
        
        .section-header h2 {
            font-size: 2rem;
        }
    }
</style>

<?php include '../../php/includes/footer.php'; ?>