-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/12/2025 às 14:38
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
-- Banco de dados: `bancodedados`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacao`
--

CREATE TABLE `avaliacao` (
  `Id` int(11) NOT NULL,
  `Nota` decimal(3,2) NOT NULL CHECK (`Nota` >= 0 and `Nota` <= 5),
  `Descricao` text DEFAULT NULL,
  `Avaliador` int(11) NOT NULL,
  `Tipo_Avaliado` char(20) DEFAULT NULL CHECK (`Tipo_Avaliado` in ('Ponto_Turistico','Roteiro')),
  `PontosTuristicos_id` int(11) DEFAULT NULL,
  `Roteiro_id` int(11) DEFAULT NULL,
  `DataAvaliacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fotos_local`
--

CREATE TABLE `fotos_local` (
  `Id` int(11) NOT NULL,
  `PontoTuristico_id` int(11) NOT NULL,
  `Caminho_Foto` varchar(255) NOT NULL,
  `DataUpload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontosturisticos`
--

CREATE TABLE `pontosturisticos` (
  `Id` int(11) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Nome` varchar(150) NOT NULL,
  `Foto_Perfil` varchar(255) DEFAULT NULL,
  `Foto_Capa` varchar(255) DEFAULT NULL,
  `Localidade` varchar(200) DEFAULT NULL,
  `Endereco` varchar(255) DEFAULT NULL,
  `Bio` text DEFAULT NULL,
  `Avaliacao` decimal(3,2) DEFAULT NULL CHECK (`Avaliacao` >= 0 and `Avaliacao` <= 5),
  `Fornecedor` int(11) DEFAULT NULL,
  `DataCadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `roteiro`
--

CREATE TABLE `roteiro` (
  `Id` int(11) NOT NULL,
  `Nome` varchar(150) NOT NULL,
  `Bio` text DEFAULT NULL,
  `Autor` int(11) NOT NULL,
  `DataCriacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `roteiro_pontos`
--

CREATE TABLE `roteiro_pontos` (
  `Id_Roteiro` int(11) NOT NULL,
  `Id_PontosTuristicos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `ID` int(11) NOT NULL,
  `Nome` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Senha` varchar(255) NOT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Data_Nascimento` date DEFAULT NULL,
  `CPF` varchar(14) DEFAULT NULL,
  `CNPJ` varchar(18) DEFAULT NULL,
  `Tipo` varchar(15) NOT NULL CHECK (`Tipo` in ('Turista','Fornecedor','Administrador')),
  `Bio` text DEFAULT NULL,
  `Foto_Perfil` varchar(255) DEFAULT NULL,
  `Foto_Capa` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`ID`, `Nome`, `Email`, `Senha`, `Telefone`, `Data_Nascimento`, `CPF`, `CNPJ`, `Tipo`, `Bio`, `Foto_Perfil`, `Foto_Capa`) VALUES
(7, 'TaNoMapa', 'tanomapa@gmail.com', '$2y$10$O4AAiu5Kqur/EjeezDmZR.QIWm5XCN5aAKfxLdHou3o/2XRmTZf1C', '', NULL, NULL, NULL, 'Fornecedor', 'Encontre as melhores opções de roteiros e destinos incríveis', 'uploads/perfil/69357f6383bfa.jpeg', 'uploads/capa/69357f6384bd7.jpeg');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Avaliador` (`Avaliador`),
  ADD KEY `PontosTuristicos_id` (`PontosTuristicos_id`),
  ADD KEY `Roteiro_id` (`Roteiro_id`);

--
-- Índices de tabela `fotos_local`
--
ALTER TABLE `fotos_local`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `PontoTuristico_id` (`PontoTuristico_id`);

--
-- Índices de tabela `pontosturisticos`
--
ALTER TABLE `pontosturisticos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Fornecedor` (`Fornecedor`);

--
-- Índices de tabela `roteiro`
--
ALTER TABLE `roteiro`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `idx_autor` (`Autor`);

--
-- Índices de tabela `roteiro_pontos`
--
ALTER TABLE `roteiro_pontos`
  ADD PRIMARY KEY (`Id_Roteiro`,`Id_PontosTuristicos`),
  ADD KEY `Id_PontosTuristicos` (`Id_PontosTuristicos`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `CPF` (`CPF`),
  ADD UNIQUE KEY `CNPJ` (`CNPJ`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacao`
--
ALTER TABLE `avaliacao`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `fotos_local`
--
ALTER TABLE `fotos_local`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `pontosturisticos`
--
ALTER TABLE `pontosturisticos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `roteiro`
--
ALTER TABLE `roteiro`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `avaliacao`
--
ALTER TABLE `avaliacao`
  ADD CONSTRAINT `avaliacao_ibfk_1` FOREIGN KEY (`Avaliador`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacao_ibfk_2` FOREIGN KEY (`PontosTuristicos_id`) REFERENCES `pontosturisticos` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacao_ibfk_3` FOREIGN KEY (`Roteiro_id`) REFERENCES `roteiro` (`Id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fotos_local`
--
ALTER TABLE `fotos_local`
  ADD CONSTRAINT `fotos_local_ibfk_1` FOREIGN KEY (`PontoTuristico_id`) REFERENCES `pontosturisticos` (`Id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pontosturisticos`
--
ALTER TABLE `pontosturisticos`
  ADD CONSTRAINT `pontosturisticos_ibfk_1` FOREIGN KEY (`Fornecedor`) REFERENCES `usuarios` (`ID`) ON DELETE SET NULL;

--
-- Restrições para tabelas `roteiro`
--
ALTER TABLE `roteiro`
  ADD CONSTRAINT `roteiro_ibfk_1` FOREIGN KEY (`Autor`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE;

--
-- Restrições para tabelas `roteiro_pontos`
--
ALTER TABLE `roteiro_pontos`
  ADD CONSTRAINT `roteiro_pontos_ibfk_1` FOREIGN KEY (`Id_Roteiro`) REFERENCES `roteiro` (`Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roteiro_pontos_ibfk_2` FOREIGN KEY (`Id_PontosTuristicos`) REFERENCES `pontosturisticos` (`Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
