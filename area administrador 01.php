<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dreams Books - Área Admin</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>Dreams Books</h1>

        <nav>
            <a href="index.php">Início</a>
            <a href="catalogo.php">Catálogo</a>
            <a href="logout.php">Sair</a>
        </nav>

    </header>

    <main>
        <h2>Área do Administrador</h2>
        <div class="menu-admin">
            <a href="cadastrar_exemplar.php">Cadastrar Exemplar</a>
            <a href="cadastrar_livro.php">Cadastrar Livro</a>
            <a href="cadastrar_editora.php">Cadastrar Editora</a>
            <a href="cadastrar_autor.php">Cadastrar Autor</a>
            <a href="listar.php">Ver Livros Cadastrados</a>
        </div>
    </main>
</body>
</html>