<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";
require_once "_fotos.php";

$mensagem = "";
$tipo = "";

$exemplares = $conexao->query("SELECT codigo_ex FROM exemplares ORDER BY codigo_ex");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isbn = intval($_POST["isbn"]);
    $edicao = intval($_POST["edicao"]);
    $custo = trim($_POST["custo"]);
    $titulo = trim($_POST["titulo"]);
    $acervo = intval($_POST["acervo"]);

    $custo = str_replace(",", ".", $custo);

    if ($isbn == 0 || $edicao == 0 || $custo == "" || $titulo == "" || $acervo == 0) {
        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";
    } else {
        $sql = "SELECT isbn FROM livros WHERE isbn = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $isbn);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este ISBN já está cadastrado.";
            $tipo = "erro";
        } else {
            $foto = "";
            $erro_foto = "";
            $foto = salvar_foto($_FILES["foto"] ?? null, (string)$isbn, $erro_foto);

            if ($erro_foto !== "") {
                $mensagem = $erro_foto;
                $tipo = "erro";
            } else {
                $sql = "INSERT INTO livros (isbn, `edição`, custo, titulo, acervo, foto)
                        VALUES (?, ?, ?, ?, ?, ?)";

                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("iidsis", $isbn, $edicao, $custo, $titulo, $acervo, $foto);

                if ($stmt->execute()) {
                    $mensagem = "Livro cadastrado com sucesso!";
                    $tipo = "ok";
                } else {
                    $mensagem = "Erro ao cadastrar livro: " . $stmt->error;
                    $tipo = "erro";

                    if ($foto !== "") {
                        foto_remover($foto);
                    }
                }

                $stmt->close();
            }
        }
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dreams Books - Cadastrar Livro</title>
    <link rel="stylesheet" href="style.css">

    <style>
    .campo-foto {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .campo-foto input[type="file"] {
        padding: 10px 12px;
        border: 1px dashed #d9c6e3;
        border-radius: 8px;
        background: #faf6fc;
        font-size: 13px;
        cursor: pointer;
    }

    .campo-foto small {
        color: #8a7a90;
        font-size: 12px;
    }
    </style>
</head>

<body>
    <header>
        <h1>Dreams Books</h1>
        <nav>
            <a href="admin.php">Voltar para Admin</a>
        </nav>
    </header>

    <main>
        <div class="formulario">
            <h1>Cadastrar Livro</h1>

            <form method="POST" enctype="multipart/form-data">
                <label>ISBN</label>
                <input type="number" name="isbn" required>

                <label>Edição</label>
                <input type="number" name="edicao" required>

                <label>Custo (R$)</label>
                <input type="text" name="custo" placeholder="Ex: 59,90" required>

                <label>Título</label>
                <input type="text" name="titulo" required>

                <label>Exemplar (acervo)</label>
                <select name="acervo" required>
                    <option value="">Selecione...</option>
                    <?php while ($exemplar = $exemplares->fetch_assoc()): ?>
                        <option value="<?= (int)$exemplar["codigo_ex"] ?>">
                            Exemplar <?= (int)$exemplar["codigo_ex"] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div class="campo-foto">
                    <label>Capa do livro</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small>JPG, PNG, WEBP ou GIF. Máximo 5 MB.</small>
                </div>

                <button type="submit">
                    Cadastrar
                </button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>

            <div class="rodape">
                Precisa de um exemplar novo? <a href="cadastrar_exemplar.php">Cadastre aqui</a>
            </div>
        </div>
    </main>
</body>
</html>