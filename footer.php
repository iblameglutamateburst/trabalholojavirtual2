<footer>
    <div class="footer-logo">
        <a href="index.php" class="logo">
            <span class="logo-livro"></span>
            <span class="logo-texto">Dreams Books</span>
        </a>
        <p class="footer-slogan">Entre páginas, novos sonhos</p>
    </div>

    <div class="footer-links">
        <a href="#">Política de privacidade</a>
        <a href="#">Termos de uso</a>
        <a href="#">Ajuda</a>
    </div>

    <div class="footer-news">
        <label>Receba nossas novidades</label>
        <form method="post" action="newsletter.php">
            <input type="email" name="email" placeholder="Seu e-mail..." required>
            <button type="submit">cadastrar</button>
        </form>
    </div>
</footer>

<a href="#" class="botao-topo" id="botaoTopo">↑</a>

<script>
const botaoTopo = document.getElementById("botaoTopo");
window.addEventListener("scroll", () => {
    botaoTopo.classList.toggle("visivel", window.scrollY > 400);
});
botaoTopo.addEventListener("click", (e) => {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: "smooth" });
});
const observador = new IntersectionObserver((entradas) => {
    entradas.forEach((entrada) => {
        if (entrada.isIntersecting) {
            entrada.target.classList.add("revelado");
            observador.unobserve(entrada.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll(".card-livro, .card-destaque, .card-categoria, .valor-card, .painel, .admin-card").forEach((el) => {
    if (el.getBoundingClientRect().top > window.innerHeight) {
        el.classList.add("pra-revelar");
        observador.observe(el);
    }
});
</script>