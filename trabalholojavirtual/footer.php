<footer class="rodape-site">
    <div class="rodape-conteudo">
        <div class="rodape-marca">
            <strong>📚 Dreams Books</strong>
            <p>Seu próximo livro começa aqui.</p>
        </div>
        <nav class="rodape-links">
            <a href="index.php">Início</a>
            <a href="catalogo.php">Catálogo</a>
            <?php if (isset($_SESSION["id"])): ?>
                <a href="meus_pedidos.php">Meus pedidos</a>
                <a href="conta.php">Minha conta</a>
            <?php endif; ?>
        </nav>
        <small>&copy; <?= date("Y") ?> Dreams Books. Feito para quem ama histórias.</small>
    </div>
</footer>