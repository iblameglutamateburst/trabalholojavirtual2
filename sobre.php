<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sobre nós - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <h2 class="secao-titulo">Sobre nós</h2>



        <div>
            <div class="balao">
                <strong>A Dreams Books</strong> é uma livraria online, criada para aproximar você das histórias que ama, levar você a descobrir novos mundos e tornar a escolha do seu próprio livro uma experiência simples e especial.
            </div>

            <div class="sobre-valores">
                <div class="valor-card" style="background:#fcf1a7;">
                    <span>🚚</span>
                    <strong>Entrega rápida</strong>
                    <small>Seu livro chega novinho na sua porta</small>
                </div>
                <div class="valor-card" style="background:#b8dcfb;">
                    <span>📚</span>
                    <strong>Curadoria especial</strong>
                    <small>Títulos escolhidos com carinho</small>
                </div>
                <div class="valor-card" style="background:#f3b4eb;">
                    <span>💬</span>
                    <strong>Suporte humano</strong>
                    <small>A gente responde de verdade</small>
                </div>
            </div>

            <div style="text-align:right;margin-top:20px;">
                <span class="balao-mini">Onde cada livro vira um sonho</span>
            </div>
        </div>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>