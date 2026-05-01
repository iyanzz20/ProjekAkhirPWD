<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();
updateExpiredReservations($pdo);

$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$selectedYear  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending' AND is_deleted = 0");
$stmt->execute();
$pendingCount = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(jumlah_orang) as total FROM reservations WHERE tgl_kunjungan = CURDATE() AND status = 'confirmed' AND is_deleted = 0");
$stmt->execute();
$todayVisitors = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT SUM(total_harga) as total FROM reservations WHERE MONTH(tgl_kunjungan) = ? AND YEAR(tgl_kunjungan) = ? AND status = 'confirmed' AND is_deleted = 0");
$stmt->execute([$selectedMonth, $selectedYear]);
$monthlyIncome = $stmt->fetch()['total'] ?? 0;

$chartLabels = [];
$chartData = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
    $chartLabels[] = $day; 
    
    $stmt = $pdo->prepare("
        SELECT SUM(jumlah_orang) as total 
        FROM reservations 
        WHERE DAY(tgl_kunjungan) = ? 
        AND MONTH(tgl_kunjungan) = ? 
        AND YEAR(tgl_kunjungan) = ? 
        AND status = 'confirmed' 
        AND is_deleted = 0
    ");
    $stmt->execute([$day, $selectedMonth, $selectedYear]);
    $chartData[] = (int)($stmt->fetch()['total'] ?? 0);
}

$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Admin Vredeburg</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="validasi.php">Validasi</a></li>
                <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
            </ul>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <span class="section-badge mb-3">Laporan Bulanan</span>
                <h1 class="section-title">Statistik Penjualan</h1>
                <p class="text-muted-custom mb-0">Data untuk bulan <?= $monthNames[$selectedMonth] . ' ' . $selectedYear; ?></p>
            </div>
            
            <form method="GET" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm">
                    <?php foreach ($monthNames as $m => $name): ?>
                        <option value="<?= $m; ?>" <?= $m === $selectedMonth ? 'selected' : ''; ?>><?= $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="year" class="form-select form-select-sm">
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                        <option value="<?= $y; ?>" <?= $y === $selectedYear ? 'selected' : ''; ?>><?= $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-heritage btn-sm">Filter</button>
            </form>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="feature-card shadow-sm border-0">
                    <h6 class="text-muted small fw-bold text-uppercase">Butuh Validasi</h6>
                    <h2 class="fw-bold mb-0 text-danger"><?= $pendingCount; ?></h2>
                    <a href="validasi.php" class="link-heritage small mt-2 d-inline-block">Lihat Semua &rarr;</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card shadow-sm border-0">
                    <h6 class="text-muted small fw-bold text-uppercase">Pengunjung Hari Ini</h6>
                    <h2 class="fw-bold mb-0"><?= $todayVisitors; ?></h2>
                    <p class="text-muted small mt-2">Update otomatis setiap hari</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card shadow-sm border-0">
                    <h6 class="text-muted small fw-bold text-uppercase">Income (<?= $monthNames[$selectedMonth]; ?>)</h6>
                    <h2 class="fw-bold mb-0 text-success"><?= rupiah($monthlyIncome); ?></h2>
                    <p class="text-muted small mt-2">Berdasarkan tiket terverifikasi</p>
                </div>
            </div>
        </div>

        <div class="card-panel shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Tren Pengunjung Harian</h4>
                <span class="badge bg-light text-dark border">Total Slot: 50/jam</span>
            </div>
            <div style="height: 400px;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
</main>

<script>
const ctx = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Jumlah Orang',
            data: <?= json_encode($chartData); ?>,
            backgroundColor: '#7A2E1D',
            borderRadius: 5,
            hoverBackgroundColor: '#4F1F14'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    title: (items) => `Tanggal ${items[0].label} <?= $monthNames[$selectedMonth]; ?>`
                }
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                ticks: { stepSize: 10 },
                grid: { color: '#E8D9C4' }
            },
            x: { 
                title: { display: true, text: 'Tanggal' },
                grid: { display: false }
            }
        }
    }
});
</script>

</body>
</html>