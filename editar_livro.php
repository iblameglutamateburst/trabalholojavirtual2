<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$mensagem = "";
$tipo = "";
$isbn = (int)($_GET["isbn"] ?? $_POST["isbn"] ?? 0);

$stmt = $conexao->prepare("SELECT * FROM livros WHERE isbn = ?");
$stmt->bind_param("i", $isbn);
$stmt->execute();
$livro = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$livro) {
    header("Location: listar_livros.php");
    exit;
}

$autor_atual = $conexao->query("SELECT nome_autor FROM autores WHERE autoria = $isbn ORDER BY codigo_autor DESC LIMIT 1")->fetch_assoc()["nome_autor"] ?? "";
$editora_atual = $conexao->query("SELECT nome FROM editora WHERE publicacao = $isbn ORDER BY codigo_editora DESC LIMIT 1")->fetch_assoc()["nome"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $custo = (float)str_replace(",", ".", preg_replace('/[^0-9,.]/', '', $_POST["custo"]));
    $titulo = trim($_POST["titulo"]);
    $categoria = trim($_POST["categoria"]);
    $estoque = max(0, (int)$_POST["estoque"]);
    $descricao = trim($_POST["descricao"]);
    $nome_autor = trim($_POST["nome_autor"]);
    $nome_editora = trim($_POST["nome_editora"]);

    if ($titulo == "" || $custo <= 0) {
        $mensagem = "Preencha o título e um preço válido.";
        $tipo = "erro";
    } else {
        if (!empty($_FILES["foto"]["name"])) {
            $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
            if (in_array($ext, ["jpg", "jpeg", "png", "webp", "gif"])) {
                $pasta = "fotos/";
                if (!is_dir($pasta)) {
                    mkdir($pasta, 0777, true);
                }
                $foto = $pasta . $isbn . "." . $ext;
                move_uploaded_file($_FILES["foto"]["tmp_name"], $foto);
                $stmt = $conexao->prepare("UPDATE livros SET foto = ? WHERE isbn = ?");
                $stmt->bind_param("si", $foto, $isbn);
                $stmt->execute();
                $stmt->close();
            }
        }

        $stmt = $conexao->prepare("UPDATE livros SET titulo = ?, custo = ?, categoria = ?, descricao = ?, estoque = ? WHERE isbn = ?");
        $stmt->bind_param("sdssii", $titulo, $custo, $categoria, $descricao, $estoque, $isbn);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            if ($nome_autor != "") {
                $stmt2 = $conexao->prepare("SELECT codigo_autor FROM autores WHERE autoria = ? LIMIT 1");
                $stmt2->bind_param("i", $isbn);
                $stmt2->execute();
                $linha = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();

                if ($linha) {
                    $stmt2 = $conexao->prepare("UPDATE autores SET nome_autor = ? WHERE codigo_autor = ?");
                    $stmt2->bind_param("si", $nome_autor, $linha["codigo_autor"]);
                } else {
                    $prox = (int)$conexao->query("SELECT COALESCE(MAX(codigo_autor), 0) + 1 AS prox FROM autores")->fetch_assoc()["prox"];
                    $stmt2 = $conexao->prepare("INSERT INTO autores (codigo_autor, autoria, nome_autor) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iis", $prox, $isbn, $nome_autor);
                }
                $stmt2->execute();
                $stmt2->close();
            }

            if ($nome_editora != "") {
                $stmt2 = $conexao->prepare("SELECT codigo_editora FROM editora WHERE publicacao = ? LIMIT 1");
                $stmt2->bind_param("i", $isbn);
                $stmt2->execute();
                $linha = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();

                if ($linha) {
                    $stmt2 = $conexao->prepare("UPDATE editora SET nome = ? WHERE codigo_editora = ?");
                    $stmt2->bind_param("si", $nome_editora, $linha["codigo_editora"]);
                } else {
                    $prox = (int)$conexao->query("SELECT COALESCE(MAX(codigo_editora), 0) + 1 AS prox FROM editora")->fetch_assoc()["prox"];
                    $stmt2 = $conexao->prepare("INSERT INTO editora (codigo_editora, nome, publicacao) VALUES (?, ?, ?)");
                    $stmt2->bind_param("isi", $prox, $nome_editora, $isbn);
                }
                $stmt2->execute();
                $stmt2->close();
            }

            $mensagem = "Livro atualizado com sucesso!";
            $tipo = "ok";
            $livro["titulo"] = $titulo;
            $livro["custo"] = $custo;
            $livro["categoria"] = $categoria;
            $livro["descricao"] = $descricao;
            $livro["estoque"] = $estoque;
            $autor_atual = $nome_autor;
            $editora_atual = $nome_editora;
        } else {
            $mensagem = "Erro ao atualizar o livro: " . $conexao->error;
            $tipo = "erro";
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
            <h1>Editar livro</h1>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="isbn" value="<?= $livro["isbn"] ?>">

                <label>ISBN (não pode mudar)</label>
                <div class="campo">
                    <span class="icone">🔢</span>
                    <input type="number" value="<?= $livro["isbn"] ?>" disabled>
                </div>

                <label>Título</label>
                <div class="campo">
                    <span class="icone">📖</span>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($livro["titulo"]) ?>" required>
                </div>

                <label>Autor</label>
                <div class="campo">
                    <span class="icone">✍️</span>
                    <input type="text" name="nome_autor" value="<?= htmlspecialchars($autor_atual) ?>">
                </div>

                <label>Editora</label>
                <div class="campo">
                    <span class="icone">🏢</span>
                    <input type="text" name="nome_editora" value="<?= htmlspecialchars($editora_atual) ?>">
                </div>

                <label>Categoria</label>
                <div class="campo">
                    <span class="icone">🏷️</span>
                    <input type="text" name="categoria" value="<?= htmlspecialchars($livro["categoria"] ?? "") ?>" list="categorias">
                    <datalist id="categorias">
                        <option value="Fantasia">
                        <option value="Romance">
                        <option value="Terror">
                    </datalist>
                </div>

                <label>Preço (R$)</label>
                <div class="campo">
                    <span class="icone">💰</span>
                    <input type="text" name="custo" value="<?= $livro["custo"] ?>" required>
                </div>

                <label>Estoque</label>
                <div class="campo">
                    <span class="icone">📦</span>
                    <input type="number" name="estoque" value="<?= $livro["estoque"] ?>" min="0" required>
                </div>

                <label>Trocar capa (opcional)</label>
                <div class="campo">
                    <span class="icone">🖼️</span>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>

                <label>Sinopse</label>
                <textarea name="descricao" rows="5"><?= htmlspecialchars($livro["descricao"] ?? "") ?></textarea>

                <button type="submit">Salvar alterações</button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>"><?= $mensagem ?></div>
            <?php endif; ?>

            <div class="rodape">
                <a href="listar_livros.php">← Voltar para livros</a>
            </div>
        </div>
    </main>
</body>
</html>
