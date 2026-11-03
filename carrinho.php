<?php
session_start();
require "conexao.php";

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

$aviso = "";

// Adicionar item — valida existência e estoque (Controle de estoque + Validação dos dados)
if (isset($_POST["isbn"])) {
    $isbn = (int)$_POST["isbn"];
    $qtd = max(1, (int)($_POST["quantidade"] ?? 1));

    $stmt = $conexao->prepare("SELECT estoque FROM livros WHERE isbn = ?");
    $stmt->bind_param("i", $isbn);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$dados) {
        $aviso = "Livro não encontrado.";
    } else if ((int)$dados["estoque"] <= 0) {
        $aviso = "Este livro está esgotado.";
    } else {
        $atual = $_SESSION["carrinho"][$isbn] ?? 0;
        $novo = $atual + $qtd;
        if ($novo > (int)$dados["estoque"]) {
            $novo = (int)$dados["estoque"];
            $aviso = "Quantidade ajustada ao estoque disponível ({$dados['estoque']} unidades).";
        }
        $_SESSION["carrinho"][$isbn] = $novo;
    }
}

// Botão + — só aumenta se tiver estoque
if (isset($_GET["mais"])) {
    $i = (int)$_GET["mais"];
    if (isset($_SESSION["carrinho"][$i])) {
        $stmt = $conexao->prepare("SELECT estoque FROM livros WHERE isbn = ?");
        $stmt->bind_param("i", $i);
        $stmt->execute();
        $estoque = (int)($stmt->get_result()->fetch_assoc()["estoque"] ?? 0);
        $stmt->close();

        if ($_SESSION["carrinho"][$i] < $estoque) {
            $_SESSION["carrinho"][$i]++;
        }
    }
}

// Botão − — remove ao chegar em zero
if (isset($_GET["menos"])) {
    $i = (int)$_GET["menos"];
    if (isset($_SESSION["carrinho"][$i])) {
        $_SESSION["carrinho"][$i]--;
        if ($_SESSION["carrinho"][$i] <= 0) {
            unset($_SESSION["carrinho"][$i]);
        }
    }
}

// Remover item
if (isset($_GET["remover"])) {
    unset($_SESSION["carrinho"][(int)$_GET["remover"]]);
}

// Limpar carrinho
if (isset($_GET["limpar"])) {
    $_SESSION["carrinho"] = [];
    header("Location: carrinho.php");
    exit;
}

$itens = [];
$total = 0;

if (!empty($_SESSION["carrinho"])) {
    $ids = implode(",", array_keys($_SESSION["carrinho"]));
    $sql = "SELECT l.isbn, l.titulo, l.custo, l.foto, l.estoque, a.nome_autor
            FROM livros l
            LEFT JOIN autores a ON a.autoria = l.isbn
            WHERE l.isbn IN ($ids)";
    $resultado = mysqli_query($conexao, $sql);

    while ($livro = mysqli_fetch_assoc($resultado)) {
        $livro["qtd"] = (int)$_SESSION["carrinho"][$livro["isbn"]];

        // Estoque caiu depois de adicionar? Ajusta ou remove sozinho
        if ($livro["qtd"] > (int)$livro["estoque"]) {
            if ((int)$livro["estoque"] <= 0) {
                unset($_SESSION["carrinho"][$livro["isbn"]]);
                $aviso = "Um item ficou indisponível e foi removido do carrinho.";
                continue;
            }
            $_SESSION["carrinho"][$livro["isbn"]] = (int)$livro["estoque"];
            $livro["qtd"] = (int)$livro["estoque"];
            $aviso = "Quantidades ajustadas ao estoque disponível.";
        }

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

<?php if ($aviso != ""): ?>
    <div class="mensagem ok"><?= htmlspecialchars($aviso) ?></div>
<?php endif; ?>

<div class="carrinho">
<?php if (empty($itens)) { ?>
    <p class="nenhum">Seu carrinho está vazio.</p>
    <div class="acoes">
        <button type="button" onclick="location.href='catalogo.php'">Explorar catálogo</button>
    </div>
<?php } else { ?>
    <table>
        <tr>
            <th>Produto</th>
            <th>Quantidade</th>
            <th>Estoque</th>
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
                    <?php if ($item["qtd"] < (int)$item["estoque"]): ?>
                        <a href="carrinho.php?mais=<?= $item["isbn"] ?>">+</a>
                    <?php else: ?>
                        <span class="qtd-max">+</span>
                    <?php endif; ?>
                </div>
            </td>
            <td>
                <?php if ((int)$item["estoque"] - $item["qtd"] <= 2): ?>
                    <span class="estoque-alerta">⚠️ só <?= $item["estoque"] ?></span>
                <?php else: ?>
                    <span class="estoque-ok"><?= $item["estoque"] ?> disp.</span>
                <?php endif; ?>
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
        <a href="carrinho.php?limpar=1" class="botao-excluir" onclick="return confirm('Esvaziar o carrinho?')">Limpar carrinho</a>
    </div>
<?php } ?>
</div>
</main>

<?php include "footer.php"; ?>
</body>
</html>