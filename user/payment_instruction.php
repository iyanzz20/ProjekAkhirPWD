<?php
require_once "../config/auth_user.php";

$id = (int) ($_GET['id'] ?? 0);
$userId = currentUserId();

$stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.name,
        u.email
    FROM reservations r
    JOIN users u ON u.id = r.user_id
    WHERE r.id = ?
    AND r.user_id = ?
    AND r.is_deleted = 0
    LIMIT 1
");
$stmt->execute(array($id, $userId));
$reservation = $stmt->fetch();

if (!$reservation) {
    redirect('dashboard.php');
}

/*
|--------------------------------------------------------------------------
| Kode booking sekarang memakai ID reservasi
| Contoh hasil: VRD-00001
|--------------------------------------------------------------------------
*/
$bookingCode = 'VRD-' . str_pad($reservation['id'], 5, '0', STR_PAD_LEFT);

$message = "Halo Admin, saya ingin konfirmasi pembayaran untuk reservasi atas nama "
    . $reservation['name']
    . " dengan kode booking "
    . $bookingCode
    . " pada tanggal "
    . date('d/m/Y', strtotime($reservation['visit_date']))
    . " jam "
    . substr($reservation['visit_time'], 0, 5)
    . ". Jumlah pengunjung "
    . $reservation['total_people']
    . " orang. Total pembayaran "
    . rupiah($reservation['total_price'])
    . ".";

$waLink = "https://wa.me/" . ADMIN_WHATSAPP . "?text=" . urlencode($message);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Instruksi Pembayaran - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?= time(); ?>" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            Benteng Vredeburg
        </a>

        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-heritage btn-sm">
                Dashboard
            </a>
            <a href="../logout.php" class="btn btn-heritage btn-sm">
                Logout
            </a>
        </div>
    </div>
</nav>

<main class="section-padding">
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Reservasi berhasil dibuat. Silakan konfirmasi pembayaran melalui WhatsApp.
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card-panel">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                        <div>
                            <span class="section-badge mb-3">Checkout</span>
                            <h1 class="section-title mb-1">Instruksi Pembayaran</h1>
                            <p class="text-muted-custom mb-0">
                                Pembayaran masih manual melalui WhatsApp admin.
                            </p>
                        </div>

                        <span class="<?= e(statusBadgeClass($reservation['status'])); ?> align-self-start">
                            <?= e(statusLabel($reservation['status'])); ?>
                        </span>
                    </div>

                    <div class="booking-box mb-4">
                        <span>Kode Booking</span>
                        <strong><?= e($bookingCode); ?></strong>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="detail-box">
                                <span>Nama</span>
                                <strong><?= e($reservation['name']); ?></strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-box">
                                <span>Email</span>
                                <strong><?= e($reservation['email']); ?></strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-box">
                                <span>Tanggal Kunjungan</span>
                                <strong><?= e(date('d/m/Y', strtotime($reservation['visit_date']))); ?></strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-box">
                                <span>Jam Kunjungan</span>
                                <strong>
                                    <?= e(substr($reservation['visit_time'], 0, 5)); ?>
                                    -
                                    <?= e(date('H:i', strtotime($reservation['visit_time'] . ' +1 hour'))); ?>
                                </strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-box">
                                <span>Jumlah Orang</span>
                                <strong><?= e($reservation['total_people']); ?> orang</strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-box">
                                <span>Total Bayar</span>
                                <strong><?= e(rupiah($reservation['total_price'])); ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if ($reservation['status'] === 'pending'): ?>
                        <div class="alert alert-warning">
                            Batas konfirmasi pembayaran:
                            <strong><?= e(date('d/m/Y H:i', strtotime($reservation['payment_deadline']))); ?></strong>.
                            Jika melewati batas ini, reservasi otomatis menjadi kadaluarsa.
                        </div>

                        <a href="<?= e($waLink); ?>" target="_blank" class="btn btn-success btn-lg w-100 mb-3">
                            Kirim Bukti via WhatsApp
                        </a>
                    <?php elseif ($reservation['status'] === 'paid'): ?>
                        <div class="alert alert-success mb-0">
                            Pembayaran sudah diverifikasi admin. Silakan datang sesuai jadwal kunjungan.
                        </div>
                    <?php elseif ($reservation['status'] === 'expired'): ?>
                        <div class="alert alert-secondary mb-0">
                            Reservasi ini sudah kadaluarsa karena melewati batas konfirmasi pembayaran.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger mb-0">
                            Reservasi ini tidak aktif.
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="dashboard.php" class="btn btn-outline-heritage">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>