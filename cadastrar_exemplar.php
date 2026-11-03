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
    $codigo_ex = intval($_POST["codigo_ex"]);
    $dataAquisicao = $_POST["dataAquisicao"];

    if ($codigo_ex == 0 || $dataAquisicao == "") {
        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";
    } else {
        $sql = "SELECT codigo_ex FROM exemplares WHERE codigo_ex = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $codigo_ex);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este código de exemplar já existe.";
            $tipo = "erro";
        } else {
            $sql = "INSERT INTO exemplares (codigo_ex, dataAquisicao)
                    VALUES (?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("is", $codigo_ex, $dataAquisicao);

            if ($stmt->execute()) {
                $mensagem = "Exemplar cadastrado com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar exemplar.";
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
    <title>Dreams Books - Cadastrar Exemplar</title>
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
            <h1>Cadastrar Exemplar</h1>
            <form method="POST">
                <label>Código do exemplar</label>
                <div class="campo">
                    <span class="icone">📦</span>
                    <input type="number" name="codigo_ex" required>
                </div>

                <label>Data de aquisição</label>
                <input type="date" name="dataAquisicao" required>

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