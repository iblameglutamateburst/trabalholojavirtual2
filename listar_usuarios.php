<?php
session_start();
if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}
require_once "conexao.php";

$mensagem = "";
$tipo = "";

if (isset($_GET["excluir"])) {
    $id = (int)$_GET["excluir"];

    if ($id == $_SESSION["id"]) {
        $mensagem = "Você não pode excluir a própria conta.";
        $tipo = "erro";
    } else {
        $stmt = $conexao->prepare("SELECT nome FROM usuario WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $alvo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$alvo) {
            $mensagem = "Usuário não encontrado.";
            $tipo = "erro";
        } else {
            mysqli_begin_transaction($conexao);
            $ok = true;

            $stmt = $conexao->prepare("DELETE FROM itens_pedido WHERE pedido_id IN (SELECT id FROM pedidos WHERE usuario_id = ?)");
            $stmt->bind_param("i", $id);
            $ok = $ok && $stmt->execute();
            $stmt->close();

            $stmt = $conexao->prepare("DELETE FROM pedidos WHERE usuario_id = ?");
            $stmt->bind_param("i", $id);
            $ok = $ok && $stmt->execute();
            $stmt->close();

            $stmt = $conexao->prepare("DELETE FROM favoritos WHERE usuario_id = ?");
            $stmt->bind_param("i", $id);
            $ok = $ok && $stmt->execute();
            $stmt->close();

            $stmt = $conexao->prepare("DELETE FROM avaliacoes WHERE usuario_id = ?");
            $stmt->bind_param("i", $id);
            $ok = $ok && $stmt->execute();
            $stmt->close();

            $stmt = $conexao->prepare("DELETE FROM usuario WHERE id = ?");
            $stmt->bind_param("i", $id);
            $ok = $ok && $stmt->execute();
            $stmt->close();

            if ($ok) {
                mysqli_commit($conexao);
                $mensagem = "Usuário \"" . htmlspecialchars($alvo["nome"]) . "\" excluído com sucesso.";
                $tipo = "ok";
            } else {
                mysqli_rollback($conexao);
                $mensagem = "Não foi possível excluir: " . $conexao->error;
                $tipo = "erro";
            }
        }
    }
}

$por_pagina = 10;
$pagina = max(1, (int)($_GET["pagina"] ?? 1));
$busca = trim($_GET["busca"] ?? "");

$where = "";
$params = [];
$tipos = "";

if ($busca != "") {
    $where = " WHERE nome LIKE ? OR email LIKE ?";
    $termo = "%" . $busca . "%";
    $params = [$termo, $termo];
    $tipos = "ss";
}

$sql_total = "SELECT COUNT(*) AS total FROM usuario" . $where;
$stmt = $conexao->prepare($sql_total);
if ($tipos != "") {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$total_registros = (int)$stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$total_paginas = max(1, (int)ceil($total_registros / $por_pagina));
if ($pagina > $total_paginas) {
    $pagina = $total_paginas;
}
$offset = ($pagina - 1) * $por_pagina;

$sql = "SELECT id, nome, email, admin FROM usuario" . $where . " ORDER BY nome LIMIT ? OFFSET ?";
$stmt = $conexao->prepare($sql);
if ($tipos != "") {
    $stmt->bind_param($tipos . "ii", ...array_merge($params, [$por_pagina, $offset]));
} else {
    $stmt->bind_param("ii", $por_pagina, $offset);
}
$stmt->execute();
$resultado = $stmt->get_result();
$stmt->close();

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Usuários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "header.php"; ?>
    <main>
        <h2>Usuários cadastrados</h2>

        <?php if ($mensagem != ""): ?>
            <div class="mensagem <?= $tipo ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="get" class="pesquisa">
            <input type="text" name="busca" placeholder="Pesquisar por nome ou e-mail..." value="<?= htmlspecialchars($busca) ?>">
            <button type="submit">Pesquisar</button>
            <?php if ($busca != ""): ?>
                <a href="listar_usuarios.php" class="botao-limpar">Limpar</a>
            <?php endif; ?>
        </form>

        <p class="contador-lista">
            <?= $total_registros ?> usuário<?= $total_registros != 1 ? "s" : "" ?> encontrado<?= $total_registros != 1 ? "s" : "" ?>
            <?= $busca != "" ? "para \"" . htmlspecialchars($busca) . "\"" : "" ?>
        </p>

        <?php if ($resultado->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
                <?php while ($usuario = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $usuario["id"] ?></td>
                        <td><?= htmlspecialchars($usuario["nome"]) ?></td>
                        <td><?= htmlspecialchars($usuario["email"]) ?></td>
                        <td><?= $usuario["admin"] == 1 ? "⚙️ Admin" : "👤 Cliente" ?></td>
                        <td>
                            <a href="editar_usuario.php?id=<?= $usuario["id"] ?>" class="botao-editar">Editar</a>
                            <?php if ($usuario["id"] != $_SESSION["id"]): ?>
                                <a href="listar_usuarios.php?excluir=<?= $usuario["id"] ?>&busca=<?= urlencode($busca) ?>&pagina=<?= $pagina ?>"
                                   class="botao-excluir"
                                   onclick="return confirm('Excluir <?= htmlspecialchars(addslashes($usuario["nome"])) ?>? Os pedidos, favoritos e avaliações dele também serão apagados.')">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <?php if ($total_paginas > 1): ?>
                <div class="paginacao">
                    <?php if ($pagina > 1): ?>
                        <a href="listar_usuarios.php?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>" class="pagina-seta">← Anterior</a>
                    <?php else: ?>
                        <span class="pagina-seta desativada">← Anterior</span>
                    <?php endif; ?>

                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fim = min($total_paginas, $pagina + 2);
                    if ($inicio > 1) {
                        echo '<a href="listar_usuarios.php?pagina=1&busca=' . urlencode($busca) . '" class="pagina-num">1</a>';
                        if ($inicio > 2) echo '<span class="pagina-reticencias">…</span>';
                    }
                    for ($p = $inicio; $p <= $fim; $p++) {
                        $classe = $p == $pagina ? "pagina-num ativa" : "pagina-num";
                        echo '<a href="listar_usuarios.php?pagina=' . $p . '&busca=' . urlencode($busca) . '" class="' . $classe . '">' . $p . '</a>';
                    }
                    if ($fim < $total_paginas) {
                        if ($fim < $total_paginas - 1) echo '<span class="pagina-reticencias">…</span>';
                        echo '<a href="listar_usuarios.php?pagina=' . $total_paginas . '&busca=' . urlencode($busca) . '" class="pagina-num">' . $total_paginas . '</a>';
                    }
                    ?>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="listar_usuarios.php?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>" class="pagina-seta">Próxima →</a>
                    <?php else: ?>
                        <span class="pagina-seta desativada">Próxima →</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="nenhum">Nenhum usuário encontrado.</p>
        <?php endif; ?>
    </main>
    <?php include "footer.php"; ?>
</body>
</html>