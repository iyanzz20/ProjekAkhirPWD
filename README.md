# Sistem Reservasi Tiket Benteng Vredeburg

Sistem informasi berbasis web untuk manajemen reservasi tiket Museum Benteng Vredeburg. Sistem ini memungkinkan pengunjung untuk melakukan pemesanan tiket secara online dengan kuota terbatas (50 orang per jam) dan menyediakan panel admin untuk validasi pembayaran.

## 🚀 Panduan Instalasi (Local Server)

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal menggunakan XAMPP atau Laragon:

### 1. Persiapan Folder

* Clone atau salin seluruh folder proyek ini.
* Pindahkan folder proyek ke direktori server lokal Anda:
* **XAMPP**: `C:\xampp\htdocs\nama-proyek-kamu`
* **Laragon**: `C:\laragon\www\nama-proyek-kamu`



### 2. Import Database

* Buka browser dan akses **phpMyAdmin** (`http://localhost/phpmyadmin`).
* Buat database baru dengan nama: `db_vredeburg_reservation`.
* Pilih database tersebut, lalu klik menu **Import**.
* Pilih file SQL yang berada di: `config/db_vredeburg_reservation.sql`.
* Klik **Go** atau **Import** dan tunggu hingga selesai.

### 3. Konfigurasi Koneksi

* Buka file `config/koneksi.php`.
* Pastikan pengaturan `host`, `dbname`, `username`, dan `password` sudah sesuai dengan settingan MySQL Anda (default XAMPP biasanya password-nya kosong/empty).

### 4. Menjalankan Aplikasi

* Buka browser dan ketik: `http://localhost/nama-folder-proyek-kamu`.

---

## 🔐 Akun Akses Default

Gunakan akun berikut untuk menguji coba sistem (berdasarkan data SQL):

### **Role: Admin**

* **Email**: `admin@admin.com`
* **Password**: `admins`
* **Fitur**: Dashboard statistik bulanan, validasi pembayaran WhatsApp, riwayat reservasi (Edit/Hard Delete).

### **Role: User**

* **Email**: `tes@tes.com`
* **Password**: `testing`
* **Fitur**: Cari info museum, buat reservasi baru (minimal H+1), cek kuota real-time (AJAX), edit reservasi (hanya status pending), edit profil & password.

---

## 🛠️ Fitur Utama

* **Manajemen Kuota**: Pembatasan otomatis 50 orang per slot jam kunjungan.
* **Auto-Cancel**: Reservasi otomatis dibatalkan jika tidak dikonfirmasi dalam waktu 2 jam.
* **Audit Trail**: Mencatat siapa yang membuat, mengubah, atau memvalidasi setiap data transaksi.
* **WhatsApp Integration**: Link otomatis untuk mempermudah user mengirim bukti transfer ke Admin.
* **Responsive Design**: Tampilan yang menyesuaikan perangkat (Mobile & Desktop) menggunakan Bootstrap 5 dan CSS Heritage kustom.

---

*Dibuat untuk keperluan Tugas Praktikum Sistem Informasi.*
