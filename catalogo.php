<?php
session_start();
require "conexao.php";

$busca = trim($_GET["busca"] ?? "");
$categoria = trim($_GET["categoria"] ?? "");
$preco = trim($_GET["preco"] ?? "");
$ordem = trim($_GET["ordem"] ?? "");

$condicoes = [];

if ($busca != "") {
    $b = mysqli_real_escape_string($conexao, $busca);
    $condicoes[] = "(l.titulo LIKE '%$b%' OR l.categoria LIKE '%$b%' OR a.nome_autor LIKE '%$b%')";
}

if ($categoria != "") {
    $c = mysqli_real_escape_string($conexao, $categoria);
    $condicoes[] = "l.categoria = '$c'";
}

if ($preco == "50") {
    $condicoes[] = "l.custo <= 50";
} else if ($preco == "100") {
    $condicoes[] = "l.custo <= 100";
}

$sql = "SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, l.estoque, a.nome_autor
        FROM livros l
        LEFT JOIN autores a ON a.autoria = l.isbn";

if (!empty($condicoes)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= $ordem == "preco" ? " ORDER BY l.custo ASC" : " ORDER BY l.titulo";

$resultado = $conexao->query($sql);
$categorias = $conexao->query("SELECT DISTINCT categoria FROM livros WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");

mysqli_close($conexao);

$titulo_pagina = "Todos os livros";
if ($categoria != "") $titulo_pagina = "Categoria: " . $categoria;
if ($busca != "") $titulo_pagina = "Resultados para \"" . $busca . "\"";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titulo_pagina ?> - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <h2 class="secao-titulo"><?= htmlspecialchars($titulo_pagina) ?></h2>

    <form method="get" class="filtros">
        <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
        <div class="filtro-grupo">
            <label>Categoria</label>
            <select name="categoria" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($cat["categoria"]) ?>" <?= $categoria == $cat["categoria"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($cat["categoria"]) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filtro-grupo">
            <label>Preço</label>
            <select name="preco" onchange="this.form.submit()">
                <option value="">Qualquer preço</option>
                <option value="50" <?= $preco == "50" ? "selected" : "" ?>>Até R$ 50</option>
                <option value="100" <?= $preco == "100" ? "selected" : "" ?>>Até R$ 100</option>
            </select>
        </div>
        <div class="filtro-grupo">
            <label>Ordenar</label>
            <select name="ordem" onchange="this.form.submit()">
                <option value="">A-Z</option>
                <option value="preco" <?= $ordem == "preco" ? "selected" : "" ?>>Menor preço</option>
            </select>
        </div>
    </form>

    <?php if ($resultado->num_rows == 0): ?>
        <p class="nenhum">Nenhum livro encontrado. <a href="catalogo.php">Ver todos</a></p>
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
                    <h3><?= htmlspecialchars($livro["titulo"]) ?></h3>
                    <?php if (!empty($livro["nome_autor"])): ?>
                        <p class="autor-livro"><a class="autor-link" href="autor.php?nome=<?= urlencode($livro["nome_autor"]) ?>"><?= htmlspecialchars($livro["nome_autor"]) ?></a></p>
                    <?php endif; ?>                    
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