<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$aba = ($_GET["aba"] ?? "") === "favoritos" ? "favoritos" : "perfil";

$stmt = $conexao->prepare("SELECT nome, email, admin FROM usuario WHERE id = ?");
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$compras = 0;
$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM cliente WHERE email_cliente = ?");
$stmt->bind_param("s", $usuario["email"]);
$stmt->execute();
$compras = $stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$favoritos = null;

if ($aba === "favoritos") {
    $uid = (int)$_SESSION["id"];
    $favoritos = $conexao->query("SELECT l.isbn, l.titulo, l.custo, l.foto, l.categoria, a.nome_autor
                                  FROM favoritos f
                                  JOIN livros l ON l.isbn = f.isbn
                                  LEFT JOIN autores a ON a.autoria = l.isbn
                                  WHERE f.usuario_id = $uid
                                  ORDER BY f.data_favorito DESC");
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Minha conta</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <h2>Minha conta</h2>

        <div class="abas-conta">
            <a href="conta.php" class="<?= $aba === "perfil" ? "aba-ativa" : "" ?>">👤 Perfil</a>
            <a href="conta.php?aba=favoritos" class="<?= $aba === "favoritos" ? "aba-ativa" : "" ?>">❤ Favoritos</a>
        </div>

        <?php if ($aba === "perfil"): ?>

        <div class="conta-grid">

            <div class="painel painel-perfil">
                <div class="conta-avatar"><?= strtoupper(substr($usuario["nome"], 0, 1)) ?></div>
                <h3><?= htmlspecialchars($usuario["nome"]) ?></h3>
                <p class="detalhe"><?= htmlspecialchars($usuario["email"]) ?></p>
                <span class="chip-categoria"><?= $usuario["admin"] == 1 ? "Administrador" : "Cliente" ?></span>

                <div class="conta-stats">
                    <div class="stat">
                        <strong><?= $compras ?></strong>
                        <span>compras</span>
                    </div>
                    <div class="stat">
                        <strong><?= $usuario["admin"] == 1 ? "Sim" : "Não" ?></strong>
                        <span>admin</span>
                    </div>
                </div>
            </div>

            <div class="painel">
                <h3>Ações rápidas</h3>
                <div class="conta-acoes">
                    <a href="catalogo.php" class="conta-acao">📚 Explorar catálogo</a>
                    <a href="conta.php?aba=favoritos" class="conta-acao">❤ Meus favoritos</a>
                    <a href="carrinho.php" class="conta-acao">🛒 Meu carrinho</a>
                    <a href="meus_pedidos.php" class="conta-acao">📦 Meus pedidos</a>
                    <?php if ($usuario["admin"] == 1): ?>
                        <a href="admin.php" class="conta-acao">⚙️ Área admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="conta-acao conta-acao-sair">🚪 Sair da conta</a>
                </div>
            </div>

        </div>

        <?php else: ?>

        <?php if ($favoritos->num_rows == 0): ?>
            <div class="nenhum">
                <p>Você ainda não salvou nenhum livro.</p>
                <p style="margin-top:12px;"><a href="catalogo.php" class="botao-editar">Explorar catálogo</a></p>
            </div>
        <?php else: ?>
            <div class="livros">
                <?php while ($livro = $favoritos->fetch_assoc()): ?>
                    <div class="livro">
                        <?php if (!empty($livro["foto"]) && file_exists($livro["foto"])): ?>
                            <img src="<?= $livro["foto"] ?>" class="livro-foto">
                        <?php else: ?>
                            <div class="livro-imagem">📕</div>
                        <?php endif; ?>
                        <?php if (!empty($livro["categoria"])): ?>
                            <span class="chip-categoria"><?= htmlspecialchars($livro["categoria"]) ?></span>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($livro["titulo"]) ?></h3>
                        <p class="detalhe"><?= htmlspecialchars($livro["nome_autor"] ?? "") ?></p>
                        <p class="preco">R$ <?= number_format($livro["custo"], 2, ",", ".") ?></p>
                        <div class="botoes-favorito">
                            <a href="livro.php?isbn=<?= $livro["isbn"] ?>"><button type="button">Ver livro</button></a>
                            <a href="acao_livro.php?isbn=<?= $livro["isbn"] ?>&acao=remover&voltar=conta" class="botao-favorito">❤ Remover</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <?php endif; ?>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>