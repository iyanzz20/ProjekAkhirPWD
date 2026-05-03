<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureUserLogin();

$id = (int) ($_GET['id'] ?? 0);
$userId = currentUserId();

$stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.nama_lengkap,
        u.email
    FROM reservations r
    JOIN users u ON u.id_user = r.id_user
    WHERE r.id_reservasi = ?
    AND r.id_user = ?
    AND r.is_deleted = 0
    LIMIT 1
");
$stmt->execute([$id, $userId]);
$res = $stmt->fetch();

if (!$res) {
    redirect('dashboard.php');
}

$bookingCode = 'Kode-' . str_pad($res['id_reservasi'], 3, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Reservasi - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">Benteng Vredeburg</a>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-heritage btn-sm">Dashboard</a>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="mb-3">
                    <a href="dashboard.php" class="text-decoration-none text-muted-custom small">
                        &larr; Kembali ke Daftar Riwayat
                    </a>
                </div>

                <div class="card-panel shadow-sm">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <span class="section-badge mb-2 d-inline-block">Detail Tiket</span>
                            <h1 class="h3 fw-bold section-title"><?= e($bookingCode); ?></h1>
                            <p class="text-muted-custom mb-0">Informasi lengkap reservasi Anda.</p>
                        </div>
                        <span class="<?= statusBadgeClass($res['status']); ?> fs-6 py-2 px-3">
                            <?= statusLabel($res['status']); ?>
                        </span>
                    </div>

                    <hr class="my-4">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-muted-custom small fw-bold text-uppercase">Informasi Pengunjung</h6>
                            <p class="mb-1 fw-bold"><?= e($res['nama_lengkap']); ?></p>
                            <p class="text-muted small"><?= e($res['email']); ?></p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted-custom small fw-bold text-uppercase">Waktu Kunjungan</h6>
                            <p class="mb-1 fw-bold"><?= date('l, d F Y', strtotime($res['tgl_kunjungan'])); ?></p>
                            <p class="text-muted small"><?= substr($res['jam_kunjungan'], 0, 5); ?> - <?= date('H:i', strtotime($res['jam_kunjungan'] . ' +1 hour')); ?> WIB</p>
                        </div>

                        <div class="col-12">
                            <div class="bg-light p-3 rounded-3 border">
                                <h6 class="text-muted-custom small fw-bold text-uppercase mb-3">Rincian Tiket</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Harga per Tiket</span>
                                    <span><?= rupiah(TICKET_PRICE); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Jumlah Orang</span>
                                    <span>x<?= $res['jumlah_orang']; ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Total Pembayaran</span>
                                    <span class="h5 fw-bold text-heritage mb-0"><?= rupiah($res['total_harga']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="alert alert-secondary border-0 small">
                                <ul class="mb-0">
                                    <li>Dipesan pada: <?= date('d/m/Y H:i', strtotime($res['created_at'])); ?></li>
                                    <?php if($res['status'] === 'confirmed'): ?>
                                        <li class="text-success fw-bold">Diverifikasi oleh Admin pada: <?= date('d/m/Y H:i', strtotime($res['updated_at'])); ?></li>
                                    <?php elseif($res['status'] === 'canceled'): ?>
                                        <li class="text-danger">Dibatalkan otomatis oleh sistem atau admin.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex gap-2">
                        
                        <a href="https://wa.me/<?= ADMIN_WHATSAPP ?>" target="_blank" class="btn btn-outline-success">
                            Bantuan WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
@media print {
    .navbar, .btn, .text-muted-custom, .alert, .section-badge { display: none !important; }
    .card-panel { border: none !important; box-shadow: none !important; }
    body { background: white !important; }
}
</style>

</body>
</html>