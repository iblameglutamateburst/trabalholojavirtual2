<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";
$mensagem = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);

    if ($nome == "") {
        $mensagem = "Digite o nome da categoria.";
        $tipo = "erro";
    } else {
        $sql = "SELECT id FROM categorias WHERE nome = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $nome);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $mensagem = "Esta categoria já existe.";
            $tipo = "erro";
        } else {
            $sql = "INSERT INTO categorias (nome) VALUES (?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("s", $nome);

            if ($stmt->execute()) {
                $mensagem = "Categoria cadastrada com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar categoria.";
                $tipo = "erro";
            }
        }
        $stmt->close();
    }
}

if (isset($_GET["excluir"])) {
    $id = (int)$_GET["excluir"];
    $conexao->query("DELETE FROM categorias WHERE id = $id");
    $mensagem = "Categoria excluída.";
    $tipo = "ok";
}

$categorias = $conexao->query("SELECT id, nome FROM categorias ORDER BY nome");

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Categorias</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="formulario">
            <h1>Categorias</h1>
            <form method="POST">
                <label>Nome da categoria</label>
                <div class="campo">
                    <span class="icone">🏷️</span>
                    <input type="text" name="nome" placeholder="Ex: Ficção Científica" required>
                </div>

                <button type="submit">
                    Adicionar categoria
                </button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <div class="lista-simples">
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <div class="item-lista">
                        <span><?= htmlspecialchars($cat["nome"]) ?></span>
                        <a href="cadastrar_categoria.php?excluir=<?= $cat["id"] ?>" class="botao-excluir">Excluir</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>
</body>
</html>