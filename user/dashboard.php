<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureUserLogin();

updateExpiredReservations($pdo);

$search = trim($_GET['search'] ?? '');
$page   = max((int) ($_GET['page'] ?? 1), 1);
$limit  = 5;
$offset = ($page - 1) * $limit;
$userId = currentUserId();

$where  = "WHERE r.id_user = ? AND r.is_deleted = 0";
$params = [$userId];

if ($search !== '') {
    $where .= " AND (
        r.id_reservasi LIKE ? 
        OR r.tgl_kunjungan LIKE ? 
        OR r.status LIKE ?
    )";
    $keyword = '%' . $search . '%';
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM reservations r {$where}");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetch()['total'];
$totalPages = max((int) ceil($totalRows / $limit), 1);

$sql = "
    SELECT r.*
    FROM reservations r
    {$where}
    ORDER BY r.created_at DESC
    LIMIT {$limit} OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">Benteng Vredeburg</a>
        <div class="d-flex gap-3 align-items-center">
            <span class="text-white d-none d-md-inline">Halo, <?= e(currentUserName()); ?></span>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success shadow-sm">Reservasi berhasil! Silakan segera lakukan konfirmasi pembayaran via WhatsApp.</div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <span class="section-badge mb-2 d-inline-block">Member Area</span>
                <h1 class="h3 fw-bold section-title">Riwayat Reservasi</h1>
                <p class="text-muted-custom mb-0">Pantau status tiket dan jadwal kunjungan Anda.</p>
            </div>
            <a href="reservation.php" class="btn btn-heritage">Buat Reservasi Baru</a>
        </div>

        <!-- Panel Pencarian -->
        <div class="card-panel mb-4 shadow-sm">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Cari ID, Tanggal (YYYY-MM-DD), atau Status" value="<?= e($search); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-heritage w-100">Cari</button>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="card-panel shadow-sm">
            <div class="table-responsive ">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Tgl Kunjungan</th>
                            <th>Jam</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Batas Bayar</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$reservations): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted-custom">
                                    Belum ada riwayat reservasi yang ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td class="fw-bold text-dark">#VRE-<?= str_pad($res['id_reservasi'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?= date('d M Y', strtotime($res['tgl_kunjungan'])); ?></td>
                                <td><?= substr($res['jam_kunjungan'], 0, 5); ?> WIB</td>
                                <td><?= $res['jumlah_orang']; ?> Orang</td>
                                <td class="fw-semibold"><?= rupiah($res['total_harga']); ?></td>
                                <td>
                                    <span class="<?= statusBadgeClass($res['status']); ?>">
                                        <?= statusLabel($res['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($res['status'] === 'pending'): ?>
                                        <small class="text-danger fw-bold">
                                            <?= date('H:i', strtotime($res['created_at'] . ' +2 hours')); ?> WIB
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($res['status'] === 'pending'): ?>
                                        <a href="payment_instruction.php?id=<?= $res['id_reservasi']; ?>" class="btn btn-sm btn-success">Bayar</a>
                                    <?php else: ?>
                                        <a href="detail_reservasi.php?id=<?= $res['id_reservasi']; ?>" class="btn btn-sm btn-outline-secondary">Detail</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                            <a class="page-link text-secondary" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

</body>
</html>