<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/functions.php';

ensureAdminLogin();

if (isset($_GET['id'])) {
    $id_res = (int)$_GET['id'];

    try {
        $sql = "DELETE FROM reservations WHERE id_reservasi = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_res]);

        header("Location: history.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        die("Gagal menghapus data secara permanen: " . $e->getMessage());
    }
} else {
    redirect('history.php');
}