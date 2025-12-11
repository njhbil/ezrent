<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../login.php"); 
    exit(); 
}

require_once '../../php/config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        
        $vid = $pdo->query("SELECT vehicle_id FROM bookings WHERE id = $id")->fetchColumn();
        
        if ($vid) {
            $v_status = ($new_status == 'confirmed' || $new_status == 'ready' || $new_status == 'active') ? 'disewa' : 'tersedia';
            $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?")->execute([$v_status, $vid]);
        }
        
        $pdo->commit();
        echo "<script>alert('Status pesanan berhasil diubah menjadi: " . ucfirst($new_status) . "'); window.location='bookings.php';</script>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Gagal: " . $e->getMessage() . "');</script>";
    }
}

$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT b.*, u.nama_lengkap, u.email, u.nomor_telepon, u.foto_profil, v.nama as kendaraan, v.plat_nomor 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN vehicles v ON b.vehicle_id = v.id 
        WHERE 1=1";

$params = [];
if (!empty($filter_status)) { 
    $sql .= " AND b.status = ?"; 
    $params[] = $filter_status; 
}
if (!empty($search)) { 
    $sql .= " AND (b.kode_booking LIKE ? OR u.nama_lengkap LIKE ?)"; 
    $params[] = "%$search%"; 
    $params[] = "%$search%"; 
}
$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = [
    'pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn(),
    'active' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'active'")->fetchColumn(),
    'income' => $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'completed'")->fetchColumn() ?: 0
];

$page_title = "Manajemen Pesanan";
include 'header.php';
?>

<style>
    .card-stat { border:none; border-radius:12px; background:white; box-shadow:0 2px 10px rgba(0,0,0,0.03); transition:.3s; }
    .card-stat:hover { transform:translateY(-5px); }
    
    .table-custom { border-collapse: separate; border-spacing: 0 8px; }
    .table-custom thead th { border:none; font-weight:600; color:#6c757d; font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; padding: 15px; background: #f8f9fa; }
    .table-custom tbody tr { background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: .2s; }
    .table-custom td { vertical-align: middle; padding: 15px; border: none; }
    .table-custom td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .table-custom td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

    .badge-status { padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.75rem; display: inline-block; min-width: 90px; text-align: center; }
    .st-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .st-confirmed { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .st-ready { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .st-active { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .st-completed { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
    .st-cancelled { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

    .booking-code { font-family: monospace; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; }
    
    .status-options { display: flex; flex-direction: column; gap: 8px; }
    .status-option { position: relative; }
    .status-option input { position: absolute; opacity: 0; }
    .status-option label { 
        display: flex; align-items: center; gap: 12px; padding: 12px 16px; 
        border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; 
        transition: all 0.2s; background: #fff;
    }
    .status-option label:hover { border-color: #d1d5db; background: #f9fafb; }
    .status-option input:checked + label { border-color: #d50000; background: #fef2f2; }
    .status-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .status-label { font-weight: 600; color: #1f2937; font-size: 0.9rem; }
    .status-desc { font-size: 0.75rem; color: #6b7280; margin-left: auto; }
    
    @media (max-width: 767px) {
        .table-custom thead { display: none; }
        .table-custom tbody tr { display: block; margin-bottom: 1rem; padding: 1rem; border-radius: 8px; }
        .table-custom td { display: block; padding: 0.5rem 0; text-align: left; }
        .table-custom td::before { content: attr(data-label); font-weight: 600; color: #6c757d; font-size: 0.75rem; display: block; margin-bottom: 0.25rem; }
        .table-custom td:last-child { text-align: left; }
        .card-stat { margin-bottom: 0.5rem; }
        .card-stat p, .card-stat small { font-size: 0.75rem; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark mb-0">Daftar Pesanan</h2>
        <p class="text-muted mb-0 d-none d-md-block">Kelola status pesanan secara manual.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card card-stat p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div><small class="text-muted">Butuh Konfirmasi</small><h2 class="mb-0 fw-bold text-warning"><?php echo $stats['pending']; ?></h2></div>
                <div class="bg-warning bg-opacity-10 p-2 p-md-3 rounded-circle text-warning d-none d-sm-flex"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card card-stat p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div><small class="text-muted">Sedang Disewa</small><h2 class="mb-0 fw-bold text-success"><?php echo $stats['active']; ?></h2></div>
                <div class="bg-success bg-opacity-10 p-2 p-md-3 rounded-circle text-success d-none d-sm-flex"><i class="fas fa-car-side"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-stat p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div><small class="text-muted">Pendapatan</small><h2 class="mb-0 fw-bold text-primary">Rp <?php echo number_format($stats['income']/1000, 0); ?>k</h2></div>
                <div class="bg-primary bg-opacity-10 p-2 p-md-3 rounded-circle text-primary d-none d-sm-flex"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select border-0 bg-light" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $filter_status=='pending'?'selected':''; ?>>Menunggu</option>
                    <option value="confirmed" <?php echo $filter_status=='confirmed'?'selected':''; ?>>Dikonfirmasi</option>
                    <option value="ready" <?php echo $filter_status=='ready'?'selected':''; ?>>Siap Diambil</option>
                    <option value="active" <?php echo $filter_status=='active'?'selected':''; ?>>Dipinjam</option>
                    <option value="completed" <?php echo $filter_status=='completed'?'selected':''; ?>>Selesai</option>
                    <option value="cancelled" <?php echo $filter_status=='cancelled'?'selected':''; ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text border-0 bg-light"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari Kode Booking / Nama..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3 text-end">
                <a href="bookings.php" class="btn btn-light border"><i class="fas fa-sync-alt"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-custom">
        <thead>
            <tr>
                <th>Booking Info</th>
                <th>Pelanggan</th>
                <th>Kendaraan</th>
                <th>Durasi & Total</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($bookings)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data pesanan.</td></tr>
            <?php else: ?>
                <?php foreach($bookings as $b): ?>
                <tr>
                    <td>
                        <span class="booking-code"><?php echo $b['kode_booking']; ?></span><br>
                        <small class="text-muted text-nowrap"><i class="far fa-clock me-1"></i> <?php echo date('d/m/y H:i', strtotime($b['created_at'])); ?></small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($b['nama_lengkap']); ?></div>
                        <div class="small text-muted"><i class="fab fa-whatsapp me-1 text-success"></i> <?php echo htmlspecialchars($b['nomor_telepon']); ?></div>
                    </td>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($b['kendaraan']); ?></div>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($b['plat_nomor']); ?></span>
                    </td>
                    <td>
                        <div class="fw-bold text-primary">Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?></div>
                        <small class="text-muted"><?php echo $b['total_days']; ?> Hari (<?php echo date('d M', strtotime($b['start_date'])); ?> - <?php echo date('d M', strtotime($b['end_date'])); ?>)</small>
                    </td>
                    <td>
                        <?php 
                            $st = $b['status'];
                            $labels = [
                                'pending' => 'Menunggu',
                                'confirmed' => 'Dikonfirmasi',
                                'ready' => 'Siap Diambil',
                                'active' => 'Dipinjam',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan'
                            ];
                            $label = $labels[$st] ?? ucfirst($st);
                        ?>
                        <span class="badge-status st-<?php echo $st; ?>"><?php echo $label; ?></span>
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-sliders-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header">Kelola Pesanan</h6></li>
                                
                                <li>
                                    <button class="dropdown-item" 
                                            onclick="openStatusModal('<?php echo $b['id']; ?>', '<?php echo $b['kode_booking']; ?>', '<?php echo $b['status']; ?>')">
                                        <i class="fas fa-edit me-2 text-primary"></i>Ubah Status Manual
                                    </button>
                                </li>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $b['id']; ?>">
                                        <i class="fas fa-eye me-2 text-secondary"></i>Lihat Detail
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="detailModal<?php echo $b['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-light">
                                <h5 class="modal-title fw-bold">Detail Pesanan #<?php echo $b['kode_booking']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <!-- Info Pelanggan -->
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                            <i class="fas fa-user me-2"></i>Informasi Pelanggan
                                        </h6>
                                        <div class="d-flex align-items-center mb-3">
                                            <?php 
                                            $profileImg = !empty($b['foto_profil']) ? '../../assets/images/profiles/' . $b['foto_profil'] : null;
                                            if ($profileImg && file_exists('../../assets/images/profiles/' . $b['foto_profil'])): 
                                            ?>
                                            <img src="<?php echo $profileImg; ?>" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                            <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-weight: 600;">
                                                <?php echo strtoupper(substr($b['nama_lengkap'], 0, 1)); ?>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($b['nama_lengkap']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($b['email']); ?></div>
                                            </div>
                                        </div>
                                        <div class="small">
                                            <p class="mb-1"><i class="fab fa-whatsapp text-success me-2"></i><?php echo htmlspecialchars($b['nomor_telepon']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Foto KTP -->
                                    <div class="col-md-6 mb-4">
                                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                            <i class="fas fa-id-card me-2"></i>Identitas (KTP)
                                        </h6>
                                        <?php if (!empty($b['ktp_image'])): 
                                            $ktpPath = '../../php/uploads/ktp/' . $b['ktp_image'];
                                        ?>
                                        <div class="ktp-preview p-2 bg-light rounded border mb-2">
                                            <img src="<?php echo $ktpPath; ?>" 
                                                 alt="Foto KTP" 
                                                 class="img-fluid rounded" 
                                                 style="max-height: 150px; cursor: pointer;"
                                                 onclick="openKTPModal('<?php echo $ktpPath; ?>')">
                                        </div>
                                        <small class="text-muted">No. KTP: <strong><?php echo htmlspecialchars($b['ktp_number'] ?? '-'); ?></strong></small>
                                        <br>
                                        <a href="<?php echo $ktpPath; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-external-link-alt me-1"></i>Buka Foto KTP
                                        </a>
                                        <?php else: ?>
                                        <div class="alert alert-warning small mb-0">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Foto KTP belum diupload
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Info Booking -->
                                <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                    <i class="fas fa-calendar-check me-2"></i>Detail Pesanan
                                </h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Lokasi Ambil</span> 
                                        <strong><?php echo htmlspecialchars($b['pickup_location']); ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Lokasi Kembali</span> 
                                        <strong><?php echo htmlspecialchars($b['return_location']); ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Periode</span> 
                                        <strong><?php echo date('d M Y', strtotime($b['start_date'])); ?> - <?php echo date('d M Y', strtotime($b['end_date'])); ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Total Hari</span> 
                                        <strong><?php echo $b['total_days']; ?> Hari</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Total Harga</span> 
                                        <strong class="text-success">Rp <?php echo number_format($b['total_price'], 0, ',', '.'); ?></strong>
                                    </li>
                                    <?php if (!empty($b['notes'])): ?>
                                    <li class="list-group-item px-0">
                                        <span class="d-block mb-1 text-muted">Catatan</span>
                                        <p class="mb-0 small bg-light p-2 rounded"><?php echo htmlspecialchars($b['notes']); ?></p>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="button" class="btn btn-primary" onclick="openStatusModal('<?php echo $b['id']; ?>', '<?php echo $b['kode_booking']; ?>', '<?php echo $b['status']; ?>')" data-bs-dismiss="modal">
                                    <i class="fas fa-edit me-1"></i>Ubah Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="booking_id" id="modal_booking_id">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Ubah Status Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Kode Booking</label>
                        <input type="text" class="form-control" id="modal_booking_code" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Status Baru:</label>
                        <div class="status-options">
                            <div class="status-option" onclick="selectStatus('pending')">
                                <input type="radio" name="status" value="pending" id="st_pending">
                                <label for="st_pending">
                                    <span class="status-icon" style="background: #fff7ed; color: #c2410c;">⏳</span>
                                    <span class="status-label">Menunggu</span>
                                    <span class="status-desc">Menunggu pembayaran/konfirmasi</span>
                                </label>
                            </div>
                            <div class="status-option" onclick="selectStatus('confirmed')">
                                <input type="radio" name="status" value="confirmed" id="st_confirmed">
                                <label for="st_confirmed">
                                    <span class="status-icon" style="background: #eff6ff; color: #1d4ed8;">✓</span>
                                    <span class="status-label">Dikonfirmasi</span>
                                    <span class="status-desc">Pembayaran diterima</span>
                                </label>
                            </div>
                            <div class="status-option" onclick="selectStatus('ready')">
                                <input type="radio" name="status" value="ready" id="st_ready">
                                <label for="st_ready">
                                    <span class="status-icon" style="background: #fef3c7; color: #d97706;">🚗</span>
                                    <span class="status-label">Siap Diambil</span>
                                    <span class="status-desc">Kendaraan siap untuk pickup</span>
                                </label>
                            </div>
                            <div class="status-option" onclick="selectStatus('active')">
                                <input type="radio" name="status" value="active" id="st_active">
                                <label for="st_active">
                                    <span class="status-icon" style="background: #f0fdf4; color: #15803d;">🔑</span>
                                    <span class="status-label">Sedang Dipinjam</span>
                                    <span class="status-desc">Kendaraan sedang dipakai</span>
                                </label>
                            </div>
                            <div class="status-option" onclick="selectStatus('completed')">
                                <input type="radio" name="status" value="completed" id="st_completed">
                                <label for="st_completed">
                                    <span class="status-icon" style="background: #f3f4f6; color: #374151;">✅</span>
                                    <span class="status-label">Selesai</span>
                                    <span class="status-desc">Kendaraan sudah dikembalikan</span>
                                </label>
                            </div>
                            <div class="status-option" onclick="selectStatus('cancelled')">
                                <input type="radio" name="status" value="cancelled" id="st_cancelled">
                                <label for="st_cancelled">
                                    <span class="status-icon" style="background: #fef2f2; color: #b91c1c;">✕</span>
                                    <span class="status-label">Dibatalkan</span>
                                    <span class="status-desc">Pesanan dibatalkan</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function selectStatus(status) {
    document.getElementById('st_' + status).checked = true;
}

function openStatusModal(id, code, currentStatus) {
    // Isi data ke dalam modal
    document.getElementById('modal_booking_id').value = id;
    document.getElementById('modal_booking_code').value = code;
    
    // Select current status
    const radio = document.getElementById('st_' + currentStatus);
    if (radio) radio.checked = true;
    
    // Tampilkan modal
    var myModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    myModal.show();
}

function openKTPModal(imgSrc) {
    document.getElementById('ktpFullImage').src = imgSrc;
    var ktpModal = new bootstrap.Modal(document.getElementById('ktpFullModal'));
    ktpModal.show();
}
</script>

<!-- KTP Fullscreen Modal -->
<div class="modal fade" id="ktpFullModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Foto KTP</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="ktpFullImage" alt="KTP" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

        </div>
    </main>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>