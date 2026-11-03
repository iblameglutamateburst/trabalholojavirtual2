<?php
session_start();

require_once "conexao.php";

$mensagem = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $sql = "SELECT id, nome, senha, verificado, admin FROM usuario WHERE email = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if ($usuario["verificado"] == 0) {
            $mensagem = "Confirme seu e-mail antes de entrar.";
            $tipo = "erro";
        } else if (password_verify($senha, $usuario["senha"])) {
            $_SESSION["id"] = $usuario["id"];
            $_SESSION["nome"] = $usuario["nome"];
            $_SESSION["admin"] = $usuario["admin"];

            header("Location: index.php");
            exit;
        } else {
            $mensagem = "Senha incorreta!";
            $tipo = "erro";
        }
    } else {
        $mensagem = "Usuário não encontrado!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Entrar</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <div class="formulario">

            <h1>Entrar na sua conta</h1>
            <form method="POST">
                <label>E-mail</label>
                <div class="campo">
                    <span class="icone">👤</span>
                    <input type="email" name="email" placeholder="Seu@gmail.com" required>
                </div>

                <label>Senha</label>
                <div class="campo">
                    <span class="icone">🔒</span>
                    <input type="password" name="senha" placeholder="Digite sua senha" required>
                </div>

                <div class="opcoes">
                    <label style="display:flex;align-items:center;gap:6px;font-weight:400;margin-top:0;">
                        <input type="checkbox" name="lembrar"> Lembrar de mim
                    </label>
                    <a href="#">Esqueceu sua senha?</a>
                </div>

                <button type="submit">Entrar</button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <div class="divisor">ou</div>

            <button type="button" class="botao-google">🔵 Entrar com Google</button>

            <div class="rodape">
                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
            </div>
        </div>
    </main>
</body>
</html>