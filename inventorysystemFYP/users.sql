-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 10:50 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `roti_seri_bakery_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Administrator','Inventory Manager','Bakery Staff') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `username`, `fullName`, `email`, `phoneNumber`, `address`, `password`, `role`, `created_at`) VALUES
('aadmin', 'aadmin', 'aria', 'aria@gmail.com', '0123456781', 'awan', '$2y$10$pABhRo0syw61ssRtejS8Ae1aCBHwQSOgQ5UZEIYqZIdsP3wRPuw0q', 'Administrator', '2025-04-12 18:14:17'),
('admin1', 'admin1', 'Hana', 'hana@gmail.com', '01976545678', 'Tawau, Sabah', '$2y$10$Rna7JJL8E94J/Yv6n4JCaORFTk4IufUVCL0U3XAjJ.tRp8H2ZurLa', 'Administrator', '2025-03-24 18:26:42'),
('adminTest', 'adminTest', 'Admin Test', 'admin@test.com', '0123456789', 'Test Address', '$2y$10$txGCdV4DtFaAH2MO4upBb.t7GfILBn0ctjVWJvmDakONoP/uKCaa2', 'Administrator', '2025-03-25 06:22:04'),
('manager01', 'manager01', 'MANAGER', 'manager@gmail.com', '0156789234', 'Perak', '$2y$10$oAnTLr7.au7bBu4F8eujtuZH3BNl9KTyzyNJnhw63YS/x9sCYKHdW', 'Inventory Manager', '2025-04-13 17:36:10'),
('staff001', 'staff1', 'twinklebaee', 'hafi@gmail.com', '1234567890', 'dd', '$2y$10$9uuJ3NvCp0blEgaEVRMXNuylGP60gaHPADvZTzOUNylJCttfEgfRq', 'Inventory Manager', '2025-04-12 18:32:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phoneNumber` (`phoneNumber`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
