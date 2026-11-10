<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}
require_once "conexao.php";

$uid = (int)$_SESSION["id"];

$stmt = $conexao->prepare("SELECT nome, email, admin FROM usuario WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header("Location: logout.php");
    exit;
}

$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$total_pedidos = (int)$stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM favoritos WHERE usuario_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$total_favoritos = (int)$stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$conexao->close();

$inicial = strtoupper(mb_substr($usuario["nome"], 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta - Dreams Books</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "header.php"; ?>
    <main>
        <h2>Minha conta</h2>

        <div class="conta-grid">
            <div class="card-conta">
                <div class="perfil-topo">
                    <div class="avatar-conta"><?= $inicial ?></div>
                    <div>
                        <p class="perfil-nome"><?= htmlspecialchars($usuario["nome"]) ?></p>
                        <p class="perfil-email"><?= htmlspecialchars($usuario["email"]) ?></p>
                        <?php if ($usuario["admin"] == 1): ?>
                            <span class="badge-admin">ADMINISTRADOR</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="perfil-stats">
                    <div class="stat-item">
                        <span class="stat-numero"><?= $total_pedidos ?></span>
                        <span class="stat-label">compras</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-numero"><?= $total_favoritos ?></span>
                        <span class="stat-label">favoritos</span>
                    </div>
                </div>
            </div>

            <div class="card-conta">
                <h3>Ações rápidas</h3>
                <div class="acoes-conta">
                    <a href="editar_perfil.php" class="acao-card destaque">
                        <span class="acao-icone">✏️</span> Editar dados e senha
                    </a>
                    <a href="meus_pedidos.php" class="acao-card">
                        <span class="acao-icone">📦</span> Meus pedidos
                    </a>
                    <a href="favoritos.php" class="acao-card">
                        <span class="acao-icone">❤️</span> Meus favoritos
                    </a>
                    <a href="carrinho.php" class="acao-card">
                        <span class="acao-icone">🛒</span> Meu carrinho
                    </a>
                    <a href="catalogo.php" class="acao-card">
                        <span class="acao-icone">📚</span> Explorar catálogo
                    </a>
                    <?php if ($usuario["admin"] == 1): ?>
                        <a href="admin.php" class="acao-card">
                            <span class="acao-icone">⚙️</span> Área admin
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="acao-card sair">
                        <span class="acao-icone">🚪</span> Sair da conta
                    </a>
                </div>
            </div>
        </div>
    </main>
    <?php include "footer.php"; ?>
</body>
</html>