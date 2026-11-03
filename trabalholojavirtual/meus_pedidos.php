<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$stmt = $conexao->prepare("SELECT email FROM usuario WHERE id = ?");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conexao->prepare("SELECT c.data_compra, l.titulo, l.custo, l.foto, a.nome_autor
                           FROM cliente c
                           LEFT JOIN livros l ON l.acervo = c.Compra
                           LEFT JOIN autores a ON a.autoria = l.isbn
                           WHERE c.email_cliente = ?
                           ORDER BY c.data_compra DESC");
$stmt->bind_param("s", $usuario["email"]);
$stmt->execute();
$resultado = $stmt->get_result();

$pedidos = [];
while ($linha = $resultado->fetch_assoc()) {
    $pedidos[$linha["data_compra"]][] = $linha;
}
$stmt->close();

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

        <?php if (empty($pedidos)): ?>
            <div class="nenhum">
                <p>Você ainda não fez nenhuma compra.</p>
                <p style="margin-top:12px;"><a href="catalogo.php" class="botao-editar">Explorar catálogo</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $data => $itens): ?>
                <div class="pedido-card">
                    <div class="pedido-topo">
                        <span class="pedido-numero">📦 Pedido de <?= date("d/m/Y", strtotime($data)) ?></span>
                        <span class="pedido-total">
                            R$ <?= number_format(array_sum(array_column($itens, "custo")), 2, ",", ".") ?>
                        </span>
                    </div>

                    <?php foreach ($itens as $item): ?>
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
                                <small><?= htmlspecialchars($item["nome_autor"] ?? "") ?></small>
                            </div>
                            <span class="pedido-item-preco">R$ <?= number_format($item["custo"], 2, ",", ".") ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>