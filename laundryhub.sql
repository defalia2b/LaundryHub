-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 01:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundryhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `harga`
--

CREATE TABLE `harga` (
  `id_harga` int(11) NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `id_mitra` int(11) NOT NULL,
  `harga` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `harga`
--

INSERT INTO `harga` (`id_harga`, `jenis`, `id_mitra`, `harga`) VALUES
(61, 'cuci', 23, 5000),
(62, 'setrika', 23, 6000),
(63, 'komplit', 23, 8000),
(64, 'cuci', 24, 6000),
(65, 'setrika', 24, 5000),
(66, 'komplit', 24, 10000),
(67, 'cuci', 25, 5000),
(68, 'setrika', 25, 5000),
(69, 'komplit', 25, 7000);

-- --------------------------------------------------------

--
-- Table structure for table `laporan_ulasan`
--

CREATE TABLE `laporan_ulasan` (
  `id_laporan` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_mitra` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `tgl_laporan` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_laporan` enum('Menunggu','Ditinjau') NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan_ulasan`
--

INSERT INTO `laporan_ulasan` (`id_laporan`, `id_transaksi`, `id_mitra`, `alasan`, `tgl_laporan`, `status_laporan`) VALUES
(2, 28, 23, 'Komentar kasar atau tidak sopan', '2025-07-14 02:06:21', 'Ditinjau');

-- --------------------------------------------------------

--
-- Table structure for table `mitra`
--

CREATE TABLE `mitra` (
  `id_mitra` int(11) NOT NULL,
  `nama_laundry` varchar(50) DEFAULT NULL,
  `nama_pemilik` varchar(50) DEFAULT NULL,
  `telp` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `foto` text NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mitra`
--

INSERT INTO `mitra` (`id_mitra`, `nama_laundry`, `nama_pemilik`, `telp`, `email`, `alamat`, `latitude`, `longitude`, `foto`, `password`) VALUES
(23, 'Laundry Cahaya Tembaga', 'Cahya', '0123123123', 'mitra1@gmail.com', 'State Auditor, 31, Jalan Jenderal Gatot Subroto, RW 05, Bendungan Hilir, Tanah Abang, Central Jakarta, Special capital Region of Jakarta, Java, 10210, Indonesia', -6.20551311, 106.80285850, 'mitra_6874646173a18.jpg', '123456'),
(24, 'Jaya Laundry', 'Jaya', '0123123123', 'mitra2@gmail.com', 'RW 13, Cengkareng Barat, Cengkareng, West Jakarta, Special capital Region of Jakarta, Java, 11730, Indonesia', -6.13707582, 106.72359413, 'mitra_687468881c008.jpg', '123456'),
(25, 'Laundry Pak Herman', 'Herman Nuswantoro', '0123123123', 'mitra3@gmail.com', 'Ancol Dreamland Theme Park, No.7, Jalan Tridasawarsa, RW 06, Sunter Agung, Tanjung Priok, North Jakarta, Special capital Region of Jakarta, Java, 14430, Indonesia', -6.12485800, 106.83276995, 'mitra_6874690865bcd.jpg', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `telp` varchar(15) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `foto` text NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `email`, `telp`, `alamat`, `latitude`, `longitude`, `foto`, `password`) VALUES
(20, 'Melon Dusk', 'pelanggan1@gmail.com', '0123123123', 'Gang D2, RW 05, Pademangan Barat, Pademangan, North Jakarta, Special capital Region of Jakarta, Java, 14420, Indonesia', -6.13751958, 106.83826217, 'pelanggan_687465424617d.jpg', '123'),
(21, 'Kim John Yum', 'pelanggan2@gmail.com', '0123123123', 'Melody Golf 3, Melody Golf, North Jakarta, Special capital Region of Jakarta, Java, 14460, Indonesia', -6.09037354, 106.74728445, 'pelanggan_6874698d850c6.jpg', '123');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_mitra` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tgl_mulai` datetime NOT NULL,
  `tgl_selesai` datetime DEFAULT NULL,
  `jenis` varchar(20) DEFAULT NULL,
  `total_item` int(11) DEFAULT NULL,
  `estimasi_berat` double DEFAULT NULL,
  `berat` double DEFAULT NULL,
  `harga_estimasi` int(11) DEFAULT NULL,
  `harga_final` int(11) DEFAULT NULL,
  `alamat_antar_jemput` varchar(255) NOT NULL,
  `catatan` text NOT NULL,
  `status_pesanan` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_mitra`, `id_pelanggan`, `tgl_mulai`, `tgl_selesai`, `jenis`, `total_item`, `estimasi_berat`, `berat`, `harga_estimasi`, `harga_final`, `alamat_antar_jemput`, `catatan`, `status_pesanan`) VALUES
(19, 23, 20, '2025-07-14 04:03:03', NULL, 'setrika', NULL, 5, 6, 30000, 36000, 'Mushola Syuhada, Jalan Tanah Abang V, RW 03, Petojo Selatan, Gambir, Central Jakarta, Special capital Region of Jakarta, Java, 10160, Indonesia', '', 'Selesai'),
(20, 25, 20, '2025-07-14 05:50:59', NULL, 'komplit', NULL, 5, 4, 35000, 28000, 'Gang D2, RW 05, Pademangan Barat, Pademangan, North Jakarta, Special capital Region of Jakarta, Java, 14420, Indonesia', '', 'Sedang Dicuci'),
(21, 25, 20, '2025-07-21 13:33:30', NULL, 'cuci', NULL, 7, NULL, 35000, NULL, 'Gang D2, RW 05, Pademangan Barat, Pademangan, North Jakarta, Special capital Region of Jakarta, Java, 14420, Indonesia', '', 'Menunggu Konfirmasi');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_mitra` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `tgl_transaksi` datetime DEFAULT NULL,
  `total_bayar` int(11) DEFAULT NULL,
  `status_pembayaran` enum('Belum Bayar','Lunas','Gagal') NOT NULL DEFAULT 'Belum Bayar',
  `payment_gateway_id` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text NOT NULL,
  `status_ulasan` enum('Aktif','Dihapus') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pesanan`, `id_mitra`, `id_pelanggan`, `tgl_transaksi`, `total_bayar`, `status_pembayaran`, `payment_gateway_id`, `rating`, `komentar`, `status_ulasan`) VALUES
(28, 19, 23, 20, '2025-07-14 04:03:57', 36000, 'Lunas', NULL, 8, 'wangy', 'Dihapus'),
(29, 20, 25, 20, '2025-07-14 05:51:54', 28000, 'Lunas', NULL, 10, 'apa aja', 'Aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `harga`
--
ALTER TABLE `harga`
  ADD PRIMARY KEY (`id_harga`);

--
-- Indexes for table `laporan_ulasan`
--
ALTER TABLE `laporan_ulasan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_mitra` (`id_mitra`);

--
-- Indexes for table `mitra`
--
ALTER TABLE `mitra`
  ADD PRIMARY KEY (`id_mitra`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `harga`
--
ALTER TABLE `harga`
  MODIFY `id_harga` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `laporan_ulasan`
--
ALTER TABLE `laporan_ulasan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mitra`
--
ALTER TABLE `mitra`
  MODIFY `id_mitra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laporan_ulasan`
--
ALTER TABLE `laporan_ulasan`
  ADD CONSTRAINT `laporan_ulasan_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `laporan_ulasan_ibfk_2` FOREIGN KEY (`id_mitra`) REFERENCES `mitra` (`id_mitra`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
