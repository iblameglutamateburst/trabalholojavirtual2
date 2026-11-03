<?php
$qtd_carrinho = 0;
if (!empty($_SESSION["carrinho"])) {
    $qtd_carrinho = array_sum($_SESSION["carrinho"]);
}
$pagina_atual = basename($_SERVER["PHP_SELF"]);
?>
<header>
    <a href="index.php" class="logo">📚 <span>Dreams Books</span></a>

    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <label for="menu-toggle" class="menu-botao">☰</label>

    <nav class="menu">
        <a href="catalogo.php" class="<?= $pagina_atual == "catalogo.php" ? "ativo" : "" ?>">Catálogo</a>
        <?php if (isset($_SESSION["id"])): ?>
            <a href="favoritos.php">Favoritos</a>
            <a href="conta.php">Minha conta</a>
            <?php if ($_SESSION["admin"] == 1): ?>
                <a href="admin.php">Área admin</a>
            <?php endif; ?>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="cadastro.php">Cadastrar</a>
        <?php endif; ?>
        <a href="carrinho.php" class="carrinho-link">🛒 Carrinho
            <?php if ($qtd_carrinho > 0): ?>
                <span class="badge"><?= $qtd_carrinho ?></span>
            <?php endif; ?>
        </a>
    </nav>
</header>