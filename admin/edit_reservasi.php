<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureAdminLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT r.*, u.nama_lengkap 
    FROM reservations r 
    JOIN users u ON r.id_user = u.id_user 
    WHERE r.id_reservasi = ? AND r.is_deleted = 0 
    LIMIT 1
");
$stmt->execute([$id]);
$res = $stmt->fetch();

if (!$res) redirect('history.php');

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Edit Reservasi - <?= e(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">Admin Vredeburg</a>
        <a href="history.php" class="btn btn-outline-heritage btn-sm">Kembali</a>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="mb-4">
            <span class="section-badge mb-2">Admin Privilege</span>
            <h1 class="h3 fw-bold section-title">Edit Reservasi #<?= str_pad($res['id_reservasi'], 5, '0', STR_PAD_LEFT); ?></h1>
            <p class="text-muted-custom">Atas nama: <strong><?= e($res['nama_lengkap']); ?></strong></p>
        </div>

        <form action="proses_edit_reservasi.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">
            <input type="hidden" name="id_reservasi" value="<?= $res['id_reservasi']; ?>">
            <input type="hidden" id="ticket_price" value="<?= TICKET_PRICE; ?>">

            <div class="card-panel shadow-sm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Kunjungan</label>
                        <input type="date" name="tgl_kunjungan" id="tgl_kunjungan" class="form-control" value="<?= $res['tgl_kunjungan']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jam Kunjungan</label>
                        <select name="jam_kunjungan" id="jam_kunjungan" class="form-select" required>
                            <?php foreach (getOperationalTimes() as $time): ?>
                                <option value="<?= $time; ?>" <?= $res['jam_kunjungan'] === $time ? 'selected' : ''; ?>>
                                    <?= substr($time, 0, 5); ?> WIB
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small id="quota_info" class="text-muted-custom"></small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Reservasi</label>
                        <select name="status" class="form-select border-primary fw-bold" required>
                            <option value="pending" <?= $res['status'] === 'pending' ? 'selected' : ''; ?>>Pending (Menunggu)</option>
                            <option value="confirmed" <?= $res['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed (Sudah Bayar)</option>
                            <option value="canceled" <?= $res['status'] === 'canceled' ? 'selected' : ''; ?>>Canceled (Batal)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jumlah Orang</label>
                        <input type="number" name="jumlah_orang" id="jumlah_orang" class="form-control" min="1" max="50" value="<?= $res['jumlah_orang']; ?>" required>
                        <small id="people_warning" class="text-danger d-none">Melebihi kuota 50!</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Total Biaya (Otomatis)</label>
                        <h3 id="total_price_text" class="text-brick fw-bold"><?= rupiah($res['total_harga']); ?></h3>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-heritage btn-lg px-5" id="confirmButton">Update Data Reservasi</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tglInput = document.getElementById('tgl_kunjungan');
    const jamInput = document.getElementById('jam_kunjungan');
    const paxInput = document.getElementById('jumlah_orang');
    const btnSubmit = document.getElementById('confirmButton');
    const priceText = document.getElementById('total_price_text');
    const ticketPrice = parseInt(document.getElementById('ticket_price').value);
    const resId = <?= $res['id_reservasi']; ?>;

    let sisaKuotaGlobal = 50;

    function fetchQuota() {
        const tgl = tglInput.value;
        const jam = jamInput.value;
        fetch(`../ajax/cek_kuota.php?tanggal=${tgl}&jam=${jam}&exclude_id=${resId}`)
            .then(res => res.json())
            .then(data => {
                sisaKuotaGlobal = data.sisa;
                document.getElementById('quota_info').innerHTML = `Sisa kuota: <strong>${data.sisa}</strong>`;
                validateForm();
            });
    }

    function validateForm() {
        const val = parseInt(paxInput.value) || 0;
        const total = val * ticketPrice;
        priceText.innerText = "Rp " + total.toLocaleString('id-ID');

        if(val > sisaKuotaGlobal) {
            document.getElementById('people_warning').classList.remove('d-none');
            btnSubmit.disabled = true;
        } else {
            document.getElementById('people_warning').classList.add('d-none');
            btnSubmit.disabled = false;
        }
    }

    tglInput.addEventListener('change', fetchQuota);
    jamInput.addEventListener('change', fetchQuota);
    paxInput.addEventListener('input', validateForm);
    fetchQuota();
});
</script>
</body>
</html>