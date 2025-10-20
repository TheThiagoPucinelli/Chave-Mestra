-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08/07/2025 às 16:30
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `chave-mestra2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `chave`
--

CREATE TABLE `chave` (
  `id_chave` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `numero_identificacao` varchar(20) NOT NULL,
  `descricao` text DEFAULT NULL,
  `quantidade` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `cpf_adm` char(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chave`
--

INSERT INTO `chave` (`id_chave`, `nome`, `numero_identificacao`, `descricao`, `quantidade`, `status`, `cpf_adm`) VALUES
(1, 'lab21', '122', '21', 2, '0', '05932857005'),
(2, 'lab3', '55', 'infomatica 2', 3, '0', '05932857005'),
(23, 'LAB. 1', '01', 'Laboratório 1 - sala de informática', 2, '0', '05932857005'),
(24, 'LAB. 2', '02', 'Laboratório 2 - sala de eletrônica', 3, '0', '05932857005'),
(25, 'SALA REUNIÃO', '03', 'Sala de reunião principal', 1, '0', '05932857005'),
(26, 'ESCRITÓRIO', '04', 'Chave do escritório administrativo', 1, '0', '05932857005'),
(27, 'ARQUIVO', '05', 'Arquivo geral do departamento', 1, '0', '05932857005'),
(28, 'COZINHA', '06', 'Acesso à cozinha da instituição', 1, '0', '05932857005'),
(29, 'DEPÓSITO', '07', 'Depósito de materiais', 2, '0', '05932857005'),
(30, 'LAB. QUÍMICA', '08', 'Laboratório de química', 2, '0', '05932857005'),
(31, 'LAB. FÍSICA', '09', 'Laboratório de física', 2, '0', '05932857005'),
(32, 'LAB. BIOLÓGICO', '10', 'Laboratório de biologia', 2, '0', '05932857005'),
(33, 'lab7', '87', 'infomatica 6', 3, '0', '05932857005');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimo`
--

CREATE TABLE `emprestimo` (
  `id_emprestimo` int(11) NOT NULL,
  `data_reserva` date DEFAULT NULL,
  `data_inicio_reserva` date DEFAULT NULL,
  `data_fim_reserva` date DEFAULT NULL,
  `hora_data_retirada` datetime DEFAULT NULL,
  `hora_data_devolucao` datetime DEFAULT NULL,
  `id_chave` int(11) NOT NULL,
  `cpf_solicitante` char(11) NOT NULL,
  `cpf_adm` varchar(11) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emprestimo`
--

INSERT INTO `emprestimo` (`id_emprestimo`, `data_reserva`, `data_inicio_reserva`, `data_fim_reserva`, `hora_data_retirada`, `hora_data_devolucao`, `id_chave`, `cpf_solicitante`, `cpf_adm`, `categoria`) VALUES
(4, '2025-07-08', '2025-07-08', '2025-07-08', '2025-07-08 11:00:32', '2025-07-08 10:40:55', 1, '05932857005', '05932857005', 'aluno'),
(5, NULL, '2025-07-08', NULL, '2025-07-08 10:40:38', '2025-07-08 10:41:09', 2, '05932857005', '05932857005', NULL),
(6, '2025-07-08', '2025-07-08', '2025-07-09', '2025-07-08 10:59:58', '2025-07-08 11:03:13', 28, '05932857005', '05932857005', 'funcionario'),
(7, '2025-07-08', '2025-07-08', '2025-07-09', '2025-07-08 11:08:48', '2025-07-08 11:09:01', 27, '05932857005', '05932857005', 'aluno'),
(8, '2025-07-08', '2025-07-08', '2025-07-08', '2025-07-08 11:09:31', '2025-07-08 11:17:11', 28, '05932857005', '05932857005', 'funcionario'),
(9, '2025-07-08', '2025-07-08', '2025-07-08', NULL, NULL, 27, '01732472025', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitante`
--

CREATE TABLE `solicitante` (
  `cpf` char(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `solicitante`
--

INSERT INTO `solicitante` (`cpf`) VALUES
('01732472025'),
('05932857005');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `cpf` char(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`cpf`, `nome`, `senha`, `email`) VALUES
('01732472025', 'Thiago usuario', '$2y$10$kkYMVP0Hhg5RmsSWlbOL7uC0Ihy6QiDNssMr5yyx3nG.7xk/QTqQ6', 'ts@gmail.com'),
('05932857005', 'Thiago', '$2y$10$Y.4tcfVPYQS7xExC3A.Y0.nAwLa8UbEE9we6LFqg4omdoE.VGSegG', 'thiago.pucinelli.177@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_adm`
--

CREATE TABLE `usuario_adm` (
  `cpf` char(11) NOT NULL,
  `tipo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario_adm`
--

INSERT INTO `usuario_adm` (`cpf`, `tipo`) VALUES
('05932857005', 'Administrador');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `chave`
--
ALTER TABLE `chave`
  ADD PRIMARY KEY (`id_chave`),
  ADD UNIQUE KEY `numero_identificacao` (`numero_identificacao`),
  ADD KEY `cpf_adm` (`cpf_adm`);

--
-- Índices de tabela `emprestimo`
--
ALTER TABLE `emprestimo`
  ADD PRIMARY KEY (`id_emprestimo`),
  ADD KEY `id_chave` (`id_chave`),
  ADD KEY `cpf_solicitante` (`cpf_solicitante`),
  ADD KEY `cpf_adm` (`cpf_adm`);

--
-- Índices de tabela `solicitante`
--
ALTER TABLE `solicitante`
  ADD PRIMARY KEY (`cpf`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`cpf`);

--
-- Índices de tabela `usuario_adm`
--
ALTER TABLE `usuario_adm`
  ADD PRIMARY KEY (`cpf`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `chave`
--
ALTER TABLE `chave`
  MODIFY `id_chave` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de tabela `emprestimo`
--
ALTER TABLE `emprestimo`
  MODIFY `id_emprestimo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `chave`
--
ALTER TABLE `chave`
  ADD CONSTRAINT `chave_ibfk_1` FOREIGN KEY (`cpf_adm`) REFERENCES `usuario_adm` (`cpf`);

--
-- Restrições para tabelas `emprestimo`
--
ALTER TABLE `emprestimo`
  ADD CONSTRAINT `emprestimo_ibfk_1` FOREIGN KEY (`id_chave`) REFERENCES `chave` (`id_chave`),
  ADD CONSTRAINT `emprestimo_ibfk_2` FOREIGN KEY (`cpf_solicitante`) REFERENCES `solicitante` (`cpf`),
  ADD CONSTRAINT `emprestimo_ibfk_3` FOREIGN KEY (`cpf_adm`) REFERENCES `usuario_adm` (`cpf`);

--
-- Restrições para tabelas `solicitante`
--
ALTER TABLE `solicitante`
  ADD CONSTRAINT `solicitante_ibfk_1` FOREIGN KEY (`cpf`) REFERENCES `usuario` (`cpf`);

--
-- Restrições para tabelas `usuario_adm`
--
ALTER TABLE `usuario_adm`
  ADD CONSTRAINT `usuario_adm_ibfk_1` FOREIGN KEY (`cpf`) REFERENCES `usuario` (`cpf`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
