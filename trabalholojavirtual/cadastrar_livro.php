<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";
$mensagem = "";
$tipo = "";

$exemplares = $conexao->query("SELECT codigo_ex FROM exemplares ORDER BY codigo_ex");
$categorias = $conexao->query("SELECT nome FROM categorias ORDER BY nome");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isbn = intval($_POST["isbn"]);
    $edicao = intval($_POST["edicao"]);
    $custo = str_replace(",", ".", trim($_POST["custo"]));
    $titulo = trim($_POST["titulo"]);
    $acervo = intval($_POST["acervo"]);
    $categoria = trim($_POST["categoria"]);
    $descricao = trim($_POST["descricao"]);

    if ($isbn == 0 || $edicao == 0 || $custo == "" || $titulo == "" || $acervo == 0 || $categoria == "") {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipo = "erro";
    } else if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] != 0) {
        $mensagem = "Selecione uma foto do livro.";
        $tipo = "erro";
    } else {
        $sql = "SELECT isbn FROM livros WHERE isbn = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $isbn);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este ISBN já existe.";
            $tipo = "erro";
        } else {
            $extensao = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));

            if (!in_array($extensao, ["jpg", "jpeg", "png", "webp"])) {
                $mensagem = "Formato de foto inválido. Use JPG, PNG ou WEBP.";
                $tipo = "erro";
            } else {
                if (!is_dir("fotos")) {
                    mkdir("fotos");
                }

                $foto_nome = "fotos/" . $isbn . "." . $extensao;

                if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $foto_nome)) {
                    $mensagem = "Erro ao salvar a foto.";
                    $tipo = "erro";
                } else {
                    $sql = "INSERT INTO livros (isbn, edição, custo, titulo, acervo, foto, categoria, descricao)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conexao->prepare($sql);
                    $stmt->bind_param("iidsisss", $isbn, $edicao, $custo, $titulo, $acervo, $foto_nome, $categoria, $descricao);

                    if ($stmt->execute()) {
                        $mensagem = "Livro cadastrado com sucesso!";
                        $tipo = "ok";
                    } else {
                        $mensagem = "Erro ao cadastrar livro.";
                        $tipo = "erro";
                    }
                }
            }
        }
        $stmt->close();
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Cadastrar Livro</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="formulario">
            <h1>Cadastrar Livro</h1>
            <form method="POST" enctype="multipart/form-data">
                <label>ISBN</label>
                <div class="campo">
                    <span class="icone">📖</span>
                    <input type="number" name="isbn" required>
                </div>

                <label>Edição</label>
                <div class="campo">
                    <span class="icone">🔢</span>
                    <input type="number" name="edicao" required>
                </div>

                <label>Custo (R$)</label>
                <div class="campo">
                    <span class="icone">💰</span>
                    <input type="text" name="custo" placeholder="94.90" required>
                </div>

                <label>Título</label>
                <div class="campo">
                    <span class="icone">📚</span>
                    <input type="text" name="titulo" required>
                </div>

                <label>Categoria</label>
                <select name="categoria" required>
                    <option value="">Selecione...</option>
                    <?php while ($cat = $categorias->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($cat["nome"]) ?>">
                            <?= htmlspecialchars($cat["nome"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Exemplar (acervo)</label>
                <select name="acervo" required>
                    <option value="">Selecione...</option>
                    <?php while ($exemplar = $exemplares->fetch_assoc()): ?>
                        <option value="<?= $exemplar["codigo_ex"] ?>">
                            Exemplar <?= $exemplar["codigo_ex"] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Descrição</label>
                <textarea name="descricao" placeholder="Sinopse do livro..."></textarea>

                <label>Foto do livro</label>
                <input type="file" name="foto" accept="image/*" required>

                <button type="submit">
                    Cadastrar
                </button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <div class="rodape">
                Precisa de um exemplar novo? <a href="cadastrar_exemplar.php">Cadastre aqui</a>
            </div>
        </div>
    </main>
</body>
</html>