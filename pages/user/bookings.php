<?php 
$page_title = "Pesan Kendaraan - EzRent";
include 'header.php'; 

// Koneksi ke database
require_once '../../php/config/database.php';

try {
    // Query untuk mengambil data kendaraan yang tersedia
    $stmt = $pdo->query("
        SELECT * FROM vehicles 
        WHERE status = 'tersedia' 
        ORDER BY 
            CASE jenis 
                WHEN 'mobil' THEN 1
                WHEN 'motor' THEN 2
                WHEN 'sepeda_listrik' THEN 3
                WHEN 'sepeda' THEN 4
            END,
            harga_per_hari ASC
    ");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $vehicles = [];
    $error = "Terjadi kesalahan saat mengambil data kendaraan: " . $e->getMessage();
}
?>

<style>
    /* Hero Animations */
    .hero {
        position: relative;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
        padding: 6rem 0 5rem;
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
        font-size: 3rem;
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
        margin-bottom: 2rem;
        line-height: 1.7;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.5s;
    }
    
    .welcome-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid #d50000;
        padding: 1.5rem;
        border-radius: 12px;
        max-width: 500px;
        margin: 0 auto;
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
    
    .stagger-1 { transition-delay: 0.05s; }
    .stagger-2 { transition-delay: 0.1s; }
    .stagger-3 { transition-delay: 0.15s; }
    .stagger-4 { transition-delay: 0.2s; }
    .stagger-5 { transition-delay: 0.25s; }
    .stagger-6 { transition-delay: 0.3s; }
    
    /* Vehicle Cards */
    .vehicle-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        max-width: 380px;
        width: 100%;
        border: 2px solid transparent;
    }
    
    .vehicle-card:hover {
        transform: translateY(-8px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.15);
    }
    
    .vehicle-image {
        height: 200px;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .vehicle-image::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 30% 70%, rgba(213, 0, 0, 0.2) 0%, transparent 50%);
    }
    
    .vehicle-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d50000;
        position: relative;
        z-index: 1;
    }
    
    /* Filter Section */
    .filter-section {
        background: #fafafa;
        padding: 2rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .filter-btn {
        background: white;
        color: #0a0a0a;
        border: 2px solid #e5e7eb;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .filter-btn:hover {
        border-color: #d50000;
        color: #d50000;
    }
    
    .filter-btn.active {
        background: linear-gradient(135deg, #d50000 0%, #ff1744 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(213, 0, 0, 0.3);
    }
    
    .search-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #d50000;
        box-shadow: 0 0 0 3px rgba(213, 0, 0, 0.1);
    }
    
    /* Help Section */
    .help-card {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        border: 2px solid transparent;
    }
    
    .help-card:hover {
        transform: translateY(-5px);
        border-color: #d50000;
        box-shadow: 0 12px 40px rgba(213, 0, 0, 0.1);
    }
    
    .help-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.1) 0%, rgba(213, 0, 0, 0.05) 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #d50000;
    }
    
    /* Book Button */
    .book-btn {
        background: linear-gradient(135deg, #d50000 0%, #ff1744 100%);
        color: white;
        text-decoration: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(213, 0, 0, 0.3);
    }
    
    .book-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(213, 0, 0, 0.4);
    }
    
    /* Section Header */
    .section-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .section-header h2 {
        font-size: 2rem;
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
                    Akses Member Aktif
                </div>
                <h1>Pesan Kendaraan</h1>
                <p>
                    Selamat datang, <strong style="color: #d50000;"><?php echo htmlspecialchars($user_name); ?></strong>! 
                    Pilih kendaraan favorit Anda dan pesan dengan mudah.
                </p>
                
                <!-- Welcome Message for Logged-in User -->
                <div class="welcome-card">
                    <div style="display: flex; align-items: center; gap: 1rem; justify-content: center;">
                        <div style="width: 48px; height: 48px; background: rgba(213, 0, 0, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #d50000;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <div style="text-align: left;">
                            <h3 style="color: #ffffff; margin-bottom: 0.25rem; font-size: 1.1rem;">
                                Akses Penuh Terbuka!
                            </h3>
                            <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin: 0;">
                                Semua fitur pemesanan sekarang dapat Anda gunakan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search & Filter Section -->
    <section class="filter-section">
        <div class="container">
            <!-- Search Bar -->
            <div style="margin-bottom: 1.5rem;">
                <div style="position: relative; max-width: 500px; margin: 0 auto;">
                    <input type="text" id="searchInput" class="search-input"
                           placeholder="Cari kendaraan berdasarkan merek, model, atau jenis...">
                    <div style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; align-items: center;">
                <span style="font-weight: 600; color: #0a0a0a;">Filter:</span>
                <button class="filter-btn active" data-filter="all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Semua
                </button>
                <button class="filter-btn" data-filter="mobil">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    Mobil
                </button>
                <button class="filter-btn" data-filter="motor">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 100-2 1 1 0 000 2zm-3 11.5V14l-3-3 4-3 2 3h2"/></svg>
                    Motor
                </button>
                <button class="filter-btn" data-filter="sepeda_listrik">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Sepeda Listrik
                </button>
                <button class="filter-btn" data-filter="sepeda">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="12" cy="12" r="1"/><path d="M12 12V5m0 7l6.5 5.5M12 12l-6.5 5.5"/></svg>
                    Sepeda
                </button>
            </div>

            <!-- Results Counter -->
            <div style="text-align: center; margin-top: 1rem;">
                <span id="resultsCount" style="color: #64748b; font-size: 0.9rem;">
                    Menampilkan <?php echo count($vehicles); ?> kendaraan tersedia
                </span>
            </div>
        </div>
    </section>

    <!-- Vehicles Grid Section -->
    <section style="padding: 4rem 0; background: #fafafa;">
        <div class="container">
            <?php if (isset($error)): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($vehicles)): ?>
                <div style="text-align: center; padding: 4rem 0;">
                    <div style="width: 80px; height: 80px; background: rgba(213, 0, 0, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #d50000;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 style="color: #0a0a0a; margin-bottom: 1rem;">Tidak ada kendaraan tersedia</h3>
                    <p style="color: #64748b;">Silakan coba lagi nanti atau hubungi customer service kami.</p>
                </div>
            <?php else: ?>
                <div id="vehiclesGrid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; justify-items: center;">
                    <?php $cardIndex = 0; foreach ($vehicles as $vehicle): ?>
                        <div class="vehicle-card reveal stagger-<?php echo ($cardIndex % 6) + 1; ?>" 
                             data-jenis="<?php echo htmlspecialchars($vehicle['jenis']); ?>"
                             data-merek="<?php echo htmlspecialchars($vehicle['merek']); ?>"
                             data-model="<?php echo htmlspecialchars($vehicle['model']); ?>"
                             data-nama="<?php echo htmlspecialchars($vehicle['nama']); ?>">
                            <!-- Vehicle Image -->
                            <div class="vehicle-image">
                                <div class="vehicle-icon">
                                    <?php 
                                    switch($vehicle['jenis']) {
                                        case 'motor': 
                                            echo '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 100-2 1 1 0 000 2zm-3 11.5V14l-3-3 4-3 2 3h2"/></svg>';
                                            break;
                                        case 'sepeda_listrik': 
                                            echo '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>';
                                            break;
                                        case 'sepeda': 
                                            echo '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="12" cy="12" r="1"/><path d="M12 12V5m0 7l6.5 5.5M12 12l-6.5 5.5"/></svg>';
                                            break;
                                        default: 
                                            echo '<svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Vehicle Info -->
                            <div style="padding: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                    <div style="flex: 1;">
                                        <h3 style="color: #0a0a0a; margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($vehicle['nama']); ?>
                                        </h3>
                                        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($vehicle['model'] . ' • ' . $vehicle['tahun']); ?>
                                        </p>
                                        <p style="color: #64748b; font-size: 0.9rem;">
                                            Plat: <?php echo htmlspecialchars($vehicle['plat_nomor']); ?>
                                        </p>
                                    </div>
                                    <div style="background: linear-gradient(135deg, #d50000 0%, #ff1744 100%); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; text-transform: uppercase;">
                                        <?php echo strtoupper($vehicle['jenis']); ?>
                                    </div>
                                </div>

                                <!-- Features -->
                                <div style="margin-bottom: 1.5rem;">
                                    <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
                                        <span style="background: #f3f4f6; color: #0a0a0a; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; font-weight: 500; display: flex; align-items: center; gap: 0.25rem;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                            <?php echo htmlspecialchars($vehicle['kapasitas']); ?> Kursi
                                        </span>
                                        <span style="background: #f3f4f6; color: #0a0a0a; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; font-weight: 500;">
                                            <?php echo htmlspecialchars($vehicle['warna']); ?>
                                        </span>
                                        <span style="background: #f3f4f6; color: #0a0a0a; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; font-weight: 500;">
                                            <?php echo htmlspecialchars($vehicle['transmisi']); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Price & Action -->
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-size: 1.5rem; font-weight: 700; color: #d50000;">
                                            Rp <?php echo number_format($vehicle['harga_per_hari'], 0, ',', '.'); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #64748b;">per hari</div>
                                    </div>
                                    
                                    <!-- UNLOCKED FEATURE: User can book directly -->
                                    <a href="booking-process.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="book-btn">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        Pesan
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php $cardIndex++; endforeach; ?>
                </div>

                <!-- No Results Message -->
                <div id="noResults" style="display: none; text-align: center; padding: 4rem 0;">
                    <div style="width: 80px; height: 80px; background: rgba(213, 0, 0, 0.1); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #d50000;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    </div>
                    <h3 style="color: #0a0a0a; margin-bottom: 1rem;">Tidak ada kendaraan yang sesuai</h3>
                    <p style="color: #64748b;">Coba ubah kata kunci pencarian atau filter yang digunakan.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Quick Actions Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div class="section-header reveal">
                <h2>Butuh Bantuan?</h2>
                <p>Tim support kami siap membantu Anda 24/7</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div class="help-card reveal stagger-1">
                    <div class="help-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Hubungi Kami</h3>
                    <p style="color: #64748b; line-height: 1.6; font-size: 0.9rem;">
                        Telepon: (021) 1234-5678<br>
                        WhatsApp: 0812-3456-7890
                    </p>
                </div>
                <div class="help-card reveal stagger-2">
                    <div class="help-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Live Chat</h3>
                    <p style="color: #64748b; line-height: 1.6; font-size: 0.9rem;">
                        Chat langsung dengan customer service kami untuk bantuan cepat
                    </p>
                </div>
                <div class="help-card reveal stagger-3">
                    <div class="help-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <h3 style="margin-bottom: 1rem; color: #0a0a0a; font-size: 1.25rem; font-weight: 600;">Email Support</h3>
                    <p style="color: #64748b; line-height: 1.6; font-size: 0.9rem;">
                        support@ezrent.com<br>
                        Response dalam 1-2 jam kerja
                    </p>
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

    const searchInput = document.getElementById('searchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const vehicleCards = document.querySelectorAll('.vehicle-card');
    const vehiclesGrid = document.getElementById('vehiclesGrid');
    const noResults = document.getElementById('noResults');
    const resultsCount = document.getElementById('resultsCount');
    
    let activeFilter = 'all';
    let searchTerm = '';

    // Filter functionality
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeFilter = this.dataset.filter;
            filterVehicles();
        });
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        searchTerm = this.value.toLowerCase().trim();
        filterVehicles();
    });

    function filterVehicles() {
        let visibleCount = 0;
        
        vehicleCards.forEach(card => {
            const jenis = card.dataset.jenis;
            const merek = card.dataset.merek.toLowerCase();
            const model = card.dataset.model.toLowerCase();
            const nama = card.dataset.nama.toLowerCase();
            
            const matchesFilter = activeFilter === 'all' || jenis === activeFilter;
            const matchesSearch = searchTerm === '' || 
                                merek.includes(searchTerm) || 
                                model.includes(searchTerm) || 
                                nama.includes(searchTerm) ||
                                jenis.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            if (vehiclesGrid) vehiclesGrid.style.display = 'none';
            if (noResults) noResults.style.display = 'block';
        } else {
            if (vehiclesGrid) vehiclesGrid.style.display = 'grid';
            if (noResults) noResults.style.display = 'none';
        }

        if (resultsCount) resultsCount.textContent = `Menampilkan ${visibleCount} kendaraan tersedia`;
    }

    filterVehicles();
});
</script>

<style>
@media (max-width: 768px) {
    .hero h1 {
        font-size: 2.5rem !important;
    }
    
    .hero p {
        font-size: 1rem;
    }
    
    #vehiclesGrid {
        grid-template-columns: 1fr;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem !important;
        font-size: 0.85rem;
    }
    
    .filter-btn svg {
        display: none;
    }
}
</style>

<?php include '../../php/includes/footer.php'; ?>
