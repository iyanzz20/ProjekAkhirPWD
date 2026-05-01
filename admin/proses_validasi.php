<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_res = (int)$_POST['id_reservasi'];
    $admin_name = currentUserName(); 

    try {
        $sql = "UPDATE reservations 
                SET status = 'confirmed', 
                    updated_by = ?, 
                    updated_at = NOW() 
                WHERE id_reservasi = ? AND status = 'pending'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_name, $id_res]);

        header("Location: validasi.php?msg=success");
        exit;
    } catch (PDOException $e) {
        die("Gagal validasi: " . $e->getMessage());
    }
} else {
    redirect('validasi.php');
}