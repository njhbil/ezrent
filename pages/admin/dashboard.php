<?php
session_start();

// 1. Cek Keamanan Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 2. Koneksi Database
require_once '../../php/config/database.php';

// 3. LOGIKA PHP: MENGAMBIL DATA STATISTIK REAL-TIME
try {
    // A. Card Statistik Utama
    // Total Kendaraan
    $total_vehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    
    // Total Pesanan (Semua status)
    $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    
    // Pesanan Pending (Perlu Tindakan)
    $pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
    
    // Total User
    $total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

    // B. Statistik Keuangan (Hanya yang statusnya 'active' atau 'completed')
    $income_query = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status IN ('active', 'completed')");
    $total_income = $income_query->fetchColumn() ?: 0;

    // C. Data untuk Grafik Status Kendaraan
    $stat_tersedia = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'tersedia'")->fetchColumn();
    $stat_disewa = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'disewa'")->fetchColumn();
    $stat_maintenance = $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'maintenance'")->fetchColumn();

    // D. Mengambil 5 Pesanan Terbaru
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

<!-- Dashboard Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
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
                <i class="fas fa-clipboard-list"></i>
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
        <div class="card">
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
        <div class="card">
            <div class="card-header">
                <i class="fas fa-car"></i>
                Status Armada
            </div>
            <div class="card-body">
                <canvas id="vehicleStatusChart" style="max-height: 250px;"></canvas>
                <div class="mt-4 text-center small">
                    <span class="me-2"><i class="fas fa-circle text-success"></i> Tersedia: <strong><?php echo $stat_tersedia; ?></strong></span>
                    <span class="me-2"><i class="fas fa-circle" style="color: #3b82f6;"></i> Disewa: <strong><?php echo $stat_disewa; ?></strong></span>
                    <span><i class="fas fa-circle" style="color: #d50000;"></i> Maintenance: <strong><?php echo $stat_maintenance; ?></strong></span>
                </div>
                
                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small">Total Unit</span>
                    <span class="small fw-bold"><?php echo $total_vehicles; ?></span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 100%; background: linear-gradient(90deg, #d50000, #ff5252);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clipboard-list"></i> Pesanan Terbaru</span>
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
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data pesanan.</td></tr>
                            <?php else: ?>
                                <?php foreach($recent_bookings as $row): ?>
                                <tr>
                                    <td class="ps-4 fw-bold" style="color: #d50000;">#<?php echo $row['kode_booking']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kendaraan']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['start_date'])); ?></td>
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
        <div class="card">
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
                        <span class="badge bg-dark"><?php echo $pending_bookings; ?></span>
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
    // Grafik Pie Status Kendaraan
    const ctxPie = document.getElementById('vehicleStatusChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Tersedia', 'Disewa', 'Maintenance'],
            datasets: [{
                data: [<?php echo $stat_tersedia; ?>, <?php echo $stat_disewa; ?>, <?php echo $stat_maintenance; ?>],
                backgroundColor: ['#10b981', '#3b82f6', '#d50000'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Grafik Area Sewa
    const ctxArea = document.getElementById('rentChart').getContext('2d');
    new Chart(ctxArea, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Pendapatan (Juta)',
                data: [12, 19, 15, 25, 22, 30],
                borderColor: '#d50000',
                backgroundColor: 'rgba(213, 0, 0, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#d50000',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>