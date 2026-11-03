<?php
$qtd_carrinho = 0;
if (!empty($_SESSION["carrinho"])) {
    $qtd_carrinho = array_sum($_SESSION["carrinho"]);
}
$pagina_atual = basename($_SERVER["PHP_SELF"]);
?>
<header>
    <a href="index.php" class="logo">
        <span class="logo-livro"></span>
        <span class="logo-texto">Dreams Books</span>
    </a>

    <div class="header-busca">
        <form method="get" action="catalogo.php">
            <input type="text" name="busca" placeholder="Pesquisar livros...">
            <button type="submit">🔍</button>
        </form>
    </div>

    <div class="header-acoes">
        <?php if (isset($_SESSION["id"])): ?>
            <a href="conta.php">👤 Minha conta ▾</a>
            <a href="favoritos.php">❤️ Favoritos</a>
        <?php else: ?>
            <a href="login.php">👤 Entrar</a>
        <?php endif; ?>
    </div>
</header>

<nav class="menu-principal">
    <a href="index.php" class="<?= $pagina_atual == "index.php" ? "ativo" : "" ?>">Início</a>
    <a href="catalogo.php" class="<?= $pagina_atual == "catalogo.php" ? "ativo" : "" ?>">Livros</a>
    <a href="categorias.php" class="<?= $pagina_atual == "categorias.php" ? "ativo" : "" ?>">Categorias</a>
    <a href="sobre.php" class="<?= $pagina_atual == "sobre.php" ? "ativo" : "" ?>">Sobre nós</a>
    <?php if (isset($_SESSION["admin"]) && $_SESSION["admin"] == 1): ?>
        <a href="admin.php">Área admin</a>
    <?php endif; ?>
    <?php if (isset($_SESSION["id"])): ?>
        <a href="logout.php">Sair</a>
    <?php endif; ?>
</nav>