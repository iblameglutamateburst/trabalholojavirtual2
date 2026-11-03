<?php
session_start();
require "conexao.php";

$destaques = $conexao->query("SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, a.nome_autor
                              FROM livros l
                              LEFT JOIN autores a ON a.autoria = l.isbn
                              ORDER BY l.isbn DESC
                              LIMIT 4");

$categorias = $conexao->query("SELECT nome FROM categorias ORDER BY nome LIMIT 6");

$total_livros = $conexao->query("SELECT COUNT(*) AS total FROM livros")->fetch_assoc()["total"];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dreams Books - Sua livraria online</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>

<div class="hero">
    <div class="hero-texto">
        <span class="hero-selo">📚 Livraria online</span>
        <h1>Toda boa história começa aqui.</h1>
        <p>Descubra seu próximo livro favorito no catálogo da Dreams Books.</p>
        <div class="hero-botoes">
            <a href="catalogo.php" class="hero-botao">Explorar catálogo</a>
            <?php if (isset($_SESSION["id"])): ?>
                <a href="conta.php" class="hero-botao hero-botao-claro">Minha conta</a>
            <?php else: ?>
                <a href="cadastro.php" class="hero-botao hero-botao-claro">Criar conta grátis</a>
            <?php endif; ?>
        </div>
        <p class="hero-info"><?= $total_livros ?> livro(s) disponíveis no acervo</p>
    </div>
    <div class="hero-visual" aria-hidden="true">📖</div>
</div>

<?php if ($categorias->num_rows > 0): ?>
<div class="chips-inicio">
    <?php while ($cat = $categorias->fetch_assoc()): ?>
        <a href="catalogo.php?categoria=<?= urlencode($cat["nome"]) ?>" class="chip-categoria chip-grande">
            <?= htmlspecialchars($cat["nome"]) ?>
        </a>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php if ($destaques->num_rows > 0): ?>
<div class="secao-inicio">
    <div class="secao-topo">
        <h2>Novidades no acervo</h2>
        <a href="catalogo.php" class="link-mais">Ver todos →</a>
    </div>

    <div class="livros">
        <?php while ($livro = $destaques->fetch_assoc()): ?>
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
                <p class="detalhe"><?= htmlspecialchars($livro["nome_autor"] ?? "Autor desconhecido") ?></p>
                <p class="preco">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                <a href="livro.php?isbn=<?= $livro["isbn"] ?>"><button type="button">Ver livro</button></a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<div class="vantagens">
    <div class="vantagem">
        <span class="vantagem-icone">🚚</span>
        <h3>Entrega rápida</h3>
        <p>Seu pedido chega em dias, não em semanas.</p>
    </div>
    <div class="vantagem">
        <span class="vantagem-icone">🔒</span>
        <h3>Compra segura</h3>
        <p>Seus dados protegidos do início ao fim.</p>
    </div>
    <div class="vantagem">
        <span class="vantagem-icone">⭐</span>
        <h3>Acervo curado</h3>
        <p>Livros selecionados com cuidado pra você.</p>
    </div>
</div>

</main>

<footer class="rodape-site">
    <p>📚 Dreams Books — <?= date("Y") ?>. Feito para quem ama histórias.</p>
</footer>

</body>
</html>