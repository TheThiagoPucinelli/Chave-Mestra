-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 04/11/2025 às 07:50
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
-- Banco de dados: `chave-mestra3`
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
(57, 'lab1', '12', 'laboratotio de insafasgfag', 2, '', '11111111111'),
(58, 'Almoxarifado', '22', 'Sala asgfasgag', 4, '', '11111111111'),
(59, 'LAB.1', '01', 'Chave laboratorio 1', 2, '', '11111111111');

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimo`
--

CREATE TABLE `emprestimo` (
  `id_emprestimo` int(11) NOT NULL,
  `data_reserva` date DEFAULT NULL,
  `data_inicio_reserva` datetime NOT NULL,
  `data_fim_reserva` datetime NOT NULL,
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
(90, '2025-11-02', '2025-11-02 19:48:00', '2025-11-02 21:48:00', '2025-11-02 17:48:32', '2025-11-02 17:48:35', 58, '11111111111', '11111111111', NULL),
(91, '2025-11-02', '2025-11-02 20:50:00', '2025-11-02 21:50:00', '2025-11-02 17:50:45', '2025-11-02 17:50:52', 59, '11111111111', '11111111111', NULL),
(92, '2025-11-02', '2025-11-02 19:50:00', '2025-11-02 22:50:00', '2025-11-02 17:51:11', '2025-11-02 17:51:11', 59, '11111111111', '11111111111', 'Cancelado'),
(93, '2025-11-02', '2025-11-02 17:59:00', '2025-11-21 17:59:00', '2025-11-02 18:00:23', '2025-11-02 18:00:23', 57, '11111111111', '11111111111', 'Cancelado'),
(94, '2025-11-02', '2025-11-02 20:08:00', '2025-11-02 22:08:00', '2025-11-02 18:11:40', '2025-11-02 18:20:33', 58, '22222222222', '11111111111', NULL),
(95, '2025-11-02', '2025-11-02 22:13:00', '2025-11-02 23:09:00', '2025-11-02 18:11:49', '2025-11-02 18:20:35', 58, '22222222222', '11111111111', NULL),
(96, '2025-11-02', '2025-11-03 18:09:00', '2025-11-03 21:09:00', '2025-11-02 18:11:46', '2025-11-02 18:20:38', 59, '22222222222', '11111111111', NULL),
(97, '2025-11-02', '2025-11-04 20:11:00', '2025-11-04 23:11:00', '2025-11-02 18:11:44', '2025-11-02 18:20:41', 59, '22222222222', '11111111111', NULL),
(98, '2025-11-02', '2025-11-02 19:20:00', '2025-11-02 20:20:00', '2025-11-02 18:27:51', '2025-11-02 18:30:51', 58, '11111111111', '11111111111', NULL),
(102, '2025-11-02', '2025-11-02 20:03:00', '2025-11-02 22:03:00', '2025-11-02 19:03:45', '2025-11-02 19:03:45', 58, '22222222222', '11111111111', 'Cancelado'),
(105, '2025-11-02', '2025-11-02 21:26:00', '2025-11-02 22:26:00', '2025-11-03 22:34:42', '2025-11-03 22:37:50', 59, '22222222222', '11111111111', 'Cancelado'),
(106, '2025-11-02', '2025-11-02 21:31:00', '2025-11-02 22:31:00', '2025-11-03 22:34:39', '2025-11-03 22:37:47', 58, '22222222222', '11111111111', 'Cancelado'),
(107, '2025-11-03', '2025-11-04 10:30:00', '2025-11-04 14:30:00', '2025-11-03 22:34:34', '2025-11-03 22:37:33', 58, '11111111111', '11111111111', NULL),
(108, '2025-11-03', '2025-11-04 15:25:00', '2025-11-04 16:27:00', '2025-11-03 23:36:26', '2025-11-03 23:36:45', 58, 'ZD4WB0B4OPS', '11111111111', NULL),
(109, '2025-11-04', '2025-11-04 03:25:00', '2025-11-04 05:25:00', '2025-11-04 01:30:19', '2025-11-04 01:30:25', 59, '11111111111', '11111111111', NULL),
(110, '2025-11-04', '2025-11-04 05:31:00', '2025-11-04 06:31:00', '2025-11-04 01:35:42', '2025-11-04 01:35:50', 59, '11111111111', '11111111111', NULL),
(111, '2025-11-04', '2025-11-05 03:33:00', '2025-11-05 06:33:00', '2025-11-04 01:35:45', '2025-11-04 01:35:52', 57, '11111111111', '11111111111', NULL),
(112, '2025-11-04', '2025-11-11 15:34:00', '2025-11-11 18:35:00', '2025-11-04 01:35:39', '2025-11-04 01:35:47', 58, '11111111111', '11111111111', NULL),
(113, '2025-11-04', '2025-11-04 04:36:00', '2025-11-04 06:36:00', '2025-11-04 01:43:27', '2025-11-04 01:50:58', 59, '11111111111', '22222222222', NULL),
(114, '2025-11-04', '2025-11-04 01:45:00', '2025-11-04 01:51:00', '2025-11-04 01:43:23', '2025-11-04 01:50:53', 58, '11111111111', '22222222222', NULL),
(115, '2025-11-04', '2025-11-04 02:02:00', '2025-11-04 02:03:00', '2025-11-04 02:02:11', '2025-11-04 02:03:34', 59, '11111111111', '11111111111', NULL),
(116, '2025-11-04', '2025-11-04 02:03:00', '2025-11-04 02:04:00', '2025-11-04 02:04:56', '2025-11-04 02:06:03', 59, '11111111111', '11111111111', NULL),
(117, '2025-11-04', '2025-11-04 02:07:00', '2025-11-04 02:08:00', '2025-11-04 02:09:27', '2025-11-04 02:09:32', 59, '11111111111', '11111111111', NULL),
(118, '2025-11-04', '2025-11-04 02:10:00', '2025-11-04 02:11:00', '2025-11-04 02:10:07', '2025-11-04 02:23:12', 59, '11111111111', '11111111111', NULL),
(119, '2025-11-04', '2025-11-04 02:23:00', '2025-11-04 02:24:00', '2025-11-04 02:23:44', '2025-11-04 02:37:29', 57, '11111111111', '11111111111', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `emprestimo_fixo`
--

CREATE TABLE `emprestimo_fixo` (
  `id_fixo` int(11) NOT NULL,
  `id_chave` int(11) NOT NULL,
  `cpf_solicitante` char(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `emprestimo_fixo`
--

INSERT INTO `emprestimo_fixo` (`id_fixo`, `id_chave`, `cpf_solicitante`, `dia_semana`, `hora_inicio`, `hora_fim`) VALUES
(5, 59, '11111111111', 1, '14:00:00', '18:00:00'),
(6, 59, '11111111111', 2, '14:00:00', '18:00:00'),
(7, 59, '11111111111', 3, '14:00:00', '18:00:00'),
(8, 59, '11111111111', 4, '14:00:00', '18:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacao_usuario`
--

CREATE TABLE `notificacao_usuario` (
  `id_notificacao` int(11) NOT NULL,
  `cpf_usuario` varchar(14) NOT NULL,
  `id_emprestimo` int(11) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes_admin`
--

CREATE TABLE `notificacoes_admin` (
  `id_notificacao` int(11) NOT NULL,
  `id_emprestimo` int(11) NOT NULL,
  `cpf_admin` varchar(14) NOT NULL,
  `mensagem` varchar(255) NOT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('11111111111'),
('22222222222'),
('ZD4WB0B4OPS');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `cpf` char(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `info_categoria` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`cpf`, `nome`, `senha`, `email`, `categoria`, `info_categoria`) VALUES
('11111111111', 'Thiago Pucinelli Aires da Silva', '$2y$10$5VV5060TF3NnPok8TQn2muRcYvLv0ONp7vMyxO10nhflBwFJyiJAO', 'thiago.pucinelli.177@gmail.com', 'Aluno', '123444444TDSCAVGs'),
('22222222222', 'Thiago Teste', '$2y$10$dCufVzSbrKBYUF7FAxpjOOM8uAZntUh/Ahe/7hkQ.c194NpE0XxE2', 'thiagopucinellisenac@gmail.com', 'Aluno', 'Cavg1222sTDSTeste'),
('ZD4WB0B4OPS', 'Thiago usuario', '$2y$10$3ZJWbwtYKIOlM5Q0noLrMu1Iv0gbo6/uy6dy8Yvn6rNV/.pw.ZVYO', 'thiagousuario@gmail.com', 'aluno', '20211VG.TDS_S0019');

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
('11111111111', 'Gerente'),
('22222222222', 'Administrador');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_historico_usuario`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_historico_usuario` (
`id_emprestimo` int(11)
,`data_reserva` date
,`data_inicio_reserva` datetime
,`data_fim_reserva` datetime
,`hora_data_retirada` datetime
,`hora_data_devolucao` datetime
,`nome_chave` varchar(100)
,`numero_identificacao` varchar(20)
,`descricao` mediumtext
,`categoria` varchar(50)
,`cpf_solicitante` char(11)
,`cpf_adm` varchar(11)
,`status` varchar(12)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_historico_usuario`
--
DROP TABLE IF EXISTS `vw_historico_usuario`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historico_usuario`  AS SELECT `e`.`id_emprestimo` AS `id_emprestimo`, `e`.`data_reserva` AS `data_reserva`, `e`.`data_inicio_reserva` AS `data_inicio_reserva`, `e`.`data_fim_reserva` AS `data_fim_reserva`, `e`.`hora_data_retirada` AS `hora_data_retirada`, `e`.`hora_data_devolucao` AS `hora_data_devolucao`, `c`.`nome` AS `nome_chave`, `c`.`numero_identificacao` AS `numero_identificacao`, `c`.`descricao` AS `descricao`, `e`.`categoria` AS `categoria`, `e`.`cpf_solicitante` AS `cpf_solicitante`, `e`.`cpf_adm` AS `cpf_adm`, CASE WHEN `e`.`categoria` = 'Cancelado' THEN 'Cancelado' WHEN `e`.`hora_data_retirada` is null THEN 'Reservado' WHEN `e`.`hora_data_retirada` is not null AND `e`.`hora_data_devolucao` is null THEN 'Em uso' WHEN `e`.`hora_data_devolucao` is not null THEN 'Devolvido' ELSE 'Desconhecido' END AS `status` FROM (`emprestimo` `e` join `chave` `c` on(`e`.`id_chave` = `c`.`id_chave`))union all select `f`.`id_fixo` AS `id_emprestimo`,NULL AS `data_reserva`,NULL AS `data_inicio_reserva`,NULL AS `data_fim_reserva`,NULL AS `hora_data_retirada`,NULL AS `hora_data_devolucao`,`c`.`nome` AS `nome_chave`,`c`.`numero_identificacao` AS `numero_identificacao`,`c`.`descricao` AS `descricao`,'Fixo' AS `categoria`,`f`.`cpf_solicitante` AS `cpf_solicitante`,NULL AS `cpf_adm`,'Fixo' AS `status` from (`emprestimo_fixo` `f` join `chave` `c` on(`f`.`id_chave` = `c`.`id_chave`))  ;

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
-- Índices de tabela `emprestimo_fixo`
--
ALTER TABLE `emprestimo_fixo`
  ADD PRIMARY KEY (`id_fixo`),
  ADD KEY `id_chave` (`id_chave`),
  ADD KEY `cpf_solicitante` (`cpf_solicitante`);

--
-- Índices de tabela `notificacao_usuario`
--
ALTER TABLE `notificacao_usuario`
  ADD PRIMARY KEY (`id_notificacao`),
  ADD KEY `cpf_usuario` (`cpf_usuario`),
  ADD KEY `id_emprestimo` (`id_emprestimo`);

--
-- Índices de tabela `notificacoes_admin`
--
ALTER TABLE `notificacoes_admin`
  ADD PRIMARY KEY (`id_notificacao`);

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
  MODIFY `id_chave` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de tabela `emprestimo`
--
ALTER TABLE `emprestimo`
  MODIFY `id_emprestimo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de tabela `emprestimo_fixo`
--
ALTER TABLE `emprestimo_fixo`
  MODIFY `id_fixo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `notificacao_usuario`
--
ALTER TABLE `notificacao_usuario`
  MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `notificacoes_admin`
--
ALTER TABLE `notificacoes_admin`
  MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

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
-- Restrições para tabelas `emprestimo_fixo`
--
ALTER TABLE `emprestimo_fixo`
  ADD CONSTRAINT `emprestimo_fixo_ibfk_1` FOREIGN KEY (`id_chave`) REFERENCES `chave` (`id_chave`),
  ADD CONSTRAINT `emprestimo_fixo_ibfk_2` FOREIGN KEY (`cpf_solicitante`) REFERENCES `solicitante` (`cpf`);

--
-- Restrições para tabelas `notificacao_usuario`
--
ALTER TABLE `notificacao_usuario`
  ADD CONSTRAINT `notificacao_usuario_ibfk_1` FOREIGN KEY (`cpf_usuario`) REFERENCES `usuario` (`cpf`) ON DELETE CASCADE,
  ADD CONSTRAINT `notificacao_usuario_ibfk_2` FOREIGN KEY (`id_emprestimo`) REFERENCES `emprestimo` (`id_emprestimo`) ON DELETE CASCADE;

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
