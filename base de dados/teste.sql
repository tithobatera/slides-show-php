-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20-Mar-2025 às 15:56
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `teste`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `presentations`
--

CREATE TABLE `presentations` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `presentations`
--

INSERT INTO `presentations` (`id`, `title`, `uploaded_at`) VALUES
(319, 'Setembro amarelo', '2025-03-14 10:34:27'),
(320, 'Outubro Rosa', '2025-03-14 10:41:30'),
(321, 'Novembro Azul', '2025-03-14 10:42:16'),
(322, 'Vídeo', '2025-03-14 10:42:31'),
(323, 'Teste3', '2025-03-14 10:42:49'),
(324, 'Teste1', '2025-03-14 10:42:59'),
(325, 'Teste2', '2025-03-14 10:43:11'),
(326, 'abc', '2025-03-14 11:39:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `presentation_files`
--

CREATE TABLE `presentation_files` (
  `id` int(11) NOT NULL,
  `presentation_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `intervalo_slide` int(11) DEFAULT 5,
  `order_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `presentation_files`
--

INSERT INTO `presentation_files` (`id`, `presentation_id`, `file_path`, `intervalo_slide`, `order_number`) VALUES
(888, 319, 'uploads/Setembro_amarelo/img2.png', 2, 1),
(889, 319, 'uploads/Setembro_amarelo/img3.png', 5, 2),
(890, 320, 'uploads/Outubro_Rosa/img1.png', 2, 1),
(891, 320, 'uploads/Outubro_Rosa/img4.png', 2, 2),
(892, 321, 'uploads/Novembro_Azul/img3.png', 5, 1),
(893, 321, 'uploads/Novembro_Azul/bg_blue.jpg', 2, 2),
(894, 322, 'uploads/V__deo/video.mp4', 13, 1),
(895, 323, 'uploads/Teste3/img2.png', 2, 1),
(896, 324, 'uploads/Teste1/bg_blue.jpg', 2, 1),
(897, 325, 'uploads/Teste2/impact.webp', 2, 1),
(898, 326, 'uploads/abc/img4.png', 5, 1),
(899, 326, 'uploads/abc/logo fresenius2.png', 2, 2),
(900, 326, 'uploads/abc/img3.png', 2, 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `televisores`
--

CREATE TABLE `televisores` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `presentation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `televisores`
--

INSERT INTO `televisores` (`id`, `nome`, `url`, `ip`, `presentation_id`) VALUES
(116, 'TV_UNIDADE_3', 'TV_UNIDADE_3', '192.168.197.152', 326),
(117, 'TV_UNIDADE_2', 'TV_UNIDADE_2', '192.168.18.104', 326),
(118, 'TV_UNIDADE_4', 'TV_UNIDADE_4', '192.168.192.125', 322);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `presentations`
--
ALTER TABLE `presentations`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `presentation_files`
--
ALTER TABLE `presentation_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `presentation_id` (`presentation_id`);

--
-- Índices para tabela `televisores`
--
ALTER TABLE `televisores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `presentations`
--
ALTER TABLE `presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- AUTO_INCREMENT de tabela `presentation_files`
--
ALTER TABLE `presentation_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=901;

--
-- AUTO_INCREMENT de tabela `televisores`
--
ALTER TABLE `televisores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `presentation_files`
--
ALTER TABLE `presentation_files`
  ADD CONSTRAINT `presentation_files_ibfk_1` FOREIGN KEY (`presentation_id`) REFERENCES `presentations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
