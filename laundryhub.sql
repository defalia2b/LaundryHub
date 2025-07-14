-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2025 at 07:42 PM
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
(1, 'cuci', 3, 2000),
(2, 'setrika', 3, 1000),
(3, 'komplit', 3, 2500),
(4, 'cuci', 1, 5000),
(5, 'setrika', 1, 3000),
(6, 'komplit', 1, 7000),
(7, 'cuci', 4, 300),
(8, 'setrika', 4, 200),
(9, 'komplit', 4, 400),
(10, 'cuci', 5, 4000),
(11, 'setrika', 5, 3000),
(12, 'komplit', 5, 5000),
(13, 'cuci', 6, 7000),
(14, 'setrika', 6, 3000),
(15, 'komplit', 6, 8000),
(16, 'cuci', 7, 3000),
(17, 'setrika', 7, 2000),
(18, 'komplit', 7, 4500),
(19, 'cuci', 8, 6000),
(20, 'setrika', 8, 3000),
(21, 'komplit', 8, 7500),
(22, 'cuci', 9, 4000),
(23, 'setrika', 9, 2000),
(24, 'komplit', 9, 5000),
(25, 'cuci', 10, 5000),
(26, 'setrika', 10, 3000),
(27, 'komplit', 10, 6000),
(28, 'cuci', 10, 5000),
(29, 'setrika', 10, 3000),
(30, 'komplit', 10, 6000),
(31, 'cuci', 11, 7000),
(32, 'setrika', 11, 6000),
(33, 'komplit', 11, 10000),
(34, 'cuci', 17, 5000),
(35, 'setrika', 17, 6000),
(36, 'komplit', 17, 7000),
(37, 'cuci', 18, 0),
(38, 'setrika', 18, 0),
(39, 'komplit', 18, 0),
(40, 'cuci', 18, 5000),
(41, 'setrika', 18, 600),
(42, 'komplit', 18, 0),
(43, 'cuci', 18, 6000),
(44, 'setrika', 18, 7000),
(45, 'komplit', 18, 3000),
(46, 'cuci', 18, 0),
(47, 'setrika', 18, 0),
(48, 'komplit', 18, 0),
(49, 'cuci', 19, 5000),
(50, 'setrika', 19, 600),
(51, 'komplit', 19, 700),
(52, 'cuci', 20, 5000),
(53, 'setrika', 20, 0),
(54, 'komplit', 20, 0),
(55, 'cuci', 21, 5000),
(56, 'setrika', 21, 7000),
(57, 'komplit', 21, 0),
(58, 'cuci', 22, 5000),
(59, 'setrika', 22, 5000),
(60, 'komplit', 22, 6000);

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
(1, 'Laundry Aina', 'Aina', '083123456789', 'mitra1@gmail.com', 'Jl. Diponegoro No 55', -6.15696934, 106.77030008, 'aina.jpg', '123'),
(4, 'Defalia Laundry', 'Dewi', '3875120', 'mitra2@gmail.com', 'Jl. Surabaya No 12', NULL, NULL, 'lia.jpg', '123'),
(5, 'Laundry Gans', 'Gans', '57109', 'mitra3@gmail.com', 'Kuta No 22', NULL, NULL, 'mh.jpg', '123'),
(7, 'Laundri Laundry', 'Blackie Black', '08321456378', 'mitra5@gmail.com', 'Jl. Mawar No 78', NULL, NULL, 'hm.jpg', '123'),
(9, 'Bambang Laundry', 'Bambang Prakasa', '098527815618', 'mitra6@gmail.com', 'Jl. Hehe No 77', NULL, NULL, 'default.png', '123'),
(11, 'LaundryTestTest', 'Saya Bukan Pemilik', '08515123', 'laundrytesttest@gmail.com', 'Jalan Perumahan Kepa Duri Mas, RW 04, Duri Kepa, Kebon Jeruk, West Jakarta, Special capital Region of Jakarta, Java, 11510, Indonesia', -6.17583101, 106.76926352, '68724f0826663.png', '123'),
(21, 'a', 'a', '085156161866', 'a@a.com', 'Gelora Bung Karno Main Stadium, Jalan Plaza Selatan, RW 01, Gelora, Tanah Abang, Central Jakarta, Special capital Region of Jakarta, Java, 10270, Indonesia', -6.17161415, 106.79886567, '6873427272658.png', '123'),
(22, 'Konoha', 'Naruto', '0123123123', 'naruto@konoha.com', 'Allianz Ecopark Ancol, Pantai Indah, Kawasan Wisata Ancol, Ancol, Pademangan, North Jakarta, Special capital Region of Jakarta, Java, 14430, Indonesia', -6.12687205, 106.83524524, 'mitra_68738731aa6c4.png', '123456');

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
(8, 'Robin', 'pelanggan1@gmail.com', '0897654321', 'Premier Park 2, Cikokol, Tangerang, Banten, Java, 15117, Indonesia', -6.21052752, 106.63698330, '687251caa0b6f.png', '123'),
(9, 'Pelanggan2', 'pelanggan2@gmail.com', '08123456789', NULL, NULL, NULL, 'default.png', '123'),
(11, 'Pelanggan4', 'pelanggan4@gmail.com', '082134567', NULL, NULL, NULL, 'default.png', '123'),
(12, 'Pelanggan5', 'pelanggan5@gmail.com', '089764532132', NULL, NULL, NULL, 'default.jpg', '123'),
(13, 'Pelanggan3', 'pelanggan3@gmail.com', '09864738429', NULL, NULL, NULL, 'default.png', '123'),
(15, 'Pelanggan Ayam', 'pelanggan123@gmail.com', '08123', 'a', NULL, NULL, 'default.png', '123'),
(16, 'Blackie', 'blackie@gmail.com', '08123123123', 'Masjid Jami Al Fudhola, Jalan Haji Mushanif, RW 08, Kedaung Kali Angke, Cengkareng, West Jakarta, Special capital Region of Jakarta, Java, 11710, Indonesia', NULL, NULL, 'default.png', '123'),
(17, 'a', 'a@a.c', '12222222222', 'Garuda Padang, Jalan Haji Agus Salim, RW 03, Kebon Sirih, Menteng, Central Jakarta, Special capital Region of Jakarta, Java, 10340, Indonesia', NULL, NULL, 'default.png', '123'),
(18, 'a', 'a@a.a', '1111111111', 'RW 05, Kebon Melati, Tanah Abang, Central Jakarta, Special capital Region of Jakarta, Java, 10230, Indonesia', -6.20009193, 106.81687905, 'default.png', '123'),
(19, 'a', 'a@a.caaa', '1231231231', 'Jalan Administrasi II, RW 08, Petamburan, Tanah Abang, Central Jakarta, Special capital Region of Jakarta, Java, 10260, Indonesia', -6.20034791, 106.80490617, 'default.png', '123');

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
(1, 1, 11, '2020-04-25 00:00:00', '0000-00-00 00:00:00', 'setrika', 2, NULL, 1, NULL, NULL, 'Jl. Aceg No 44, Aceh', 'tak ada', 'Selesai'),
(2, 5, 8, '2020-04-25 00:00:00', '0000-00-00 00:00:00', 'komplit', 6, NULL, 4, NULL, NULL, 'Jl. Melati No 99, Denpasar', 'yang bersih yaaaa', 'Selesai'),
(3, 1, 11, '2020-04-26 00:00:00', '0000-00-00 00:00:00', 'cuci', 1, NULL, 5, NULL, NULL, 'Jl. Aceg No 44, Aceh', 'cepet ya', 'Selesai'),
(4, 4, 11, '2020-04-27 00:00:00', '0000-00-00 00:00:00', 'cuci', 1, NULL, 5, NULL, NULL, 'Jl. Aceg No 44, Aceh', 'cepet', 'Selesai'),
(5, 5, 11, '2020-04-27 00:00:00', '0000-00-00 00:00:00', 'komplit', 5, NULL, 6, NULL, NULL, 'Jl. Aceg No 44, Aceh', 'yg bersih y', 'Selesai'),
(6, 7, 9, '2020-04-27 00:00:00', '0000-00-00 00:00:00', 'setrika', 1, NULL, NULL, NULL, NULL, 'Jl. Goa Gong, No 99, Kec Kuta Selatan (Rumah warna hitam), Badung', 'ngebut ya\r\n', 'Penjemputan'),
(7, 5, 12, '2020-04-29 00:00:00', '0000-00-00 00:00:00', 'setrika', 4, NULL, 2, NULL, NULL, 'Jl. Umum No 77, Singaraja', 'yang sabar', 'Sedang Di Jemur'),
(8, 5, 12, '2020-05-06 00:00:00', '0000-00-00 00:00:00', 'setrika', 5, NULL, 3, NULL, NULL, 'Jl. Umum No 77, Singaraja', 'Yang Harum ya beb', 'Sedang di Cuci'),
(9, 5, 13, '2020-05-06 00:00:00', '0000-00-00 00:00:00', 'komplit', 1, NULL, 1, NULL, NULL, 'Jl. Semarang No 99, Semarang', 'tes', 'Selesai'),
(10, 1, 8, '2025-07-12 12:53:54', NULL, 'setrika', NULL, 5, 5, 15000, 15000, 'jl. ayam goreng', 'jemur terpisah', 'Selesai'),
(11, 11, 8, '2025-07-12 14:17:25', NULL, 'setrika', NULL, 5, NULL, 30000, NULL, 'Jl. HSR Burung Putih, No. 1, Jakarta Barat', 'Tolong cepat cepat saya butuh baju ganti', 'Menunggu Konfirmasi'),
(12, 11, 8, '2025-07-12 14:19:40', NULL, 'setrika', NULL, 5, NULL, 30000, NULL, 'Jl. HSR Burung Putih, No. 1, Jakarta Barat', 'Tolong cepat cepat saya butuh baju ganti', 'Menunggu Konfirmasi'),
(13, 11, 8, '2025-07-12 14:20:05', NULL, 'setrika', NULL, 8, NULL, 48000, NULL, 'Jl. HSR Burung Putih, No. 1, Jakarta Barat', '', 'Menunggu Konfirmasi'),
(14, 11, 8, '2025-07-12 14:25:54', NULL, 'komplit', NULL, 50, 51, 500000, 510000, 'Jl. HSR Burung Putih, No. 1, Jakarta Barat', 'banyak', 'Selesai'),
(15, 11, 15, '2025-07-12 15:19:40', NULL, 'komplit', NULL, 5, 6, 50000, 60000, 'Jl. Apa kek gitu', 'gak pake pemutih', 'Selesai'),
(16, 21, 8, '2025-07-13 12:10:20', NULL, 'setrika', NULL, 10, NULL, 70000, NULL, 'Jalan Talang Betutu Ujung, RW 06, Kebon Melati, Tanah Abang, Central Jakarta, Special capital Region of Jakarta, Java, 10230, Indonesia', '', 'Menunggu Konfirmasi');

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
(6, 1, 1, 11, '2020-04-25 00:00:00', 1000, 'Belum Bayar', NULL, 6, 'Mantap', 'Aktif'),
(19, 4, 4, 11, '2020-04-27 00:00:00', 10000, 'Belum Bayar', NULL, 10, 'Reccomended', 'Aktif'),
(20, 3, 1, 11, '2020-04-26 00:00:00', 10000, 'Belum Bayar', NULL, 10, 'Sangat cocok, mitranya ramah sampe ke ubun ubun', 'Aktif'),
(21, 2, 5, 8, '2020-04-25 00:00:00', 10000, 'Belum Bayar', NULL, 0, '', 'Aktif'),
(22, 5, 5, 11, '2020-04-27 00:00:00', 15000, 'Belum Bayar', NULL, 0, '', 'Aktif'),
(23, 9, 5, 13, '2020-05-06 00:00:00', 2500, 'Belum Bayar', NULL, 10, 'Sangat direkomendasikan', 'Aktif'),
(24, 10, 1, 8, '2025-07-12 12:59:21', 15000, 'Lunas', NULL, NULL, '', 'Aktif'),
(25, 14, 11, 8, '2025-07-12 14:28:11', 510000, 'Lunas', NULL, NULL, '', 'Aktif'),
(26, 15, 11, 15, '2025-07-12 15:20:41', 60000, 'Lunas', NULL, NULL, '', 'Aktif');

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
  MODIFY `id_harga` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `laporan_ulasan`
--
ALTER TABLE `laporan_ulasan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mitra`
--
ALTER TABLE `mitra`
  MODIFY `id_mitra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
