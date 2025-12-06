<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../../php/config/database.php';

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new = $_POST['new'];
    $cnf = $_POST['cnf'];
    
    if($new === $cnf && strlen($new) >= 6) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['user_id']]);
        $msg = "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>Password berhasil diubah!</div>";
    } else {
        $msg = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle me-2'></i>Password tidak cocok atau kurang dari 6 karakter.</div>";
    }
}

// Get admin info
$admin = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$admin->execute([$_SESSION['user_id']]);
$adminData = $admin->fetch(PDO::FETCH_ASSOC);

// Get system stats
$totalVehicles = $pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

$page_title = "Pengaturan";
include 'header.php';
?>

<div class="row g-4">
    <!-- Left Column - Settings -->
    <div class="col-lg-8">
        <!-- Password Change Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-key"></i>
                Keamanan Akun
            </div>
            <div class="card-body">
                <?php echo $msg; ?>
                <h5 class="mb-3">Ganti Password</h5>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" name="new" class="form-control" required minlength="6" placeholder="Masukkan password baru">
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Konfirmasi Password</label>
                                <input type="password" name="cnf" class="form-control" required minlength="6" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Password</button>
                </form>
            </div>
        </div>
        
        <!-- System Info Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-server"></i>
                Informasi Sistem
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-car fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0"><?php echo $totalVehicles; ?></h3>
                            <small class="text-muted">Total Kendaraan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h3 class="mb-0"><?php echo $totalUsers; ?></h3>
                            <small class="text-muted">Total Pelanggan</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-clipboard-list fa-2x text-warning mb-2"></i>
                            <h3 class="mb-0"><?php echo $totalBookings; ?></h3>
                            <small class="text-muted">Total Pesanan</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Versi Aplikasi:</strong> EzRent v1.0</p>
                        <p class="mb-2"><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></p>
                        <p class="mb-2"><strong>Database:</strong> MySQL</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Profile -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-shield"></i>
                Profil Admin
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #d50000, #b71c1c); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 2.5rem; color: #fff;">
                        <?php echo strtoupper(substr($adminData['nama_lengkap'] ?? 'A', 0, 1)); ?>
                    </div>
                </div>
                <h5 class="mb-1"><?php echo htmlspecialchars($adminData['nama_lengkap'] ?? 'Administrator'); ?></h5>
                <p class="text-muted mb-3"><?php echo htmlspecialchars($adminData['email'] ?? '-'); ?></p>
                <span class="badge bg-danger">Administrator</span>
                
                <hr>
                
                <div class="text-start">
                    <p class="mb-2"><i class="fas fa-envelope me-2 text-muted"></i> <?php echo htmlspecialchars($adminData['email'] ?? '-'); ?></p>
                    <p class="mb-2"><i class="fas fa-phone me-2 text-muted"></i> <?php echo htmlspecialchars($adminData['no_telepon'] ?? '-'); ?></p>
                    <p class="mb-0"><i class="fas fa-calendar me-2 text-muted"></i> Bergabung: <?php echo date('d M Y', strtotime($adminData['created_at'] ?? 'now')); ?></p>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-link"></i>
                Quick Links
            </div>
            <div class="card-body p-0">
                <a href="dashboard.php" class="d-block p-3 border-bottom text-decoration-none text-dark">
                    <i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard
                </a>
                <a href="vehicles.php" class="d-block p-3 border-bottom text-decoration-none text-dark">
                    <i class="fas fa-car me-2 text-success"></i> Kelola Kendaraan
                </a>
                <a href="bookings.php" class="d-block p-3 border-bottom text-decoration-none text-dark">
                    <i class="fas fa-clipboard-list me-2 text-warning"></i> Kelola Pesanan
                </a>
                <a href="users.php" class="d-block p-3 text-decoration-none text-dark">
                    <i class="fas fa-users me-2 text-info"></i> Kelola Pengguna
                </a>
            </div>
        </div>
    </div>
</div>

        </div>
    </main>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>