<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureUserLogin();

$userId = currentUserId();
$csrfToken = generateCsrfToken();

$stmt = $pdo->prepare("SELECT nama_lengkap, email FROM users WHERE id_user = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) redirect('../logout.php');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - <?= e(APP_NAME); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">Benteng Vredeburg</a>
        <a href="dashboard.php" class="btn btn-outline-heritage btn-sm">Kembali</a>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
                    <div class="alert alert-success shadow-sm">Profil berhasil diperbarui!</div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger shadow-sm"><?= e($_GET['error']); ?></div>
                <?php endif; ?>

                <div class="card-panel shadow-sm">
                    <h2 class="h4 fw-bold mb-4">Pengaturan Profil</h2>
                    
                    <form action="proses_edit_profile.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken); ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="<?= e($user['nama_lengkap']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e($user['email']); ?>" required>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3 text-muted">Ganti Password</h6>
                        <p class="small text-muted mb-3 italic">* Kosongkan jika tidak ingin mengubah password.</p>

                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="old_password" class="form-control" placeholder="Wajib jika ganti password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-heritage btn-lg w-100">Simpan Perubahan</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</main>

</body>
</html>