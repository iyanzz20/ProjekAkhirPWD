<?php
require_once "../config/auth_user.php";

$id = (int) ($_GET['id'] ?? 0);
$userId = currentUserId();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT id, status
        FROM reservations
        WHERE id = ?
        AND user_id = ?
        AND is_deleted = 0
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute(array($id, $userId));
    $reservation = $stmt->fetch();

    if (!$reservation || $reservation['status'] !== 'pending') {
        $pdo->rollBack();
        redirect('dashboard.php');
    }

    $update = $pdo->prepare("
        UPDATE reservations
        SET status = 'cancelled',
            updated_by = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $update->execute(array($userId, $id));

    $history = $pdo->prepare("
        INSERT INTO reservation_histories
        (reservation_id, old_status, new_status, note, created_by)
        VALUES (?, 'pending', 'cancelled', 'Reservasi dibatalkan oleh user.', ?)
    ");
    $history->execute(array($id, $userId));

    $pdo->commit();

    redirect('dashboard.php?cancelled=1');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    redirect('dashboard.php');
}
