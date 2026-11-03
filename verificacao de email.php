<?php
require_once "conexao.php";
$token = "";

if (isset($_GET["token"])) {
    $token = $_GET["token"];
}

$mensagem = "";
$tipo = "";

if ($token == "") {
    $mensagem = "Token não informado.";
    $tipo = "erro";
} else {
    $sql = "SELECT id FROM usuario WHERE token = ? AND verificado = 0";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $sql = "UPDATE usuario SET verificado = 1, token = '' WHERE token = ?";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $token);

        if ($stmt->execute()) {
            $mensagem = "E-mail verificado! Sua conta está ativa.";
            $tipo = "ok";
        } else {
            $mensagem = "Erro ao ativar a conta.";
            $tipo = "erro";
        }

    } else {
        $mensagem = "Link inválido ou já utilizado.";
        $tipo = "erro";
    }
    $stmt->close();
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Dreams Books - Verificação</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <div class="formulario">
            <h1>Verifique seu e-mail</h1>
            <div class="mensagem <?= $tipo ?>">
                <?= $mensagem ?>
            </div>

            <div class="rodape">
                <a href="login.php">Voltar para o Login</a>
            </div>
        </div>
    </main>
</body>
</html>