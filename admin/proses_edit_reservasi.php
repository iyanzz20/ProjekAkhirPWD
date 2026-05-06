<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_res  = (int)$_POST['id_reservasi'];
    $tgl     = $_POST['tgl_kunjungan'];
    $jam     = $_POST['jam_kunjungan'];
    $pax     = (int)$_POST['jumlah_orang'];
    $status  = $_POST['status'];
    $total   = $pax * TICKET_PRICE;

    $stmt = $pdo->prepare("SELECT jumlah_orang FROM reservations WHERE id_reservasi = ?");
    $stmt->execute([$id_res]);
    $old_pax = $stmt->fetch()['jumlah_orang'];

    $terisi = getUsedQuota($pdo, $tgl, $jam);
    
    if ($status !== 'canceled' && ($terisi - $old_pax + $pax) > 50) {
        die("Gagal: Perubahan ini menyebabkan kuota melebihi 50 orang.");
    }

    try {
        $sql = "UPDATE reservations SET 
                tgl_kunjungan = ?, 
                jam_kunjungan = ?, 
                jumlah_orang = ?, 
                total_harga = ?, 
                status = ?,
                updated_at = NOW(), 
                updated_by = ?
                WHERE id_reservasi = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tgl, $jam, $pax, $total, $status, currentUserName(), $id_res]);

        header("Location: history.php?msg=updated");
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}