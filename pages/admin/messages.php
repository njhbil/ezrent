<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../../php/config/database.php';

// --- HANDLE REPLY ---
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $id = $_POST['msg_id'];
    $reply = $_POST['reply'];
    $admin_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("UPDATE messages SET reply = ?, admin_id = ?, status = 'replied', replied_at = NOW() WHERE id = ?");
    $stmt->execute([$reply, $admin_id, $id]);
    
    header("Location: messages.php?success=1"); exit();
}

// Ambil Pesan
$msgs = $pdo->query("
    SELECT m.*, u.nama_lengkap, u.email 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalMsgs = count($msgs);
$newMsgs = count(array_filter($msgs, fn($m) => $m['status'] == 'new'));
$repliedMsgs = count(array_filter($msgs, fn($m) => $m['status'] == 'replied'));

$page_title = "Pesan Masuk";
include 'header.php';
?>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>Balasan berhasil dikirim!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column - Messages List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-envelope"></i> Daftar Pesan</span>
                <span class="badge bg-primary"><?php echo $totalMsgs; ?> Pesan</span>
            </div>
            <div class="card-body p-0">
                <?php if(empty($msgs)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada pesan masuk</h5>
                        <p class="text-muted">Pesan dari pelanggan akan muncul di sini</p>
                    </div>
                <?php else: ?>
                    <?php foreach($msgs as $m): ?>
                    <div class="p-4 border-bottom <?php echo ($m['status']=='new') ? 'bg-light' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div style="width: 45px; height: 45px; background: <?php echo ($m['status']=='new') ? 'linear-gradient(135deg, #d50000, #b71c1c)' : '#6b7280'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; margin-right: 1rem;">
                                    <?php echo strtoupper(substr($m['nama_lengkap'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($m['nama_lengkap']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($m['email']); ?></small>
                                </div>
                            </div>
                            <div class="text-end">
                                <?php if($m['status']=='new'): ?>
                                    <span class="badge bg-danger">Baru</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Dibalas</span>
                                <?php endif; ?>
                                <br><small class="text-muted"><?php echo date('d M Y H:i', strtotime($m['created_at'])); ?></small>
                            </div>
                        </div>
                        
                        <h6 class="text-primary mb-2"><i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($m['subject']); ?></h6>
                        
                        <div class="bg-white p-3 rounded border mb-3">
                            <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                        </div>
                        
                        <?php if($m['reply']): ?>
                            <div class="bg-success bg-opacity-10 p-3 rounded border border-success">
                                <strong class="text-success"><i class="fas fa-reply me-1"></i> Balasan Admin:</strong>
                                <p class="mb-1 mt-2"><?php echo nl2br(htmlspecialchars($m['reply'])); ?></p>
                                <small class="text-muted">Dibalas pada: <?php echo date('d M Y H:i', strtotime($m['replied_at'])); ?></small>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#replyBox<?php echo $m['id']; ?>">
                                <i class="fas fa-reply me-1"></i> Balas Pesan
                            </button>
                            
                            <div class="collapse mt-3" id="replyBox<?php echo $m['id']; ?>">
                                <form method="POST" class="bg-light p-3 rounded">
                                    <input type="hidden" name="reply_message" value="1">
                                    <input type="hidden" name="msg_id" value="<?php echo $m['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Tulis Balasan</label>
                                        <textarea name="reply" class="form-control" rows="4" placeholder="Ketik balasan Anda di sini..." required></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Kirim Balasan</button>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#replyBox<?php echo $m['id']; ?>">Batal</button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Stats & Info -->
    <div class="col-lg-4">
        <!-- Stats Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i>
                Statistik Pesan
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                    <div>
                        <i class="fas fa-envelope fa-2x text-primary"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0"><?php echo $totalMsgs; ?></h3>
                        <small class="text-muted">Total Pesan</small>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-danger bg-opacity-10 rounded">
                    <div>
                        <i class="fas fa-envelope-open fa-2x text-danger"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0"><?php echo $newMsgs; ?></h3>
                        <small class="text-muted">Belum Dibalas</small>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center p-3 bg-success bg-opacity-10 rounded">
                    <div>
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="mb-0"><?php echo $repliedMsgs; ?></h3>
                        <small class="text-muted">Sudah Dibalas</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tips Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-lightbulb"></i>
                Tips Respon
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Balas pesan dalam 24 jam untuk kepuasan pelanggan</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Gunakan bahasa yang sopan dan profesional</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Berikan solusi yang jelas untuk pertanyaan pelanggan</small>
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Periksa pesan baru secara berkala</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

        </div>
    </main>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>