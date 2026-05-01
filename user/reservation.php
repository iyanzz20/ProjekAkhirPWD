<?php
require_once "../config/auth_user.php";

$csrfToken = generateCsrfToken();
$name = currentUserName();
$email = currentUserEmail();
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

<main class="section-padding">
    <div class="container">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= e($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <span class="section-badge mb-3">Proses Reservasi</span>
            <h1 class="section-title mb-2">Pilih Jadwal Kunjungan</h1>
            <p class="text-muted-custom mb-0">Satu reservasi berlaku untuk satu jam kunjungan.</p>
        </div>

        <form action="process_reservation.php" method="POST" id="reservationForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">
            <input type="hidden" id="ticket_price" value="<?= TICKET_PRICE; ?>">

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-panel">
                        <h4 class="fw-bold mb-4">Data Pemesanan</h4>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" value="<?= e($name); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= e($email); ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="visit_date" class="form-label">Tanggal Kunjungan</label>
                                <input type="date" name="visit_date" id="visit_date" class="form-control" required>
                                <div class="invalid-feedback">Tanggal kunjungan wajib dipilih.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="visit_time" class="form-label">Jam Kunjungan</label>
                                <select name="visit_time" id="visit_time" class="form-select" required disabled>
                                    <option value="">Pilih tanggal dulu</option>
                                </select>
                                <small id="quota_info" class="text-muted-custom">Kuota akan muncul setelah tanggal dipilih.</small>
                                <div class="invalid-feedback">Jam kunjungan wajib dipilih.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="total_people" class="form-label">Jumlah Orang</label>
                                <input type="number" name="total_people" id="total_people" class="form-control" min="1" value="1" required>
                                <small id="people_warning" class="text-danger d-none">Jumlah orang melebihi sisa kuota.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Harga Tiket per Orang</label>
                                <input type="text" class="form-control" value="<?= e(rupiah(TICKET_PRICE)); ?>" readonly>
                            </div>

                            <div class="col-12">
                                <div class="total-box">
                                    <div>
                                        <span>Total Bayar</span>
                                        <h3 id="total_price_text" class="mb-0"><?= e(rupiah(TICKET_PRICE)); ?></h3>
                                    </div>
                                    <small>Real-time</small>
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
                    <div class="summary-card sticky-summary">
                        <span class="section-badge mb-3">Ringkasan</span>
                        <h4 class="fw-bold mb-4">Ringkasan Reservasi</h4>

                        <div class="summary-item">
                            <span>Nama</span>
                            <strong><?= e($name); ?></strong>
                        </div>

                        <div class="summary-item">
                            <span>Email</span>
                            <strong><?= e($email); ?></strong>
                        </div>

                        <div class="summary-item">
                            <span>Tanggal Kunjungan</span>
                            <strong id="summary_date">Belum dipilih</strong>
                        </div>

                        <div class="summary-item">
                            <span>Jam Kunjungan</span>
                            <strong id="summary_time">Belum dipilih</strong>
                        </div>

                        <div class="summary-item">
                            <span>Jumlah Orang</span>
                            <strong id="summary_people">1 orang</strong>
                        </div>

                        <hr>

                        <div class="summary-total">
                            <span>Total Harga</span>
                            <strong id="summary_total"><?= e(rupiah(TICKET_PRICE)); ?></strong>
                        </div>

                        <div class="alert alert-warning mt-4 mb-0">
                            Setelah konfirmasi, status reservasi menjadi Menunggu Konfirmasi dan akan otomatis batal jika tidak dibayar dalam 2 jam.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="../assets/js/reservation.js"></script>

</body>
</html>
