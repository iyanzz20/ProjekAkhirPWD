<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();
updateExpiredReservations($pdo);

$search = trim($_GET['search'] ?? '');
$page   = max((int) ($_GET['page'] ?? 1), 1);
$limit  = 10;
$offset = ($page - 1) * $limit;

$where  = "WHERE r.is_deleted = 0";
$params = [];

if ($search !== '') {
    $where .= " AND (
        r.id_reservasi LIKE ? 
        OR u.nama_lengkap LIKE ? 
        OR r.tgl_kunjungan LIKE ?
    )";
    $keyword = '%' . $search . '%';
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM reservations r JOIN users u ON r.id_user = u.id_user {$where}");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetch()['total'];
$totalPages = max((int) ceil($totalRows / $limit), 1);

$sql = "
    SELECT r.*, u.nama_lengkap, u.email
    FROM reservations r
    JOIN users u ON r.id_user = u.id_user
    {$where}
    ORDER BY r.created_at DESC
    LIMIT {$limit} OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>History Reservasi - <?= e(APP_NAME); ?></title>
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
                <li class="nav-item"><a class="nav-link active" href="history.php">History</a></li>
            </ul>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="mb-4">
            <span class="section-badge mb-2">Arsip Data</span>
            <h1 class="h3 fw-bold section-title">Seluruh Riwayat Reservasi</h1>
            <p class="text-muted-custom">Data transaksi pengunjung dari waktu ke waktu.</p>
        </div>

        <div class="card-panel mb-4 shadow-sm">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Cari Kode, Nama, atau Tanggal (YYYY-MM-DD)..." value="<?= e($search); ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-heritage w-100">Cari Data</button>
                </div>
            </form>
        </div>

        <div class="card-panel shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Pengunjung</th>
                            <th>Jadwal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Updated By</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$history): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td class="fw-bold">#VRE-<?= str_pad($row['id_reservasi'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($row['nama_lengkap']); ?></div>
                                    <div class="small text-muted"><?= e($row['email']); ?></div>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= date('d/m/Y', strtotime($row['tgl_kunjungan'])); ?></div>
                                    <div class="small"><?= substr($row['jam_kunjungan'], 0, 5); ?> WIB</div>
                                </td>
                                <td><?= rupiah($row['total_harga']); ?></td>
                                <td>
                                    <span class="<?= statusBadgeClass($row['status']); ?>">
                                        <?= statusLabel($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= e($row['updated_by'] ?? '-'); ?></small>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">Opsi</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item" href="detail_reservasi.php?id=<?= $row['id_reservasi']; ?>">Lihat Detail</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="proses_delete.php?id=<?= $row['id_reservasi']; ?>" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus data ini? (Soft Delete)')">
                                                    Hapus Data
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>