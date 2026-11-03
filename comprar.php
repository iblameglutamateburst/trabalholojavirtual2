<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $forma = $_POST["forma_pagamento"] ?? "";
    $cep = trim($_POST["cep"] ?? "");
    $rua = trim($_POST["rua"] ?? "");
    $numero = trim($_POST["numero"] ?? "");
    $cep = preg_replace('/\D/', '', $cep);
    $cidade = trim($_POST["cidade"] ?? "");

    if (empty($_SESSION["carrinho"])) {
        $erro = "Seu carrinho está vazio.";
    } else if ($forma == "" || $cep == "" || $rua == "" || $numero == "" || $cidade == "") {
        $erro = "Preencha todos os campos de entrega e pagamento.";
        } else if (strlen($cep) != 8) {
    $erro = "CEP inválido — digite os 8 números.";
    } else {
        $uid = (int)$_SESSION["id"];
        $ids = implode(",", array_keys($_SESSION["carrinho"]));
        $resultado = mysqli_query($conexao, "SELECT isbn, custo, estoque, titulo FROM livros WHERE isbn IN ($ids)");

        $itens = [];
        $total = 0;
        $sem_estoque = [];

        while ($livro = mysqli_fetch_assoc($resultado)) {
            $qtd = (int)$_SESSION["carrinho"][$livro["isbn"]];
            if ($qtd > (int)$livro["estoque"]) {
                $sem_estoque[] = $livro["titulo"];
                continue;
            }
            $itens[] = ["isbn" => $livro["isbn"], "qtd" => $qtd, "preco" => $livro["custo"]];
            $total += $livro["custo"] * $qtd;
        }

        if (!empty($sem_estoque)) {
            $erro = "Estoque insuficiente para: " . htmlspecialchars(implode(", ", $sem_estoque));
        } else if (empty($itens)) {
            $erro = "Seu carrinho está vazio.";
        } else {
            mysqli_begin_transaction($conexao);

            $stmt = $conexao->prepare("INSERT INTO pedidos (usuario_id, total, forma_pagamento) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $uid, $total, $forma);
            $stmt->execute();
            $pedido_id = $conexao->insert_id;
            $stmt->close();

            $stmt = $conexao->prepare("INSERT INTO itens_pedido (pedido_id, isbn, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            foreach ($itens as $item) {
                $stmt->bind_param("iiid", $pedido_id, $item["isbn"], $item["qtd"], $item["preco"]);
                $stmt->execute();
                mysqli_query($conexao, "UPDATE livros SET estoque = estoque - {$item['qtd']} WHERE isbn = {$item['isbn']}");
            }
            $stmt->close();

            mysqli_commit($conexao);

            $_SESSION["carrinho"] = [];
            header("Location: meus_pedidos.php?compra=ok");
            exit;
        }
    }
}

$itens_carrinho = [];
$total_carrinho = 0;

if (!empty($_SESSION["carrinho"])) {
    $ids = implode(",", array_keys($_SESSION["carrinho"]));
    $resultado = mysqli_query($conexao, "SELECT l.isbn, l.titulo, l.custo, l.foto, a.nome_autor
                                         FROM livros l
                                         LEFT JOIN autores a ON a.autoria = l.isbn
                                         WHERE l.isbn IN ($ids)");
    while ($livro = mysqli_fetch_assoc($resultado)) {
        $livro["qtd"] = $_SESSION["carrinho"][$livro["isbn"]];
        $total_carrinho += $livro["custo"] * $livro["qtd"];
        $itens_carrinho[] = $livro;
    }
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Finalizar compra - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
<div class="stepper">
    <div class="passo"><span class="numero">1</span> Carrinho</div>
    <div class="passo"><span class="numero">2</span> Endereço</div>
    <div class="passo ativo"><span class="numero">3</span> Pagamento</div>
</div>

<?php if ($erro != ""): ?>
    <div class="mensagem erro"><?= $erro ?></div>
<?php endif; ?>

<?php if (empty($itens_carrinho) && $erro == ""): ?>
    <p class="nenhum">Seu carrinho está vazio.</p>
<?php else: ?>
<div class="checkout">

    <div class="card card-form">
        <h2>Entrega e pagamento</h2>
        <form method="POST">
            <label>CEP</label>
            <div class="campo"><span class="icone">📍</span><input type="text" name="cep" placeholder="00000-000" required></div>

            <label>Rua</label>
            <div class="campo"><span class="icone">🏠</span><input type="text" name="rua" placeholder="Rua, avenida..." required></div>

            <label>Número</label>
            <div class="campo"><span class="icone">🔢</span><input type="text" name="numero" required></div>

            <label>Cidade</label>
            <div class="campo"><span class="icone">🏙️</span><input type="text" name="cidade" required></div>

            <label>Forma de pagamento</label>
            <div class="pagamento">
                <label class="pagamento-opcao"><input type="radio" name="forma_pagamento" value="Pix" required><span>💠 Pix</span></label>
                <label class="pagamento-opcao"><input type="radio" name="forma_pagamento" value="Cartão"><span>💳 Cartão</span></label>
                <label class="pagamento-opcao"><input type="radio" name="forma_pagamento" value="Boleto"><span>🧾 Boleto</span></label>
            </div>

            <button type="submit">Finalizar pedido</button>
        </form>
    </div>

    <div class="card card-resumo">
        <h2>Resumo do pedido</h2>
        <?php foreach ($itens_carrinho as $item): ?>
            <div class="item">
                <div class="mini">
                    <?php if (!empty($item["foto"]) && file_exists($item["foto"])): ?>
                        <img src="<?= $item["foto"] ?>">
                    <?php else: ?>
                        📕
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($item["titulo"]) ?></strong><br>
                    <small><?= $item["qtd"] ?>x — R$ <?= number_format($item["custo"], 2, ",", ".") ?></small>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="divisor"></div>
        <p class="total">Total: R$ <?= number_format($total_carrinho, 2, ",", ".") ?></p>
    </div>

</div>
<?php endif; ?>
</main>

<?php include "footer.php"; ?>
</body>
</html>