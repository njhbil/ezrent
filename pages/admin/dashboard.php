<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../../php/config/database.php';

try {
    $total_vehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

    $income_query = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status IN ('active', 'completed')");
    $total_income = $income_query->fetchColumn() ?: 0;

    $stat_tersedia = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'tersedia'")->fetchColumn();
    $stat_disewa = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'disewa'")->fetchColumn();
    $stat_maintenance = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'maintenance'")->fetchColumn();

    $stmt = $pdo->query("
        SELECT 
            b.id, b.kode_booking, b.start_date, b.end_date, b.total_price, b.status,
            u.nama_lengkap,
            v.nama as nama_kendaraan
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

$page_title = "Dashboard Admin - EzRent";
include 'header.php'; 
?>

<style>
    /* Porsche-Inspired Premium Typography */
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap');
    
    :root {
        --porsche-black: #1a1a1a;
        --porsche-gold: #d4af37;
        --porsche-red: #d50000;
        --porsche-silver: #c0c0c0;
        --porsche-bg: #f8f8f8;
        --shadow-luxury: 0 10px 40px rgba(0, 0, 0, 0.12);
        --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.18);
    }
    
    .content-area {
        font-family: 'Space Grotesk', sans-serif;
    }
    
    /* Premium Stats Cards */
    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 2px;
        padding: 2.5rem 2rem;
        position: relative;
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-luxury);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--porsche-red), var(--porsche-gold));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
        border-color: rgba(213, 0, 0, 0.15);
    }
    
    .stat-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--porsche-black), #2a2a2a);
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .stat-icon::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s ease;
    }
    
    .stat-card:hover .stat-icon::after {
        left: 100%;
    }
    
    .stat-icon i {
        font-size: 1.8rem;
        color: var(--porsche-gold);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-icon i {
        transform: scale(1.1);
    }
    
    .stat-value {
        font-family: 'Playfair Display', serif;
        font-size: 2.8rem;
        font-weight: 700;
        color: var(--porsche-black);
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .stat-change {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.4rem 0.9rem;
        border-radius: 2px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        letter-spacing: 0.03em;
    }
    
    .stat-change.up {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    
    .stat-change.down {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    /* Premium Card Design */
    .card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 2px;
        box-shadow: var(--shadow-luxury);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    
    .card:hover {
        box-shadow: var(--shadow-hover);
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--porsche-black) 0%, #2a2a2a 100%);
        color: var(--porsche-gold);
        padding: 1.5rem 2rem;
        border-bottom: none;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    
    .card-header i {
        color: var(--porsche-gold);
        font-size: 1.1rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    /* Premium Table */
    .table {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 0.9rem;
    }
    
    .table thead {
        background: linear-gradient(135deg, #f8f8f8, #f0f0f0);
        border-bottom: 2px solid var(--porsche-black);
    }
    
    .table thead th {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 0.75rem;
        color: var(--porsche-black);
        padding: 1.25rem 1rem;
        border: none;
    }
    
    .table tbody tr {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(213, 0, 0, 0.02), rgba(212, 175, 55, 0.02));
        transform: translateX(4px);
    }
    
    .table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        color: #333;
    }
    
    /* Premium Badges */
    .badge {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 600;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-radius: 2px;
    }
    
    .badge.bg-warning {
        background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
        color: #78350f;
        border: 1px solid #f59e0b;
    }
    
    .badge.bg-info {
        background: linear-gradient(135deg, #60a5fa, #3b82f6) !important;
        color: #1e3a8a;
        border: 1px solid #3b82f6;
    }
    
    .badge.bg-primary {
        background: linear-gradient(135deg, var(--porsche-red), #b71c1c) !important;
        color: #fff;
        border: 1px solid var(--porsche-red);
    }
    
    .badge.bg-success {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        color: #064e3b;
        border: 1px solid #059669;
    }
    
    .badge.bg-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        color: #fff;
        border: 1px solid #dc2626;
    }
    
    /* Premium Buttons */
    .btn {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 0.75rem 1.5rem;
        border-radius: 2px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        font-size: 0.85rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--porsche-red), #b71c1c);
        color: #fff;
        box-shadow: 0 6px 20px rgba(213, 0, 0, 0.3);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #b71c1c, #8b0000);
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(213, 0, 0, 0.4);
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: #78350f;
        box-shadow: 0 6px 20px rgba(251, 191, 36, 0.3);
    }
    
    .btn-warning:hover {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        transform: translateY(-2px);
        color: #78350f;
    }
    
    .btn-info {
        background: linear-gradient(135deg, #60a5fa, #3b82f6);
        color: #fff;
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    }
    
    .btn-info:hover {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        transform: translateY(-2px);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    }
    
    .btn-success:hover {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-2px);
    }
    
    .btn-outline-primary {
        background: transparent;
        color: var(--porsche-red);
        border: 2px solid var(--porsche-red);
        box-shadow: none;
    }
    
    .btn-outline-primary:hover {
        background: var(--porsche-red);
        color: #fff;
        border-color: var(--porsche-red);
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(213, 0, 0, 0.3);
    }
    
    /* Chart Enhancements */
    canvas {
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.05));
    }
    
    /* Progress Bar */
    .progress {
        height: 8px;
        border-radius: 2px;
        background: rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    .progress-bar {
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Premium Divider */
    hr {
        border: none;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
        margin: 2rem 0;
    }
    
    /* Luxury Text Styles */
    .text-luxury {
        font-family: 'Playfair Display', serif;
        font-weight: 600;
        color: var(--porsche-black);
    }
    
    /* Hover Effects */
    .hover-lift {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hover-lift:hover {
        transform: translateY(-4px);
    }
</style>

<!-- Dashboard Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-value">Rp <?php echo number_format($total_income / 1000000, 1); ?>M</div>
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> +12% dari bulan lalu
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-value"><?php echo $total_bookings; ?></div>
            <div class="stat-label">Total Pesanan</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> +5 minggu ini
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-value"><?php echo $pending_bookings; ?></div>
            <div class="stat-label">Butuh Konfirmasi</div>
            <?php if ($pending_bookings > 0): ?>
            <div class="stat-change down">
                <i class="fas fa-exclamation-circle"></i> Perlu tindakan
            </div>
            <?php else: ?>
            <div class="stat-change up">
                <i class="fas fa-check-circle"></i> Semua terproses
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo $total_users; ?></div>
            <div class="stat-label">Pelanggan Aktif</div>
            <div class="stat-change up">
                <i class="fas fa-arrow-up"></i> +3 user baru
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card hover-lift">
            <div class="card-header">
                <i class="fas fa-chart-area"></i>
                Aktivitas Sewa Bulanan
            </div>
            <div class="card-body">
                <canvas id="rentChart" style="max-height: 320px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card hover-lift">
            <div class="card-header">
                <i class="fas fa-car-side"></i>
                Status Armada
            </div>
            <div class="card-body">
                <canvas id="vehicleStatusChart" style="max-height: 250px;"></canvas>
                <div class="mt-4 text-center small">
                    <span class="me-3"><i class="fas fa-circle" style="color: #10b981;"></i> Tersedia: <strong><?php echo $stat_tersedia; ?></strong></span>
                    <span class="me-3"><i class="fas fa-circle" style="color: #3b82f6;"></i> Disewa: <strong><?php echo $stat_disewa; ?></strong></span>
                    <span><i class="fas fa-circle" style="color: #d50000;"></i> Maintenance: <strong><?php echo $stat_maintenance; ?></strong></span>
                </div>
                
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="small text-uppercase" style="letter-spacing: 0.1em; font-weight: 600;">Total Unit</span>
                    <span class="small fw-bold text-luxury"><?php echo $total_vehicles; ?></span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 100%; background: linear-gradient(90deg, var(--porsche-red), var(--porsche-gold));"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card hover-lift">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list-alt"></i> Pesanan Terbaru</span>
                <a href="bookings.php" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Kode</th>
                                <th>Pelanggan</th>
                                <th>Kendaraan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_bookings)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted" style="font-style: italic;">Belum ada data pesanan.</td></tr>
                            <?php else: ?>
                                <?php foreach($recent_bookings as $row): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-luxury" style="color: var(--porsche-red);">#<?php echo $row['kode_booking']; ?></td>
                                    <td style="font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td style="color: #666;"><?php echo htmlspecialchars($row['nama_kendaraan']); ?></td>
                                    <td style="font-family: 'Space Grotesk', monospace; font-size: 0.85rem;"><?php echo date('d/m/Y', strtotime($row['start_date'])); ?></td>
                                    <td>
                                        <?php 
                                            $st = $row['status'];
                                            $badge = 'secondary';
                                            if($st=='pending') $badge='warning';
                                            if($st=='confirmed') $badge='info';
                                            if($st=='active') $badge='primary';
                                            if($st=='completed') $badge='success';
                                            if($st=='cancelled') $badge='danger';
                                        ?>
                                        <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($st); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="bookings.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card hover-lift">
            <div class="card-header">
                <i class="fas fa-bolt"></i>
                Aksi Cepat
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="vehicles.php" class="btn btn-primary d-flex align-items-center justify-content-between p-3">
                        <span><i class="fas fa-plus-circle me-2"></i> Tambah Kendaraan</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="bookings.php" class="btn btn-warning d-flex align-items-center justify-content-between p-3">
                        <span><i class="fas fa-check-double me-2"></i> Konfirmasi Pesanan</span>
                        <span class="badge" style="background: rgba(0,0,0,0.2);"><?php echo $pending_bookings; ?></span>
                    </a>
                    <a href="users.php" class="btn btn-info d-flex align-items-center justify-content-between p-3">
                        <span><i class="fas fa-user-plus me-2"></i> Kelola User</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="reports.php" class="btn btn-success d-flex align-items-center justify-content-between p-3">
                        <span><i class="fas fa-file-invoice-dollar me-2"></i> Laporan Keuangan</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Grafik Pie Status Kendaraan - Porsche Style
    const ctxPie = document.getElementById('vehicleStatusChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Tersedia', 'Disewa', 'Maintenance'],
            datasets: [{
                data: [<?php echo $stat_tersedia; ?>, <?php echo $stat_disewa; ?>, <?php echo $stat_maintenance; ?>],
                backgroundColor: ['#10b981', '#3b82f6', '#d50000'],
                borderWidth: 0,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    titleFont: { family: 'Space Grotesk', size: 14, weight: '600' },
                    bodyFont: { family: 'Space Grotesk', size: 13 },
                    padding: 12,
                    cornerRadius: 2,
                    displayColors: true,
                    boxWidth: 12,
                    boxHeight: 12
                }
            }
        }
    });

    // Grafik Area Sewa - Porsche Style
    const ctxArea = document.getElementById('rentChart').getContext('2d');
    new Chart(ctxArea, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Pendapatan (Juta)',
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#d50000',
                backgroundColor: function(context) {
                    const ctx = context.chart.ctx;
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(213, 0, 0, 0.2)');
                    gradient.addColorStop(1, 'rgba(213, 0, 0, 0.01)');
                    return gradient;
                },
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#d50000',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#d4af37',
                pointHoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a1a',
                    titleFont: { family: 'Space Grotesk', size: 14, weight: '600' },
                    bodyFont: { family: 'Space Grotesk', size: 13 },
                    padding: 12,
                    cornerRadius: 2
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.04)',
                        drawBorder: false
                    },
                    ticks: {
                        font: { family: 'Space Grotesk', size: 11, weight: '500' },
                        color: '#666'
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        font: { family: 'Space Grotesk', size: 11, weight: '600' },
                        color: '#1a1a1a'
                    }
                }
            }
        }
    });
</script>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>