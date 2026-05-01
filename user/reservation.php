<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureUserLogin();

$csrfToken = generateCsrfToken();
$name = currentUserName();
$email = currentUserEmail();

$minDate = date('Y-m-d', strtotime('+1 day'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservasi Tiket - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">Benteng Vredeburg</a>
        <div class="d-flex gap-2 align-items-center">
            <a href="dashboard.php" class="btn btn-outline-heritage btn-sm">Dashboard</a>
            <a href="../logout.php" class="btn btn-heritage btn-sm">Logout</a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger shadow-sm"><?= e($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <span class="section-badge mb-3">Proses Reservasi</span>
            <h1 class="section-title mb-2">Pilih Jadwal Kunjungan</h1>
            <p class="text-muted-custom mb-0">Satu reservasi berlaku untuk satu jam kunjungan (H+1).</p>
        </div>

        <form action="proses_reservasi.php" method="POST" id="reservationForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">
            <input type="hidden" id="ticket_price" value="<?= TICKET_PRICE; ?>">

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-panel shadow-sm">
                        <h4 class="fw-bold mb-4">Data Pemesanan</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control bg-light" value="<?= e($name); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control bg-light" value="<?= e($email); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="tgl_kunjungan" class="form-label">Tanggal Kunjungan</label>
                                <input type="date" name="tgl_kunjungan" id="tgl_kunjungan" 
                                       class="form-control" min="<?= $minDate; ?>" required>
                                <div class="invalid-feedback">Tanggal kunjungan wajib dipilih.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="jam_kunjungan" class="form-label">Jam Kunjungan</label>
                                <select name="jam_kunjungan" id="jam_kunjungan" class="form-select" required disabled>
                                    <option value="">Pilih tanggal dulu</option>
                                    <?php foreach (getOperationalTimes() as $time): ?>
                                        <option value="<?= $time; ?>"><?= substr($time, 0, 5); ?> WIB</option>
                                    <?php endforeach; ?>
                                </select>
                                <small id="quota_info" class="text-muted-custom">Pilih tanggal untuk melihat kuota.</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jumlah_orang" class="form-label">Jumlah Orang</label>
                                <input type="number" name="jumlah_orang" id="jumlah_orang" 
                                       class="form-control" min="1" max="50" value="1" required disabled>
                                <small id="people_warning" class="text-danger d-none">Melebihi sisa kuota!</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Harga Tiket per Orang</label>
                                <input type="text" class="form-control bg-light" value="<?= e(rupiah(TICKET_PRICE)); ?>" readonly>
                            </div>

                            <div class="col-12">
                                <div class="total-box p-3 rounded-3 d-flex justify-content-between align-items-center" style="background: #f8f9fa;">
                                    <div>
                                        <span class="text-muted small">Total Bayar</span>
                                        <h3 id="total_price_text" class="mb-0 fw-bold text-dark"><?= e(rupiah(TICKET_PRICE)); ?></h3>
                                    </div>
                                    <small class="badge bg-secondary">Real-time</small>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-heritage btn-lg w-100" id="confirmButton" disabled>
                                    Konfirmasi Reservasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="summary-card sticky-top shadow-sm" style="top: 100px; padding: 20px; background: #fff; border-radius: 12px;">
                        <span class="section-badge mb-3">Ringkasan</span>
                        <h4 class="fw-bold mb-4">Ringkasan Reservasi</h4>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Nama</span>
                            <strong class="text-end"><?= e($name); ?></strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Email</span>
                            <strong class="text-end text-truncate ms-2" style="max-width: 150px;"><?= e($email); ?></strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tanggal</span>
                            <strong id="summary_date" class="text-end">-</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jam</span>
                            <strong id="summary_time" class="text-end">-</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Jumlah</span>
                            <strong id="summary_people" class="text-end">1 orang</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Total Harga</span>
                            <strong id="summary_total" class="h4 mb-0 text-heritage"><?= e(rupiah(TICKET_PRICE)); ?></strong>
                        </div>

                        <div class="alert alert-warning mt-4 mb-0 small border-0">
                            <strong>Catatan:</strong> Status akan <em>Pending</em>. Batas konfirmasi pembayaran adalah 2 jam setelah reservasi dibuat.
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

    const sDate = document.getElementById('summary_date');
    const sTime = document.getElementById('summary_time');
    const sPeople = document.getElementById('summary_people');
    const sTotal = document.getElementById('summary_total');

    let sisaKuotaGlobal = 0;

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    };

    tglInput.addEventListener('change', () => {
        if(tglInput.value) {
            jamInput.disabled = false;
            sDate.innerText = tglInput.value;
            fetchQuota();
        }
    });

    jamInput.addEventListener('change', () => {
        sTime.innerText = jamInput.options[jamInput.selectedIndex].text;
        fetchQuota();
    });

    paxInput.addEventListener('input', () => {
        const val = parseInt(paxInput.value) || 0;
        const total = val * ticketPrice;
        
        priceText.innerText = formatRupiah(total);
        sTotal.innerText = formatRupiah(total);
        sPeople.innerText = val + " orang";

        if(val > sisaKuotaGlobal) {
            document.getElementById('people_warning').classList.remove('d-none');
            btnSubmit.disabled = true;
        } else if (val <= 0) {
            btnSubmit.disabled = true;
        } else {
            document.getElementById('people_warning').classList.add('d-none');
            if(jamInput.value) btnSubmit.disabled = false;
        }
    });

    function fetchQuota() {
        const tgl = tglInput.value;
        const jam = jamInput.value;
        if(!tgl || !jam) return;

        fetch(`../ajax/cek_kuota.php?tanggal=${tgl}&jam=${jam}`)
            .then(res => res.json())
            .then(data => {
                sisaKuotaGlobal = data.sisa;
                const info = document.getElementById('quota_info');
                if(data.sisa > 0) {
                    info.innerHTML = `<span class="text-success fw-bold">Tersedia: ${data.sisa} tempat</span>`;
                    paxInput.disabled = false;
                    paxInput.max = data.sisa;
                    btnSubmit.disabled = (parseInt(paxInput.value) > data.sisa || parseInt(paxInput.value) <= 0);
                } else {
                    info.innerHTML = `<span class="text-danger fw-bold">Penuh! Pilih jam/hari lain.</span>`;
                    paxInput.disabled = true;
                    btnSubmit.disabled = true;
                }
            })
            .catch(err => console.error("Gagal mengambil data kuota"));
    }
});
</script>

</body>
</html>