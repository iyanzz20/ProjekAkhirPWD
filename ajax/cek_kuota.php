<?php
require_once '../config/koneksi.php';
require_once '../config/functions.php';

$tgl = $_GET['tanggal'] ?? '';
$jam = $_GET['jam'] ?? '';
$exclude_id = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

if ($tgl && $jam) {
    $sql = "SELECT COALESCE(SUM(jumlah_orang), 0) AS used_quota
            FROM reservations
            WHERE tgl_kunjungan = ?
            AND jam_kunjungan = ?
            AND status IN ('pending', 'confirmed')
            AND is_deleted = 0";
    
    if ($exclude_id > 0) {
        $sql .= " AND id_reservasi != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tgl, $jam, $exclude_id]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tgl, $jam]);
    }

    $row = $stmt->fetch();
    $terisi = (int) $row['used_quota'];
    $sisa = DEFAULT_QUOTA - $terisi;
    
    echo json_encode([
        'status' => 'success',
        'sisa'   => max(0, $sisa)
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
}