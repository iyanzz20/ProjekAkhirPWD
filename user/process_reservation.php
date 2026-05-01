<?php
require_once "../config/auth_user.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('reservation.php');
}

if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    redirect('reservation.php?error=' . urlencode('Token keamanan tidak valid.'));
}

$userId = currentUserId();
$visitDate = $_POST['visit_date'] ?? '';
$visitTime = normalizeTime($_POST['visit_time'] ?? '');
$totalPeople = (int) ($_POST['total_people'] ?? 0);

if ($visitDate === '' || $visitTime === '' || $totalPeople <= 0) {
    redirect('reservation.php?error=' . urlencode('Data reservasi tidak lengkap.'));
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
    redirect('reservation.php?error=' . urlencode('Format tanggal tidak valid.'));
}

if ($visitDate < date('Y-m-d')) {
    redirect('reservation.php?error=' . urlencode('Tanggal kunjungan tidak boleh sebelum hari ini.'));
}

/*
|--------------------------------------------------------------------------
| Validasi jam reservasi
| Hanya menerima jam 08:00, 12:00, dan 16:00
|--------------------------------------------------------------------------
*/
$validTimes = array(
    '08:00:00',
    '12:00:00',
    '16:00:00'
);

if (!in_array($visitTime, $validTimes, true)) {
    redirect('reservation.php?error=' . urlencode('Jam kunjungan tidak valid. Pilih jam 08.00, 12.00, atau 16.00.'));
}

try {
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Auto expired reservasi pending yang lewat 2 jam
    |--------------------------------------------------------------------------
    */
    $updateExpired = $pdo->prepare("
        UPDATE reservations
        SET 
            status = 'expired',
            updated_by = 'system',
            updated_at = NOW()
        WHERE status = 'pending'
        AND payment_deadline < NOW()
        AND is_deleted = 0
    ");
    $updateExpired->execute();

    /*
    |--------------------------------------------------------------------------
    | Kuota default
    |--------------------------------------------------------------------------
    */
    $quotaLimit = 50;

    /*
    |--------------------------------------------------------------------------
    | Cek kuota terpakai
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_people), 0) AS used_quota
        FROM reservations
        WHERE visit_date = ?
        AND visit_time = ?
        AND status IN ('pending', 'paid')
        AND is_deleted = 0
        FOR UPDATE
    ");
    $stmt->execute(array($visitDate, $visitTime));
    $row = $stmt->fetch();

    $usedQuota = (int) $row['used_quota'];
    $remainingQuota = $quotaLimit - $usedQuota;

    if ($remainingQuota <= 0) {
        $pdo->rollBack();
        redirect('reservation.php?error=' . urlencode('Kuota pada jam tersebut sudah penuh.'));
    }

    if ($totalPeople > $remainingQuota) {
        $pdo->rollBack();
        redirect('reservation.php?error=' . urlencode('Jumlah orang melebihi sisa kuota. Sisa kuota: ' . $remainingQuota . ' orang.'));
    }

    /*
    |--------------------------------------------------------------------------
    | Hitung pembayaran
    |--------------------------------------------------------------------------
    */
    $pricePerPerson = TICKET_PRICE;
    $totalPrice = $totalPeople * $pricePerPerson;
    $paymentDeadline = date('Y-m-d H:i:s', strtotime('+2 hours'));

    /*
    |--------------------------------------------------------------------------
    | Simpan reservasi
    | Tidak memakai reservation_code.
    | Kode booking akan memakai id reservasi otomatis dari database.
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        INSERT INTO reservations
        (
            user_id,
            visit_date,
            visit_time,
            total_people,
            price_per_person,
            total_price,
            status,
            payment_deadline,
            created_by
        )
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
    ");

    $stmt->execute(array(
        $userId,
        $visitDate,
        $visitTime,
        $totalPeople,
        $pricePerPerson,
        $totalPrice,
        $paymentDeadline,
        $userId
    ));

    $reservationId = (int) $pdo->lastInsertId();

    $pdo->commit();

    redirect('payment_instruction.php?id=' . $reservationId . '&success=1');

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    redirect('reservation.php?error=' . urlencode('Gagal menyimpan reservasi: ' . $e->getMessage()));
}