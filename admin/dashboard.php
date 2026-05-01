<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
}
echo "Admin";
?>
<a href="../logout.php" class="btn btn-heritage btn-sm">
                Logout
            </a>
<?php
die();
?>

$stmt = $pdo->prepare("SELECT visit_date, COUNT(*) as reservations_count FROM reservations GROUP BY visit_date ORDER BY visit_date DESC LIMIT 7");
$stmt->execute();
$data = $stmt->fetchAll();
?>

<h3>Grafik Penjualan (7 Hari Terakhir)</h3>
<canvas id="salesChart"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($data, 'visit_date')) ?>,
            datasets: [{
                label: 'Jumlah Reservasi',
                data: <?= json_encode(array_column($data, 'reservations_count')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        }
    });
</script>