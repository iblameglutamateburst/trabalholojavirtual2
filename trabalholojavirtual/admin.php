<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$total_livros = $conexao->query("SELECT COUNT(*) AS total FROM livros")->fetch_assoc()["total"];
$total_exemplares = $conexao->query("SELECT COUNT(*) AS total FROM exemplares")->fetch_assoc()["total"];
$total_usuarios = $conexao->query("SELECT COUNT(*) AS total FROM usuario")->fetch_assoc()["total"];
$total_categorias = $conexao->query("SELECT COUNT(*) AS total FROM categorias")->fetch_assoc()["total"];
$total_vendas = $conexao->query("SELECT COUNT(*) AS total FROM cliente WHERE Compra IS NOT NULL")->fetch_assoc()["total"];

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Área Admin</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="admin-cabecalho">
            <h2>Área do Administrador</h2>
            <p>Gerencie a livraria por aqui.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icone">📚</span>
                <strong><?= $total_livros ?></strong>
                <span>Livros</span>
            </div>
            <div class="stat-card">
                <span class="stat-icone">📦</span>
                <strong><?= $total_exemplares ?></strong>
                <span>Exemplares</span>
            </div>
            <div class="stat-card">
                <span class="stat-icone">👥</span>
                <strong><?= $total_usuarios ?></strong>
                <span>Usuários</span>
            </div>
            <div class="stat-card">
                <span class="stat-icone">🏷️</span>
                <strong><?= $total_categorias ?></strong>
                <span>Categorias</span>
            </div>
            <div class="stat-card">
                <span class="stat-icone">🛒</span>
                <strong><?= $total_vendas ?></strong>
                <span>Vendas</span>
            </div>
        </div>

        <h2 style="margin-top:40px;">Gerenciar</h2>

        <div class="admin-grid">
            <a href="cadastrar_livro.php" class="admin-card">
                <span class="admin-icone">📚</span>
                <strong>Cadastrar Livro</strong>
                <small>Adiciona um novo livro ao acervo</small>
            </a>

            <a href="cadastrar_exemplar.php" class="admin-card">
                <span class="admin-icone">📦</span>
                <strong>Cadastrar Exemplar</strong>
                <small>Registra uma cópia física</small>
            </a>

            <a href="cadastrar_editora.php" class="admin-card">
                <span class="admin-icone">🏢</span>
                <strong>Cadastrar Editora</strong>
                <small>Vincula editoras aos livros</small>
            </a>

            <a href="cadastrar_autor.php" class="admin-card">
                <span class="admin-icone">✍️</span>
                <strong>Cadastrar Autor</strong>
                <small>Vincula autores aos livros</small>
            </a>

            <a href="cadastrar_categoria.php" class="admin-card">
                <span class="admin-icone">🏷️</span>
                <strong>Categorias</strong>
                <small>Cria e remove categorias do filtro</small>
            </a>

            <a href="listar.php" class="admin-card">
                <span class="admin-icone">📋</span>
                <strong>Livros Cadastrados</strong>
                <small>Ver, editar e gerenciar o acervo</small>
            </a>
        </div>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>