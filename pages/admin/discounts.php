<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../login.php"); 
    exit(); 
}

require_once '../../php/config/database.php';

// Handle Add Discount
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_discount'])) {
    try {
        $sql = "INSERT INTO discount_codes (code, description, discount_type, discount_value, min_booking_amount, max_discount_amount, usage_limit, start_date, end_date, is_active, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            strtoupper($_POST['code']),
            $_POST['description'],
            $_POST['discount_type'],
            $_POST['discount_value'],
            $_POST['min_booking_amount'] ?: 0,
            $_POST['max_discount_amount'] ?: NULL,
            $_POST['usage_limit'] ?: NULL,
            $_POST['start_date'] ?: NULL,
            $_POST['end_date'] ?: NULL,
            $_POST['is_active'] ?? 1,
            $_SESSION['user_id']
        ]);
        echo "<script>alert('Kode diskon berhasil ditambahkan!'); window.location='discounts.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle Update Discount
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_discount'])) {
    try {
        $sql = "UPDATE discount_codes SET code = ?, description = ?, discount_type = ?, discount_value = ?, 
                min_booking_amount = ?, max_discount_amount = ?, usage_limit = ?, start_date = ?, end_date = ?, is_active = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            strtoupper($_POST['code']),
            $_POST['description'],
            $_POST['discount_type'],
            $_POST['discount_value'],
            $_POST['min_booking_amount'] ?: 0,
            $_POST['max_discount_amount'] ?: NULL,
            $_POST['usage_limit'] ?: NULL,
            $_POST['start_date'] ?: NULL,
            $_POST['end_date'] ?: NULL,
            $_POST['is_active'] ?? 1,
            $_POST['discount_id']
        ]);
        echo "<script>alert('Kode diskon berhasil diupdate!'); window.location='discounts.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle Delete Discount
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM discount_codes WHERE id = ?")->execute([$_GET['delete']]);
        echo "<script>alert('Kode diskon berhasil dihapus!'); window.location='discounts.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: Tidak bisa menghapus kode diskon yang sedang digunakan.');</script>";
    }
}

// Handle Toggle Active Status
if (isset($_GET['toggle']) && isset($_GET['status'])) {
    $pdo->prepare("UPDATE discount_codes SET is_active = ? WHERE id = ?")->execute([$_GET['status'], $_GET['toggle']]);
    echo "<script>window.location='discounts.php';</script>";
}

// Get Statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM discount_codes")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM discount_codes WHERE is_active = 1 AND (end_date IS NULL OR end_date > NOW())")->fetchColumn(),
    'used_today' => $pdo->query("SELECT COUNT(*) FROM discount_usage WHERE DATE(used_at) = CURDATE()")->fetchColumn(),
    'total_savings' => $pdo->query("SELECT SUM(discount_amount) FROM discount_usage")->fetchColumn() ?: 0
];

// Get All Discounts
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$sql = "SELECT d.*, u.nama_lengkap as created_by_name,
        (SELECT COUNT(*) FROM discount_usage WHERE discount_id = d.id) as total_used
        FROM discount_codes d
        LEFT JOIN users u ON d.created_by = u.id
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (d.code LIKE ? OR d.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'active') {
    $sql .= " AND d.is_active = 1 AND (d.end_date IS NULL OR d.end_date > NOW())";
} elseif ($filter === 'expired') {
    $sql .= " AND d.end_date IS NOT NULL AND d.end_date < NOW()";
} elseif ($filter === 'inactive') {
    $sql .= " AND d.is_active = 0";
}

$sql .= " ORDER BY d.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Diskon";
include 'header.php';
?>

<style>
    .discount-card {
        border: none;
        border-radius: 12px;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        transition: all 0.3s;
        overflow: hidden;
    }
    .discount-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    .discount-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .badge-percentage {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .badge-fixed {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    .code-display {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        font-size: 1.2rem;
        color: #d50000;
        background: #fef2f2;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 2px dashed #d50000;
        display: inline-block;
    }
    .usage-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
    }
    .usage-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        transition: width 0.3s;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark mb-0">Manajemen Kode Diskon</h2>
        <p class="text-muted mb-0">Kelola kode promo dan diskon untuk pelanggan</p>
    </div>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addDiscountModal">
        <i class="fas fa-plus-circle me-2"></i>Buat Kode Diskon
    </button>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="discount-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Total Kode</small>
                    <h2 class="mb-0 fw-bold text-dark"><?php echo $stats['total']; ?></h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="discount-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Kode Aktif</small>
                    <h2 class="mb-0 fw-bold text-success"><?php echo $stats['active']; ?></h2>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="discount-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Digunakan Hari Ini</small>
                    <h2 class="mb-0 fw-bold text-info"><?php echo $stats['used_today']; ?></h2>
                </div>
                <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="discount-card p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Total Hemat</small>
                    <h2 class="mb-0 fw-bold text-warning">Rp <?php echo number_format($stats['total_savings'], 0, ',', '.'); ?></h2>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="filter" class="form-select border-0 bg-light" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" <?php echo $filter=='active'?'selected':''; ?>>Aktif</option>
                    <option value="expired" <?php echo $filter=='expired'?'selected':''; ?>>Kadaluarsa</option>
                    <option value="inactive" <?php echo $filter=='inactive'?'selected':''; ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text border-0 bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari kode diskon..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3 text-end">
                <a href="discounts.php" class="btn btn-light border"><i class="fas fa-sync-alt"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Discounts List -->
<div class="row g-3">
    <?php if(empty($discounts)): ?>
        <div class="col-12">
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada kode diskon</h5>
                    <p class="text-muted">Buat kode diskon pertama untuk menarik pelanggan</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach($discounts as $d): 
            $isExpired = $d['end_date'] && strtotime($d['end_date']) < time();
            $isActive = $d['is_active'] && !$isExpired;
            $usagePercent = $d['usage_limit'] ? ($d['total_used'] / $d['usage_limit']) * 100 : 0;
        ?>
        <div class="col-lg-6 col-12">
            <div class="discount-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="code-display"><?php echo $d['code']; ?></span>
                        <p class="text-muted mb-0 mt-2"><?php echo htmlspecialchars($d['description']); ?></p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item" onclick="editDiscount(<?php echo htmlspecialchars(json_encode($d)); ?>)">
                                <i class="fas fa-edit me-2 text-primary"></i>Edit
                            </button></li>
                            <li><a class="dropdown-item" href="?toggle=<?php echo $d['id']; ?>&status=<?php echo $isActive?'0':'1'; ?>">
                                <i class="fas fa-toggle-<?php echo $isActive?'on':'off'; ?> me-2 text-warning"></i><?php echo $isActive?'Nonaktifkan':'Aktifkan'; ?>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="?delete=<?php echo $d['id']; ?>" onclick="return confirm('Yakin hapus kode diskon ini?')">
                                <i class="fas fa-trash me-2"></i>Hapus
                            </a></li>
                        </ul>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Tipe Diskon</small>
                        <span class="discount-badge <?php echo $d['discount_type']=='percentage'?'badge-percentage':'badge-fixed'; ?>">
                            <?php if($d['discount_type']=='percentage'): ?>
                                <i class="fas fa-percent me-1"></i><?php echo $d['discount_value']; ?>%
                            <?php else: ?>
                                <i class="fas fa-money-bill-wave me-1"></i>Rp <?php echo number_format($d['discount_value'], 0, ',', '.'); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        <?php if($isExpired): ?>
                            <span class="badge bg-secondary">Kadaluarsa</span>
                        <?php elseif($isActive): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Min. Booking</small>
                        <strong>Rp <?php echo number_format($d['min_booking_amount'], 0, ',', '.'); ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Max. Diskon</small>
                        <strong><?php echo $d['max_discount_amount'] ? 'Rp '.number_format($d['max_discount_amount'], 0, ',', '.') : 'Tidak ada batas'; ?></strong>
                    </div>
                </div>

                <?php if($d['usage_limit']): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Penggunaan</small>
                        <small class="fw-bold"><?php echo $d['total_used']; ?> / <?php echo $d['usage_limit']; ?></small>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-bar-fill" style="width: <?php echo min($usagePercent, 100); ?>%"></div>
                    </div>
                </div>
                <?php else: ?>
                <div class="mb-3">
                    <small class="text-muted">Penggunaan: </small>
                    <strong><?php echo $d['total_used']; ?>x (Unlimited)</strong>
                </div>
                <?php endif; ?>

                <div class="row g-2 text-center border-top pt-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Mulai</small>
                        <small class="fw-bold"><?php echo $d['start_date'] ? date('d M Y', strtotime($d['start_date'])) : 'Sekarang'; ?></small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Berakhir</small>
                        <small class="fw-bold"><?php echo $d['end_date'] ? date('d M Y', strtotime($d['end_date'])) : 'Tidak ada'; ?></small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Discount Modal -->
<div class="modal fade" id="addDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Buat Kode Diskon Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kode Diskon <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="Contoh: WELCOME10" required style="text-transform: uppercase;">
                            <small class="text-muted">Gunakan huruf kapital tanpa spasi</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipe Diskon <span class="text-danger">*</span></label>
                            <select name="discount_type" class="form-select" required onchange="toggleDiscountType(this, 'add')">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi singkat tentang diskon ini..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nilai Diskon <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" class="form-control" placeholder="10" required step="0.01" min="0">
                            <small class="text-muted" id="discount-hint-add">Masukkan nilai persentase (1-100)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Min. Nilai Booking</label>
                            <input type="number" name="min_booking_amount" class="form-control" placeholder="500000" step="1000">
                            <small class="text-muted">Minimal pemesanan (opsional)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Max. Potongan</label>
                            <input type="number" name="max_discount_amount" class="form-control" placeholder="100000" step="1000">
                            <small class="text-muted">Batas maksimal diskon (opsional)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Batas Penggunaan</label>
                            <input type="number" name="usage_limit" class="form-control" placeholder="100">
                            <small class="text-muted">Kosongkan untuk unlimited</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="datetime-local" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Berakhir</label>
                            <input type="datetime-local" name="end_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_add" checked>
                                <label class="form-check-label fw-bold" for="is_active_add">Aktifkan kode diskon</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_discount" class="btn btn-danger">
                        <i class="fas fa-save me-2"></i>Simpan Kode Diskon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Discount Modal -->
<div class="modal fade" id="editDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Edit Kode Diskon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="discount_id" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kode Diskon <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_code" class="form-control" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipe Diskon <span class="text-danger">*</span></label>
                            <select name="discount_type" id="edit_type" class="form-select" required onchange="toggleDiscountType(this, 'edit')">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nilai Diskon <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" id="edit_value" class="form-control" required step="0.01" min="0">
                            <small class="text-muted" id="discount-hint-edit">Masukkan nilai persentase (1-100)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Min. Nilai Booking</label>
                            <input type="number" name="min_booking_amount" id="edit_min" class="form-control" step="1000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Max. Potongan</label>
                            <input type="number" name="max_discount_amount" id="edit_max" class="form-control" step="1000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Batas Penggunaan</label>
                            <input type="number" name="usage_limit" id="edit_limit" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="datetime-local" name="start_date" id="edit_start" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Berakhir</label>
                            <input type="datetime-local" name="end_date" id="edit_end" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active">
                                <label class="form-check-label fw-bold" for="edit_active">Aktifkan kode diskon</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_discount" class="btn btn-danger">
                        <i class="fas fa-save me-2"></i>Update Kode Diskon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDiscountType(select, mode) {
    const hint = document.getElementById('discount-hint-' + mode);
    if (select.value === 'percentage') {
        hint.textContent = 'Masukkan nilai persentase (1-100)';
    } else {
        hint.textContent = 'Masukkan nominal dalam Rupiah';
    }
}

function editDiscount(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_code').value = data.code;
    document.getElementById('edit_type').value = data.discount_type;
    document.getElementById('edit_description').value = data.description || '';
    document.getElementById('edit_value').value = data.discount_value;
    document.getElementById('edit_min').value = data.min_booking_amount || '';
    document.getElementById('edit_max').value = data.max_discount_amount || '';
    document.getElementById('edit_limit').value = data.usage_limit || '';
    
    // Format datetime untuk input
    if (data.start_date) {
        const start = new Date(data.start_date);
        document.getElementById('edit_start').value = start.toISOString().slice(0, 16);
    }
    if (data.end_date) {
        const end = new Date(data.end_date);
        document.getElementById('edit_end').value = end.toISOString().slice(0, 16);
    }
    
    document.getElementById('edit_active').checked = data.is_active == 1;
    
    toggleDiscountType(document.getElementById('edit_type'), 'edit');
    
    new bootstrap.Modal(document.getElementById('editDiscountModal')).show();
}
</script>

<?php include '../../php/includes/footer.php'; ?>
