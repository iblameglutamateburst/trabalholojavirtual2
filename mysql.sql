-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 28/09/2026 às 17:41
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
-- Banco de dados: `biblioteca`
--
CREATE DATABASE IF NOT EXISTS `biblioteca` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `biblioteca`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `autores`
--

CREATE TABLE `autores` (
  `codigo_autor` int(11) NOT NULL,
  `autoria` int(11) DEFAULT NULL,
  `email_autor` varchar(100) DEFAULT NULL,
  `nome_autor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `autores`
--

INSERT INTO `autores` (`codigo_autor`, `autoria`, `email_autor`, `nome_autor`) VALUES
(1, 2147483647, NULL, 'Antoine de Saint-Exupéry');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `isbn` int(11) NOT NULL,
  `nota` tinyint(4) NOT NULL,
  `comentario` text DEFAULT NULL,
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Romance'),
(2, 'Terror'),
(3, 'Fantasia');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `codigo_cliente` int(11) NOT NULL,
  `nome_cliente` varchar(100) DEFAULT NULL,
  `email_cliente` varchar(100) DEFAULT NULL,
  `rg` int(11) DEFAULT NULL,
  `Compra` int(11) DEFAULT NULL,
  `data_compra` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `editora`
--

CREATE TABLE `editora` (
  `codigo_editora` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `publicacao` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `editora`
--

INSERT INTO `editora` (`codigo_editora`, `nome`, `publicacao`) VALUES
(2147483647, 'HarperCollins Brasil', 2147483647);

-- --------------------------------------------------------

--
-- Estrutura para tabela `exemplares`
--

CREATE TABLE `exemplares` (
  `codigo_ex` int(11) NOT NULL,
  `dataAquisicao` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `exemplares`
--

INSERT INTO `exemplares` (`codigo_ex`, `dataAquisicao`) VALUES
(2, '1998-08-12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `usuario_id` int(11) NOT NULL,
  `isbn` int(11) NOT NULL,
  `data_favorito` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `favoritos`
--

INSERT INTO `favoritos` (`usuario_id`, `isbn`, `data_favorito`) VALUES
(1, 2147483647, '2026-09-28 15:38:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `livros`
--

CREATE TABLE `livros` (
  `isbn` int(11) NOT NULL,
  `edição` int(11) DEFAULT NULL,
  `custo` decimal(10,2) DEFAULT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `acervo` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `livros`
--

INSERT INTO `livros` (`isbn`, `edição`, `custo`, `titulo`, `acervo`, `foto`, `categoria`, `descricao`) VALUES
(2147483647, 2147483647, 95.99, 'O pequeno príncipe', 2, 'fotos/9788595081512.webp', 'Fantasia', 'O pequeno príncipe conta a história de um menino vindo de um pequeno asteroide que encontra um piloto de avião perdido no deserto do Saara. O príncipe narra suas aventuras em diferentes planetas em busca de um carneiro para manter o seu asteroide livre da infestação de baobás e, assim, proteger a rosa que cultiva. Mas, a cada novo planeta visitado, conhece personagens como o rei, o geógrafo e o acendedor de lampiões, que fazem com que reveja seus propósitos e sua opinião sobre os adultos. Até encontrar a raposa, com quem aprende o significado da palavra “cativar”. Desde a sua primeira publicação, em 1943, O pequeno príncipe faz parte da formação de uma legião de leitores em todo o mundo. Assim como o aviador, que poderia ser o próprio piloto Antoine de Saint-Exupéry, os adultos não escapam às singelas e tocantes metáforas da criança;\r\nNão raro queremos ver no desenho a jiboia que engoliu o elefante em vez de um chapéu. A tradução de Cezar Tridapalli busca manter a elegância da linguagem original, mas sem parecer antiquada ou feita só para as pessoas grandes.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `admin` tinyint(4) DEFAULT 0,
  `verificado` tinyint(4) DEFAULT 0,
  `token` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id`, `nome`, `email`, `senha`, `admin`, `verificado`, `token`) VALUES
(1, 'Arthur Ritter', 'arthurritterbranden@gmail.com', '$2y$10$nHJFZh.KdVREkFkGxzW6juEPngAcIpzMk/Tc0QQB5tN7vOcHblAJe', 1, 1, ''),
(2, 'Bruna Ritter', 'bruna@gmial.com', '$2y$10$I528rKb0kI7twJyDUivuR.957oC6THzIPmPVNwTPWX37pUilkEely', 0, 1, '');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`codigo_autor`),
  ADD KEY `autoria` (`autoria`);

--
-- Índices de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unica_avaliacao` (`usuario_id`,`isbn`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`codigo_cliente`),
  ADD KEY `Compra` (`Compra`);

--
-- Índices de tabela `editora`
--
ALTER TABLE `editora`
  ADD PRIMARY KEY (`codigo_editora`),
  ADD KEY `publicacao` (`publicacao`);

--
-- Índices de tabela `exemplares`
--
ALTER TABLE `exemplares`
  ADD PRIMARY KEY (`codigo_ex`);

--
-- Índices de tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`usuario_id`,`isbn`);

--
-- Índices de tabela `livros`
--
ALTER TABLE `livros`
  ADD PRIMARY KEY (`isbn`),
  ADD KEY `acervo` (`acervo`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `autores`
--
ALTER TABLE `autores`
  ADD CONSTRAINT `autores_ibfk_1` FOREIGN KEY (`autoria`) REFERENCES `livros` (`isbn`);

--
-- Restrições para tabelas `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`Compra`) REFERENCES `exemplares` (`codigo_ex`);

--
-- Restrições para tabelas `editora`
--
ALTER TABLE `editora`
  ADD CONSTRAINT `editora_ibfk_1` FOREIGN KEY (`publicacao`) REFERENCES `livros` (`isbn`);

--
-- Restrições para tabelas `livros`
--
ALTER TABLE `livros`
  ADD CONSTRAINT `livros_ibfk_1` FOREIGN KEY (`acervo`) REFERENCES `exemplares` (`codigo_ex`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
