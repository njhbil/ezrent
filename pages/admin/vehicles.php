<?php
session_start();

// 1. Cek Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../../php/config/database.php';

// --- FUNGSI HELPER ---
function uploadImage($file) {
    $targetDir = "../../assets/images/vehicles/";
    
    // Buat folder jika belum ada
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Validasi Ekstensi
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    if (in_array($fileType, $allowTypes)) {
        // Validasi Ukuran (Max 5MB)
        if ($file["size"] > 5000000) {
            return ['status' => false, 'msg' => 'Ukuran file terlalu besar (Max 5MB).'];
        }

        // Rename file agar unik (mencegah bentrok nama)
        $newFileName = uniqid() . '.' . $fileType;
        $targetFilePath = $targetDir . $newFileName;

        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return ['status' => true, 'fileName' => $newFileName];
        } else {
            return ['status' => false, 'msg' => 'Gagal mengupload file ke server.'];
        }
    } else {
        return ['status' => false, 'msg' => 'Hanya file JPG, JPEG, PNG, GIF, & WEBP yang diperbolehkan.'];
    }
}

// --- LOGIKA HANDLING REQUEST ---

// A. Handle Tambah Kendaraan (Create)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vehicle'])) {
    
    $imageName = 'default.jpg'; // Gambar default jika user tidak upload

    // Cek apakah ada file yang diupload
    if (!empty($_FILES["image"]["name"])) {
        $uploadResult = uploadImage($_FILES["image"]);
        if ($uploadResult['status']) {
            $imageName = $uploadResult['fileName'];
        } else {
            echo "<script>alert('Error Upload: " . $uploadResult['msg'] . "');</script>";
        }
    }

    // Simpan nama file dalam format JSON (sesuai struktur DB Anda)
    $images_json = json_encode([$imageName]);

    try {
        $sql = "INSERT INTO vehicles (nama, jenis, merek, model, tahun, plat_nomor, warna, kapasitas, bahan_bakar, transmisi, harga_per_hari, status, deskripsi, images) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nama'], $_POST['jenis'], $_POST['merek'], $_POST['model'], $_POST['tahun'],
            $_POST['plat_nomor'], $_POST['warna'], $_POST['kapasitas'], $_POST['bahan_bakar'],
            $_POST['transmisi'], $_POST['harga_per_hari'], $_POST['status'], $_POST['deskripsi'], $images_json
        ]);
        
        echo "<script>alert('Kendaraan berhasil ditambahkan!'); window.location='vehicles.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Database Error: " . $e->getMessage() . "');</script>";
    }
}

// B. Handle Hapus Kendaraan (Delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Ambil info gambar dulu untuk dihapus fisiknya
    $stmt = $pdo->prepare("SELECT images FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $v = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($v) {
        // Decode JSON gambar
        $imgs = json_decode($v['images'], true);
        if (!empty($imgs)) {
            foreach ($imgs as $img) {
                // Hapus file jika bukan default.jpg
                if ($img !== 'default.jpg' && file_exists("../../assets/images/vehicles/" . $img)) {
                    unlink("../../assets/images/vehicles/" . $img);
                }
            }
        }

        // Hapus record dari database
        $pdo->prepare("DELETE FROM vehicles WHERE id = ?")->execute([$id]);
        echo "<script>alert('Kendaraan berhasil dihapus!'); window.location='vehicles.php';</script>";
        exit();
    }
}

// C. Ambil Data (Read)
$vehicles = $pdo->query("SELECT * FROM vehicles ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Kendaraan";
include 'header.php';
?>

<!-- Top Bar -->
<div class="top-bar">
    <h1>Manajemen Kendaraan</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
        <i class="fas fa-plus me-2"></i>Tambah Kendaraan
    </button>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <?php
    $total = count($vehicles);
    $tersedia = count(array_filter($vehicles, fn($v) => $v['status'] == 'tersedia'));
    $disewa = count(array_filter($vehicles, fn($v) => $v['status'] == 'disewa'));
    $maintenance = count(array_filter($vehicles, fn($v) => $v['status'] == 'maintenance'));
    ?>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-car"></i></div>
            <div class="stat-value"><?php echo $total; ?></div>
            <div class="stat-label">Total Kendaraan</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="color:#10b981;"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?php echo $tersedia; ?></div>
            <div class="stat-label">Tersedia</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="color:#3b82f6;"><i class="fas fa-key"></i></div>
            <div class="stat-value"><?php echo $disewa; ?></div>
            <div class="stat-label">Disewa</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="color:#f59e0b;"><i class="fas fa-wrench"></i></div>
            <div class="stat-value"><?php echo $maintenance; ?></div>
            <div class="stat-label">Maintenance</div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Kendaraan</span>
        <small><?php echo $total; ?> kendaraan</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Info Kendaraan</th>
                        <th>Jenis</th>
                        <th>Plat Nomor</th>
                        <th>Harga / Hari</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($vehicles)): ?>
                        <tr><td colspan="7" class="text-center py-5">
                            <div style="color:#9ca3af;">
                                <i class="fas fa-car fa-3x mb-3"></i>
                                <p class="mb-0">Belum ada data kendaraan</p>
                            </div>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach($vehicles as $v): 
                            $imgs = json_decode($v['images'], true);
                            $thumb = !empty($imgs) ? $imgs[0] : 'default.jpg';
                        ?>
                        <tr>
                            <td>
                                <img src="../../assets/images/vehicles/<?php echo $thumb; ?>" 
                                     alt="Foto" 
                                     style="width: 80px; height: 55px; object-fit: cover; border: 1px solid #e5e7eb;">
                            </td>
                            <td>
                                <strong style="color:#000;"><?php echo htmlspecialchars($v['nama']); ?></strong><br>
                                <small style="color:#6b7280;">
                                    <?php echo htmlspecialchars($v['merek'] . ' ' . $v['model'] . ' (' . $v['tahun'] . ')'); ?>
                                </small>
                            </td>
                            <td>
                                <?php 
                                    $icon = 'car';
                                    if($v['jenis']=='motor') $icon='motorcycle';
                                    if($v['jenis']=='sepeda' || $v['jenis']=='sepeda_listrik') $icon='bicycle';
                                ?>
                                <span style="display:inline-flex;align-items:center;gap:6px;background:#f5f5f5;padding:4px 12px;">
                                    <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo ucfirst($v['jenis']); ?>
                                </span>
                            </td>
                            <td><span style="background:#f9fafb;padding:4px 10px;border:1px solid #e5e7eb;font-family:monospace;"><?php echo htmlspecialchars($v['plat_nomor']); ?></span></td>
                            <td><strong>Rp <?php echo number_format($v['harga_per_hari'], 0, ',', '.'); ?></strong></td>
                            <td>
                                <?php if($v['status'] == 'tersedia'): ?>
                                    <span class="badge" style="background:#10b981;">Tersedia</span>
                                <?php elseif($v['status'] == 'disewa'): ?>
                                    <span class="badge" style="background:#3b82f6;">Disewa</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Maintenance</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit-vehicle.php?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $v['id']; ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Hapus kendaraan ini?')" title="Hapus">
                                     <i class="fas fa-trash"></i>
                                </a>
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
    </main>

<!-- Modal Tambah Kendaraan -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_vehicle" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-weight:600;">Tambah Kendaraan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Foto Kendaraan</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <small style="color:#6b7280;">Format: JPG, PNG, WEBP. Max: 5MB</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Kendaraan</label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Toyota Avanza" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" name="jenis" required>
                                <option value="mobil">Mobil</option>
                                <option value="motor">Motor</option>
                                <option value="sepeda_listrik">Sepeda Listrik</option>
                                <option value="sepeda">Sepeda</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Merek</label>
                            <input type="text" class="form-control" name="merek" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plat Nomor</label>
                            <input type="text" class="form-control" name="plat_nomor" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warna</label>
                            <input type="text" class="form-control" name="warna" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kapasitas (Orang)</label>
                            <input type="number" class="form-control" name="kapasitas" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bahan Bakar</label>
                            <select class="form-select" name="bahan_bakar">
                                <option value="bensin">Bensin</option>
                                <option value="solar">Solar</option>
                                <option value="listrik">Listrik</option>
                                <option value="manual">Manual (Sepeda)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transmisi</label>
                            <select class="form-select" name="transmisi">
                                <option value="manual">Manual</option>
                                <option value="matic">Matic</option>
                                <option value="otomatis">Otomatis</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga per Hari (Rp)</label>
                            <input type="number" class="form-control" name="harga_per_hari" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Awal</label>
                            <select class="form-select" name="status">
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi kendaraan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>