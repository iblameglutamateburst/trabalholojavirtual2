<?php
session_start();

session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dreams Books - Minha conta</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <div class="formulario">

            <h1>Você saiu da sua conta!</h1>

            <p style="text-align: center;">
                Obrigado por visitar a Dreams Books.
            </p>

            <div class="rodape">
                <a href="index.php">Voltar para página inicial</a>
            </div>

        </div>
    </main>
</body>
</html>