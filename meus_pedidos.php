<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$uid = (int)$_SESSION["id"];
$busca = trim($_GET["busca"] ?? "");

$sql = "SELECT * FROM pedidos WHERE usuario_id = $uid";
if ($busca != "") {
    $busca_segura = mysqli_real_escape_string($conexao, $busca);
    $sql .= " AND (id = " . (int)$busca . " OR DATE_FORMAT(data_pedido, '%d/%m/%Y') LIKE '%$busca_segura%')";
}
$sql .= " ORDER BY data_pedido DESC";

$pedidos = $conexao->query($sql);

$detalhe_id = (int)($_GET["detalhe"] ?? 0);
$itens_detalhe = [];
if ($detalhe_id > 0) {
    $stmt = $conexao->prepare("SELECT ip.quantidade, ip.preco_unitario, l.titulo, l.foto
                               FROM itens_pedido ip
                               LEFT JOIN livros l ON l.isbn = ip.isbn
                               WHERE ip.pedido_id = ? AND (SELECT usuario_id FROM pedidos WHERE id = ?) = ?");
    $stmt->bind_param("iii", $detalhe_id, $detalhe_id, $uid);
    $stmt->execute();
    $itens_detalhe = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

function pillStatus($status) {
    $mapa = [
        "Entregue" => "pill-entregue",
        "Em transporte" => "pill-transporte",
        "Processado" => "pill-processado",
        "Cancelado" => "pill-cancelado"
    ];
    $classe = $mapa[$status] ?? "pill-processado";
    return "<span class='pill-status {$classe}'>" . htmlspecialchars($status) . "</span>";
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minhas Compras - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <h2 class="secao-titulo">Minhas Compras</h2>

    <?php if (($_GET["compra"] ?? "") === "ok"): ?>
        <div class="mensagem ok">🎉 Pedido finalizado com sucesso! Obrigado por comprar na Dreams Books.</div>
    <?php endif; ?>

    <div class="busca-compras">
        <form method="get">
            <input type="text" name="busca" placeholder="Pesquisar por número, data...." value="<?= htmlspecialchars($busca) ?>">
            <button type="submit">🔍</button>
        </form>
    </div>

    <?php if ($pedidos->num_rows == 0): ?>
        <p class="nenhum">Nenhuma compra encontrada. <a href="catalogo.php">Explorar catálogo</a></p>
    <?php else: ?>
        <div class="tabela-compras">
            <table>
                <tr>
                    <th>Pedido</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#D0<?= str_pad($pedido["id"], 5, "0", STR_PAD_LEFT) ?></strong></td>
                        <td><?= date("d/m/Y", strtotime($pedido["data_pedido"])) ?></td>
                        <td>R$ <?= number_format($pedido["total"], 2, ",", ".") ?></td>
                        <td><?= pillStatus($pedido["status"] ?? "Processado") ?></td>
                        <td><a href="meus_pedidos.php?detalhe=<?= $pedido["id"] ?>" class="botao-detalhes">Ver detalhes</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($itens_detalhe)): ?>
        <div class="painel" style="margin-top:24px;">
            <h3>Detalhes do pedido #D0<?= str_pad($detalhe_id, 5, "0", STR_PAD_LEFT) ?></h3>
            <?php foreach ($itens_detalhe as $item): ?>
                <div class="opiniao-card" style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="mini">
                            <?php if (!empty($item["foto"]) && file_exists($item["foto"])): ?>
                                <img src="<?= $item["foto"] ?>">
                            <?php else: ?>
                                📕
                            <?php endif; ?>
                        </div>
                        <strong><?= htmlspecialchars($item["titulo"] ?? "Livro") ?></strong>
                    </div>
                    <span><?= $item["quantidade"] ?>x · R$ <?= number_format($item["preco_unitario"] * $item["quantidade"], 2, ",", ".") ?></span>
                </div>
            <?php endforeach; ?>
            <a href="meus_pedidos.php" class="botao-detalhes">Fechar detalhes</a>
        </div>
    <?php endif; ?>
</main>

<?php include "footer.php"; ?>
</body>
</html>