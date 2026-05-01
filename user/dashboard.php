<?php
require_once "../config/auth_user.php";

$search = trim($_GET['search'] ?? '');
$page = max((int) ($_GET['page'] ?? 1), 1);
$limit = 8;
$offset = ($page - 1) * $limit;
$userId = currentUserId();

$where = "WHERE r.user_id = ? AND r.is_deleted = 0";
$params = array($userId);

if ($search !== '') {
    $where .= " AND (
        CONCAT('VRD-', LPAD(r.id, 5, '0')) LIKE ?
        OR CAST(r.id AS CHAR) LIKE ?
        OR r.visit_date LIKE ?
        OR r.status LIKE ?
    )";

    $keyword = '%' . $search . '%';

    $params[] = $keyword;
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
    <link href="../assets/css/style.css?v=<?= time(); ?>" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            Benteng Vredeburg
        </a>

        <div class="d-flex gap-2 align-items-center">
            <span class="small text-muted-custom d-none d-md-inline">
                Halo, <?= e(currentUserName()); ?>
            </span>

            <a href="reservation.php" class="btn btn-outline-heritage btn-sm">
                Reservasi Baru
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
                Reservasi berhasil dibuat. Silakan lakukan konfirmasi pembayaran melalui WhatsApp.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['cancelled'])): ?>
            <div class="alert alert-success">
                Reservasi berhasil dibatalkan.
            </div>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <span class="section-badge mb-3">
                    Dashboard User
                </span>

                <h1 class="section-title mb-1">
                    Riwayat Reservasi
                </h1>

                <p class="text-muted-custom mb-0">
                    Pantau status reservasi dan instruksi pembayaran kamu.
                </p>
            </div>

            <a href="reservation.php" class="btn btn-heritage">
                Buat Reservasi
            </a>
        </div>

        <div class="card-panel mb-4">
            <form method="GET" class="row g-2">
                <div class="col-md-10">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari kode booking, tanggal, atau status" 
                        value="<?= e($search); ?>"
                    >
                </div>

                <div class="col-md-2">
                    <button class="btn btn-heritage w-100">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="card-panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Orang</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$reservations): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted-custom py-4">
                                    Belum ada data reservasi.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($reservations as $reservation): ?>
                            <?php
                            $bookingCode = 'KODE-' . str_pad($reservation['id'], 5, '0', STR_PAD_LEFT);
                            ?>

                            <tr>
                                <td class="fw-semibold">
                                    <?= e($bookingCode); ?>
                                </td>

                                <td>
                                    <?= e(date('d/m/Y', strtotime($reservation['visit_date']))); ?>
                                </td>

                                <td>
                                    <?= e(substr($reservation['visit_time'], 0, 5)); ?>
                                </td>

                                <td>
                                    <?= e($reservation['total_people']); ?>
                                </td>

                                <td>
                                    <?= e(rupiah($reservation['total_price'])); ?>
                                </td>

                                <td>
                                    <span class="<?= e(statusBadgeClass($reservation['status'])); ?>">
                                        <?= e(statusLabel($reservation['status'])); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <?= e(date('d/m/Y H:i', strtotime($reservation['payment_deadline']))); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <td class="text-end">
                                    <?php if ($reservation['status'] === 'pending'): ?>
                                        <a 
                                            href="payment_instruction.php?id=<?= e($reservation['id']); ?>" 
                                            class="btn btn-sm btn-outline-heritage"
                                        >
                                            Bayar
                                        </a>

                                        <a 
                                            href="cancel_reservation.php?id=<?= e($reservation['id']); ?>" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Batalkan reservasi ini?')"
                                        >
                                            Batal
                                        </a>
                                    <?php else: ?>
                                        <a 
                                            href="payment_instruction.php?id=<?= e($reservation['id']); ?>" 
                                            class="btn btn-sm btn-outline-secondary"
                                        >
                                            Detail
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-end mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                                <a 
                                    class="page-link" 
                                    href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"
                                >
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

</body>
</html>