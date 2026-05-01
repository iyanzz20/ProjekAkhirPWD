<?php
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE status = 'Pending'");
$stmt->execute();
$reservations = $stmt->fetchAll();
?>

<h3>Daftar Reservasi Pending</h3>
<table>
    <tr>
        <th>ID Reservasi</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Waktu</th>
        <th>Validasi</th>
    </tr>
    <?php foreach ($reservations as $reservation): ?>
        <tr>
            <td><?= $reservation['id'] ?></td>
            <td><?= $reservation['user_id'] ?></td>
            <td><?= $reservation['visit_date'] ?></td>
            <td><?= $reservation['visit_time'] ?></td>
            <td><a href="validate_payment_action.php?id=<?= $reservation['id'] ?>">Verifikasi</a></td>
        </tr>
    <?php endforeach; ?>
</table>