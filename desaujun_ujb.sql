-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 26, 2026 at 11:33 AM
-- Server version: 11.4.12-MariaDB-cll-lve-log
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `desaujun_ujb`
--

-- --------------------------------------------------------

--
-- Table structure for table `laporan_keuangan`
--

CREATE TABLE `laporan_keuangan` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `tahun_anggaran` year(4) NOT NULL,
  `periode` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `nama_file` varchar(255) NOT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `laporan_keuangan`
--

INSERT INTO `laporan_keuangan` (`id`, `judul`, `tahun_anggaran`, `periode`, `keterangan`, `nama_file`, `ukuran_file`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 'Transparansi Keuangan Tahun 2025', '2025', 'Agustus-Desember', '', 'images/infografis/1785462696-IMG-20260730-WA0000.jpg.jpeg', 0, 2, '2026-07-31 01:51:36', '2026-07-31 01:51:36');

-- --------------------------------------------------------

--
-- Table structure for table `pariwisata`
--

CREATE TABLE `pariwisata` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `jenis` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pariwisata`
--

INSERT INTO `pariwisata` (`id`, `nama`, `longitude`, `latitude`, `jenis`, `foto`, `created_at`, `updated_at`) VALUES
(1, 'Kandaga Waterpark', 108.3734240, -6.7781604, '', 'images/map/pariwisata/1785772416-kandagawaterpark.jpg', '2026-08-03 08:53:36', '2026-08-03 08:53:36'),
(2, 'Tirta Indah Waterpark', 108.3832581, -6.7762572, 'Kolam renang keluarga', 'images/map/pariwisata/1785818592-e2cc1f4b846ab3f3402acba25a39c217.jpg', '2026-08-03 21:43:12', '2026-08-03 21:43:12'),
(3, 'Safe House Camping', 108.3698498, -6.7763896, '', 'images/map/pariwisata/1785826938-DSCF5680--1-.JPG', '2026-08-03 23:54:05', '2026-08-04 00:02:18'),
(4, 'Tebing Kandaga', 108.3688756, -6.7740249, '', 'images/map/pariwisata/1785827003-tebingkandaga.JPG', '2026-08-03 23:54:05', '2026-08-04 00:03:23'),
(5, 'Bukit Perwira Cafe and Resto', 108.3677904, -6.7756143, '', 'images/map/pariwisata/1785826985-bukitperwira.jpg', '2026-08-03 23:54:05', '2026-08-04 00:03:05'),
(6, 'Pondok Durian Sinapeul Titin - Heri', 108.3765757, -6.7752966, '', 'images/map/pariwisata/1785826923-pondokdurian.jpeg', '2026-08-03 23:54:05', '2026-08-04 00:02:03'),
(7, 'Gunung Jajar', 108.3827434, -6.7684583, '', 'images/map/pariwisata/1785826798-gunungjajar.jpg', '2026-08-03 23:54:05', '2026-08-03 23:59:58'),
(8, 'Pondok Duren Sinapeul Kang Iding', 108.3673871, -6.7759650, '', 'images/map/pariwisata/1785826962-DSC01511.jpg', '2026-08-03 23:54:05', '2026-08-04 00:02:42'),
(0, 'Safe House Camping', 108.3653499, -6.7772004, 's', NULL, '2026-08-05 03:31:16', '2026-08-05 03:31:16'),
(0, 'Safe House Camping', 108.3653499, -6.7772004, 's', NULL, '2026-08-05 03:31:16', '2026-08-05 03:31:16'),
(0, 'Safe House Camping', 108.3653499, -6.7772004, 's', NULL, '2026-08-05 03:31:17', '2026-08-05 03:31:17'),
(0, 'Safe House Camping', 108.3653499, -6.7772004, 's', NULL, '2026-08-05 03:31:17', '2026-08-05 03:31:17'),
(0, 'Safe House Camping', 108.3653499, -6.7772004, 's', NULL, '2026-08-05 03:31:17', '2026-08-05 03:31:17'),
(0, 'Safe House Camping', 108.3653499, -6.7772004, 's', NULL, '2026-08-05 03:31:17', '2026-08-05 03:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL,
  `nama_pengadu` varchar(100) NOT NULL,
  `kontak` varchar(100) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `isi_pengaduan` text NOT NULL,
  `status` enum('baru','diproses','selesai') NOT NULL DEFAULT 'baru',
  `tanggapan` text DEFAULT NULL,
  `ditanggapi_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `nama_pengadu`, `kontak`, `kategori`, `isi_pengaduan`, `status`, `tanggapan`, `ditanggapi_oleh`, `created_at`, `updated_at`) VALUES
(1, 'Singh', 'ananya@rocketdigitaltech.com', 'lainnya', 'Hello http://desa-ujungberung.id,\r\n \r\nIf you’re looking to boost your website’s visibility, I can help you achieve top Google rankings.\r\n \r\nI’ll prepare a complete SEO plan with actionable steps and potential growth insights for your products or services.\r\n \r\nOnce you share your target keywords and target market, I’ll send a full proposal.\r\n \r\nBest Regards,\r\nAnanya', 'baru', NULL, NULL, '2026-08-02 21:38:43', '2026-08-02 21:38:43'),
(2, 'Aman Katiyar', 'aman@rocketdigitaltech.com', 'lainnya', 'Hello http://desa-ujungberung.id,\r\n\r\nI wanted to reach out to see if you’re open to exploring ways to grow your website traffic and boost online performance.\r\n\r\nWe offer customized SEO services that deliver measurable improvements.\r\n\r\nOnce you share your target keywords and target market, I’ll send a full proposal.\r\n\r\nBest Regards,\r\nAman', 'baru', NULL, NULL, '2026-08-04 21:08:53', '2026-08-04 21:08:53'),
(3, 'Ananya Singh', 'ananya@rocketdigitaltech.com', 'pelayanan', 'Hello http://desa-ujungberung.id,\r\n\r\nIf you’re looking to boost your website’s visibility, I can help you achieve top Google rankings.\r\n\r\nI’ll prepare a complete SEO plan with actionable steps and potential growth insights for your products or services.\r\n\r\nOnce you share your target keywords and target market, I’ll send a full proposal.\r\n\r\nBest Regards,\r\nAnanya', 'baru', NULL, NULL, '2026-08-14 17:56:42', '2026-08-14 17:56:42'),
(4, 'DavidTizDS', 'no.reply.ClaudeMuller@gmail.com', 'infrastruktur', 'Greetings! desa-ujungberung.id, \r\nYour website appeared while I was looking through listings. \r\nOur system allows companies to share proposals with website operators. \r\nYou can reach thousands of websites quickly. \r\nThe service can be used with a relatively small budget. \r\n  \r\nFeel free to reach out if this looks relevant. \r\n \r\nAppreciate your time. \r\nContact us. \r\nTelegram - https://t.me/FeedbackFormEU \r\nWhatsApp - +375259112693 \r\nWhatsApp  https://wa.me/+375259112693 \r\nWe only use chat for communication.', 'baru', NULL, NULL, '2026-08-15 05:37:17', '2026-08-15 05:37:17');

-- --------------------------------------------------------

--
-- Table structure for table `program_desa`
--

CREATE TABLE `program_desa` (
  `id` int(11) NOT NULL,
  `kategori` enum('prioritas','pariwisata','produk_unggulan','kegiatan_rutin') NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jadwal` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `status` enum('tampil','draft') NOT NULL DEFAULT 'tampil',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value_text` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistik_desa`
--

CREATE TABLE `statistik_desa` (
  `id` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `jumlah_penduduk` int(11) DEFAULT 0,
  `jumlah_kk` int(11) DEFAULT 0,
  `jumlah_laki_laki` int(11) DEFAULT 0,
  `jumlah_perempuan` int(11) DEFAULT 0,
  `luas_wilayah_ha` decimal(10,2) DEFAULT 0.00,
  `jumlah_dusun` int(11) DEFAULT 0,
  `jumlah_rt` int(11) DEFAULT 0,
  `jumlah_rw` int(11) DEFAULT 0,
  `ketinggian_mdpl` int(11) DEFAULT 0,
  `kode_pos` varchar(10) DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `statistik_desa`
--

INSERT INTO `statistik_desa` (`id`, `tahun`, `jumlah_penduduk`, `jumlah_kk`, `jumlah_laki_laki`, `jumlah_perempuan`, `luas_wilayah_ha`, `jumlah_dusun`, `jumlah_rt`, `jumlah_rw`, `ketinggian_mdpl`, `kode_pos`, `is_aktif`, `updated_by`, `updated_at`) VALUES
(1, '2025', 3450, 1020, 0, 0, 850.00, 5, 18, 0, 650, '45461', 1, NULL, '2026-07-30 16:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `umkm`
--

CREATE TABLE `umkm` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `latitude` decimal(11,8) NOT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `umkm`
--

INSERT INTO `umkm` (`id`, `nama`, `longitude`, `latitude`, `jenis`, `foto`) VALUES
(1, 'UMKM', 108.36200394, -6.77359372, 'Warung Makan', NULL),
(2, 'Pondok Durian Sinapeul Kang Iding Sardi', 108.36750682, -6.77592959, 'Warung Makan', NULL),
(3, 'Warung Bu Iding Sardi', 108.36735964, -6.77594590, 'Toko', NULL),
(4, 'UMKM', 108.37816794, -6.77696002, 'Toko Kelontong', NULL),
(5, 'UMKM', 108.36213268, -6.77408580, 'Cuci Motor', NULL),
(6, 'Potong Rambut \"Nanang Sukardi\"', 108.36211558, -6.77403120, 'Pangkas Rambut', NULL),
(7, 'Warung Oleh-oleh \"Ambang Kumis\"', 108.36705923, -6.78161741, 'Toko', NULL),
(8, 'Warung Seblak \"Mamah Nia\"', 108.36497817, -6.77929854, 'Warung Makan', NULL),
(9, 'Pondok Durian Sinapeul Titin-Heri', 108.37647613, -6.77524008, 'Warung Makan', NULL),
(10, 'Durian Abah Hasan Sinapeul', 108.37631218, -6.77512855, 'Warung Makan', NULL),
(11, 'Empal Genthong & Sate Kambing Abah Hasan', 108.37626625, -6.77511623, 'Warung Makan', NULL),
(12, 'Pondok Durian Mang Ali', 108.37616265, -6.77602381, 'Warung Makan', NULL),
(13, 'UMKM', 108.37637253, -6.77697167, 'Toko', NULL),
(14, 'UMKM', 108.37630447, -6.77676126, 'Produksi', NULL),
(15, 'UMKM', 108.37814983, -6.77681020, 'Produksi', NULL),
(16, 'Kedai Durian Sinapeul \"Pak Dedi\"', 108.36258162, -6.78157845, 'Warung Makan', NULL),
(17, 'Warung Seblak \"Teh Ika\"', 108.37666187, -6.77785095, 'Warung Makan', NULL),
(18, 'UMKM', 108.37905843, -6.77669933, 'Produksi', NULL),
(19, 'UMKM', 108.38118374, -6.77644397, 'Toko', NULL),
(20, 'UMKM', 108.38579412, -6.77450596, 'Warung Makan', NULL),
(21, 'UMKM', 108.38676240, -6.77488351, 'Toko', NULL),
(22, 'miebayam', 108.38704370, -6.77513887, 'Warung Makan', NULL),
(23, 'UMKM', 108.38659912, -6.77516118, 'Toko', NULL),
(24, 'UMKM', 108.38622764, -6.77527371, 'Toko', NULL),
(25, 'UMKM', 108.37118682, -6.78370388, 'Toko', NULL),
(26, 'Teras Seblak \"DK\"', 108.37126594, -6.78445896, 'Warung Makan', NULL),
(27, 'warung kelontong', 108.37138530, -6.78434110, 'Toko', NULL),
(28, 'Tahu UJB', 108.37062154, -6.78508719, 'Warung Makan', NULL),
(29, 'Seblak Katel Prasmanan Buchi', 108.37046698, -6.78593583, NULL, NULL),
(30, 'UMKM', 108.37042976, -6.78579966, NULL, NULL),
(31, 'UMKM', 108.37036606, -6.78587157, NULL, NULL),
(32, 'es teh cimplong', 108.37011661, -6.78605834, NULL, NULL),
(33, 'UMKM', 108.36951580, -6.78733545, NULL, NULL),
(34, 'Warung Bi Rum', 108.36954167, -6.78793000, NULL, NULL),
(35, 'UMKM', 108.39306761, -6.77453426, 'Toko', NULL),
(36, 'UMKM', 108.36485915, -6.77960550, 'Warung Makan', NULL),
(37, 'UMKM', 108.36513575, -6.78008626, 'Toko Kelontong', NULL),
(38, 'UMKM', 108.36501002, -6.77985420, 'Toko', NULL),
(39, 'Rumah Makan Saritani Prasmanan Khas Sunda', 108.36475957, -6.78183414, 'Warung Makan', NULL),
(40, 'Pangkas Rambut A Laling', 108.36521823, -6.78172261, 'Pangkas Rambut', NULL),
(41, 'UMKM', 108.36518403, -6.78172794, 'Toko', NULL),
(42, 'Depot Air Mineral \"Rezki\'n Toya\"', 108.36528763, -6.78172994, 'Toko', NULL),
(43, 'UMKM', 108.36540431, -6.78157979, 'Warung Makan', NULL),
(44, 'UMKM', 108.36553272, -6.78158478, 'Persewaan', NULL),
(45, 'UMKM', 108.36554982, -6.78175324, 'Warung Makan', NULL),
(46, 'UMKM', 108.36570170, -6.78158711, 'Warung Makan', NULL),
(47, 'Kios Buah-Buahan \"Pak Abeng Nu Bageur\"', 108.36575501, -6.78172627, 'Warung Makan', NULL),
(48, 'UMKM', 108.36591896, -6.78171429, 'Toko', NULL),
(49, 'Toko Toto', 108.36609732, -6.78170563, 'Toko', NULL),
(50, 'Rumah Makan Pak Ulun (Lesehan Asih Rasa)', 108.36619187, -6.78149822, 'Warung Makan', NULL),
(51, 'UMKM', 108.36656570, -6.78147758, 'Toko', NULL),
(52, 'UAC Chicken Geprek', 108.36677860, -6.78167567, 'Warung Makan', NULL),
(53, 'Durian Super \"Rajenra Zayn\"', 108.36693116, -6.78164204, 'Warung Makan', NULL),
(54, 'UMKM', 108.36682621, -6.78151520, 'Penjahit', NULL),
(55, 'Harmoni Distro and Clothing', 108.36689193, -6.78148923, 'Sablon Kaos & Butik', NULL),
(56, 'Toko Aditya Jaya', 108.36701028, -6.78150488, 'Toko', NULL),
(57, '\"TH\" Frozen Food & Plastik', 108.36790245, -6.78147092, 'Toko', NULL),
(58, 'Tirta Indah Waterpark', 108.38584373, -6.77617197, NULL, 'images/map/umkm/1785772501-e2cc1f4b846ab3f3402acba25a39c217.jpg'),
(59, 'Tirta Indah Waterpark', 108.38607976, -6.77599085, NULL, 'images/map/umkm/1785773544-e2cc1f4b846ab3f3402acba25a39c217.jpg'),
(60, 'UMKM', 108.36736288, -6.77601578, NULL, NULL),
(61, 'UMKM', 108.36755857, -6.77596893, NULL, NULL),
(62, 'UMKM', 108.36801133, -6.77737837, NULL, NULL),
(63, 'UMKM', 108.36815759, -6.77770513, NULL, NULL),
(64, 'UMKM', 108.36818137, -6.77875057, NULL, NULL),
(65, 'UMKM', 108.36818648, -6.78020227, NULL, NULL),
(66, 'PT Matahari Solusindo', 108.36811888, -6.78093059, NULL, NULL),
(67, 'Aneu Cell', 108.36869538, -6.78155781, NULL, NULL),
(68, 'Utama Tani Toko', 108.36866219, -6.78171695, NULL, NULL),
(69, 'UMKM', 108.36880334, -6.78174459, NULL, NULL),
(70, 'UMKM', 108.36881172, -6.78156813, NULL, NULL),
(71, 'UMKM', 108.36896695, -6.78159843, NULL, NULL),
(72, 'UMKM', 108.36990255, -6.78186033, NULL, NULL),
(73, 'UMKM', 108.37010689, -6.78169398, NULL, NULL),
(74, 'Toko Zian Jaya', 108.37074794, -6.78164670, NULL, NULL),
(75, 'UMKM', 108.37075800, -6.78188741, NULL, NULL),
(76, 'Afzar Buah', 108.37090317, -6.78163672, NULL, NULL),
(77, 'J-K CELL', 108.37107718, -6.78158578, NULL, NULL),
(78, 'UMKM', 108.37642738, -6.77696693, NULL, NULL),
(79, 'UMKM', 108.37644878, -6.77678810, NULL, NULL),
(80, 'UMKM', 108.38125482, -6.77640968, NULL, NULL),
(81, 'Bukit Perwira Cafe dan Resto', 108.36779309, -6.77560710, NULL, NULL),
(82, 'Cafe Durian Telar Maja (Tema Cafe)', 108.36957364, -6.77639036, NULL, NULL),
(83, 'Kedai Durian Dera', 108.36800403, -6.77722635, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `jabatan`, `role`, `status`, `created_at`) VALUES
(1, 'admindesa', '$2b$10$g4EajyY4vBxfZKgDl.Rwvu6xOmuS7JLhhUuWy7uYdsu8dtU8JQi12', 'Admin Desa', 'Sekretaris Desa', 'superadmin', 'aktif', '2026-07-30 16:32:20'),
(2, 'developer', '$2y$10$wYLrIBgKJC3tmFLs41emr.0G1KKaTAdam030TmbgpJSO.BP5YSrjG', 'Rifda Najla Azzahra', 'Developer', 'admin', 'aktif', '2026-07-31 00:10:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ditanggapi_oleh` (`ditanggapi_oleh`);

--
-- Indexes for table `program_desa`
--
ALTER TABLE `program_desa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_section_key` (`section`,`key_name`);

--
-- Indexes for table `statistik_desa`
--
ALTER TABLE `statistik_desa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `umkm`
--
ALTER TABLE `umkm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `program_desa`
--
ALTER TABLE `program_desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statistik_desa`
--
ALTER TABLE `statistik_desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `umkm`
--
ALTER TABLE `umkm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  ADD CONSTRAINT `laporan_keuangan_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`ditanggapi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `program_desa`
--
ALTER TABLE `program_desa`
  ADD CONSTRAINT `program_desa_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `statistik_desa`
--
ALTER TABLE `statistik_desa`
  ADD CONSTRAINT `statistik_desa_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
