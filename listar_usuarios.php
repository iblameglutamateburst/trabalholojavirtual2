<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$busca = isset($_GET["busca"]) ? mysqli_real_escape_string($conexao, $_GET["busca"]) : "";

if (isset($_GET["excluir"])) {
    $id = (int)$_GET["excluir"];
    if ($id != $_SESSION["id"]) {
        $conexao->query("DELETE FROM usuario WHERE id = $id");
    }
}

$sql = "SELECT id, nome, email, admin FROM usuario";
if ($busca != "") {
    $sql .= " WHERE nome LIKE '%$busca%' OR email LIKE '%$busca%'";
}
$sql .= " ORDER BY nome";
$resultado = $conexao->query($sql);

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

        <form method="get" class="pesquisa">
            <input type="text" name="busca" placeholder="Pesquisar por nome ou e-mail..." value="<?= htmlspecialchars($busca) ?>">
            <button type="submit">Pesquisar</button>
        </form>

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
                                <a href="listar_usuarios.php?excluir=<?= $usuario["id"] ?>&busca=<?= urlencode($busca) ?>" class="botao-excluir" onclick="return confirm('Excluir este usuário?')">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="nenhum">Nenhum usuário encontrado.</p>
        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>