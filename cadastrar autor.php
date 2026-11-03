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
    $codigo_autor = intval($_POST["codigo_autor"]);
    $autoria = intval($_POST["autoria"]);
    $email_autor = trim($_POST["email_autor"]);
    $nome_autor = trim($_POST["nome_autor"]);

    if ($codigo_autor == 0 || $autoria == 0 || $email_autor == "" || $nome_autor == "") {
        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";

    } else if (!filter_var($email_autor, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "E-mail do autor inválido.";
        $tipo = "erro";
    } else {
        $sql = "SELECT codigo_autor FROM autores WHERE codigo_autor = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $codigo_autor);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este código de autor já existe.";
            $tipo = "erro";
        } else {
            $sql = "INSERT INTO autores (codigo_autor, autoria, email_autor, nome_autor)
                    VALUES (?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("iiss", $codigo_autor, $autoria, $email_autor, $nome_autor);

            if ($stmt->execute()) {
                $mensagem = "Autor cadastrado com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar autor.";
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
    <title>Dreams Books - Cadastrar Autor</title>
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
            <h1>Cadastrar Autor</h1>
            <form method="POST">
                <label>Código do autor</label>
                <input type="number" name="codigo_autor" required>

                <label>Livro de autoria</label>
                <select name="autoria" required>

                    <option value="">Selecione...</option>

                    <?php while ($livro = $livros->fetch_assoc()): ?>
                        <option value="<?= $livro["isbn"] ?>">
                            <?= htmlspecialchars($livro["titulo"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>E-mail do autor</label>
                <input type="email" name="email_autor" required>

                <label>Nome do autor</label>
                <input type="text" name="nome_autor" required>

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