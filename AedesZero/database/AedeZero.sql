-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 16, 2026 at 01:07 PM
-- Server version: 13.0.2-MariaDB
-- PHP Version: 8.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aedesZero`
--

-- --------------------------------------------------------

--
-- Table structure for table `denuncias`
--

CREATE TABLE `denuncias` (
  `id` int(11) NOT NULL,
  `bairro` varchar(150) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `tipoFoco` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('pendente','em_andamento','resolvida') NOT NULL DEFAULT 'pendente',
  `criado_em` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `denuncias`
--

INSERT INTO `denuncias` (`id`, `bairro`, `endereco`, `tipoFoco`, `descricao`, `foto`, `status`, `criado_em`) VALUES
(1, 'Centro', 'Rua Pedro Luiz, nº 120', 'Dengue', 'Terreno baldio com vários pneus velhos acumulando água da chuva.', NULL, 'em_andamento', '2026-10-12 20:14:43'),
(2, 'Mangalô', 'Avenida Silva, próximo ao mercado', 'Zika', 'Caixa d\'água destampada no topo de uma construção em reforma.', NULL, 'em_andamento', '2026-10-12 20:14:43'),
(4, 'Catu', 'Rua Principal, em frente à praça', 'Dengue', 'Piscina sem tratamento químico em imóvel desocupado.', NULL, 'pendente', '2026-10-12 20:14:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `denuncias`
--
ALTER TABLE `denuncias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_denuncias_status` (`status`),
  ADD KEY `idx_denuncias_criado_em` (`criado_em`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `denuncias`
--
ALTER TABLE `denuncias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
