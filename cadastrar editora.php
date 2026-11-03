<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}
require_once "conexao.php";

$mensagem = "";
$tipo = "";

$livros = $conexao->query("SELECT isbn, titulo FROM livros ORDER BY titulo");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo_editora = intval($_POST["codigo_editora"]);
    $nome = trim($_POST["nome"]);
    $publicacao = intval($_POST["publicacao"]);

    if ($codigo_editora == 0 || $nome == "" || $publicacao == 0) {
        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";
    } else {
        $sql = "SELECT codigo_editora FROM editora WHERE codigo_editora = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $codigo_editora);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este código de editora já existe.";
            $tipo = "erro";

        } else {
            $sql = "INSERT INTO editora (codigo_editora, nome, publicacao)
                    VALUES (?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("isi", $codigo_editora, $nome, $publicacao);

            if ($stmt->execute()) {
                $mensagem = "Editora cadastrada com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar editora.";
                $tipo = "erro";
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
    <title>Dreams Books - Cadastrar Editora</title>
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
        <div class="formulario">
            <h1>Cadastrar Editora</h1>
            <form method="POST">
                <label>Código da editora</label>
                <input type="number" name="codigo_editora" required>

                <label>Nome da editora</label>
                <input type="text" name="nome" required>

                <label>Livro publicado</label>
                <select name="publicacao" required>

                    <option value="">Selecione...</option>

                    <?php while ($livro = $livros->fetch_assoc()): ?>
                        <option value="<?= $livro["isbn"] ?>">
                            <?= htmlspecialchars($livro["titulo"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">
                    Cadastrar
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