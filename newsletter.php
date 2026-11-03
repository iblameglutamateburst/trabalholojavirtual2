<?php
session_start();
require "conexao.php";

$email = trim($_POST["email"] ?? "");
$sucesso = false;

if ($email != "" && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $conexao->prepare("INSERT IGNORE INTO newsletter (email) VALUES (?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
    $sucesso = true;
}

mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Newsletter - Dreams Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <div class="formulario" style="text-align:center;">
        <?php if ($sucesso): ?>
            <div class="mensagem ok">🎉 Pronto! Você vai receber nossas novidades.</div>
        <?php else: ?>
            <div class="mensagem erro">E-mail inválido. Tenta de novo!</div>
        <?php endif; ?>
        <a href="index.php" class="botao-hero">Voltar para a página inicial</a>
    </div>
</main>

<?php include "footer.php"; ?>
</body>
</html>