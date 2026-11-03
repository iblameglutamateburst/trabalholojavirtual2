<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo = "";

// salva a compra na tabela cliente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome    = mysqli_real_escape_string($conexao, $_POST["nome"] ?? "");
    $email   = mysqli_real_escape_string($conexao, $_POST["email"] ?? "");
    $rg      = (int)($_POST["rg"] ?? 0);
    $cep     = mysqli_real_escape_string($conexao, $_POST["cep"] ?? "");
    $rua     = mysqli_real_escape_string($conexao, $_POST["rua"] ?? "");
    $numero  = mysqli_real_escape_string($conexao, $_POST["numero"] ?? "");
    $bairro  = mysqli_real_escape_string($conexao, $_POST["bairro"] ?? "");
    $cidade  = mysqli_real_escape_string($conexao, $_POST["cidade"] ?? "");

    if ($nome == "" || $email == "" || empty($_SESSION["carrinho"])) {
        $mensagem = "Preencha todos os dados obrigatórios.";
        $tipo = "erro";
    } else {
        // registra um cliente + compra por item do carrinho
        foreach ($_SESSION["carrinho"] as $isbn => $qtd) {
            // pega um exemplar disponível do livro
            $res = mysqli_query($conexao, "SELECT codigo_ex FROM exemplares
                        WHERE codigo_ex NOT IN (SELECT Compra FROM cliente WHERE Compra IS NOT NULL)
                        AND codigo_ex IN (SELECT acervo FROM livros WHERE isbn = $isbn) LIMIT 1");
            $exemplar = mysqli_fetch_assoc($res);

            $codigo_ex = $exemplar ? $exemplar["codigo_ex"] : "NULL";
            $hoje = date("Y-m-d");

            mysqli_query($conexao, "INSERT INTO cliente (nome_cliente, email_cliente, rg, Compra, data_compra)
                        VALUES ('$nome', '$email', $rg, $codigo_ex, '$hoje')");
        }

        $_SESSION["carrinho"] = [];
        $mensagem = "Compra finalizada com sucesso! 🎉";
        $tipo = "ok";
    }
}

// total do carrinho
$total = 0;
$qtd_itens = 0;
if (!empty($_SESSION["carrinho"])) {
    $ids = implode(",", array_keys($_SESSION["carrinho"]));
    $res = mysqli_query($conexao, "SELECT isbn, custo, titulo FROM livros WHERE isbn IN ($ids)");
    while ($l = mysqli_fetch_assoc($res)) {
        $qtd = $_SESSION["carrinho"][$l["isbn"]];
        $total += $l["custo"] * $qtd;
        $qtd_itens += $qtd;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Checkout - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>📚 Dreams Books</h1>
    <nav>
        <a href="conta.php">Minha conta</a>
        <a href="carrinho.php">🛒 Carrinho</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<main>

<div class="stepper">
    <div class="passo ativo"><span class="numero">1</span> Endereço</div>
    <div class="passo"><span class="numero">2</span> Pagamento</div>
    <div class="passo"><span class="numero">3</span> Confirmação</div>
</div>

<?php if ($mensagem != "") { ?>
    <p class="mensagem <?php echo $tipo == "ok" ? "ok" : "erro"; ?>"><?php echo $mensagem; ?></p>
<?php } ?>

<form method="post" class="checkout">

    <div class="card card-form">
        <h2>Dados de entrega</h2>

        <label>Nome completo</label>
        <input type="text" name="nome" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>RG</label>
        <input type="number" name="rg" required>

        <div class="buscar-cep">
            <div>
                <label>CEP</label>
                <input type="text" name="cep" placeholder="00000-000">
            </div>
            <button type="button">Buscar</button>
        </div>

        <label>Rua</label>
        <input type="text" name="rua">

        <div class="linha">
            <div><label>Número</label><input type="text" name="numero"></div>
            <div><label>Complemento (opcional)</label><input type="text" name="complemento"></div>
        </div>

        <div class="linha">
            <div><label>Bairro</label><input type="text" name="bairro"></div>
            <div><label>Cidade</label><input type="text" name="cidade"></div>
        </div>

        <button type="submit">Finalizar compra</button>
    </div>

    <div class="card card-resumo">
        <h2>Resumo do pedido</h2>
        <?php if ($qtd_itens > 0) { ?>
            <p class="detalhe"><?php echo $qtd_itens; ?> item(ns) no carrinho</p>
            <div class="divisor"></div>
            <p class="total">Total: R$ <?php echo number_format($total, 2, ",", "."); ?></p>
        <?php } else { ?>
            <p class="detalhe">Carrinho vazio.</p>
        <?php } ?>
    </div>

</form>
</main>

</body>
</html>