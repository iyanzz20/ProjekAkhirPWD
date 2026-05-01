<?php
session_start();
require_once "config/koneksi.php";
require_once "config/functions.php";

if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') redirect('admin/dashboard.php');
    redirect('user/dashboard.php');
}

updateExpiredReservations($pdo);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = "Email dan password wajib diisi.";
        } else {
            $stmt = $pdo->prepare("
                SELECT id_user, nama_lengkap, email, password, role
                FROM users
                WHERE email = ?
                AND is_deleted = 0
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nama']    = $user['nama_lengkap'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                }
                redirect('user/dashboard.php');
            } else {
                $error = "Email atau password salah.";
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
    <title>Login - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card">
                <span class="section-badge mb-3">Login</span>
                <h3 class="fw-bold mb-2">Masuk ke Sistem</h3>
                <p class="text-muted-custom mb-4">Login untuk melakukan reservasi dan melihat riwayat pesanan.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error); ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-heritage w-100">Login</button>
                </form>

                <p class="text-center mt-4 mb-0">
                    Belum punya akun? <a href="register.php" class="link-heritage">Register</a>
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
