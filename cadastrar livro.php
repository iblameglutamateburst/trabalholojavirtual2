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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isbn = intval($_POST["isbn"]);
    $edicao = intval($_POST["edicao"]);
    $custo = $_POST["custo"];
    $titulo = trim($_POST["titulo"]);
    $acervo = intval($_POST["acervo"]);

    if ($isbn == 0 || $edicao == 0 || $custo == "" || $titulo == "" || $acervo == 0) {
        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";
    } else {
        $sql = "SELECT isbn FROM livros WHERE isbn = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $isbn);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este ISBN já está cadastrado.";
            $tipo = "erro";
        } else {
            $sql = "INSERT INTO livros (isbn, edição, custo, titulo, acervo)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("iidsi", $isbn, $edicao, $custo, $titulo, $acervo);

            if ($stmt->execute()) {
                $mensagem = "Livro cadastrado com sucesso!";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar livro: " . $stmt->error;
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
    <title>Dreams Books - Cadastrar Livro</title>
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
            <h1>Cadastrar Livro</h1>
            <form method="POST">
                <label>ISBN</label>
                <input type="number" name="isbn" required>

                <label>Edição</label>
                <input type="number" name="edicao" required>

                <label>Custo (R$)</label>
                <input type="text" name="custo" placeholder="Ex: 59.90" required>

                <label>Título</label>
                <input type="text" name="titulo" required>

                <label>Exemplar (acervo)</label>
                <select name="acervo" required>
                    <option value="">Selecione...</option>
                    <?php while ($exemplar = $exemplares->fetch_assoc()): ?>
                        <option value="<?= $exemplar["codigo_ex"] ?>">
                            Exemplar <?= $exemplar["codigo_ex"] ?>
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

            <div class="rodape">
                Precisa de um exemplar novo? <a href="cadastrar_exemplar.php">Cadastre aqui</a>
            </div>
        </div>
    </main>
</body>
</html>