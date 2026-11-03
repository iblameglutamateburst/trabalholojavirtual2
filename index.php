<?php
session_start();
require "conexao.php";

$destaques = $conexao->query("SELECT l.isbn, l.titulo, l.custo, l.foto, a.nome_autor,
                              COALESCE((SELECT AVG(nota) FROM avaliacoes av WHERE av.isbn = l.isbn), 0) AS media
                              FROM livros l
                              LEFT JOIN autores a ON a.autoria = l.isbn
                              ORDER BY media DESC, l.titulo
                              LIMIT 4");

$categorias = $conexao->query("SELECT DISTINCT categoria FROM livros WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");

$autores = $conexao->query("SELECT DISTINCT nome_autor FROM autores WHERE nome_autor IS NOT NULL ORDER BY RAND() LIMIT 7");

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dreams Books - Entre páginas, novos sonhos</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>

    <div class="hero">
        <span class="estrela-deco" style="top:20px;left:40px;">★</span>
        <span class="estrela-deco" style="top:60px;right:80px;">★</span>
        <span class="estrela-deco" style="bottom:40px;left:120px;">✦</span>
        <span class="estrela-deco" style="bottom:70px;right:40px;">★</span>
        <span class="hero-icone">📖✨</span>
        <h1>Dreams Books</h1>
        <p>Onde cada livro vira um sonho</p>
        <a href="catalogo.php" class="botao-hero">ver livros →</a>
    </div>

    <div class="secao-topo">
        <h2 class="secao-titulo">Destaques da semana</h2>
        <a href="catalogo.php" class="botao-ver-todos">ver todos →</a>
    </div>

    <div class="destaques">
        <?php while ($livro = $destaques->fetch_assoc()): ?>
            <div class="card-destaque">
                <div class="mini">
                    <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
                        <img src="<?= $livro["foto"] ?>">
                    <?php else: ?>
                        📕
                    <?php endif; ?>
                </div>
                <div>
                    <h3><?= htmlspecialchars($livro["titulo"]) ?></h3>
                    <?php if (!empty($livro["nome_autor"])): ?>
                        <p class="autor-livro"><a class="autor-link" href="autor.php?nome=<?= urlencode($livro["nome_autor"]) ?>"><?= htmlspecialchars($livro["nome_autor"]) ?></a></p>
                    <?php endif; ?>                    
                    <div class="estrelas"><?php for ($i = 1; $i <= 5; $i++) echo $i <= round($livro["media"]) ? "★" : "☆"; ?></div>
                    <p class="preco-card">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                    <a href="livro.php?isbn=<?= $livro["isbn"] ?>" class="botao-carrinho-mini">Ver livro</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h2 class="secao-titulo">Categorias</h2>
    <div class="categorias-grid">
        <?php
        $icones = ["Romance" => "💖", "Fantasia" => "🐉", "Terror" => "👻", "Suspense" => "🕵️", "Ficção" => "🚀", "Infantil" => "🧸", "Autoajuda" => "🌱"];
        $cores = ["#fcf1a7", "#b8dcfb", "#f9c6b0", "#f3b4eb", "#c4e5c8", "#d2d0f5", "#fce38a"];
        $i = 0;
        while ($cat = $categorias->fetch_assoc()):
            $nome = $cat["categoria"];
            $icone = $icones[$nome] ?? "📚";
            $cor = $cores[$i % count($cores)];
            $i++;
        ?>
            <a href="catalogo.php?categoria=<?= urlencode($nome) ?>" class="card-categoria" style="background: <?= $cor ?>;">
                <span class="cat-icone"><?= $icone ?></span>
                <?= htmlspecialchars($nome) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <h2 class="secao-titulo">Autores</h2>
<div class="autores-linha">
    <?php while ($autor = $autores->fetch_assoc()):
        $iniciais = "";
        foreach (explode(" ", $autor["nome_autor"]) as $parte) {
            if ($parte != "") $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
        }
    ?>
        <a href="autor.php?nome=<?= urlencode($autor["nome_autor"]) ?>" class="autor-circulo">
            <div class="foto-autor"><?= mb_substr($iniciais, 0, 2) ?></div>
            <span><?= htmlspecialchars($autor["nome_autor"]) ?></span>
        </a>
    <?php endwhile; ?>
</div>

</main>

<?php include "footer.php"; ?>
</body>
</html>