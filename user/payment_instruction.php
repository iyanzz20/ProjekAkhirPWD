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
$reservation = $stmt->fetch();

if (!$reservation) {
    redirect('dashboard.php');
}

$bookingCode = 'VRE-' . str_pad($reservation['id_reservasi'], 5, '0', STR_PAD_LEFT);

$deadline = date('d/m/Y H:i', strtotime($reservation['created_at'] . ' +2 hours'));

$message = "Halo Admin, saya ingin konfirmasi pembayaran untuk reservasi:\n\n"
    . "*Nama:* " . $reservation['nama_lengkap'] . "\n"
    . "*Kode Booking:* " . $bookingCode . "\n"
    . "*Tanggal:* " . date('d/m/Y', strtotime($reservation['tgl_kunjungan'])) . "\n"
    . "*Jam:* " . substr($reservation['jam_kunjungan'], 0, 5) . " WIB\n"
    . "*Jumlah:* " . $reservation['jumlah_orang'] . " orang\n"
    . "*Total:* " . rupiah($reservation['total_harga']) . "\n\n"
    . "Mohon untuk segera divalidasi. Terima kasih.";

$waLink = "https://wa.me/" . ADMIN_WHATSAPP . "?text=" . urlencode($message);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Instruksi Pembayaran - <?= e(APP_NAME); ?></title>
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
                <div class="card-panel shadow-sm">
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <span class="section-badge mb-3">Checkout</span>
                            <h1 class="h3 fw-bold section-title">Instruksi Pembayaran</h1>
                            <p class="text-muted-custom">Selesaikan pembayaran Anda agar kuota tidak hangus.</p>
                        </div>
                        <span class="<?= statusBadgeClass($reservation['status']); ?>">
                            <?= statusLabel($reservation['status']); ?>
                        </span>
                    </div>

                    <div class="p-3 mb-4 text-center rounded-3" style="background: #f8f9fa; border: 2px dashed #dee2e6;">
                        <span class="text-muted small d-block">Kode Booking</span>
                        <h2 class="fw-bold text-dark mb-0"><?= e($bookingCode); ?></h2>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">Nama Pemesan</span>
                                <strong><?= e($reservation['nama_lengkap']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">Jadwal Kunjungan</span>
                                <strong><?= date('d/m/Y', strtotime($reservation['tgl_kunjungan'])); ?> | <?= substr($reservation['jam_kunjungan'], 0, 5); ?> WIB</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3">
                                <span class="text-muted small d-block">Jumlah Pengunjung</span>
                                <strong><?= $reservation['jumlah_orang']; ?> Orang</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-light">
                                <span class="text-muted small d-block">Total Pembayaran</span>
                                <strong class="text-primary h5 mb-0"><?= rupiah($reservation['total_harga']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if ($reservation['status'] === 'pending'): ?>
                        <div class="alert alert-warning border-0 shadow-sm">
                            <i class="bi bi-clock-history"></i> Batas konfirmasi pembayaran: 
                            <strong><?= e($deadline); ?> WIB</strong>.
                            <br><small>Jika melewati batas ini, sistem akan membatalkan reservasi Anda secara otomatis.</small>
                        </div>

                        <div class="card card-body bg-light border-0 mb-4">
                            <h6 class="fw-bold">Cara Pembayaran:</h6>
                            <ol class="small mb-0">
                                <li>Transfer ke rekening Bank ABC <strong>123-456-789</strong> a/n Pengelola Vredeburg.</li>
                                <li>Screenshot bukti transfer Anda.</li>
                                <li>Klik tombol hijau di bawah untuk mengirim bukti ke WhatsApp Admin.</li>
                            </ol>
                        </div>

                        <a href="<?= e($waLink); ?>" target="_blank" class="btn btn-success btn-lg w-100 mb-3 shadow-sm">
                            Kirim Bukti via WhatsApp
                        </a>
                    <?php elseif ($reservation['status'] === 'confirmed'): ?>
                        <div class="alert alert-success py-3 border-0">
                            <strong>Pembayaran Berhasil!</strong> Tiket Anda sudah valid. Silakan tunjukkan Kode Booking di atas saat tiba di lokasi.
                        </div>
                    <?php elseif ($reservation['status'] === 'canceled'): ?>
                        <div class="alert alert-danger py-3 border-0">
                            <strong>Reservasi Dibatalkan.</strong> Waktu pembayaran telah habis atau reservasi ini telah dibatalkan.
                        </div>
                    <?php endif; ?>

                    <div class="mt-4 text-center">
                        <a href="dashboard.php" class="btn btn-link text-decoration-none text-muted">
                            &larr; Kembali ke Dashboard
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>