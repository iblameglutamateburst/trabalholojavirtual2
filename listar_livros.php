<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$busca = isset($_GET["busca"]) ? mysqli_real_escape_string($conexao, $_GET["busca"]) : "";

if (isset($_GET["excluir"])) {
    $isbn = (int)$_GET["excluir"];
    $conexao->query("DELETE FROM autores WHERE autoria = $isbn");
    $conexao->query("DELETE FROM editora WHERE publicacao = $isbn");
    $conexao->query("DELETE FROM favoritos WHERE isbn = $isbn");
    $conexao->query("DELETE FROM avaliacoes WHERE isbn = $isbn");
    $conexao->query("DELETE FROM livros WHERE isbn = $isbn");
}

$sql = "SELECT l.*, a.nome_autor FROM livros l LEFT JOIN autores a ON a.autoria = l.isbn";
if ($busca != "") {
    $sql .= " WHERE l.titulo LIKE '%$busca%' OR l.categoria LIKE '%$busca%' OR l.isbn = " . (int)$busca;
}
$sql .= " ORDER BY l.titulo";
$resultado = $conexao->query($sql);

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Livros</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <h2>Livros cadastrados</h2>

        <div class="acoes" style="margin-bottom:20px;">
            <a href="cadastrar_livro.php" class="botao-editar">+ Cadastrar novo livro</a>
        </div>

        <form method="get" class="pesquisa">
            <input type="text" name="busca" placeholder="Pesquisar por título, categoria ou ISBN..." value="<?= htmlspecialchars($busca) ?>">
            <button type="submit">Pesquisar</button>
        </form>

        <?php if ($resultado->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Capa</th>
                    <th>Título</th>
                    <th>ISBN</th>
                    <th>Categoria</th>
                    <th>Autor</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
                <?php while ($livro = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
                                <img src="<?= $livro["foto"] ?>" style="width:45px;border-radius:6px;">
                            <?php else: ?>
                                📕
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($livro["titulo"]) ?></td>
                        <td><?= $livro["isbn"] ?></td>
                        <td><?= htmlspecialchars($livro["categoria"] ?? "—") ?></td>
                        <td><?= htmlspecialchars($livro["nome_autor"] ?? "—") ?></td>
                        <td>R$ <?= number_format($livro["custo"], 2, ",", ".") ?></td>
                        <td>
                            <?php if ((int)$livro["estoque"] <= 2): ?>
                                <span class="estoque-alerta">⚠️ <?= $livro["estoque"] ?></span>
                            <?php else: ?>
                                <?= $livro["estoque"] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editar_livro.php?isbn=<?= $livro["isbn"] ?>" class="botao-editar">Editar</a>
                            <a href="listar_livros.php?excluir=<?= $livro["isbn"] ?>&busca=<?= urlencode($busca) ?>" class="botao-excluir" onclick="return confirm('Excluir este livro e todos os seus vínculos?')">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="nenhum">Nenhum livro encontrado.</p>
        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>
