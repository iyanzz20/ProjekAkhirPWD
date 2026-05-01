<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $visit_date = $_POST['visit_date'];
    $visit_time = $_POST['visit_time'];
    $quota_limit = $_POST['quota_limit'];

    $stmt = $pdo->prepare("UPDATE quota_slots SET quota_limit = ? WHERE visit_date = ? AND visit_time = ?");
    $stmt->execute([$quota_limit, $visit_date, $visit_time]);
}
?>

<form method="POST">
    <input type="date" name="visit_date" required>
    <input type="time" name="visit_time" required>
    <input type="number" name="quota_limit" required>
    <button type="submit">Update Kuota</button>
</form>