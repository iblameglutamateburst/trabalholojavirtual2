<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST["isbn"])) {
    $isbn = (int)$_POST["isbn"];
    $qtd  = max(1, (int)$_POST["quantidade"]);
    $_SESSION["carrinho"][$isbn] = ($_SESSION["carrinho"][$isbn] ?? 0) + $qtd;
}

if (isset($_GET["mais"])) {
    $i = (int)$_GET["mais"];
    if (isset($_SESSION["carrinho"][$i])) {
        $_SESSION["carrinho"][$i]++;
    }
}

if (isset($_GET["menos"])) {
    $i = (int)$_GET["menos"];
    if (isset($_SESSION["carrinho"][$i])) {
        $_SESSION["carrinho"][$i]--;
        if ($_SESSION["carrinho"][$i] <= 0) {
            unset($_SESSION["carrinho"][$i]);
        }
    }
}

if (isset($_GET["remover"])) {
    unset($_SESSION["carrinho"][(int)$_GET["remover"]]);
}

$itens = [];
$total = 0;

if (!empty($_SESSION["carrinho"])) {
    $ids = implode(",", array_keys($_SESSION["carrinho"]));
    $sql = "SELECT l.isbn, l.titulo, l.custo, l.foto, a.nome_autor
            FROM livros l
            LEFT JOIN autores a ON a.autoria = l.isbn
            WHERE l.isbn IN ($ids)";
    $resultado = mysqli_query($conexao, $sql);

    while ($livro = mysqli_fetch_assoc($resultado)) {
        $livro["qtd"] = $_SESSION["carrinho"][$livro["isbn"]];
        $livro["subtotal"] = $livro["custo"] * $livro["qtd"];
        $total += $livro["subtotal"];
        $itens[] = $livro;
    }
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu carrinho - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
<h2>Meu carrinho</h2>

<div class="carrinho">
<?php if (empty($itens)) { ?>
    <p class="nenhum">Seu carrinho está vazio.</p>
<?php } else { ?>
    <table>
        <tr>
            <th>Produto</th>
            <th>Quantidade</th>
            <th>Preço</th>
            <th>Subtotal</th>
            <th></th>
        </tr>
        <?php foreach ($itens as $item) { ?>
        <tr>
            <td>
                <div class="produto-info">
                    <div class="mini">
                        <?php if (!empty($item["foto"]) && file_exists($item["foto"])): ?>
                            <img src="<?= $item["foto"] ?>">
                        <?php else: ?>
                            📕
                        <?php endif; ?>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($item["titulo"]) ?></strong><br>
                        <small><?= htmlspecialchars($item["nome_autor"] ?? "") ?></small>
                    </div>
                </div>
            </td>
            <td>
                <div class="qtd">
                    <a href="carrinho.php?menos=<?= $item["isbn"] ?>">−</a>
                    <?= $item["qtd"] ?>
                    <a href="carrinho.php?mais=<?= $item["isbn"] ?>">+</a>
                </div>
            </td>
            <td>R$ <?= number_format($item["custo"], 2, ",", ".") ?></td>
            <td>R$ <?= number_format($item["subtotal"], 2, ",", ".") ?></td>
            <td><a href="carrinho.php?remover=<?= $item["isbn"] ?>" class="botao-excluir">Remover</a></td>
        </tr>
        <?php } ?>
    </table>

    <p class="total-linha">Total: R$ <?= number_format($total, 2, ",", ".") ?></p>

    <div class="acoes">
        <button type="button" onclick="location.href='catalogo.php'">Continuar comprando</button>
        <button type="button" onclick="location.href='comprar.php'">Finalizar compra →</button>
    </div>
<?php } ?>
</div>
</main>

</body>
</html>