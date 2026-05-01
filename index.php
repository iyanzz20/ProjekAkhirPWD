<?php
session_start();

function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$isLoggedIn = isset($_SESSION['user_id']);
$name = $_SESSION['name'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservasi Tiket Benteng Vredeburg</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-vredeburg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            Benteng Vredeburg
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#home">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#sejarah">Sejarah</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#galeri">Galeri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#aturan">Aturan</a>
                </li>
            </ul>

            <div class="d-flex gap-2">
                <?php if ($isLoggedIn): ?>
                    <a href="user/reservation.php" class="btn btn-outline-heritage btn-sm">
                        Reservasi
                    </a>
                    <a href="logout.php" class="btn btn-heritage btn-sm">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-heritage btn-sm">
                        Login
                    </a>
                    <a href="register.php" class="btn btn-heritage btn-sm">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<section class="hero-vredeburg" id="home">
    <div class="hero-overlay"></div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">

                <h2 class="hero-title mb-3">
                    Selamat Datang
                </h2>

                <p class="hero-description mb-4">
                    Pilih tanggal kunjungan, cek kuota secara langsung, dan lakukan reservasi tiket museum dengan alur yang lebih tertib.
                </p>

                <div class="d-flex flex-wrap gap-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="user/reservation.php" class="btn btn-heritage btn-lg px-4">
                            Reservasi Sekarang
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-heritage btn-lg px-4">
                            Reservasi Sekarang
                        </a>
                    <?php endif; ?>

                    <a href="#sejarah" class="btn btn-light-heritage btn-lg px-4">
                        Lihat Informasi
                    </a>
                </div>
            </div>

            <div class="col-lg-6 mt-5 mt-lg-0">
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-paper" id="sejarah">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <span class="section-badge mb-3">Sejarah Singkat</span>

                <h1 class="section-title mb-3">
                    Mengenal Benteng Vredeburg
                </h1>

                <p class="text-muted-custom mb-0">
                    Benteng Vredeburg adalah benteng bersejarah yang dibangun oleh Belanda pada tahun 1765 di Yogyakarta. Awalnya digunakan sebagai pusat pertahanan dan markas militer, benteng ini kini berfungsi sebagai Museum Benteng Vredeburg sejak 1992. Museum ini menyajikan koleksi tentang sejarah perjuangan kemerdekaan Indonesia dan menjadi salah satu ikon sejarah di Yogyakarta.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-cream" id="galeri">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge mb-3">Galeri</span>
            <h2 class="section-title">Suasana Museum</h2>
            <p class="text-muted-custom">
                Beberapa gambaran area kunjungan dan suasana museum.
            </p>
        </div>

        <div id="galleryVredeburgSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3500">
            
            <div class="carousel-indicators gallery-indicators">
                <button type="button" data-bs-target="#galleryVredeburgSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#galleryVredeburgSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#galleryVredeburgSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>

            <div class="carousel-inner">

                <div class="carousel-item active">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/bentengvredeburg-mei_05112024153108.jpg" alt="Ruang Pameran">
                                <div class="gallery-content">
                                    <h5>Ruang Pameran Tetap</h5>
                                    <p>Ruang yang berisi pameran dari peninggalan belanda.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/1730791104.JPG" alt="Area Museum">
                                <div class="gallery-content">
                                    <h5>Ruang Auditorium</h5>
                                    <p>Ruang yang dimanfaatkan sebagai ruang seminar, workshop, dan lainnya.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/2_02072019050715.jpg" alt="Bangunan Bersejarah">
                                <div class="gallery-content">
                                    <h5>Ruang Studi Koleksi</h5>
                                    <p>Tempat penyimpanan koleksi-koleksi yang tidak disajikan dalam ruang tata pameran.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/2_01072019222621.JPG" alt="Museum Bersejarah">
                                <div class="gallery-content">
                                    <h5>Ruang Konservasi</h5>
                                    <p>Ruang konservasi berfungsi untuk merawat koleksi baik secara kuratif maupun preventif.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/1730788489.jpg" alt="Koleksi Museum">
                                <div class="gallery-content">
                                    <h5>Tempat Parkir</h5>
                                    <p>Parkir museum tersedia di depan pintu masuk museum sisi selatan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/perpustakaan_22102024113325.jpg" alt="Pameran Museum">
                                <div class="gallery-content">
                                    <h5>Ruang Perpustakaan</h5>
                                    <p>Ruang perpustakaan yang mendukung pengalaman kunjungan lebih informatif.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/2_02072019034837.JPG" alt="Interior Museum">
                                <div class="gallery-content">
                                    <h5>Loket Penjualan Tiket</h5>
                                    <p>Tersedia di sisi barat pintu masuk museum dan sisi selatan museum.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/1_02072019032947.JPG" alt="Lorong Museum">
                                <div class="gallery-content">
                                    <h5>Ruang Laktaksi</h5>
                                    <p>Penyediaan ruang laktasi Pemberian Air Susu Ibu Selama Waktu Kerja Di Tempat Kerja..</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="gallery-card">
                                <img src="https://vredeburg.id/images/1_02072019030612.JPG" alt="Karya Museum">
                                <div class="gallery-content">
                                    <h5>Ruang Satpam</h5>
                                    <p>Terdapat di empat tempat, yaitu pos pintu barat, tengah, timur, selatan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <button class="carousel-control-prev gallery-control" type="button" data-bs-target="#galleryVredeburgSlider" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>

            <button class="carousel-control-next gallery-control" type="button" data-bs-target="#galleryVredeburgSlider" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Berikutnya</span>
            </button>

        </div>
    </div>
</section>

<section class="section-padding bg-paper" id="aturan">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5">
                <div class="info-panel h-100">
                    <span class="section-badge mb-3">Aturan Kunjungan</span>
                    <h2 class="section-title mb-3">
                        Ketentuan Sebelum Berkunjung
                    </h2>
                    <p class="text-muted-custom mb-0">
                        Sistem reservasi membantu pengelola membatasi jumlah pengunjung agar kunjungan tetap nyaman, aman, dan terarah.
                    </p>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="rule-list">
                    <div class="rule-item">
                        <span>1</span>
                        <p>Pengunjung wajib melakukan reservasi sebelum datang.</p>
                    </div>

                    <div class="rule-item">
                        <span>2</span>
                        <p>Kuota kunjungan dibatasi maksimal 50 orang per jam.</p>
                    </div>

                    <div class="rule-item">
                        <span>3</span>
                        <p>Pengunjung datang sesuai tanggal dan jam yang telah dipilih.</p>
                    </div>

                    <div class="rule-item">
                        <span>4</span>
                        <p>Pengunjung wajib menjaga kebersihan dan ketertiban area museum.</p>
                    </div>

                    <div class="rule-item">
                        <span>5</span>
                        <p>Konfirmasi pembayaran dilakukan melalui WhatsApp admin.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container text-center">
        <span class="section-badge light mb-3">Reservasi Online</span>
        <h2 class="cta-title mb-3">
            Siap Melakukan Kunjungan?
        </h2>
        <p class="cta-description mb-4">
            Cek kuota secara langsung dan lakukan reservasi tiket sekarang.
        </p>

        <?php if ($isLoggedIn): ?>
            <a href="user/reservation.php" class="btn btn-light-heritage btn-lg px-4">
                Reservasi Sekarang
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-light-heritage btn-lg px-4">
                Reservasi Sekarang
            </a>
        <?php endif; ?>
    </div>
</section>

<footer class="footer-vredeburg">
    <div class="container text-center">
        <p class="mb-1 fw-semibold">Sistem Reservasi Tiket Wisata Benteng Vredeburg</p>
        <small>&copy; <?= date('Y'); ?> All rights reserved.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>