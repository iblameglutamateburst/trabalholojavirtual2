<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";
require_once "_fotos.php";

if (is_file(__DIR__ . "/_fotos.php")) {
    require_once "_fotos.php";
}

if (!function_exists("salvar_foto")) {
    function salvar_foto($arquivo, $isbn, &$erro) {
        if (!is_array($arquivo) || $arquivo["error"] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($arquivo["error"] !== UPLOAD_ERR_OK) {
            $erro = "Falha no envio da imagem.";
            return null;
        }

        $ext = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
        $permitidas = ["jpg", "jpeg", "png", "gif", "webp"];

        if (!in_array($ext, $permitidas, true)) {
            $erro = "Formato inválido. Use JPG, PNG, GIF ou WEBP.";
            return null;
        }

        $pasta = __DIR__ . "/fotos";
        if (!is_dir($pasta)) {
            @mkdir($pasta, 0777, true);
        }

        $nome = preg_replace('/\D/', '', (string)$isbn) . "_" . time() . "." . $ext;

        if (!move_uploaded_file($arquivo["tmp_name"], $pasta . "/" . $nome)) {
            $erro = "Não foi possível salvar a imagem.";
            return null;
        }

        return "fotos/" . $nome;
    }
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isbn      = preg_replace('/\D/', '', $_POST["isbn"] ?? "");
    $titulo    = trim($_POST["titulo"] ?? "");
    $autor     = trim($_POST["autor"] ?? "");
    $editora   = trim($_POST["editora"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $custo     = (float)str_replace(",", ".", $_POST["custo"] ?? "0");
    $estoque   = (int)($_POST["estoque"] ?? 0);

    if (strlen($isbn) !== 10 && strlen($isbn) !== 13) {
        $erro = "O ISBN deve ter 10 ou 13 números.";
    } else if ($titulo === "") {
        $erro = "Informe o título do livro.";
    } else {
        $stmt = $conexao->prepare("SELECT isbn FROM livros WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $erro = "Já existe um livro com esse ISBN.";
        }

        $stmt->close();
    }

    $foto = "";

    if ($erro === "") {
        $erroFoto = "";
        $enviada = salvar_foto($_FILES["foto"] ?? null, $isbn, $erroFoto);

        if ($erroFoto !== "") {
            $erro = $erroFoto;
        } else if ($enviada !== null) {
            $foto = $enviada;
        }
    }

    if ($erro === "") {
        $stmt = $conexao->prepare("INSERT INTO livros (isbn, titulo, custo, estoque, foto, categoria) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $isbn, $titulo, $custo, $estoque, $foto, $categoria);

        if (!$stmt->execute()) {
            $erro = "Erro ao cadastrar: " . $stmt->error;
        }

        $stmt->close();

        if ($erro === "") {
            if ($autor !== "") {
                $stmt = $conexao->prepare("INSERT INTO autores (autoria, nome_autor) VALUES (?, ?)");
                $stmt->bind_param("ss", $isbn, $autor);
                $stmt->execute();
                $stmt->close();
            }

            if ($editora !== "") {
                $stmt = $conexao->prepare("INSERT INTO editora (publicacao, nome_editora) VALUES (?, ?)");
                $stmt->bind_param("ss", $isbn, $editora);
                $stmt->execute();
                $stmt->close();
            }

            $conexao->close();
            header("Location: listar_livros.php?cadastrado=1");
            exit;
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
    <title>Dreams Books - Cadastrar livro</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <h2>Cadastrar livro</h2>

        <?php if ($erro !== ""): ?>
            <div class="mensagem erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="card card-form">
            <form method="POST" enctype="multipart/form-data">
                <label>ISBN</label>
                <div class="campo"><span class="icone">🔢</span><input type="text" name="isbn" required></div>

                <label>Título</label>
                <div class="campo"><span class="icone">📖</span><input type="text" name="titulo" required></div>

                <label>Autor</label>
                <div class="campo"><span class="icone">✍️</span><input type="text" name="autor"></div>

                <label>Editora</label>
                <div class="campo"><span class="icone">🏢</span><input type="text" name="editora"></div>

                <label>Categoria</label>
                <div class="campo"><span class="icone">🏷️</span><input type="text" name="categoria"></div>

                <label>Preço</label>
                <div class="campo"><span class="icone">💰</span><input type="text" name="custo" placeholder="0,00" required></div>

                <label>Estoque</label>
                <div class="campo"><span class="icone">📦</span><input type="number" name="estoque" min="0" value="0"></div>

                <label>Capa</label>
                <input type="file" name="foto" accept="image/*">

                <button type="submit">Cadastrar livro</button>
            </form>
        </div>
    </main>
    
    <?php include "footer.php"; ?>
</body>
</html>