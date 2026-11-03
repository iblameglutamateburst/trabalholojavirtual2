<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>Dreams Books</h1>
        <nav>
            <a href="catalogo.php">Catálogo</a>

            <?php if (isset($_SESSION["id"])): ?>
                Olá, <?= htmlspecialchars($_SESSION["nome"]) ?>
                <a href="conta.php">Minha conta</a>

                <?php if ($_SESSION["admin"] == 1): ?>
                    <a href="admin.php">Área admin</a>
                <?php endif; ?>
                <a href="logout.php">Sair</a>

            <?php else: ?>
                <a href="login.php">Entrar</a>
                <a href="cadastro.php">Cadastrar</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Bem-vindo à Dreams Books</h2>
        <p>
            Sua livraria online. Confira nosso <a href="catalogo.php">catálogo de livros</a>.
        </p>
    </main>
</body>
</html>