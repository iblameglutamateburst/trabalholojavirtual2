<?php
session_start();

require_once "conexao.php";

$mensagem = "";
$tipo = "";
$livro = null;

if (isset($_GET["isbn"])) {
    $isbn = intval($_GET["isbn"]);
    $sql = "SELECT isbn, titulo, edição, custo, acervo
            FROM livros
            WHERE isbn = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $isbn);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $livro = $resultado->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $livro != null) {
    $nome_cliente = trim($_POST["nome_cliente"]);
    $email_cliente = trim($_POST["email_cliente"]);
    $rg = intval($_POST["rg"]);
    
    if ($nome_cliente == "" || $email_cliente == "" || $rg == 0) {
        $mensagem = "Preencha todos os dados da compra.";
        $tipo = "erro";
    } else if (!filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "E-mail inválido.";
        $tipo = "erro";
    } else {
        $sql = "SELECT MAX(codigo_cliente) + 1 AS novo_codigo FROM cliente";

        $resultado = $conexao->query($sql);
        $linha = $resultado->fetch_assoc();

        $novo_codigo = $linha["novo_codigo"] != null ? $linha["novo_codigo"] : 1;

        $sql = "INSERT INTO cliente (codigo_cliente, nome_cliente, email_cliente, rg, Compra, data_compra)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexao->prepare($sql);

        $hoje = date("Y-m-d");
        $exemplar = $livro["acervo"];
        $stmt->bind_param("issiis", $novo_codigo, $nome_cliente, $email_cliente, $rg, $exemplar, $hoje);
        if ($stmt->execute()) {
            $mensagem = "Compra realizada com sucesso!";
            $tipo = "ok";
        } else {
            $mensagem = "Erro ao realizar a compra.";
            $tipo = "erro";
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
    <title>Dreams Books - Comprar</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>Dreams Books</h1>
        <nav>
            <a href="catalogo.php">Catálogo</a>
        </nav>
    </header>

    <main>
        <?php if ($livro == null): ?>
            <div class="nenhum">
                <p>Livro não encontrado.</p>
            </div>
        <?php else: ?>

            <div class="formulario">
                <h1>Finalizar compra</h1>
                <p><strong>Livro:</strong> <?= htmlspecialchars($livro["titulo"]) ?></p>
                <p><strong>Edição:</strong> <?= htmlspecialchars($livro["edição"]) ?></p>
                <p class="preco">
                    R$ <?= number_format($livro["custo"], 2, ",", ".") ?>
                </p>

                <form method="POST">
                    <label>Nome completo</label>
                    <input type="text" name="nome_cliente" required>

                    <label>E-mail</label>
                    <input type="email" name="email_cliente" required>

                    <label>RG</label>
                    <input type="number" name="rg" required>

                    <button type="submit">
                        Confirmar compra
                    </button>
                </form>

                <?php if ($mensagem != ""): ?>
                    <div class="mensagem <?= $tipo ?>">
                        <?= $mensagem ?>
                    </div>
                <?php endif; ?>

                <div class="rodape">
                    <a href="catalogo.php">Voltar para o catálogo</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>