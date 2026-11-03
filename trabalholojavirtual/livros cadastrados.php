<?php

session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";
$sql = "SELECT l.isbn, l.titulo, l.edição, l.custo, l.acervo,
               e.nome AS editora,
               a.nome_autor
        FROM livros l
        LEFT JOIN editora e ON e.publicacao = l.isbn
        LEFT JOIN autores a ON a.autoria = l.isbn
        ORDER BY l.titulo";
$resultado = $conexao->query($sql);

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dreams Books - Livros Cadastrados</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>Dreams Books</h1>
        <nav>
            <a href="admin.php">Voltar para Admin</a>
        </nav>
    </header>

    <main>
        <h2>Livros Cadastrados</h2>
        <?php if ($resultado->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ISBN</th>
                    <th>Título</th>
                    <th>Edição</th>
                    <th>Custo</th>
                    <th>Editora</th>
                    <th>Autor</th>
                    <th>Exemplar</th>
                </tr>

                <?php while ($livro = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $livro["isbn"] ?></td>
                        <td><?= htmlspecialchars($livro["titulo"]) ?></td>
                        <td><?= $livro["edição"] ?></td>
                        <td>R$ <?= number_format($livro["custo"], 2, ",", ".") ?></td>
                        <td><?= $livro["editora"] != null ? htmlspecialchars($livro["editora"]) : "-" ?></td>
                        <td><?= $livro["nome_autor"] != null ? htmlspecialchars($livro["nome_autor"]) : "-" ?></td>
                        <td><?= $livro["acervo"] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php else: ?>
            <div class="nenhum">
                <p>Nenhum livro cadastrado ainda.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>