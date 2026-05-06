<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureUserLogin();

$id = (int)($_GET['id'] ?? 0);
$userId = currentUserId();

$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id_reservasi = ? AND id_user = ? AND is_deleted = 0 LIMIT 1");
$stmt->execute([$id, $userId]);
$res = $stmt->fetch();

if (!$res || $res['status'] !== 'pending') {
    redirect('dashboard.php');
}

$csrfToken = generateCsrfToken();
$minDate = date('Y-m-d', strtotime('+1 day'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Reservasi - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">Benteng Vredeburg</a>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-heritage btn-sm">Batal</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="mb-4">
            <span class="section-badge mb-2">Mode Edit</span>
            <h1 class="h3 fw-bold section-title">Ubah Jadwal Reservasi</h1>
            <p class="text-muted-custom">Anda hanya dapat mengubah data selama status masih <strong>Pending</strong>.</p>
        </div>

        <form action="proses_edit_reservasi.php" method="POST" id="reservationForm">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">
            <input type="hidden" name="id_reservasi" value="<?= $res['id_reservasi']; ?>">
            <input type="hidden" id="ticket_price" value="<?= TICKET_PRICE; ?>">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card-panel shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Kunjungan Baru</label>
                                <input type="date" name="tgl_kunjungan" id="tgl_kunjungan" 
                                       class="form-control" min="<?= $minDate; ?>" 
                                       value="<?= $res['tgl_kunjungan']; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jam Kunjungan Baru</label>
                                <select name="jam_kunjungan" id="jam_kunjungan" class="form-select" required>
                                    <?php foreach (getOperationalTimes() as $time): ?>
                                        <option value="<?= $time; ?>" <?= $res['jam_kunjungan'] === $time ? 'selected' : ''; ?>>
                                            <?= substr($time, 0, 5); ?> WIB
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small id="quota_info" class="text-muted-custom small">Mengecek kuota...</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jumlah Orang</label>
                                <input type="number" name="jumlah_orang" id="jumlah_orang" 
                                       class="form-control" min="1" max="50" 
                                       value="<?= $res['jumlah_orang']; ?>" required>
                                <small id="people_warning" class="text-danger d-none">Melebihi sisa kuota!</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Total Harga Baru</label>
                                <h3 id="total_price_text" class="text-heritage fw-bold mt-1"><?= rupiah($res['total_harga']); ?></h3>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-heritage btn-lg w-100" id="confirmButton">
                                    Simpan Perubahan
                                </button>
                                <p class="text-center mt-3 small text-muted">
                                    Catatan: Mengubah data akan memperbarui batas waktu pembayaran (2 jam dari sekarang).
                                </p>
                            </div>
                        </div>
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
    
    const resId = document.querySelector('input[name="id_reservasi"]').value;

    let sisaKuotaGlobal = 50;

    function fetchQuota() {
        const tgl = tglInput.value;
        const jam = jamInput.value;
        if(!tgl || !jam) return;

        fetch(`../ajax/cek_kuota.php?tanggal=${tgl}&jam=${jam}&exclude_id=${resId}`)
            .then(res => res.json())
            .then(data => {
                sisaKuotaGlobal = data.sisa;
                const info = document.getElementById('quota_info');
                info.innerHTML = `Sisa kuota tersedia: <strong>${data.sisa}</strong>`;
                validateForm();
            });
    }

    function validateForm() {
        const val = parseInt(paxInput.value) || 0;
        const total = val * ticketPrice;
        
        const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
        priceText.innerText = formatted;

        if(val > sisaKuotaGlobal) {
            document.getElementById('people_warning').classList.remove('d-none');
            btnSubmit.disabled = true;
        } else if (val <= 0) {
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