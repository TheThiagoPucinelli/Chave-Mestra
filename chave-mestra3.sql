-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20/10/2025 às 21:01
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

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
(48, '2025-10-15', '2025-10-15 16:36:00', '2025-10-16 16:36:00', '2025-10-20 14:02:17', '2025-10-20 14:02:21', 58, '11111111111', '11111111111', 'funcionario'),
(49, '2025-10-20', '2025-10-20 15:50:00', '2025-10-20 18:50:00', '2025-10-20 15:54:19', '2025-10-20 15:59:27', 59, '11111111111', '11111111111', NULL),
(50, '2025-10-20', '2025-10-20 15:59:00', '2025-10-21 20:59:00', NULL, NULL, 59, '11111111111', NULL, NULL);

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
('22222222222');

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
('22222222222', 'Thiago Teste', '$2y$10$dCufVzSbrKBYUF7FAxpjOOM8uAZntUh/Ahe/7hkQ.c194NpE0XxE2', 'thiagopucinellisenac@gmail.com', 'Aluno', 'Cavg1222sTDSTeste');

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
('11111111111', 'Administrador');

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
,`descricao` text
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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_historico_usuario`  AS SELECT `e`.`id_emprestimo` AS `id_emprestimo`, `e`.`data_reserva` AS `data_reserva`, `e`.`data_inicio_reserva` AS `data_inicio_reserva`, `e`.`data_fim_reserva` AS `data_fim_reserva`, `e`.`hora_data_retirada` AS `hora_data_retirada`, `e`.`hora_data_devolucao` AS `hora_data_devolucao`, `c`.`nome` AS `nome_chave`, `c`.`numero_identificacao` AS `numero_identificacao`, `c`.`descricao` AS `descricao`, `e`.`categoria` AS `categoria`, `e`.`cpf_solicitante` AS `cpf_solicitante`, `e`.`cpf_adm` AS `cpf_adm`, CASE WHEN `e`.`hora_data_retirada` is null THEN 'Reservado' WHEN `e`.`hora_data_retirada` is not null AND `e`.`hora_data_devolucao` is null THEN 'Em uso' WHEN `e`.`hora_data_devolucao` is not null THEN 'Devolvido' ELSE 'Desconhecido' END AS `status` FROM (`emprestimo` `e` join `chave` `c` on(`e`.`id_chave` = `c`.`id_chave`)) ;

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
  MODIFY `id_emprestimo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `emprestimo_fixo`
--
ALTER TABLE `emprestimo_fixo`
  MODIFY `id_fixo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
