-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 04:01 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_vredeburg_reservation`
--

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id_reservasi` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `tgl_kunjungan` date NOT NULL,
  `jam_kunjungan` time NOT NULL,
  `jumlah_orang` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','canceled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id_reservasi`, `id_user`, `no_telepon`, `tgl_kunjungan`, `jam_kunjungan`, `jumlah_orang`, `total_harga`, `status`, `created_at`, `created_by`, `updated_at`, `updated_by`, `is_deleted`, `deleted_at`, `deleted_by`) VALUES
(1, 3, '081323322336', '2026-05-02', '08:00:00', 30, '300000.00', 'canceled', '2026-05-01 10:04:38', 'Testing', '2026-05-08 02:01:27', 'system', 1, '2026-05-01 16:09:02', 'Admin'),
(2, 3, '081323329344', '2026-06-01', '08:00:00', 5, '50000.00', 'canceled', '2026-05-01 13:48:24', 'Testing', '2026-05-08 02:01:23', 'system', 0, NULL, NULL),
(3, 3, '081323320238', '2026-05-02', '08:00:00', 40, '400000.00', 'confirmed', '2026-05-01 16:00:36', 'Testing', '2026-05-08 02:01:18', 'Admin', 0, NULL, NULL),
(5, 3, '081323329672', '2026-05-08', '15:00:00', 45, '450000.00', 'confirmed', '2026-05-06 14:25:17', 'Testing', '2026-05-08 02:01:13', 'Admin', 0, NULL, NULL),
(6, 3, '081323325685', '2026-05-08', '15:00:00', 5, '50000.00', 'canceled', '2026-05-06 14:31:29', 'Testing', '2026-05-08 02:01:09', 'system', 0, NULL, NULL),
(7, 3, '081323322212', '2026-05-09', '15:00:00', 5, '50000.00', 'confirmed', '2026-05-08 01:44:04', 'Testing', '2026-05-08 01:50:17', 'Admin', 0, NULL, NULL),
(8, 3, '081323321110', '2026-05-09', '10:00:00', 5, '50000.00', 'pending', '2026-05-08 01:55:04', 'Testing', '2026-05-08 01:58:59', 'Admin', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) DEFAULT 'system',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `email`, `password`, `role`, `created_at`, `created_by`, `updated_at`, `updated_by`, `is_deleted`, `deleted_at`, `deleted_by`) VALUES
(1, 'Admin', 'admin@admin.com', '$2y$10$P7hPQq/w4ATC3UDLEpu/tO.tcAk6qIuCix6nmGPelkRbbNmFmH7wm', 'admin', '2026-05-01 01:34:54', 'system', '2026-05-01 09:20:04', NULL, 0, NULL, NULL),
(3, 'Testing Lengkap', 'tes@tes.com', '$2y$10$JwxUFWKdSBBDsFoejdK4kueijq.I8lZiVatcCZFllgV9dZEEvuVyO', 'user', '2026-05-01 09:17:22', 'system', '2026-05-08 01:29:39', 'Testing Lengkap', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `fk_reservation_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id_reservasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
