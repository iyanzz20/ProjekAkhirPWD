<?php
date_default_timezone_set('Asia/Jakarta');

define('APP_NAME', 'Reservasi Benteng Vredeburg');
define('TICKET_PRICE', 10000);
define('DEFAULT_QUOTA', 50);
define('ADMIN_WHATSAPP', '62895328895145');

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function rupiah($number)
{
    return 'Rp' . number_format((int) $number, 0, ',', '.');
}

function redirect($path)
{
    header("Location: " . $path);
    exit;
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function currentUserId()
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function currentUserName()
{
    return $_SESSION['name'] ?? '';
}

function currentUserEmail()
{
    return $_SESSION['email'] ?? '';
}

function ensureUserLogin()
{
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
        redirect('../login.php');
    }
}

function ensureGuest()
{
    if (isset($_SESSION['user_id'])) {
        if (($_SESSION['role'] ?? '') === 'admin') {
            redirect('admin/dashboard.php');
        }

        redirect('user/dashboard.php');
    }
}

function getOperationalTimes()
{
    return array(
        '08:00:00',
        '12:00:00',
        '16:00:00'
    );
}

function isValidOperationalTime($time)
{
    $normalized = strlen($time) === 5 ? $time . ':00' : $time;
    return in_array($normalized, getOperationalTimes(), true);
}

function normalizeTime($time)
{
    return strlen($time) === 5 ? $time . ':00' : $time;
}

function updateExpiredReservations($pdo)
{
    $stmt = $pdo->prepare("
        SELECT id
        FROM reservations
        WHERE status = 'pending'
        AND payment_deadline < NOW()
        AND is_deleted = 0
    ");
    
    $expiredReservations = $stmt->fetchAll();

    if (!$expiredReservations) {
        return;
    }

    $update = $pdo->prepare("
        UPDATE reservations
        SET status = 'expired',
            updated_by = 'system',
            updated_at = NOW()
        WHERE id = ?
    ");

    $history = $pdo->prepare("
        INSERT INTO reservation_histories
        (reservation_id, old_status, new_status, note, created_by)
        VALUES (?, 'pending', 'expired', 'Reservasi otomatis dibatalkan karena melewati batas pembayaran 2 jam.', 'system')
    ");

    foreach ($expiredReservations as $reservation) {
        $update->execute(array($reservation['id']));
        $history->execute(array($reservation['id']));
    }
}

function getQuotaLimit($pdo, $visitDate, $visitTime)
{
    $stmt = $pdo->prepare("
        SELECT quota_limit
        FROM quota_slots
        WHERE visit_date = ?
        AND visit_time = ?
        AND is_deleted = 0
        LIMIT 1
    ");
    $stmt->execute(array($visitDate, $visitTime));
    $quota = $stmt->fetch();

    return $quota ? (int) $quota['quota_limit'] : DEFAULT_QUOTA;
}

function getUsedQuota($pdo, $visitDate, $visitTime)
{
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_people), 0) AS used_quota
        FROM reservations
        WHERE visit_date = ?
        AND visit_time = ?
        AND status IN ('pending', 'paid')
        AND is_deleted = 0
    ");
    $stmt->execute(array($visitDate, $visitTime));
    $row = $stmt->fetch();

    return (int) $row['used_quota'];
}

function createReservationCode()
{
    return 'VRD-' . date('YmdHis') . '-' . random_int(100, 999);
}

function statusLabel($status)
{
    $labels = array(
        'pending' => 'Menunggu Konfirmasi',
        'paid' => 'Sudah Bayar / Valid',
        'expired' => 'Kadaluarsa',
        'cancelled' => 'Dibatalkan',
        'rejected' => 'Ditolak'
    );

    return $labels[$status] ?? $status;
}

function statusBadgeClass($status)
{
    $classes = array(
        'pending' => 'badge bg-warning text-dark',
        'paid' => 'badge bg-success',
        'expired' => 'badge bg-secondary',
        'cancelled' => 'badge bg-danger',
        'rejected' => 'badge bg-danger'
    );

    return $classes[$status] ?? 'badge bg-light text-dark';
}
