<?php
session_start();
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Página não encontrada - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <div class="pagina-404">
        <span class="icone-404">😴📖💤</span>
        <h1>Ops! Página não encontrada.</h1>
        <p>A página que você está procurando não existe ou foi removida</p>
        <a href="index.php" class="botao-hero">Voltar para a página inicial</a>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>