<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';
ensureUserLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Keamanan tidak valid.");
    }

    $id_user = currentUserId();
    $tgl     = $_POST['tgl_kunjungan'];
    $jam     = $_POST['jam_kunjungan'];
    $pax     = (int)$_POST['jumlah_orang'];
    $total   = $pax * TICKET_PRICE;

    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    if ($tgl < $tomorrow) {
        die("Pemesanan minimal dilakukan H+1.");
    }

    $terisi = getUsedQuota($pdo, $tgl, $jam);
    if (($terisi + $pax) > DEFAULT_QUOTA) {
        die("Maaf, kuota tiba-tiba penuh. Silakan ulangi pemesanan.");
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO reservations (id_user, tgl_kunjungan, jam_kunjungan, jumlah_orang, total_harga, status, created_by)
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        $stmt->execute([
            $id_user, 
            $tgl, 
            $jam, 
            $pax, 
            $total, 
            currentUserName()
        ]);

        header("Location: dashboard.php?success=1");
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}