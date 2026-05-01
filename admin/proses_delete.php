<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();

if (isset($_GET['id'])) {
    $id_res = (int)$_GET['id'];
    $admin_name = currentUserName();

    try {
        $sql = "UPDATE reservations 
                SET is_deleted = 1, 
                    deleted_by = ?, 
                    deleted_at = NOW() 
                WHERE id_reservasi = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_name, $id_res]);

        header("Location: history.php?msg=deleted");
    } catch (PDOException $e) {
        die("Gagal menghapus: " . $e->getMessage());
    }
} else {
    redirect('history.php');
}