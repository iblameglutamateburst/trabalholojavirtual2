<?php
session_start();
require_once "conexao.php";
require_once "_fotos.php";


if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

$isbn = preg_replace('/\D/', '', $_POST["isbn_original"] ?? $_GET["isbn"] ?? "");

if ($isbn === "") {
    header("Location: listar_livros.php");
    exit;
}

$stmt = $conexao->prepare("SELECT l.*,
    (SELECT GROUP_CONCAT(nome_autor SEPARATOR ', ') FROM autores WHERE autoria = l.isbn) AS nome_autor,
    (SELECT GROUP_CONCAT(nome_editora SEPARATOR ', ') FROM editora WHERE publicacao = l.isbn) AS nome_editora
    FROM livros l
    WHERE l.isbn = ?
    LIMIT 1");
$stmt->bind_param("s", $isbn);
$stmt->execute();
$resultado = $stmt->get_result();
$livro = $resultado->fetch_assoc();
$stmt->close();

if (!$livro) {
    header("Location: listar_livros.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo    = trim($_POST["titulo"] ?? "");
    $autor     = trim($_POST["autor"] ?? "");
    $editora   = trim($_POST["editora"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $custo     = (float)str_replace(",", ".", $_POST["custo"] ?? "0");
    $estoque   = (int)($_POST["estoque"] ?? 0);
    $remover   = isset($_POST["remover_foto"]);

    if ($titulo === "") {
        $erro = "Informe o título do livro.";
    } else if ($custo <= 0) {
        $erro = "Informe um preço válido.";
    }

    $foto = $livro["foto"];

    if ($erro === "") {
        $erroFoto = "";
        $enviada = salvar_foto($_FILES["foto"] ?? null, $isbn, $erroFoto);

        if ($erroFoto !== "") {
            $erro = $erroFoto;
        } else if ($enviada !== null) {
            apagar_foto($foto);
            $foto = $enviada;
        } else if ($remover) {
            apagar_foto($foto);
            $foto = "";
        }
    }

    if ($erro === "") {
        $stmt = $conexao->prepare("UPDATE livros SET titulo = ?, custo = ?, estoque = ?, foto = ?, categoria = ? WHERE isbn = ?");
        $stmt->bind_param("sdisss", $titulo, $custo, $estoque, $foto, $categoria, $isbn);
        $stmt->execute();
        $stmt->close();

        $conexao->query("DELETE FROM autores WHERE autoria = " . (int)$isbn);
        if ($autor !== "") {
            $stmt = $conexao->prepare("INSERT INTO autores (autoria, nome_autor) VALUES (?, ?)");
            $stmt->bind_param("ss", $isbn, $autor);
            $stmt->execute();
            $stmt->close();
        }

        $conexao->query("DELETE FROM editora WHERE publicacao = " . (int)$isbn);
        if ($editora !== "") {
            $stmt = $conexao->prepare("INSERT INTO editora (publicacao, nome_editora) VALUES (?, ?)");
            $stmt->bind_param("ss", $isbn, $editora);
            $stmt->execute();
            $stmt->close();
        }

        $conexao->close();
        header("Location: listar_livros.php?editado=1");
        exit;
    }
}

$conexao->close();
$img = foto_url($livro["foto"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Editar livro</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <h2>Editar livro</h2>

        <?php if ($erro !== ""): ?>
            <div class="mensagem erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="card card-form">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="isbn_original" value="<?= htmlspecialchars($livro["isbn"]) ?>">

                <label>ISBN</label>
                <div class="campo"><span class="icone">🔢</span><input type="text" value="<?= htmlspecialchars($livro["isbn"]) ?>" disabled></div>

                <label>Título</label>
                <div class="campo"><span class="icone">📖</span><input type="text" name="titulo" value="<?= htmlspecialchars($livro["titulo"]) ?>" required></div>

                <label>Autor</label>
                <div class="campo"><span class="icone">✍️</span><input type="text" name="autor" value="<?= htmlspecialchars($livro["nome_autor"] ?? "") ?>"></div>

                <label>Editora</label>
                <div class="campo"><span class="icone">🏢</span><input type="text" name="editora" value="<?= htmlspecialchars($livro["nome_editora"] ?? "") ?>"></div>

                <label>Categoria</label>
                <div class="campo"><span class="icone">🏷️</span><input type="text" name="categoria" value="<?= htmlspecialchars($livro["categoria"] ?? "") ?>"></div>

                <label>Preço</label>
                <div class="campo"><span class="icone">💰</span><input type="text" name="custo" value="<?= number_format($livro["custo"], 2, ",", "") ?>" required></div>

                <label>Estoque</label>
                <div class="campo"><span class="icone">📦</span><input type="number" name="estoque" min="0" value="<?= (int)$livro["estoque"] ?>"></div>

                <label>Capa atual</label>
                <div class="capa-atual">
                     
                    <?php if ($img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="capa-preview" alt="Capa atual">
                        <label class="opcao-remover"><input type="checkbox" name="remover_foto"> Remover esta capa</label>
                    <?php else: ?>
                        <span class="capa-vazia">📕</span>
                    <?php endif; ?>
                </div>

                <label>Trocar capa</label>
                <div class="campo"><span class="icone">🖼️</span><input type="file" name="foto" accept="image/*"></div>

                <button type="submit">Salvar alterações</button>
            </form>
        </div>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>