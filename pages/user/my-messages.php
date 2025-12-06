<?php
session_start();

// 1. Cek Login User
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Koneksi Database
require_once '../../php/config/database.php';

// 3. Ambil Pesan
try {
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$page_title = "Pesan & Bantuan - EzRent";
include 'header.php'; 
?>

<style>
    /* Hero Animations */
    .hero {
        position: relative;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
        padding: 5rem 0 4rem;
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
        margin-bottom: 1rem;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s ease forwards 0.1s;
    }
    
    .hero h1 {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1rem;
        color: #ffffff;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.3s;
    }
    
    .hero p {
        font-size: 1.125rem;
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
    
    .stagger-1 { transition-delay: 0.1s; }
    .stagger-2 { transition-delay: 0.2s; }
    .stagger-3 { transition-delay: 0.3s; }

    body {
        background-color: #fafafa;
    }
    
    .ticket-card {
        border: none;
        border-radius: 16px;
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        border: 2px solid transparent;
    }
    
    .ticket-card:hover {
        transform: translateY(-5px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.1);
    }
    
    /* Header Kartu */
    .ticket-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        background: white;
    }
    
    /* User Message Area */
    .user-section {
        padding: 1.5rem;
        background-color: #fff;
    }
    .user-bubble {
        font-size: 1rem;
        color: #374151;
        line-height: 1.6;
    }

    /* Admin Reply Area */
    .admin-section {
        background-color: #f8fafc;
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .admin-reply-box {
        background: #d1fae5;
        border-left: 4px solid #10b981;
        padding: 1.25rem;
        border-radius: 0 8px 8px 0;
        color: #064e3b;
        font-size: 1rem;
        line-height: 1.6;
    }

    /* Info Admin di Sebelah Kanan */
    .admin-info-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    /* Avatar User Kecil di Header */
    .avatar-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        background: linear-gradient(135deg, #d50000 0%, #ff1744 100%);
    }

    /* Status Badges */
    .badge-status {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* New Message Button */
    .new-msg-btn {
        background: linear-gradient(135deg, #d50000 0%, #ff1744 100%);
        color: white;
        text-decoration: none;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(213, 0, 0, 0.3);
    }
    
    .new-msg-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(213, 0, 0, 0.4);
        color: white;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    
    .empty-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.1) 0%, rgba(213, 0, 0, 0.05) 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #d50000;
    }
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="container" style="position: relative; z-index: 1;">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <div class="hero-badge">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Riwayat Komunikasi
            </div>
            <h1>Pesan Saya</h1>
            <p>Daftar percakapan Anda dengan Tim Support EzRent</p>
        </div>
    </div>
</section>

<div class="container py-5" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <p class="text-muted mb-0">Total: <?php echo count($messages); ?> pesan</p>
        </div>
        <a href="contact.php" class="new-msg-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Tulis Pesan Baru
        </a>
    </div>

    <?php if (empty($messages)): ?>
        <div class="empty-state reveal">
            <div class="empty-icon">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 10h.01M12 10h.01M16 10h.01"/></svg>
            </div>
            <h4 class="fw-bold" style="color: #0a0a0a;">Kotak Masuk Kosong</h4>
            <p class="text-muted mb-4">Anda belum mengirim pesan apapun.</p>
            <a href="contact.php" class="new-msg-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Kirim Pesan Pertama
            </a>
        </div>
    <?php else: ?>

        <div class="d-flex flex-column">
            <?php $index = 0; foreach ($messages as $msg): ?>
                <div class="ticket-card reveal stagger-<?php echo ($index % 3) + 1; ?>">
                    <div class="ticket-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-small shadow-sm">
                                <?php echo strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: #0a0a0a;"><?php echo htmlspecialchars($msg['subject']); ?></h5>
                                <small class="text-muted">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: inline;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <?php echo date('d M Y, H:i', strtotime($msg['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        
                        <?php 
                            if ($msg['status'] == 'new') {
                                echo '<span class="badge-status bg-danger bg-opacity-10 text-danger">Menunggu</span>';
                            } elseif ($msg['status'] == 'replied') {
                                echo '<span class="badge-status bg-success bg-opacity-10 text-success">Dibalas</span>';
                            } else {
                                echo '<span class="badge-status bg-secondary bg-opacity-10 text-secondary">Selesai</span>';
                            }
                        ?>
                    </div>

                    <div class="user-section">
                        <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Pertanyaan Anda:</h6>
                        <div class="user-bubble">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>
                    </div>

                    <?php if (!empty($msg['reply'])): ?>
                        <div class="admin-section">
                            <div class="row align-items-stretch">
                                <div class="col-md-9 mb-3 mb-md-0">
                                    <h6 class="text-uppercase text-success fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        Balasan Admin:
                                    </h6>
                                    <div class="admin-reply-box shadow-sm">
                                        <?php echo nl2br(htmlspecialchars($msg['reply'])); ?>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="admin-info-card">
                                        <div class="mb-2 text-success">
                                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                        </div>
                                        <h6 class="fw-bold mb-1" style="font-size: 0.9rem; color: #0a0a0a;">Customer Support</h6>
                                        <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">Tim EzRent</small>
                                        
                                        <hr class="w-100 my-2 text-muted opacity-25">
                                        
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            Dibalas pada:<br>
                                            <strong><?php echo date('d M, H:i', strtotime($msg['replied_at'])); ?></strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-light p-3 mx-4 mb-4 rounded border border-dashed text-center">
                            <small class="text-muted fst-italic">
                                Pesan Anda sedang dalam antrean tinjauan admin.
                            </small>
                        </div>
                    <?php endif; ?>

                </div>
            <?php $index++; endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    document.querySelectorAll('.reveal').forEach(el => {
        scrollObserver.observe(el);
    });
    
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

<?php include '../../php/includes/footer.php'; ?>