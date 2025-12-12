<?php 
$page_title = "Kontak Kami - EzRent";

// Mulai session
session_start();

// Cek status login
$is_logged_in = isset($_SESSION['user_id']);

require_once '../php/config/database.php'; // Sesuaikan path ini
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }
        
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
            /* Animation dihapus di mobile nanti via media query */
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

        /* Contact Section */
        .contact-section {
            padding: 6rem 2rem;
            background: #fafafa;
            position: relative;
            overflow: hidden;
            color: #000;
        }
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .section-header { text-align: center; margin-bottom: 4rem; }
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
        }
        .section-header h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 300;
            margin-bottom: 1rem;
            color: #000;
        }
        .section-header h2 strong {
            font-weight: 700;
            color: #d50000;
        }
        .section-header p { color: #6b7280; font-size: 1.1rem; max-width: 600px; margin: 0 auto; }
        .section-line {
            width: 80px; height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            margin: 1.5rem auto 0;
        }
        .contact-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }
        
        /* Cards */
        .contact-card {
            background: #fff; border: 1px solid #e5e7eb; padding: 2.5rem 2rem;
            text-align: center; transition: all 0.5s; position: relative; overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .contact-card:hover { transform: translateY(-10px); border-color: #d50000; }
        .contact-card .card-icon {
            width: 70px; height: 70px; margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border: 2px solid #e5e7eb; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .contact-card .card-icon svg { width: 28px; height: 28px; color: #d50000; }
        .contact-card h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem; }
        .contact-card .card-value { color: #d50000; font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; }
        .contact-card .card-desc { color: #6b7280; font-size: 0.85rem; }

        /* Form Section */
        .form-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            position: relative; overflow: hidden;
        }
        .form-container {
            max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;
            position: relative; z-index: 2;
        }
        .form-info {  color: #ffffffff; margin-left: 30px !important; }
        .form-info h2 { font-size: clamp(2rem, 4vw, 3rem); font-weight: 300; margin-bottom: 1.5rem; }
        .form-info h2 strong { font-weight: 700; color: #d50000; }
        .form-info p { color: rgba(255,255,255,0.7); font-size: 1.1rem; margin-bottom: 2rem; }
        
        .info-list { display: flex; flex-direction: column; gap: 1.5rem; }
        .info-item { display: flex; align-items: flex-start; gap: 1rem; }
        .info-item .icon {
            width: 50px; height: 50px; background: rgba(213,0,0,0.1);
            border: 1px solid rgba(213,0,0,0.3); display: flex; align-items: center; justify-content: center;
        }
        .info-item .icon svg { width: 24px; height: 24px; color: #d50000; }
        
        /* Locked Form Styles */
        .form-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 3rem;
            border-radius: 16px;
            position: relative; /* Penting untuk overlay lock */
        }
        
        /* Efek Kunci / Locked */
        .locked-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            z-index: 10;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .lock-icon-container {
            width: 80px; height: 80px;
            background: rgba(213,0,0,0.2);
            border: 2px solid rgba(213,0,0,0.5);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        .lock-icon-container svg { width: 32px; height: 32px; color: #ff3333; }
        .locked-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #fff; }
        .locked-desc { color: rgba(255,255,255,0.7); margin-bottom: 2rem; max-width: 80%; }
        
        .auth-buttons { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; }
        .btn-login-lock {
            background: #d50000; color: #fff; padding: 0.8rem 2rem; border-radius: 8px;
            text-decoration: none; font-weight: 600; transition: 0.3s;
        }
        .btn-login-lock:hover { background: #b71c1c; transform: translateY(-2px); }
        .btn-register-lock {
            background: transparent; color: #fff; padding: 0.8rem 2rem; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.3); text-decoration: none; font-weight: 600; transition: 0.3s;
        }
        .btn-register-lock:hover { background: rgba(255,255,255,0.1); border-color: #fff; }

        /* Form elements (blurred behind) */
        .form-group { margin-bottom: 1.5rem; opacity: 0.3; pointer-events: none; }
        .form-group label { display: block; color: rgba(255,255,255,0.8); margin-bottom: 0.5rem; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 1rem; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px;
        }
        
        /* Animations */
        @keyframes fadeInDown { to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        @keyframes bounce { 0%, 100% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(-10px); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(213,0,0,0.4); } 70% { box-shadow: 0 0 0 20px rgba(213,0,0,0); } 100% { box-shadow: 0 0 0 0 rgba(213,0,0,0); } }

        /* --- MEDIA QUERY & FIX SCROLL HP --- */
        @media (max-width: 992px) {
            .contact-grid { grid-template-columns: repeat(2, 1fr); }
            .form-container { grid-template-columns: 1fr; gap: 3rem; }
        }

        @media (max-width: 768px) {
            /* Reset body untuk mobile */
            body { overflow-x: hidden; background: #000 !important; padding-top: 0 !important; margin: 0 !important; }
            main { position: relative; width: 100%; overflow-x: hidden; }
            
            /* === SOLUSI BACKGROUND IKUT SCROLL (FIX) === */
            .hero {
                min-height: 100vh !important;
                height: auto !important;
                padding-top: 0 !important;
                background: #000 !important;
            }
            .hero-bg {
                /* GANTI DARI FIXED KE ABSOLUTE DI SINI */
                position: absolute !important; 
                top: 0 !important;
                height: 100% !important; /* Ikuti tinggi parent (hero), bukan layar */
                animation: none !important;
                background-size: cover !important;
            }

            .hero-content { padding-top: 15vh; }
            .contact-grid { grid-template-columns: 1fr; }
            .form-card { padding: 2rem; }
            
            /* Header fix (jika header fixed) */
            header { position: absolute !important; background: transparent !important; }
        }
    </style>
</head>
<body>

<?php include '../php/includes/header.php'; ?>

<main>
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-particles" id="hero-particles"></div>
        <div class="hero-content">
            <div class="hero-badge">Contact Us</div>
            <h1>Hubungi <strong>Kami</strong></h1>
            <p>Butuh bantuan atau memiliki pertanyaan? Tim support kami siap membantu Anda 24/7 dengan pelayanan terbaik.</p>
        </div>
        <div class="scroll-indicator">
            <div class="mouse"></div>
            <span>Scroll</span>
        </div>
    </section>
    
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
    
    <section class="form-section">
        <div class="form-container">
            <div class="form-info">
                <h2>Kirim Pesan <strong>Langsung</strong></h2>
                <p>Silakan login terlebih dahulu untuk mengirim pesan, kritik, saran, atau pertanyaan kepada tim kami.</p>
                
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
                            <span>Data pesan Anda aman</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-card">
                <div class="locked-overlay">
                    <div class="lock-icon-container">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="locked-title">Akses Terkunci</h3>
                    <p class="locked-desc">Anda harus login terlebih dahulu untuk dapat mengirim pesan ke layanan pelanggan kami.</p>
                    <div class="auth-buttons">
                        <a href="../login.php" class="btn-login-lock">Masuk</a>
                        <a href="../register.php" class="btn-register-lock">Daftar</a>
                    </div>
                </div>

                <div class="fake-form">
                    <h3>Kirim Pesan</h3>
                    <p class="form-subtitle">Formulir kontak</p>
                    
                    <div class="form-group">
                        <label>Subjek Pesan</label>
                        <select disabled><option>Pilih subjek pesan</option></select>
                    </div>
                    <div class="form-group">
                        <label>Pesan Anda</label>
                        <textarea disabled placeholder="Tulis pesan Anda di sini..."></textarea>
                    </div>
                    <button class="btn-submit" disabled>Kirim Pesan</button>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../php/includes/footer.php'; ?>

<script>
// JS untuk efek Parallax & Animasi
const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add('visible'); }
    });
}, observerOptions);

document.querySelectorAll('.section-badge, .section-header h2, .section-header p, .contact-card, .form-info, .form-card').forEach(el => {
    scrollObserver.observe(el);
});

// Particles
function createParticles() {
    const container = document.getElementById('hero-particles');
    if (!container) return;
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute; width: ${Math.random()*6+2}px; height: ${Math.random()*6+2}px;
            background: rgba(255,255,255,${Math.random()*0.3+0.1}); border-radius: 50%;
            left: ${Math.random()*100}%; top: ${Math.random()*100}%;
            animation: float ${Math.random()*10+10}s infinite ease-in-out;
            animation-delay: ${Math.random()*5}s;
        `;
        container.appendChild(particle);
    }
}
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        50% { transform: translateY(-100px); }
    }
`;
document.head.appendChild(style);
createParticles();

// Mobile Viewport Fix
function fixMobileViewport() {
    if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
        document.documentElement.style.height = '-webkit-fill-available';
        document.body.style.height = '-webkit-fill-available';
    }
}
document.addEventListener('DOMContentLoaded', fixMobileViewport);
</script>

</body>
</html>