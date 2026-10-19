-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2026 at 02:54 PM
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
-- Database: `project_ttu`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id_user` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id_user`, `username`, `password`, `role`, `reset_token`, `token_expires_at`) VALUES
(1, 'administrator', '$2a$12$Cib8m5xiPkZ1nYadL9qCQejM0LOKm.ZYLEkjI9peCstI1/3O369UC', 'admin', NULL, NULL),
(2, 'user_pegawai', '$2a$12$NK3OA7Gba13yzk0rFGXxJu3Uu3ZtdOJqkVRPbhJRMkwUl/y685a7y', 'user', NULL, NULL),
(3, 'newadmin', '$2y$10$OmiJf5GNa.2hpR3y.OCrpOpvGTWGesXTxJX6yizoQcul.WhsuTKES', 'admin', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `data_gaji`
--

CREATE TABLE `data_gaji` (
  `no` int(255) NOT NULL,
  `nip_pegawai` varchar(50) DEFAULT NULL,
  `nama_pegawai` varchar(255) NOT NULL,
  `kantor` varchar(255) NOT NULL,
  `jabatan_pegawai` varchar(255) NOT NULL,
  `gaji_pokok` int(255) NOT NULL,
  `tunjangan_keluarga` int(255) NOT NULL,
  `tunjangan_pangan` int(255) NOT NULL,
  `tunjangan_jabatan` int(255) NOT NULL,
  `tunjangan_keahliankhusus` int(255) NOT NULL,
  `tunjangan_operasional` int(255) NOT NULL,
  `jumlah` int(255) NOT NULL,
  `tunjangan_kesehatan` int(255) NOT NULL,
  `tunjangan_pph` int(255) NOT NULL,
  `tunjangan_lembur` int(255) NOT NULL,
  `jumlah_total` int(255) NOT NULL,
  `jam_terlambat` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_gaji`
--

INSERT INTO `data_gaji` (`no`, `nip_pegawai`, `nama_pegawai`, `kantor`, `jabatan_pegawai`, `gaji_pokok`, `tunjangan_keluarga`, `tunjangan_pangan`, `tunjangan_jabatan`, `tunjangan_keahliankhusus`, `tunjangan_operasional`, `jumlah`, `tunjangan_kesehatan`, `tunjangan_pph`, `tunjangan_lembur`, `jumlah_total`, `jam_terlambat`) VALUES
(1, 'PGW-9901', 'Andi John', 'KANTOR PUSAT', 'Pimpinan Utama', 9000000, 1200000, 500000, 2000000, 800000, 1000000, 14500000, 500000, 250000, 0, 15250000, 0),
(2, 'PGW-9902', 'Melatonin Dewi', 'KPO UNGARAN', 'Senior Supervisor Keuangan', 5800000, 500000, 350000, 1000000, 300000, 500000, 8450000, 300000, 120000, 100000, 8970000, 0),
(3, 'PGW-9903', 'Budi', 'KC AMBARAWA', 'Back-End Engineer', 4800000, 0, 300000, 700000, 400000, 350000, 6550000, 200000, 80000, 150000, 6980000, 1),
(4, 'PGW-9904', 'Nadia', 'KC BAWEN', 'Staf Administrasi', 3500000, 150000, 200000, 350000, 0, 250000, 4450000, 180000, 40000, 0, 4670000, 0),
(5, 'PGW-9905', 'Fikri', 'KC BRINGIN', 'Customer Service', 3100000, 0, 200000, 250000, 0, 200000, 3750000, 150000, 20000, 0, 3920000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `data_pegawai`
--

CREATE TABLE `data_pegawai` (
  `nip_pegawai` varchar(255) NOT NULL,
  `nama_pegawai` varchar(255) NOT NULL,
  `jabatan_pegawai` varchar(255) NOT NULL,
  `golongan_pegawai` varchar(255) NOT NULL,
  `grade_pegawai` varchar(255) NOT NULL,
  `tahun_bekerja` int(255) NOT NULL,
  `status_kepegawaian` varchar(255) NOT NULL,
  `gajipokok_pegawai` int(255) NOT NULL,
  `tanggal_selesaikerja` date NOT NULL,
  `kantor_cabang` varchar(255) NOT NULL,
  `tanggal_bergabung` date NOT NULL,
  `email_pegawai` varchar(255) NOT NULL,
  `alamat_domisili` varchar(255) NOT NULL,
  `tempat_lahir` varchar(255) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` varchar(255) NOT NULL,
  `status_perkawinan` varchar(255) NOT NULL,
  `nomor_hp` varchar(255) NOT NULL,
  `agama` varchar(255) NOT NULL,
  `pendidikan_terakhir` varchar(255) NOT NULL,
  `golongan_darah` varchar(255) NOT NULL,
  `npwp` varchar(255) NOT NULL,
  `nomor_ktp` varchar(255) NOT NULL,
  `nomor_kk` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_pegawai`
--

INSERT INTO `data_pegawai` (`nip_pegawai`, `nama_pegawai`, `jabatan_pegawai`, `golongan_pegawai`, `grade_pegawai`, `tahun_bekerja`, `status_kepegawaian`, `gajipokok_pegawai`, `tanggal_selesaikerja`, `kantor_cabang`, `tanggal_bergabung`, `email_pegawai`, `alamat_domisili`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `status_perkawinan`, `nomor_hp`, `agama`, `pendidikan_terakhir`, `golongan_darah`, `npwp`, `nomor_ktp`, `nomor_kk`) VALUES
('PGW-9901', 'Andi John', 'Pimpinan Utama', 'Golongan D', '1', 7, 'Tetap', 9000000, '2036-05-10', 'KANTOR PUSAT', '2019-02-01', 'andi.john@example.com', 'Jl. Mawar No. 123, Kota A', 'KOTA A', '1984-05-10', 'LAKI-LAKI', 'Menikah', '089900001111', 'Islam', 'S2', 'B', '990000000000001', '1122334455660001', '9988776655440001'),
('PGW-9902', 'Melatonin Dewi', 'Senior Supervisor Keuangan', 'Golongan C', '2', 4, 'Tetap', 5800000, '2039-10-15', 'KPO UNGARAN', '2022-04-10', 'melatonin.dewi@example.com', 'Jl. Melati No. 456, Kota B', 'KOTA B', '1989-10-15', 'PEREMPUAN', 'Menikah', '089900002222', 'Islam', 'Sarjana', 'O', '990000000000002', '1122334455660002', '9988776655440002'),
('PGW-9903', 'Budi', 'Back-End Engineer', 'Golongan C', '1', 2, 'Tetap', 4800000, '2041-07-22', 'KC AMBARAWA', '2024-01-15', 'budi@example.com', 'Jl. Anggrek No. 789, Kota C', 'KOTA C', '1994-07-22', 'LAKI-LAKI', 'Belum menikah', '088800003333', 'Islam', 'Sarjana', 'A', '990000000000003', '1122334455660003', '9988776655440003'),
('PGW-9904', 'Nadia', 'Staf Administrasi', 'Golongan B', '2', 2, 'Tetap', 3500000, '2043-03-18', 'KC BAWEN', '2024-03-01', 'nadia@example.com', 'Jl. Flamboyan No. 321, Kota D', 'KOTA D', '1996-03-18', 'PEREMPUAN', 'Menikah', '088800004444', 'Islam', 'Sarjana', 'AB', '99000000000004', '1122334455660004', '9988776655440004'),
('PGW-9905', 'Fikri', 'Customer Service', 'Golongan B', '1', 1, 'Kontrak', 3100000, '2026-12-31', 'KC BRINGIN', '2025-06-01', 'fikri@example.com', 'Jl. Kenanga No. 654, Kota E', 'KOTA E', '1999-11-12', 'LAKI-LAKI', 'Belum menikah', '087700005555', 'Islam', 'D3', 'O', '990000000000005', '1122334455660005', '9988776655440005');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id_user`, `nama`, `password`) VALUES
(1, 'admin_testing', 'admin123'),
(2, 'user_testing', 'user123');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_potongan`
--

CREATE TABLE `pengaturan_potongan` (
  `id` int(11) NOT NULL,
  `potongan_per_jam` int(11) DEFAULT 5882
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_presensi`
--

CREATE TABLE `pengaturan_presensi` (
  `id` int(11) NOT NULL DEFAULT 1,
  `jam_kerja_awal` time NOT NULL DEFAULT '08:00:00',
  `jam_kerja_akhir` time NOT NULL DEFAULT '17:00:00',
  `jam_lembur_mulai` time NOT NULL DEFAULT '17:00:00',
  `hari_kerja` int(11) NOT NULL DEFAULT 22,
  `jam_perhari` int(11) NOT NULL DEFAULT 8,
  `toleransi_keterlambatan` int(11) NOT NULL DEFAULT 15,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaturan_presensi`
--

INSERT INTO `pengaturan_presensi` (`id`, `jam_kerja_awal`, `jam_kerja_akhir`, `jam_lembur_mulai`, `hari_kerja`, `jam_perhari`, `toleransi_keterlambatan`, `updated_at`) VALUES
(1, '08:00:00', '17:00:00', '17:00:00', 22, 8, 15, '2025-12-08 03:30:57');

-- --------------------------------------------------------

--
-- Table structure for table `potongan_gaji`
--

CREATE TABLE `potongan_gaji` (
  `id` int(11) NOT NULL,
  `nama_potongan` varchar(100) NOT NULL,
  `persentase` int(11) DEFAULT 0,
  `nominal` int(11) DEFAULT 0,
  `keterangan` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `potongan_gaji`
--

INSERT INTO `potongan_gaji` (`id`, `nama_potongan`, `persentase`, `nominal`, `keterangan`, `updated_at`) VALUES
(1, 'Pemotongan Keterlambatan', 0, 5882, 'Potongan tunjangan operasional per jam keterlambatan', '2025-12-08 03:30:57');

-- --------------------------------------------------------

--
-- Table structure for table `presensi`
--

CREATE TABLE `presensi` (
  `id` int(11) NOT NULL,
  `nip_pegawai` varchar(50) DEFAULT NULL,
  `nama_pegawai` varchar(100) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `jam_lembur` decimal(5,2) DEFAULT 0.00,
  `uang_lembur` decimal(12,2) DEFAULT 0.00,
  `keterangan` varchar(100) DEFAULT NULL,
  `jam_terlambat` decimal(5,2) DEFAULT 0.00,
  `potongan_tunjangan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presensi`
--

INSERT INTO `presensi` (`id`, `nip_pegawai`, `nama_pegawai`, `tanggal`, `jam_masuk`, `jam_keluar`, `jam_lembur`, `uang_lembur`, `keterangan`, `jam_terlambat`, `potongan_tunjangan`) VALUES
(1, 'PGW-9901', 'Andi John', '2026-08-01', '07:45:00', '17:00:00', 0.00, 0.00, 'Hadir Tepat Waktu', 0.00, 0),
(2, 'PGW-9902', 'Melatonin Dewi', '2026-08-01', '07:50:00', '18:00:00', 1.00, 20000.00, 'Lembur Pekerjaan', 0.00, 0),
(3, 'PGW-9903', 'Budi', '2026-08-01', '08:12:00', '19:00:00', 2.00, 40000.00, 'Terlambat & Lembur', 1.00, 5882),
(4, 'PGW-9904', 'Nadia', '2026-08-01', '07:55:00', '17:00:00', 0.00, 0.00, 'Hadir Tepat Waktu', 0.00, 0),
(5, 'PGW-9905', 'Fikri', '2026-08-01', '07:40:00', '17:00:00', 0.00, 0.00, 'Hadir Tepat Waktu', 0.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rekap_gaji_lembur`
--

CREATE TABLE `rekap_gaji_lembur` (
  `id` int(11) NOT NULL,
  `nip_pegawai` varchar(50) DEFAULT NULL,
  `bulan` tinyint(4) NOT NULL,
  `tahun` year(4) NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_keluarga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_pangan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_jabatan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_keahliankhusus` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_operasional` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_kesehatan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_pph` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tunjangan_lembur` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_jam_lembur` decimal(5,2) DEFAULT 0.00,
  `uang_lembur` decimal(15,2) DEFAULT 0.00,
  `total_gaji` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tabel_karyawan`
--

CREATE TABLE `tabel_karyawan` (
  `id_karyawan` int(11) NOT NULL,
  `nama_karyawan` varchar(50) NOT NULL,
  `jabatan_karyawan` varchar(50) NOT NULL,
  `kantor` varchar(50) NOT NULL,
  `umur` int(255) NOT NULL,
  `waktu_awal_kerja` date NOT NULL,
  `gaji` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `tarif_lembur`
--

CREATE TABLE `tarif_lembur` (
  `id` int(11) NOT NULL,
  `kategori_jabatan` varchar(100) NOT NULL,
  `tarif_luar_jam_kerja` int(11) NOT NULL,
  `tarif_luar_hari_kerja` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tarif_lembur`
--

INSERT INTO `tarif_lembur` (`id`, `kategori_jabatan`, `tarif_luar_jam_kerja`, `tarif_luar_hari_kerja`, `updated_at`) VALUES
(1, 'Ketua Satuan Kerja/Kepala Divisi/Pimpinan Cabang/Pimpinan KPO', 20000, 22500, '2025-12-08 10:39:42'),
(2, 'Anggota SKAI/Supervisor', 17500, 20000, '2026-05-21 06:00:11'),
(3, 'Staf', 15000, 17500, '2025-12-07 23:20:52'),
(4, 'Calon Pegawai', 14000, 16500, '2025-12-07 23:20:52'),
(5, 'Tenaga Kontrak/PKWT', 13000, 15000, '2025-12-07 23:20:52'),
(6, 'Satpam/OB/Driver', 12500, 14500, '2025-12-07 23:20:52'),
(7, 'Calon Pegawai Satpam/OB/Driver', 11500, 13500, '2025-12-07 23:20:52'),
(8, 'Tenaga Kontrak Pegawai Satpam/OB/Driver', 10500, 12500, '2025-12-07 23:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `temp_nip`
--

CREATE TABLE `temp_nip` (
  `NIP` varchar(50) DEFAULT NULL,
  `Nama Pegawai` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `temp_nip`
--

INSERT INTO `temp_nip` (`NIP`, `Nama Pegawai`) VALUES
('PGW-9901', 'Andi John'),
('PGW-9902', 'Melatonin Dewi'),
('PGW-9903', 'Budi'),
('PGW-9904', 'Nadia'),
('PGW-9905', 'Fikri');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `data_gaji`
--
ALTER TABLE `data_gaji`
  ADD PRIMARY KEY (`no`),
  ADD UNIQUE KEY `nip_pegawai` (`nip_pegawai`);

--
-- Indexes for table `data_pegawai`
--
ALTER TABLE `data_pegawai`
  ADD PRIMARY KEY (`nip_pegawai`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `pengaturan_potongan`
--
ALTER TABLE `pengaturan_potongan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan_presensi`
--
ALTER TABLE `pengaturan_presensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nip_pegawai` (`nip_pegawai`);

--
-- Indexes for table `rekap_gaji_lembur`
--
ALTER TABLE `rekap_gaji_lembur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nip_pegawai` (`nip_pegawai`);

--
-- Indexes for table `tabel_karyawan`
--
ALTER TABLE `tabel_karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- Indexes for table `tarif_lembur`
--
ALTER TABLE `tarif_lembur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kategori_jabatan` (`kategori_jabatan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id_user` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `data_gaji`
--
ALTER TABLE `data_gaji`
  MODIFY `no` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengaturan_potongan`
--
ALTER TABLE `pengaturan_potongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potongan_gaji`
--
ALTER TABLE `potongan_gaji`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rekap_gaji_lembur`
--
ALTER TABLE `rekap_gaji_lembur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tabel_karyawan`
--
ALTER TABLE `tabel_karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tarif_lembur`
--
ALTER TABLE `tarif_lembur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `presensi`
--
ALTER TABLE `presensi`
  ADD CONSTRAINT `presensi_ibfk_1` FOREIGN KEY (`nip_pegawai`) REFERENCES `data_gaji` (`nip_pegawai`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rekap_gaji_lembur`
--
ALTER TABLE `rekap_gaji_lembur`
  ADD CONSTRAINT `rekap_gaji_lembur_ibfk_1` FOREIGN KEY (`nip_pegawai`) REFERENCES `data_gaji` (`nip_pegawai`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
