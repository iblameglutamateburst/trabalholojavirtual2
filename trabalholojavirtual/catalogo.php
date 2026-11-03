<?php
session_start();
require "conexao.php";

$busca = isset($_GET["busca"]) ? mysqli_real_escape_string($conexao, $_GET["busca"]) : "";
$categoria = isset($_GET["categoria"]) ? mysqli_real_escape_string($conexao, $_GET["categoria"]) : "";
$preco_max = isset($_GET["preco_max"]) ? (int)$_GET["preco_max"] : 0;
$ordenar = $_GET["ordenar"] ?? "titulo";

function link_filtro($busca, $categoria, $preco_max, $ordenar) {
    $p = [];
    if ($busca != "") $p["busca"] = $busca;
    if ($categoria != "") $p["categoria"] = $categoria;
    if ($preco_max > 0) $p["preco_max"] = $preco_max;
    if ($ordenar != "titulo") $p["ordenar"] = $ordenar;
    return "catalogo.php" . (count($p) ? "?" . http_build_query($p) : "");
}

$sql = "SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, a.nome_autor, e.nome AS editora
        FROM livros l
        LEFT JOIN autores a ON a.autoria = l.isbn
        LEFT JOIN editora e ON e.publicacao = l.isbn
        WHERE 1=1";

if ($busca !== "") $sql .= " AND l.titulo LIKE '%$busca%'";
if ($categoria !== "") $sql .= " AND l.categoria = '$categoria'";
if ($preco_max > 0) $sql .= " AND l.custo <= $preco_max";

if ($ordenar == "menor_preco") $sql .= " ORDER BY l.custo ASC";
elseif ($ordenar == "maior_preco") $sql .= " ORDER BY l.custo DESC";
else $sql .= " ORDER BY l.titulo ASC";

$resultado = mysqli_query($conexao, $sql);
$categorias = mysqli_query($conexao, "SELECT nome FROM categorias ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
<form method="get" class="pesquisa">
    <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria) ?>">
    <input type="text" name="busca" placeholder="Pesquisar livros..." value="<?= htmlspecialchars($busca) ?>">
    <button type="submit">Pesquisar</button>
</form>

<div class="catalogo">

    <aside class="filtros">
        <span class="filtro-titulo">Categorias</span>
        <a href="<?= link_filtro($busca, "", $preco_max, $ordenar) ?>" class="filtro-link <?= $categoria == "" ? "ativo" : "" ?>">Todas</a>
        <?php while ($cat = mysqli_fetch_assoc($categorias)): ?>
            <a href="<?= link_filtro($busca, $cat["nome"], $preco_max, $ordenar) ?>" class="filtro-link <?= $categoria == $cat["nome"] ? "ativo" : "" ?>">
                <?= htmlspecialchars($cat["nome"]) ?>
            </a>
        <?php endwhile; ?>

        <h3>Preço</h3>
        <a href="<?= link_filtro($busca, $categoria, 0, $ordenar) ?>" class="filtro-link <?= $preco_max == 0 ? "ativo" : "" ?>">Qualquer preço</a>
        <a href="<?= link_filtro($busca, $categoria, 50, $ordenar) ?>" class="filtro-link <?= $preco_max == 50 ? "ativo" : "" ?>">Até R$ 50</a>
        <a href="<?= link_filtro($busca, $categoria, 100, $ordenar) ?>" class="filtro-link <?= $preco_max == 100 ? "ativo" : "" ?>">Até R$ 100</a>
    </aside>

    <div style="flex:1;">
        <div class="topo">
            <h2>Todos os livros</h2>
            <form method="get">
                <input type="hidden" name="busca" value="<?= htmlspecialchars($busca) ?>">
                <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria) ?>">
                <input type="hidden" name="preco_max" value="<?= $preco_max ?>">
                <select name="ordenar" onchange="this.form.submit()">
                    <option value="titulo" <?= $ordenar == "titulo" ? "selected" : "" ?>>Ordem alfabética</option>
                    <option value="menor_preco" <?= $ordenar == "menor_preco" ? "selected" : "" ?>>Menor preço</option>
                    <option value="maior_preco" <?= $ordenar == "maior_preco" ? "selected" : "" ?>>Maior preço</option>
                </select>
            </form>
        </div>

        <div class="livros">
        <?php while ($livro = mysqli_fetch_assoc($resultado)) { ?>
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
                <div class="estrelas">★★★★★</div>
                <p class="preco">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                <a href="livro.php?isbn=<?= $livro["isbn"] ?>"><button type="button">Ver livro</button></a>
            </div>
        <?php } ?>

        <?php if (mysqli_num_rows($resultado) == 0) { ?>
            <p class="nenhum">Nenhum livro encontrado.</p>
        <?php } ?>
        </div>
    </div>

</div>
</main>

</body>
</html>