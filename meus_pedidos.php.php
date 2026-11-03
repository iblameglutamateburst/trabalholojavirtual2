<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$uid = (int)$_SESSION["id"];
$busca = trim($_GET["busca"] ?? "");
$periodo = trim($_GET["periodo"] ?? "");

$sql = "SELECT DISTINCT p.* FROM pedidos p";
$condicoes = ["p.usuario_id = " . $uid];

if ($busca != "") {
    $busca_segura = mysqli_real_escape_string($conexao, $busca);
    $sql .= " JOIN itens_pedido ip ON ip.pedido_id = p.id
              JOIN livros l ON l.isbn = ip.isbn";
    $condicoes[] = "l.titulo LIKE '%$busca_segura%'";
}

if ($periodo != "" && preg_match('/^\d{4}-\d{2}$/', $periodo)) {
    $condicoes[] = "DATE_FORMAT(p.data_pedido, '%Y-%m') = '$periodo'";
}

$sql .= " WHERE " . implode(" AND ", $condicoes) . " ORDER BY p.data_pedido DESC";

$pedidos = [];
$resultado = $conexao->query($sql);

while ($pedido = $resultado->fetch_assoc()) {
    $stmt = $conexao->prepare("SELECT ip.quantidade, ip.preco_unitario, l.titulo, l.foto, a.nome_autor
                               FROM itens_pedido ip
                               LEFT JOIN livros l ON l.isbn = ip.isbn
                               LEFT JOIN autores a ON a.autoria = l.isbn
                               WHERE ip.pedido_id = ?");
    $stmt->bind_param("i", $pedido["id"]);
    $stmt->execute();
    $pedido["itens"] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $pedidos[] = $pedido;
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Meus pedidos</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <h2>Meus pedidos</h2>

        <?php if (($_GET["compra"] ?? "") === "ok"): ?>
            <div class="mensagem ok">🎉 Pedido finalizado com sucesso! Obrigado por comprar na Dreams Books.</div>
        <?php endif; ?>

        <form method="get" class="pesquisa-pedidos">
            <input type="text" name="busca" placeholder="Pesquisar por título do livro..." value="<?= htmlspecialchars($busca) ?>">
            <input type="month" name="periodo" value="<?= htmlspecialchars($periodo) ?>">
            <button type="submit">Pesquisar</button>
            <?php if ($busca != "" || $periodo != ""): ?>
                <a href="meus_pedidos.php" class="limpar-filtros">Limpar filtros</a>
            <?php endif; ?>
        </form>

        <?php if (empty($pedidos)): ?>
            <div class="nenhum">
                <p><?= ($busca != "" || $periodo != "") ? "Nenhum pedido encontrado com esses filtros." : "Você ainda não fez nenhuma compra." ?></p>
                <p style="margin-top:12px;"><a href="catalogo.php" class="botao-editar">Explorar catálogo</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card">
                    <div class="pedido-topo">
                        <span class="pedido-numero">📦 Pedido #<?= $pedido["id"] ?> — <?= date("d/m/Y", strtotime($pedido["data_pedido"])) ?></span>
                        <span class="pedido-total"><?= htmlspecialchars($pedido["forma_pagamento"]) ?> · R$ <?= number_format($pedido["total"], 2, ",", ".") ?></span>
                    </div>

                    <?php foreach ($pedido["itens"] as $item): ?>
                        <div class="pedido-item">
                            <div class="mini">
                                <?php if (!empty($item["foto"]) && file_exists($item["foto"])): ?>
                                    <img src="<?= $item["foto"] ?>">
                                <?php else: ?>
                                    📕
                                <?php endif; ?>
                            </div>
                            <div class="pedido-item-info">
                                <strong><?= htmlspecialchars($item["titulo"] ?? "Livro") ?></strong>
                                <small><?= htmlspecialchars($item["nome_autor"] ?? "") ?> · <?= $item["quantidade"] ?>x</small>
                            </div>
                            <span class="pedido-item-preco">R$ <?= number_format($item["preco_unitario"] * $item["quantidade"], 2, ",", ".") ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>