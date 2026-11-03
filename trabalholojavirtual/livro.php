<?php
session_start();
require "conexao.php";

$isbn = (int)($_GET["isbn"] ?? 0);

$sql = "SELECT l.*, a.nome_autor, e.nome AS editora
        FROM livros l
        LEFT JOIN autores a ON a.autoria = l.isbn
        LEFT JOIN editora e ON e.publicacao = l.isbn
        WHERE l.isbn = $isbn";
$resultado = mysqli_query($conexao, $sql);
$livro = mysqli_fetch_assoc($resultado);

if (!$livro) {
    header("Location: catalogo.php");
    exit;
}

$media = $conexao->query("SELECT AVG(nota) AS media, COUNT(*) AS total FROM avaliacoes WHERE isbn = $isbn")->fetch_assoc();
$opinioes = $conexao->query("SELECT a.nota, a.comentario, u.nome FROM avaliacoes a LEFT JOIN usuario u ON u.id = a.usuario_id WHERE a.isbn = $isbn ORDER BY a.data_avaliacao DESC LIMIT 10");

$favoritado = false;
$minha_avaliacao = null;

if (isset($_SESSION["id"])) {
    $uid = (int)$_SESSION["id"];
    $favoritado = $conexao->query("SELECT 1 FROM favoritos WHERE usuario_id = $uid AND isbn = $isbn")->num_rows > 0;
    $minha_avaliacao = $conexao->query("SELECT nota, comentario FROM avaliacoes WHERE usuario_id = $uid AND isbn = $isbn")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($livro["titulo"]) ?> - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
<div class="livro-detalhe">

    <div class="capa">
        <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
            <img src="<?= $livro["foto"] ?>" class="livro-foto">
        <?php else: ?>
            <div class="livro-imagem">📕</div>
        <?php endif; ?>
        <?php if (!empty($livro["categoria"])): ?>
            <span class="chip-categoria"><?= htmlspecialchars($livro["categoria"]) ?></span>
        <?php endif; ?>
        <p class="detalhe">Autor: <?= htmlspecialchars($livro["nome_autor"] ?? "—") ?></p>
        <p class="detalhe">Editora: <?= htmlspecialchars($livro["editora"] ?? "—") ?></p>
    </div>

    <div class="info">
        <h1><?= htmlspecialchars($livro["titulo"]) ?></h1>
        <p class="autor"><?= htmlspecialchars($livro["nome_autor"] ?? "") ?></p>

        <?php if ((int)$media["total"] > 0): ?>
            <div class="estrelas">
                <?php for ($i = 1; $i <= 5; $i++) echo $i <= round($media["media"]) ? "★" : "☆"; ?>
                <?= number_format($media["media"], 1, ",", ".") ?> (<?= (int)$media["total"] ?> avaliações)
            </div>
        <?php else: ?>
            <div class="estrelas">Ainda sem avaliações</div>
        <?php endif; ?>

        <p class="preco">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>

        <form method="post" action="carrinho.php">
            <input type="hidden" name="isbn" value="<?= $livro["isbn"] ?>">
            <label>Quantidade</label>
            <input type="number" name="quantidade" class="quantidade" value="1" min="1">
            <div class="botoes">
                <button type="submit">Adicionar ao carrinho</button>
                <?php if (isset($_SESSION["id"])): ?>
                    <button type="submit" formaction="acao_livro.php" name="acao" value="<?= $favoritado ? "remover" : "salvar" ?>" class="botao-favorito">
                        <?= $favoritado ? "❤️ Nos favoritos" : "🤍 Salvar nos favoritos" ?>
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div>

<div class="painel sinopse-painel">
    <h3>Sinopse</h3>
    <p class="sinopse-texto"><?= nl2br(htmlspecialchars($livro["descricao"] ?: "Sinopse ainda não cadastrada para este livro.")) ?></p>
</div>

<div class="painel avaliacoes-painel">
    <h3>Avaliações</h3>

    <?php if (isset($_SESSION["id"])): ?>
        <form method="post" action="acao_livro.php" class="avaliacao-form">
            <input type="hidden" name="isbn" value="<?= $livro["isbn"] ?>">
            <input type="hidden" name="acao" value="avaliar">
            <label>Sua nota</label>
            <select name="nota" required>
                <option value="">Escolha de 1 a 5...</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($minha_avaliacao["nota"] ?? 0) == $i ? "selected" : "" ?>>
                        <?= str_repeat("★", $i) ?> <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
            <label>Comentário (opcional)</label>
            <textarea name="comentario" rows="3" maxlength="1000" placeholder="O que você achou do livro?"><?= htmlspecialchars($minha_avaliacao["comentario"] ?? "") ?></textarea>
            <button type="submit"><?= $minha_avaliacao ? "Atualizar minha avaliação" : "Enviar avaliação" ?></button>
        </form>
    <?php else: ?>
        <p class="detalhe"><a href="login.php">Entre na sua conta</a> para avaliar este livro.</p>
    <?php endif; ?>

    <div class="opinioes">
        <?php while ($op = $opinioes->fetch_assoc()): ?>
            <div class="opiniao-card">
                <div class="opiniao-topo">
                    <strong><?= htmlspecialchars($op["nome"] ?? "Leitor") ?></strong>
                    <span class="estrelas"><?php for ($i = 1; $i <= 5; $i++) echo $i <= $op["nota"] ? "★" : "☆"; ?></span>
                </div>
                <?php if (!empty($op["comentario"])): ?>
                    <p><?= nl2br(htmlspecialchars($op["comentario"])) ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <?php if ((int)$media["total"] == 0): ?>
            <p class="detalhe">Seja o primeiro a avaliar este livro!</p>
        <?php endif; ?>
    </div>
</div>

</main>

<?php include "footer.php"; ?>
</body>
</html>