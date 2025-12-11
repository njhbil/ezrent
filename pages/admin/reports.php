<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }
require_once '../../php/config/database.php';

$total = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status IN ('completed', 'active')")->fetchColumn() ?: 0;
$trx = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Laporan";
include 'header.php';
?>

<h2 class="mb-4">Laporan Keuangan</h2>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <h5>Total Pendapatan</h5>
                <h2>Rp <?php echo number_format($total, 0, ',', '.'); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-white fw-bold">Transaksi Terakhir</div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Kode</th><th>Tanggal</th><th>Nominal</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach($trx as $t): ?>
                <tr>
                    <td><?php echo $t['kode_booking']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($t['created_at'])); ?></td>
                    <td>Rp <?php echo number_format($t['total_price']); ?></td>
                    <td><?php echo ucfirst($t['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

        </div>
    </main>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>