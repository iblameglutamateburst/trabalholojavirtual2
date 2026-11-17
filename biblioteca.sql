-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/11/2026 às 19:16
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
-- Banco de dados: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `autores`
--

CREATE TABLE `autores` (
  `codigo_autor` int(11) NOT NULL,
  `autoria` bigint(20) DEFAULT NULL,
  `email_autor` varchar(100) DEFAULT NULL,
  `nome_autor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `autores`
--

INSERT INTO `autores` (`codigo_autor`, `autoria`, `email_autor`, `nome_autor`) VALUES
(1, 2147483647, NULL, 'Antoine de Saint-Exupéry'),
(2, 9788598078304, NULL, 'Stephenie Meyer'),
(3, 547249640, NULL, 'George Orwell');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `isbn` bigint(20) NOT NULL,
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
  `publicacao` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `editora`
--

INSERT INTO `editora` (`codigo_editora`, `nome`, `publicacao`) VALUES
(1, 'HarperCollins Brasil', 2147483647),
(2, 'Intrínseca', 9788598078304),
(3, 'Houghton Mifflin Harcourt', 547249640);

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
(2, '1998-08-12'),
(126734, '0000-00-00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `usuario_id` int(11) NOT NULL,
  `isbn` bigint(20) NOT NULL,
  `data_favorito` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `isbn` bigint(20) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `itens_pedido`
--

INSERT INTO `itens_pedido` (`id`, `pedido_id`, `isbn`, `quantidade`, `preco_unitario`) VALUES
(1, 1, 9788598078304, 1, 10.99),
(2, 2, 9788598078304, 1, 10.99);

-- --------------------------------------------------------

--
-- Estrutura para tabela `livros`
--

CREATE TABLE `livros` (
  `isbn` bigint(20) NOT NULL,
  `edição` int(11) DEFAULT NULL,
  `custo` decimal(10,2) DEFAULT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `acervo` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `estoque` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `livros`
--

INSERT INTO `livros` (`isbn`, `edição`, `custo`, `titulo`, `acervo`, `foto`, `categoria`, `descricao`, `estoque`) VALUES
(547249640, 1, 85.99, '1984', NULL, '', 'Ficção Cientifica, Política, Distopia', 'A obra é uma crítica severa aos regimes totalitários (como o stalinismo e o nazismo) e retrata uma sociedade sob controle absoluto.', 10),
(2147483647, 1, 95.99, 'O pequeno príncipe', 2, 'fotos/9788595081512.webp', 'Fantasia', 'O pequeno príncipe conta a história de um menino vindo de um pequeno asteroide que encontra um piloto de avião perdido no deserto do Saara. O príncipe narra suas aventuras em diferentes planetas em busca de um carneiro para manter o seu asteroide livre da infestação de baobás e, assim, proteger a rosa que cultiva. Mas, a cada novo planeta visitado, conhece personagens como o rei, o geógrafo e o acendedor de lampiões, que fazem com que reveja seus propósitos e sua opinião sobre os adultos. Até encontrar a raposa, com quem aprende o significado da palavra \"cativar\". Desde a sua primeira publicação, em 1943, O pequeno príncipe faz parte da formação de uma legião de leitores em todo o mundo.', 10),
(9788598078304, 1, 10.99, 'Crepúsculo', NULL, '', 'Fantasia, Romance, Terror', 'Bella Swan, de 17 anos, muda-se de Phoenix para a chuvosa e nublada cidade de Forks, Washington, para morar com seu pai. Na nova escola, ela fica fascinada e intimidada por Edward Cullen, um rapaz misterioso e incrivelmente belo. Ela logo descobre que ele e sua família são vampiros \"vegetarianos\" (que se alimentam apenas de sangue animal). Os dois se apaixonam perdidamente, mas a vida de Bella entra em grave perigo quando um vampiro nômade chamado James a persegue para matá-la.', 8);

-- --------------------------------------------------------

--
-- Estrutura para tabela `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `forma_pagamento` varchar(50) NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'Processado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `total`, `forma_pagamento`, `data_pedido`, `status`) VALUES
(1, 1, 10.99, 'Pix', '2026-11-10 16:52:48', 'Processado'),
(2, 1, 10.99, 'Pix', '2026-11-10 16:56:35', 'Processado');

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
  ADD KEY `autores_ibfk_1` (`autoria`);

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
  ADD KEY `cliente_ibfk_1` (`Compra`);

--
-- Índices de tabela `editora`
--
ALTER TABLE `editora`
  ADD PRIMARY KEY (`codigo_editora`),
  ADD KEY `editora_ibfk_1` (`publicacao`);

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
-- Índices de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `itens_pedido_ibfk_1` (`pedido_id`);

--
-- Índices de tabela `livros`
--
ALTER TABLE `livros`
  ADD PRIMARY KEY (`isbn`),
  ADD KEY `livros_ibfk_1` (`acervo`);

--
-- Índices de tabela `newsletter`
--
ALTER TABLE `newsletter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedidos_ibfk_1` (`usuario_id`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `autores`
--
ALTER TABLE `autores`
  MODIFY `codigo_autor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `codigo_cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `editora`
--
ALTER TABLE `editora`
  MODIFY `codigo_editora` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `exemplares`
--
ALTER TABLE `exemplares`
  MODIFY `codigo_ex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126735;

--
-- AUTO_INCREMENT de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `newsletter`
--
ALTER TABLE `newsletter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Restrições para tabelas `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD CONSTRAINT `itens_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

--
-- Restrições para tabelas `livros`
--
ALTER TABLE `livros`
  ADD CONSTRAINT `livros_ibfk_1` FOREIGN KEY (`acervo`) REFERENCES `exemplares` (`codigo_ex`);

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
