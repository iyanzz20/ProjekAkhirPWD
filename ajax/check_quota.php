<?php
session_start();

require_once "../config/koneksi.php";
require_once "../config/functions.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    http_response_code(401);
    echo json_encode(array(
        'success' => false,
        'message' => 'Sesi login tidak valid.'
    ));
    exit;
}

updateExpiredReservations($pdo);

$visitDate = $_GET['date'] ?? '';

if ($visitDate === '') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Tanggal wajib diisi.'
    ));
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate)) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Format tanggal tidak valid.'
    ));
    exit;
}

if ($visitDate < date('Y-m-d')) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Tanggal kunjungan tidak boleh sebelum hari ini.'
    ));
    exit;
}

$data = array();

foreach (getOperationalTimes() as $time) {
    $quotaLimit = getQuotaLimit($pdo, $visitDate, $time);
    $usedQuota = getUsedQuota($pdo, $visitDate, $time);
    $remainingQuota = max($quotaLimit - $usedQuota, 0);

    $data[] = array(
        'time' => substr($time, 0, 5),
        'full_time' => $time,
        'quota_limit' => $quotaLimit,
        'used_quota' => $usedQuota,
        'remaining_quota' => $remainingQuota,
        'is_full' => $remainingQuota <= 0
    );
}

echo json_encode(array(
    'success' => true,
    'data' => $data
));
