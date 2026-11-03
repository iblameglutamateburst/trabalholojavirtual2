<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require "conexao.php";

$uid = (int)$_SESSION["id"];

$stmt = $conexao->prepare("SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, l.estoque, a.nome_autor
                           FROM favoritos f
                           JOIN livros l ON l.isbn = f.isbn
                           LEFT JOIN autores a ON a.autoria = l.isbn
                           WHERE f.usuario_id = ?
                           ORDER BY f.data_favorito DESC");
$stmt->bind_param("i", $uid);
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
<title>Meus Favoritos - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <h2 class="secao-titulo">Meus favoritos</h2>
    <p class="detalhe" style="margin-bottom:24px;">Os livros que você amou, guardadinhos aqui 💛</p>

    <?php if ($resultado->num_rows == 0): ?>
        <p class="nenhum">Você ainda não favoritou nenhum livro. <a href="catalogo.php">Explorar o catálogo</a></p>
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
                    <?php if (!empty($livro["nome_autor"])): ?>
                        <p class="autor-livro"><a class="autor-link" href="autor.php?nome=<?= urlencode($livro["nome_autor"]) ?>"><?= htmlspecialchars($livro["nome_autor"]) ?></a></p>
                    <?php endif; ?>
                    <p class="preco-card">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                    <a href="livro.php?isbn=<?= $livro["isbn"] ?>" class="botao-ver">Ver livro</a>
                    <a href="favoritar.php?isbn=<?= $livro["isbn"] ?>&origem=favoritos.php" class="botao-remover-favorito">Remover dos favoritos</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>

<?php include "footer.php"; ?>
</body>
</html>