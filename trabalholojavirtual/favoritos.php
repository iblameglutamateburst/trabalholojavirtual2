<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require "conexao.php";

$uid = (int)$_SESSION["id"];
$resultado = $conexao->query("SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, a.nome_autor
                              FROM favoritos f
                              JOIN livros l ON l.isbn = f.isbn
                              LEFT JOIN autores a ON a.autoria = l.isbn
                              WHERE f.usuario_id = $uid
                              ORDER BY f.data_favorito DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Meus favoritos</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <h2>Meus favoritos</h2>

        <?php if ($resultado->num_rows == 0): ?>
            <div class="nenhum">
                <p>Você ainda não salvou nenhum livro.</p>
                <p style="margin-top:12px;"><a href="catalogo.php" class="botao-editar">Explorar catálogo</a></p>
            </div>
        <?php else: ?>
            <div class="livros">
                <?php while ($livro = $resultado->fetch_assoc()): ?>
                    <div class="livro">
                        <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
                            <img src="<?= $livro["foto"] ?>" class="livro-foto">
                        <?php else: ?>
                            <div class="livro-imagem">📕</div>
                        <?php endif; ?>
                        <?php if (!empty($livro["categoria"])): ?>
                            <span class="chip-categoria"><?= htmlspecialchars($livro["categoria"]) ?></span>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($livro["titulo"]) ?></h3>
                        <p class="detalhe"><?= htmlspecialchars($livro["nome_autor"] ?? "") ?></p>
                        <p class="preco">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                        <a href="livro.php?isbn=<?= $livro["isbn"] ?>"><button type="button">Ver livro</button></a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>