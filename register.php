<?php
session_start();
require_once "config/koneksi.php";
require_once "config/functions.php";

ensureGuest();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $error = "Nama, email, password, dan konfirmasi password wajib diisi.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter.";
        } elseif ($password !== $confirmPassword) {
            $error = "Konfirmasi password tidak sesuai.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_deleted = 0 LIMIT 1");
            $stmt->execute(array($email));

            if ($stmt->fetch()) {
                $error = "Email sudah terdaftar.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    INSERT INTO users
                    (name, email, phone, password, role, created_by)
                    VALUES (?, ?, ?, ?, 'user', 'system')
                ");
                $stmt->execute(array($name, $email, $phone, $hash));

                $success = "Registrasi berhasil. Silakan login.";
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card">
                <span class="section-badge mb-3">Register</span>
                <h3 class="fw-bold mb-2">Buat Akun User</h3>
                <p class="text-muted-custom mb-4">Akun digunakan untuk melakukan reservasi dan melihat riwayat kunjungan.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success); ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-heritage w-100">Register</button>
                </form>

                <p class="text-center mt-4 mb-0">
                    Sudah punya akun? <a href="login.php" class="link-heritage">Login</a>
                </p>
                <p class="text-center mt-2 mb-0">
                    <a href="index.php" class="link-muted">Kembali ke halaman utama</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
