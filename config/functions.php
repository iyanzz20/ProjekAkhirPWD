<?php
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Aplikasi
define('APP_NAME', 'Reservasi Benteng Vredeburg');
define('TICKET_PRICE', 10000);
define('DEFAULT_QUOTA', 50);
define('ADMIN_WHATSAPP', '62895328895145');

/**
 * Keamanan & Utility
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function rupiah($number) {
    return 'Rp' . number_format((int) $number, 0, ',', '.');
}

function redirect($path) {
    header("Location: " . $path);
    exit;
}

/**
 * CSRF Protection
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Session Helpers
 */
function currentUserId() {
    return isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : null;
}

function currentUserName() {
    return $_SESSION['nama'] ?? '';
}

function currentUserRole() {
    return $_SESSION['role'] ?? '';
}

function currentUserEmail() {
    return $_SESSION['email'] ?? '';
}

/**
 * Middleware / Auth Guards
 */
function ensureUserLogin() {
    if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'user') {
        redirect('../login.php');
    }
}

function ensureAdminLogin() {
    if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'admin') {
        redirect('../login.php');
    }
}

function ensureGuest() {
    if (isset($_SESSION['id_user'])) {
        if (($_SESSION['role'] ?? '') === 'admin') {
            redirect('admin/dashboard.php');
        }
        redirect('user/dashboard.php');
    }
}

/**
 * Manajemen Waktu & Jam Operasional (08.00 - 21.00)
 */
function getOperationalTimes() {
    $times = [];
    for ($i = 8; $i <= 20; $i++) {
        $times[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00:00';
    }
    return $times;
}

function isValidOperationalTime($time) {
    $normalized = strlen($time) === 5 ? $time . ':00' : $time;
    return in_array($normalized, getOperationalTimes(), true);
}

/**
 * Sistem Auto-Cancel
 */
function updateExpiredReservations($pdo) {
    $sql = "UPDATE reservations 
            SET status = 'canceled', 
                updated_by = 'system', 
                updated_at = NOW() 
            WHERE status = 'pending' 
            AND created_at < NOW() - INTERVAL 2 HOUR 
            AND is_deleted = 0";
    
    try {
        $pdo->query($sql);
    } catch (PDOException $e) {
        // log error
    }
}

/**
 * Logika Kuota
 */
function getUsedQuota($pdo, $visitDate, $visitTime) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(jumlah_orang), 0) AS used_quota
        FROM reservations
        WHERE tgl_kunjungan = ?
        AND jam_kunjungan = ?
        AND status IN ('pending', 'confirmed')
        AND is_deleted = 0
    ");
    $stmt->execute([$visitDate, $visitTime]);
    $row = $stmt->fetch();

    return (int) $row['used_quota'];
}

/**
 * UI Helpers
 */
function statusLabel($status) {
    $labels = [
        'pending'   => 'Menunggu Konfirmasi',
        'confirmed' => 'Sudah Bayar / Valid',
        'canceled'  => 'Dibatalkan'
    ];
    return $labels[$status] ?? $status;
}

function statusBadgeClass($status) {
    $classes = [
        'pending'   => 'badge bg-warning text-dark',
        'confirmed' => 'badge bg-success',
        'canceled'  => 'badge bg-danger'
    ];
    return $classes[$status] ?? 'badge bg-light text-dark';
}