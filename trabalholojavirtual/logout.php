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

            <div class="icone-sucesso">✔</div>

            <h1>Você saiu da sua conta!</h1>

            <p>Obrigado por visitar a Dreams Books.</p>

            <form action="index.php" method="get">
                <button type="submit">Voltar para página inicial</button>
            </form>

        </div>
    </main>
</body>
</html>