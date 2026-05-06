<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureUserLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Token keamanan tidak valid.");
    }

    $id_res  = (int)$_POST['id_reservasi'];
    $id_user = currentUserId();
    $tgl     = $_POST['tgl_kunjungan'];
    $jam     = $_POST['jam_kunjungan'];
    $pax     = (int)$_POST['jumlah_orang'];
    $total   = $pax * TICKET_PRICE;

    $stmt = $pdo->prepare("SELECT status FROM reservations WHERE id_reservasi = ? AND id_user = ?");
    $stmt->execute([$id_res, $id_user]);
    $res = $stmt->fetch();

    if (!$res || $res['status'] !== 'pending') {
        die("Reservasi ini tidak dapat diubah.");
    }

    $stmt = $pdo->prepare("SELECT jumlah_orang FROM reservations WHERE id_reservasi = ?");
    $stmt->execute([$id_res]);
    $old_pax = $stmt->fetch()['jumlah_orang'];

    $terisi = getUsedQuota($pdo, $tgl, $jam);

    if (($terisi - $old_pax + $pax) > DEFAULT_QUOTA) {
        die("Maaf, kuota penuh untuk jadwal tersebut.");
    }

    try {
        $sql = "UPDATE reservations SET 
                tgl_kunjungan = ?, 
                jam_kunjungan = ?, 
                jumlah_orang = ?, 
                total_harga = ?, 
                updated_at = NOW(), 
                updated_by = ?,
                created_at = NOW()
                WHERE id_reservasi = ? AND id_user = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tgl, $jam, $pax, $total, currentUserName(), $id_res, $id_user]);

        header("Location: dashboard.php?msg=updated");
    } catch (PDOException $e) {
        die("Gagal memperbarui data: " . $e->getMessage());
    }
}