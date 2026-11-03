<?php
require_once "conexao.php";

$mensagem = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $confirmar = $_POST["confirmar"];
    $termos = isset($_POST["termos"]);

    if ($nome == "" || $email == "" || $senha == "") {
        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "E-mail inválido.";
        $tipo = "erro";
    } else if ($senha != $confirmar) {
        $mensagem = "As senhas não conferem.";
        $tipo = "erro";
    } else if (strlen($senha) < 6) {
        $mensagem = "A senha deve ter no mínimo 6 caracteres.";
        $tipo = "erro";
    } else if (!$termos) {
        $mensagem = "É preciso aceitar os termos de uso.";
        $tipo = "erro";
    } else {

      
        $sql = "SELECT id FROM usuario WHERE email = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $mensagem = "Este e-mail já está cadastrado.";
            $tipo = "erro";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $token = md5(uniqid(rand(), true));

            $sql = "INSERT INTO usuario (nome, email, senha, token)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssss", $nome, $email, $senha_hash, $token);

            if ($stmt->execute()) {
                $link = "http://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/verificar.php?token=" . $token;

                $mensagem = "Cadastro realizado! Confirme seu e-mail para ativar a conta.";
                $mensagem .= "<br><a href='" . $link . "'>Clique aqui para verificar seu e-mail</a>";
                $tipo = "ok";
            } else {
                $mensagem = "Erro ao cadastrar usuário.";
                $tipo = "erro";
            }
        }
        $stmt->close();
    }
}

$conexao->close();

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <main>
        <div class="formulario">
            <h1>Cadastre sua conta</h1>
            <form method="POST">
                <label>Nome completo</label>
                <input type="text" name="nome" placeholder="Digite seu nome" required>

                <label>E-mail</label>
                <input type="email" name="email" placeholder="Seu@gmail.com" required>

                <label>Senha</label>
                <input type="password" name="senha" placeholder="Crie uma senha" required>

                <label>Confirmar senha</label>
                <input type="password" name="confirmar" placeholder="Confirme sua senha" required>

                <div class="opcoes">
                    <input type="checkbox" name="termos">
                    Li e aceito os Termos de Uso e Política de Privacidade
                </div>

                <button type="submit">
                    Cadastrar
                </button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <div class="rodape">
                Já tem uma conta? <a href="login.php">Entrar</a>
            </div>
        </div>
    </main>
</body>
</html>