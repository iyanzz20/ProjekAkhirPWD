<?php
require_once '../config/koneksi.php';
require_once '../config/functions.php';

$tgl = $_GET['tanggal'] ?? '';
$jam = $_GET['jam'] ?? '';

if ($tgl && $jam) {
    $terisi = getUsedQuota($pdo, $tgl, $jam);
    $sisa = DEFAULT_QUOTA - $terisi;
    
    echo json_encode([
        'status' => 'success',
        'sisa'   => max(0, $sisa) 
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
}