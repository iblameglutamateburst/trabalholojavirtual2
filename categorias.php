<?php
session_start();
require "conexao.php";

$categorias = $conexao->query("SELECT categoria, COUNT(*) AS total FROM livros
                               WHERE categoria IS NOT NULL AND categoria != ''
                               GROUP BY categoria ORDER BY categoria");

mysqli_close($conexao);

$icones = ["Romance" => "💖", "Fantasia" => "🐉", "Terror" => "👻", "Suspense" => "🕵️", "Ficção" => "🚀", "Infantil" => "🧸", "Autoajuda" => "🌱"];
$cores = ["#fcf1a7", "#b8dcfb", "#f9c6b0", "#f3b4eb", "#c4e5c8", "#d2d0f5", "#fce38a"];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categorias - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <h2 class="secao-titulo">Categorias</h2>
    <p class="detalhe" style="margin-bottom:24px;">Escolha um universo e comece a explorar ✨</p>

    <div class="categorias-grid">
        <?php $i = 0; while ($cat = $categorias->fetch_assoc()):
            $nome = $cat["categoria"];
            $icone = $icones[$nome] ?? "📚";
            $cor = $cores[$i % count($cores)];
            $i++;
        ?>
            <a href="catalogo.php?categoria=<?= urlencode($nome) ?>" class="card-categoria" style="background: <?= $cor ?>;">
                <span class="cat-icone"><?= $icone ?></span>
                <?= htmlspecialchars($nome) ?>
                <small style="display:block;font-weight:600;margin-top:4px;"><?= $cat["total"] ?> livro(s)</small>
            </a>
        <?php endwhile; ?>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>