<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureAdminLogin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.nama_lengkap,
        u.email
    FROM reservations r
    JOIN users u ON r.id_user = u.id_user
    WHERE r.id_reservasi = ?
    AND r.is_deleted = 0
    LIMIT 1
");
$stmt->execute([$id]);
$res = $stmt->fetch();

if (!$res) {
    redirect('history.php');
}

$bookingCode = 'VRE-' . str_pad($res['id_reservasi'], 5, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Reservasi Admin - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Admin Vredeburg</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="validasi.php">Validasi</a></li>
                <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
            </ul>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <a href="history.php" class="btn btn-sm btn-light border text-decoration-none">
                        &larr; Kembali ke History
                    </a>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-sm btn-outline-dark">Cetak Detail</button>
                    </div>
                </div>

                <div class="card-panel shadow-sm">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <span class="section-badge mb-2">Internal Report</span>
                            <h1 class="h3 fw-bold section-title">Rincian Reservasi <?= e($bookingCode); ?></h1>
                            <p class="text-muted-custom mb-0">Informasi detail transaksi dan rekam jejak sistem.</p>
                        </div>
                        <div class="text-end">
                            <span class="<?= statusBadgeClass($res['status']); ?> fs-6 py-2 px-4">
                                <?= statusLabel($res['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-box shadow-sm">
                                <span>Informasi Pengunjung</span>
                                <h5 class="fw-bold mb-1 mt-2"><?= e($res['nama_lengkap']); ?></h5>
                                <p class="text-muted mb-0 small"><?= e($res['email']); ?></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-box shadow-sm">
                                <span>Waktu Kunjungan</span>
                                <h5 class="fw-bold mb-1 mt-2"><?= date('d F Y', strtotime($res['tgl_kunjungan'])); ?></h5>
                                <p class="text-muted mb-0 small"><?= substr($res['jam_kunjungan'], 0, 5); ?> WIB (Durasi 1 Jam)</p>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="booking-box shadow-sm d-flex justify-content-between align-items-center">
                                <div>
                                    <span>Total Pembayaran (<?= $res['jumlah_orang']; ?> Orang)</span>
                                    <h2 class="fw-bold mb-0 text-brick"><?= rupiah($res['total_harga']); ?></h2>
                                </div>
                                <div class="text-end text-muted small">
                                    Status: <?= ucfirst($res['status']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card bg-paper border-0 p-4" style="background: #fdfaf5; border: 1px solid var(--color-border) !important;">
                                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle"></i> Audit Trail & Log Sistem</h6>
                                <div class="row g-3 small">
                                    <div class="col-md-4">
                                        <label class="text-muted d-block">Created At</label>
                                        <strong><?= date('d/m/Y H:i:s', strtotime($res['created_at'])); ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted d-block">Created By (User)</label>
                                        <strong><?= e($res['created_by']); ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted d-block">Current Status</label>
                                        <strong class="text-uppercase"><?= $res['status']; ?></strong>
                                    </div>
                                    
                                    <?php if ($res['updated_at']): ?>
                                    <div class="col-md-4">
                                        <label class="text-muted d-block">Last Verified At</label>
                                        <strong class="text-success"><?= date('d/m/Y H:i:s', strtotime($res['updated_at'])); ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-muted d-block">Verified By (Admin)</label>
                                        <strong class="text-success"><?= e($res['updated_by']); ?></strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-4 text-center">
                            <?php if ($res['status'] === 'pending'): ?>
                                <form action="proses_validasi.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id_reservasi" value="<?= $res['id_reservasi']; ?>">
                                    <button type="submit" class="btn btn-heritage px-5 py-3">Verifikasi Sekarang</button>
                                </form>
                            <?php endif; ?>

                            <a href="proses_delete.php?id=<?= $res['id_reservasi']; ?>" 
                               class="btn btn-outline-danger ms-2"
                               onclick="return confirm('Hapus permanen dari tampilan (Soft Delete)?')">
                               Hapus Data
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<style>
@media print {
    .navbar, .btn, .section-badge, .text-end { display: none !important; }
    .card-panel { border: none !important; box-shadow: none !important; }
    body { background: white !important; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>