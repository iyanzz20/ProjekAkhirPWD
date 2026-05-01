<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();
updateExpiredReservations($pdo);

$search = trim($_GET['search'] ?? '');

$where = "WHERE r.status = 'pending' AND r.is_deleted = 0";
$params = [];

if ($search !== '') {
    $where .= " AND (r.id_reservasi LIKE ? OR u.nama_lengkap LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = "SELECT r.*, u.nama_lengkap, u.email 
        FROM reservations r 
        JOIN users u ON r.id_user = u.id_user 
        $where 
        ORDER BY r.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pendingList = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Validasi Pembayaran - <?= e(APP_NAME); ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="validasi.php">Validasi</a></li>
                <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
            </ul>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="mb-4">
            <span class="section-badge mb-2">Konfirmasi Manual</span>
            <h1 class="h3 fw-bold section-title">Validasi Pembayaran</h1>
            <p class="text-muted-custom">Daftar reservasi yang menunggu konfirmasi bukti transfer WhatsApp.</p>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Reservasi berhasil dikonfirmasi!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-panel mb-4 shadow-sm">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Cari Kode Booking atau Nama Pengunjung..." value="<?= e($search); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-heritage w-100">Cari</button>
                </div>
            </form>
        </div>

        <div class="card-panel shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Pesan</th>
                            <th>Kode</th>
                            <th>Nama Pengunjung</th>
                            <th>Jadwal Kunjungan</th>
                            <th>Total</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$pendingList): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Tidak ada pembayaran yang perlu divalidasi.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($pendingList as $row): ?>
                            <tr>
                                <td class="small"><?= date('d/m H:i', strtotime($row['created_at'])); ?></td>
                                <td class="fw-bold">#VRE-<?= str_pad($row['id_reservasi'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($row['nama_lengkap']); ?></div>
                                    <div class="small text-muted"><?= e($row['email']); ?></div>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= date('d M Y', strtotime($row['tgl_kunjungan'])); ?></div>
                                    <div class="small"><?= substr($row['jam_kunjungan'], 0, 5); ?> WIB</div>
                                </td>
                                <td class="fw-bold text-brick"><?= rupiah($row['total_harga']); ?></td>
                                <td class="text-center">
                                    <form action="proses_validasi.php" method="POST" onsubmit="return confirm('Yakin sudah cek bukti transfer untuk reservasi ini?')">
                                        <input type="hidden" name="id_reservasi" value="<?= $row['id_reservasi']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm px-3">Konfirmasi</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>