<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$mensagem = "";
$tipo = "";

$isbn = intval($_GET["isbn"] ?? $_POST["isbn_original"] ?? 0);

$stmt = $conexao->prepare("SELECT * FROM livros WHERE isbn = ?");
$stmt->bind_param("i", $isbn);
$stmt->execute();
$resultado = $stmt->get_result();
$livro = $resultado->fetch_assoc();
$stmt->close();

if (!$livro) {
    header("Location: listar.php");
    exit;
}

$exemplares = $conexao->query("SELECT codigo_ex FROM exemplares ORDER BY codigo_ex");
$categorias = $conexao->query("SELECT nome FROM categorias ORDER BY nome");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $edicao = intval($_POST["edicao"]);
    $custo = str_replace(",", ".", trim($_POST["custo"]));
    $titulo = trim($_POST["titulo"]);
    $acervo = intval($_POST["acervo"]);
    $categoria = trim($_POST["categoria"]);
    $descricao = trim($_POST["descricao"]);

    if ($edicao == 0 || $custo == "" || $titulo == "" || $acervo == 0 || $categoria == "") {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipo = "erro";
    } else {
        $foto_nome = $livro["foto"];

        if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == 0) {
            $extensao = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));

            if (!in_array($extensao, ["jpg", "jpeg", "png", "webp"])) {
                $mensagem = "Formato de foto inválido. Use JPG, PNG ou WEBP.";
                $tipo = "erro";
            } else {
                if (!is_dir("fotos")) {
                    mkdir("fotos");
                }
                $nova_foto = "fotos/" . $isbn . "_" . time() . "." . $extensao;

                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $nova_foto)) {
                    if (!empty($foto_nome) && file_exists($foto_nome)) {
                        unlink($foto_nome);
                    }
                    $foto_nome = $nova_foto;
                } else {
                    $mensagem = "Erro ao salvar a foto.";
                    $tipo = "erro";
                }
            }
        }

        if ($tipo != "erro") {
            $sql = "UPDATE livros SET edição = ?, custo = ?, titulo = ?, acervo = ?, foto = ?, categoria = ?, descricao = ? WHERE isbn = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("idsisssi", $edicao, $custo, $titulo, $acervo, $foto_nome, $categoria, $descricao, $isbn);

            if ($stmt->execute()) {
                $mensagem = "Livro atualizado com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao atualizar livro.";
                $tipo = "erro";
            }
            $stmt->close();

            $stmt = $conexao->prepare("SELECT * FROM livros WHERE isbn = ?");
            $stmt->bind_param("i", $isbn);
            $stmt->execute();
            $livro = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Editar Livro</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="formulario">
            <h1>Editar Livro</h1>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="isbn_original" value="<?= $livro["isbn"] ?>">

                <label>ISBN</label>
                <div class="campo">
                    <span class="icone">📖</span>
                    <input type="number" value="<?= $livro["isbn"] ?>" disabled>
                </div>

                <label>Edição</label>
                <div class="campo">
                    <span class="icone">🔢</span>
                    <input type="number" name="edicao" value="<?= $livro["edição"] ?>" required>
                </div>

                <label>Custo (R$)</label>
                <div class="campo">
                    <span class="icone">💰</span>
                    <input type="text" name="custo" value="<?= $livro["custo"] ?>" required>
                </div>

                <label>Título</label>
                <div class="campo">
                    <span class="icone">📚</span>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($livro["titulo"]) ?>" required>
                </div>

                <label>Categoria</label>
                <select name="categoria" required>
                    <option value="">Selecione...</option>
                    <?php while ($cat = $categorias->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($cat["nome"]) ?>" <?= $cat["nome"] == $livro["categoria"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($cat["nome"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Exemplar (acervo)</label>
                <select name="acervo" required>
                    <option value="">Selecione...</option>
                    <?php while ($exemplar = $exemplares->fetch_assoc()): ?>
                        <option value="<?= $exemplar["codigo_ex"] ?>" <?= $exemplar["codigo_ex"] == $livro["acervo"] ? "selected" : "" ?>>
                            Exemplar <?= $exemplar["codigo_ex"] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Descrição</label>
                <textarea name="descricao" placeholder="Sinopse do livro..."><?= htmlspecialchars($livro["descricao"] ?? "") ?></textarea>

                <label>Foto atual</label>
                <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
                    <img src="<?= $livro["foto"] ?>" class="foto-atual">
                <?php else: ?>
                    <p class="detalhe">Sem foto cadastrada</p>
                <?php endif; ?>

                <label>Nova foto (opcional)</label>
                <input type="file" name="foto" accept="image/*">

                <button type="submit">
                    Salvar alterações
                </button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>