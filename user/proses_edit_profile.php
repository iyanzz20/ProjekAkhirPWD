<?php
session_start();
require_once "../config/koneksi.php";
require_once "../config/functions.php";

ensureUserLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Keamanan tidak valid.");
    }

    $userId       = currentUserId();
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email        = trim($_POST['email']);
    
    $old_pass     = $_POST['old_password'];
    $new_pass     = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, email = ?, updated_at = NOW() WHERE id_user = ?");
        $stmt->execute([$nama_lengkap, $email, $userId]);

        $_SESSION['name'] = $nama_lengkap;
        $_SESSION['email'] = $email;

        if (!empty($new_pass)) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id_user = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($old_pass, $user['password'])) {
                header("Location: edit_profile.php?error=Password lama yang Anda masukkan salah!");
                exit;
            }

            if ($new_pass !== $confirm_pass) {
                header("Location: edit_profile.php?error=Konfirmasi password baru tidak cocok!");
                exit;
            }

            if (strlen($new_pass) < 6) {
                header("Location: edit_profile.php?error=Password baru minimal harus 6 karakter!");
                exit;
            }

            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_by = ? WHERE id_user = ?");
            $stmt->execute([$hashed, $nama_lengkap, $userId]);
        }

        header("Location: edit_profile.php?msg=success");
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { 
            header("Location: edit_profile.php?error=Email sudah digunakan oleh akun lain.");
        } else {
            die("Terjadi kesalahan sistem: " . $e->getMessage());
        }
    }
} else {
    redirect('dashboard.php');
}