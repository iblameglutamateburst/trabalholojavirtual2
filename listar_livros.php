<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

if (is_file(__DIR__ . "/_fotos.php")) {
    require_once "_fotos.php";
}

if (!function_exists("foto_url")) {
    function foto_url($foto) {
        if (empty($foto)) {
            return null;
        }

        $foto = str_replace("\\", "/", $foto);

        $caminho = __DIR__ . "/" . ltrim($foto, "/");
        if (is_file($caminho)) {
            return $foto;
        }

        $alt = "fotos/" . basename($foto);
        if (is_file(__DIR__ . "/" . $alt)) {
            return $alt;
        }

        return null;
    }
}

$busca = isset($_GET["busca"]) ? trim($_GET["busca"]) : "";

if (isset($_GET["excluir"])) {
    $isbn_del = preg_replace('/\D/', '', $_GET["excluir"]);

    if ($isbn_del !== "") {
        $isbn_del = mysqli_real_escape_string($conexao, $isbn_del);

        $conexao->query("DELETE FROM autores WHERE autoria = '$isbn_del'");
        $conexao->query("DELETE FROM editora WHERE publicacao = '$isbn_del'");
        $conexao->query("DELETE FROM favoritos WHERE isbn = '$isbn_del'");
        $conexao->query("DELETE FROM avaliacoes WHERE isbn = '$isbn_del'");
        $conexao->query("DELETE FROM livros WHERE isbn = '$isbn_del'");
    }

    header("Location: listar_livros.php?excluido=1");
    exit;
}

$sql = "SELECT l.*,
               (SELECT GROUP_CONCAT(nome_autor SEPARATOR ', ')
                FROM autores WHERE autoria = l.isbn) AS nome_autor
        FROM livros l";

if ($busca !== "") {
    $b = mysqli_real_escape_string($conexao, $busca);
    $sql .= " WHERE l.titulo LIKE '%$b%'
                 OR l.categoria LIKE '%$b%'
                 OR l.isbn LIKE '%$b%'";
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

        <?php if (isset($_GET["excluido"])): ?>
            <div class="mensagem ok">Livro excluído com sucesso.</div>
        <?php endif; ?>

        <?php if (isset($_GET["cadastrado"])): ?>
            <div class="mensagem ok">Livro cadastrado com sucesso.</div>
        <?php endif; ?>

        <?php if (isset($_GET["editado"])): ?>
            <div class="mensagem ok">Livro atualizado com sucesso.</div>
        <?php endif; ?>

        <div class="acoes" style="margin-bottom:20px;">
            <a href="cadastrar_livro.php" class="botao-editar">+ Cadastrar novo livro</a>
        </div>

        <form method="get" class="pesquisa">
            <input type="text" name="busca" placeholder="Pesquisar por título, categoria ou ISBN..." value="<?= htmlspecialchars($busca) ?>">
            <button type="submit">Pesquisar</button>
        </form>

        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <div class="tabela-wrapper">
                <table class="tabela-livros">
                    <thead>
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
                    </thead>
                    <tbody>
                        <?php while ($livro = $resultado->fetch_assoc()): ?>
                            <?php $img = foto_url($livro["foto"] ?? ""); ?>
                            <tr>
                                <td>
                                    <?php if ($img): ?>
                                        <img src="<?= htmlspecialchars($img) ?>"
                                             class="capa-mini"
                                             alt="Capa de <?= htmlspecialchars($livro["titulo"]) ?>">
                                    <?php else: ?>
                                        <span class="capa-vazia">📕</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($livro["titulo"]) ?></td>
                                <td><?= htmlspecialchars($livro["isbn"]) ?></td>
                                <td><?= htmlspecialchars($livro["categoria"] ?? "—") ?></td>
                                <td><?= htmlspecialchars($livro["nome_autor"] ?? "—") ?></td>
                                <td>R$ <?= number_format((float)$livro["custo"], 2, ",", ".") ?></td>
                                <td>
                                    <?php if ((int)$livro["estoque"] <= 2): ?>
                                        <span class="estoque-alerta">⚠️ <?= (int)$livro["estoque"] ?></span>
                                    <?php else: ?>
                                        <?= (int)$livro["estoque"] ?>
                                    <?php endif; ?>
                                </td>
                                <td class="acoes-linha">
                                    <a href="editar_livro.php?isbn=<?= urlencode($livro["isbn"]) ?>" class="botao-editar">Editar</a>
                                    <a href="listar_livros.php?excluir=<?= urlencode($livro["isbn"]) ?>"
                                       class="botao-excluir"
                                       onclick="return confirm('Excluir este livro e todos os seus vínculos?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="nenhum">Nenhum livro encontrado.</p>
        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>