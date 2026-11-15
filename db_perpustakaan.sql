-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260225.3766910a80
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 15, 2026 at 02:42 PM
-- Server version: 9.6.0
-- PHP Version: 8.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(100) NOT NULL,
  `tahun_terbit` int NOT NULL,
  `stok` int NOT NULL,
  `gambar` varchar(255) DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `judul`, `pengarang`, `tahun_terbit`, `stok`, `gambar`) VALUES
(6, 'Bumi', 'TereLiye', 2024, 15, '1780330227_9786020332956_Bumi-New-Cover.jpg'),
(9, 'Madilog', 'Tan Malaka', 2022, 13, '1780375037_images.jpg'),
(10, 'Tentang Kamu', 'TereLiye', 2026, 10, '1780375126_Tentang-Kamu.jpg'),
(11, 'Sherlock Holmes', 'Arthur Conan Doyle', 2014, 4, '1780375232_81QQRYFvmhL._AC_UF1000,1000_QL80_.jpg'),
(12, 'Detective Conan', 'Gosho Aoyama', 1994, 9, '1780375388_MV5BNGNjMjVmODYtMGMzZi00MWUyLTk1ZDQtYzI2ZTk2MmYzYTZiXkEyXkFqcGc@._V1_FMjpg_UX1000_.jpg'),
(13, 'Ily', 'TereLiye', 2025, 12, '1780375552_sg-11134201-7rdwj-m0fg12xkcikk6d.jpg'),
(14, 'Atomic Habits', 'James Clear', 2018, 10, '1780375663_623221001.jpg'),
(17, 'Janji', 'TereLiye', 2025, 13, '1780493841_WhatsApp_Image_2021-07-08_at_11.46.49_AM.jpeg'),
(19, 'Sendiri', 'TereLiye', 2022, 4, '1780543137_390041.jpg'),
(20, 'Hello (Again)', 'Nadia', 2024, 2, '1780543214_390356.jpg'),
(21, ' Howl’s Moving Castle', 'Jones Diana Wynne ', 2023, 1, '1780543285_389273.jpg'),
(22, 'Oppenheimer', 'J. Robert Oppenheimer', 2002, 8, '1780543427_255834.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_pinjam` int NOT NULL,
  `nama_peminjaman` varchar(100) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `id_buku` int DEFAULT NULL,
  `jumlah_pinjam` int NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Dipinjam',
  `tanggal_kembali` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_pinjam`, `nama_peminjaman`, `tanggal_pinjam`, `id_buku`, `jumlah_pinjam`, `status`, `tanggal_kembali`) VALUES
(10, 'Fauzi', '2026-05-27', 10, 2, 'Dikembalikan', '2026-06-04'),
(12, 'Excel', '2026-06-03', 12, 2, 'Dikembalikan', '2026-06-04'),
(13, 'Rudi', '2026-06-03', 13, 4, 'Dikembalikan', '2026-06-04'),
(14, 'Oliver', '2026-06-02', 14, 4, 'Dipinjam', NULL),
(15, 'toriq', '2026-06-02', 11, 1, 'Dipinjam', NULL),
(17, 'toriq', '2026-06-01', 9, 1, 'Dipinjam', NULL),
(19, 'Ibnu', '2026-06-02', 6, 5, 'Dipinjam', NULL),
(20, 'Excel', '2026-06-05', 21, 1, 'Dikembalikan', '2026-06-05'),
(21, 'Hisyam', '2026-06-03', 22, 2, 'Dipinjam', NULL),
(22, 'Syahrul', '2026-06-03', 19, 1, 'Dipinjam', NULL),
(23, 'Haasan', '2026-06-03', 20, 1, 'Dikembalikan', '2026-06-04'),
(25, 'Joseph', '2026-06-03', 6, 1, 'Dikembalikan', '2026-06-04'),
(26, 'arin', '2026-06-02', 12, 1, 'Ditolak', NULL),
(27, 'Oliver', '2026-06-01', 17, 2, 'Dikembalikan', '2026-06-04'),
(28, 'Fauzia', '2026-06-03', 12, 5, 'Dikembalikan', '2026-06-05'),
(29, 'okta', '2026-06-02', 6, 10, 'Ditolak', NULL),
(30, 'andi', '2026-06-05', 14, 1, 'Dipinjam', NULL),
(31, 'Denis', '2026-06-06', 20, 1, 'Dipinjam', NULL),
(32, 'andi', '2026-06-08', 14, 1, 'Dikembalikan', '2026-06-08');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `level`) VALUES
(3, 'admin', '$2y$12$1/Mq5zJXJ3thhJZnu9f2.eYORUIHxZlFewoGppd96UfTKM/CurFpS', 'admin'),
(4, 'user', '$2y$12$KMgTlYPhjfXAxviC8wqSluUEOg0UrjPwJF4CSZcMXtojeHtK96dCK', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_pinjam`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_pinjam` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
