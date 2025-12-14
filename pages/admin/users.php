<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../login.php"); 
    exit(); 
}

require_once '../../php/config/database.php';

// Handle create user form submission from admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = in_array($_POST['role'] ?? 'user', ['user','admin']) ? $_POST['role'] : 'user';
    $password = $_POST['password'] ?? '';

    // basic validation
    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $_SESSION['flash_error'] = 'Nama, email valid, dan password wajib diisi.';
        header('Location: users.php'); exit;
    }

    // check duplicate email
    $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $chk->execute([$email]);
    if ($chk->fetchColumn() > 0) {
        $_SESSION['flash_error'] = 'Email sudah terdaftar.';
        header('Location: users.php'); exit;
    }

    // create user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $is_verified = isset($_POST['is_verified']) && $_POST['is_verified'] == '1' ? 1 : 0;
    $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, nomor_telepon, alamat, role, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $hash, $phone, $address, $role, $is_verified]);

    $_SESSION['flash_success'] = 'User baru berhasil dibuat.';
    header('Location: users.php'); exit;
}

if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="data_pengguna_ezrent.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Nama Lengkap', 'Email', 'No Telepon', 'Alamat', 'Role', 'Status', 'Bergabung'));
    
    $rows = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $status = $row['is_verified'] ? 'Terverifikasi' : 'Pending';
        fputcsv($output, array(
            $row['id'], $row['nama_lengkap'], $row['email'], 
            $row['nomor_telepon'], $row['alamat'], $row['role'], 
            $status, $row['created_at']
        ));
    }
    fclose($output);
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    if ($id != $_SESSION['user_id']) { 
        try {
            $chk = $pdo->prepare("SELECT count(*) FROM bookings WHERE user_id = ? AND status IN ('active', 'pending')");
            $chk->execute([$id]);
            if ($chk->fetchColumn() > 0) {
                echo "<script>alert('Gagal: User ini memiliki pesanan yang sedang berjalan/pending.'); window.location='users.php';</script>";
            } else {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
                echo "<script>alert('User berhasil dihapus'); window.location='users.php';</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: Data tidak bisa dihapus karena terikat data lain.'); window.location='users.php';</script>";
        }
    }
}

if (isset($_GET['verify_id'])) {
    $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?")->execute([$_GET['verify_id']]);
    echo "<script>window.location='users.php';</script>";
}

$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 1 AND role = 'user'")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified = 0")->fetchColumn(),
    'new_month' => $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn()
];

$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

$sql = "SELECT u.*, (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) as total_orders 
        FROM users u WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nama_lengkap LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_status === 'verified') {
    $sql .= " AND is_verified = 1";
} elseif ($filter_status === 'pending') {
    $sql .= " AND is_verified = 0";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activity_sql = "
    SELECT 'booking' as type, b.created_at, u.nama_lengkap, 'melakukan pemesanan kendaraan' as action
    FROM bookings b JOIN users u ON b.user_id = u.id
    UNION ALL
    SELECT 'register' as type, created_at, nama_lengkap, 'bergabung dengan EzRent' as action
    FROM users
    ORDER BY created_at DESC LIMIT 6
";
$activities = $pdo->query($activity_sql)->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Pengguna";
include 'header.php';
?>

<style>
    .card-stat { border:none; border-radius:12px; transition:0.3s; background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
    .card-stat:hover { transform:translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
    .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; }
    .table-custom thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; font-weight: 600; color: #495057; font-size: 0.9rem; }
    .activity-item { padding-left: 15px; border-left: 2px solid #e9ecef; position: relative; padding-bottom: 20px; }
    .activity-item::before { content: ''; width: 10px; height: 10px; background: #3b82f6; border-radius: 50%; position: absolute; left: -6px; top: 5px; }
    .activity-item.register::before { background: #10b981; }
    
    @media (max-width: 767px) {
        .table-custom thead { display: none; }
        .table-custom tbody tr { display: block; margin-bottom: 1rem; padding: 1rem; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .table-custom td { display: block; padding: 0.5rem 0; text-align: left; border: none; }
        .table-custom td::before { content: attr(data-label); font-weight: 600; color: #6c757d; font-size: 0.75rem; display: block; margin-bottom: 0.25rem; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark mb-0">Manajemen Pengguna</h2>
        <p class="text-muted mb-0 d-none d-md-block">Kelola data pelanggan dan hak akses.</p>
    </div>
        <div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-1"></i> <span class="d-none d-sm-inline">Tambah User</span><span class="d-sm-none">Tambah</span>
                </button>
        </div>
</div>

<?php if(!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if(!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="users.php">
            <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" value="1" id="is_verified" name="is_verified">
                        <label class="form-check-label" for="is_verified">Tandai sebagai terverifikasi</label>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Pengguna</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-stat border-start border-4 border-primary p-3">
            <div class="text-muted small mb-1">Total Pengguna</div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h2>
                <div class="text-success small fw-bold d-none d-lg-block">
                    <i class="fas fa-arrow-up"></i> <?php echo $stats['new_month']; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat border-start border-4 border-success p-3">
            <div class="text-muted small mb-1">Pengguna Aktif</div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold text-success"><?php echo $stats['active']; ?></h2>
                <i class="fas fa-user-check fa-lg text-success opacity-25 d-none d-sm-block"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat border-start border-4 border-warning p-3">
            <div class="text-muted small mb-1">Verifikasi</div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold text-warning"><?php echo $stats['pending']; ?></h2>
                <small class="text-warning d-none d-lg-block">Perlu tindakan</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-stat border-start border-4 border-danger p-3">
            <div class="text-muted small mb-1">Admin Sistem</div>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold text-danger">1</h2>
                <i class="fas fa-shield-alt fa-lg text-danger opacity-25 d-none d-sm-block"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="mb-0 fw-bold">Daftar Semua Pengguna</h5>
                
                <form class="d-flex gap-2 flex-grow-1 justify-content-end" method="GET">
                    <select name="status" class="form-select form-select-sm" style="max-width: 150px;" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="verified" <?php echo $filter_status=='verified'?'selected':''; ?>>Aktif / Verified</option>
                        <option value="pending" <?php echo $filter_status=='pending'?'selected':''; ?>>Pending</option>
                    </select>
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
                
                <a href="?export=excel" class="btn btn-success btn-sm text-white">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Pengguna</th>
                                <th>Email & Telepon</th>
                                <th class="text-center">Total Order</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($users)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada data pengguna ditemukan.</td></tr>
                            <?php else: ?>
                                <?php foreach($users as $u): 
                                    // Generate warna avatar random
                                    $colors = ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#198754', '#20c997'];
                                    $bg_color = $colors[$u['id'] % count($colors)];
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3" style="background-color: <?php echo $bg_color; ?>">
                                                <?php echo strtoupper(substr($u['nama_lengkap'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['nama_lengkap']); ?></div>
                                                <div class="small text-muted">ID: USR<?php echo str_pad($u['id'], 3, '0', STR_PAD_LEFT); ?></div>
                                                <div class="small text-muted"><i class="far fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($u['created_at'])); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark mb-1"><?php echo htmlspecialchars($u['email']); ?></div>
                                        <div class="small text-muted"><i class="fas fa-phone-alt me-1"></i> <?php echo htmlspecialchars($u['nomor_telepon'] ?? '-'); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php if($u['total_orders'] > 0): ?>
                                            <span class="badge bg-primary rounded-pill px-3"><?php echo $u['total_orders']; ?> x</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($u['role'] == 'admin'): ?>
                                            <span class="badge bg-dark rounded-pill px-3 py-2">ADMIN</span>
                                        <?php elseif($u['is_verified']): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-2">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <?php if(!$u['is_verified']): ?>
                                                <a href="?verify_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-success" title="Verifikasi Akun" onclick="return confirm('Verifikasi user ini?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-outline-primary" title="Edit Detail" onclick="alert('Fitur edit user detail belum tersedia')">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <?php if($u['role'] != 'admin'): ?>
                                                <a href="?delete_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus User" onclick="return confirm('Yakin hapus pengguna ini? Data pesanan mungkin hilang.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                <small class="text-muted">Menampilkan <?php echo count($users); ?> data terbaru.</small>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Aktivitas Pengguna Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    <?php if(empty($activities)): ?>
                        <p class="text-muted text-center small">Belum ada aktivitas.</p>
                    <?php else: ?>
                        <?php foreach($activities as $act): ?>
                        <div class="activity-item <?php echo $act['type']; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold text-dark small"><?php echo htmlspecialchars($act['nama_lengkap']); ?></span>
                                <?php if($act['type'] == 'register'): ?>
                                    <span class="badge bg-success" style="font-size: 0.6rem;">BARU</span>
                                <?php else: ?>
                                    <span class="badge bg-primary" style="font-size: 0.6rem;">ORDER</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted small mb-1" style="line-height: 1.3;">
                                Telah <?php echo $act['action']; ?>.
                            </p>
                            <small class="text-secondary" style="font-size: 0.75rem;">
                                <i class="far fa-clock me-1"></i> 
                                <?php 
                                    $time = strtotime($act['created_at']);
                                    $diff = time() - $time;
                                    if ($diff < 60) echo "Baru saja";
                                    else if ($diff < 3600) echo floor($diff/60) . " menit lalu";
                                    else if ($diff < 86400) echo floor($diff/3600) . " jam lalu";
                                    else echo date('d M Y', $time);
                                ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card bg-primary text-white mt-4 border-0 shadow-sm" style="background: linear-gradient(45deg, #2563eb, #1d4ed8);">
            <div class="card-body p-4">
                <h5 class="fw-bold"><i class="fas fa-info-circle me-2"></i> Info Admin</h5>
                <p class="small opacity-75 mb-0">
                    Pengguna dengan status <strong>Pending</strong> tidak bisa melakukan pemesanan sampai Anda memverifikasinya (klik tombol Checklist Hijau).
                </p>
            </div>
        </div>
    </div>
</div>

        </div>
    </main>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>