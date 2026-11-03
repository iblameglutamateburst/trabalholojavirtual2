<?php
session_start();
require "conexao.php";

$nome = trim($_GET["nome"] ?? "");

$stmt = $conexao->prepare("SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, l.estoque, a.nome_autor
                           FROM livros l
                           LEFT JOIN autores a ON a.autoria = l.isbn
                           WHERE a.nome_autor = ?
                           ORDER BY l.titulo");
$stmt->bind_param("s", $nome);
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($nome ?: "Autor") ?> - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <h2 class="secao-titulo">Livros de <?= htmlspecialchars($nome ?: "Autor") ?></h2>
    <p class="detalhe" style="margin-bottom:24px;">Explore todas as obras deste autor 📚</p>

    <?php if ($resultado->num_rows == 0): ?>
        <p class="nenhum">Nenhum livro deste autor no catálogo ainda. <a href="catalogo.php">Ver todos os livros</a></p>
    <?php else: ?>
        <div class="grade-livros">
            <?php while ($livro = $resultado->fetch_assoc()): ?>
                <div class="card-livro">
                    <div class="capa-card">
                        <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
                            <img src="<?= $livro["foto"] ?>">
                        <?php else: ?>
                            <div class="capa-vazia"></div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($livro["categoria"])): ?>
                        <span class="chip-categoria"><?= htmlspecialchars($livro["categoria"]) ?></span>
                    <?php endif; ?>
                    <h3><a class="titulo-link" href="livro.php?isbn=<?= $livro["isbn"] ?>"><?= htmlspecialchars($livro["titulo"]) ?></a></h3>
                    <p class="preco-card">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                    <?php if ((int)$livro["estoque"] <= 0): ?>
                        <span class="esgotado" style="margin-top:auto;">Esgotado</span>
                    <?php else: ?>
                        <a href="livro.php?isbn=<?= $livro["isbn"] ?>" class="botao-ver">Ver livro</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>

<?php include "footer.php"; ?>
</body>
</html>