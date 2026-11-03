<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$mensagem = "";
$tipo = "";
$uid = (int)$_SESSION["id"];

$stmt = $conexao->prepare("SELECT nome, email FROM usuario WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha_atual = $_POST["senha_atual"] ?? "";
    $nova_senha = $_POST["nova_senha"] ?? "";

    if ($nome == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Preencha o nome e um e-mail válido.";
        $tipo = "erro";
    } else {
        $stmt = $conexao->prepare("SELECT senha FROM usuario WHERE id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $senha_banco = $stmt->get_result()->fetch_assoc()["senha"];
        $stmt->close();

        if ($nova_senha != "" && !password_verify($senha_atual, $senha_banco)) {
            $mensagem = "A senha atual está incorreta.";
            $tipo = "erro";
        } else if ($nova_senha != "" && strlen($nova_senha) < 6) {
            $mensagem = "A nova senha deve ter no mínimo 6 caracteres.";
            $tipo = "erro";
        } else {
            if ($nova_senha != "") {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("UPDATE usuario SET nome = ?, email = ?, senha = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nome, $email, $senha_hash, $uid);
            } else {
                $stmt = $conexao->prepare("UPDATE usuario SET nome = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nome, $email, $uid);
            }

            if ($stmt->execute()) {
                $mensagem = "Dados atualizados com sucesso!";
                $tipo = "ok";
                $usuario = ["nome" => $nome, "email" => $email];
            } else {
                $mensagem = "Erro ao atualizar dados.";
                $tipo = "erro";
            }
            $stmt->close();
        }
    }
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Editar Perfil</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="formulario">
            <h1>Editar meus dados</h1>
            <form method="POST">
                <label>Nome</label>
                <div class="campo">
                    <span class="icone">👤</span>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario["nome"]) ?>" required>
                </div>

                <label>E-mail (contato)</label>
                <div class="campo">
                    <span class="icone">✉️</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario["email"]) ?>" required>
                </div>

                <label>Senha atual (só se for trocar a senha)</label>
                <div class="campo">
                    <span class="icone">🔒</span>
                    <input type="password" name="senha_atual" placeholder="Deixe vazio para manter">
                </div>

                <label>Nova senha</label>
                <div class="campo">
                    <span class="icone">🔑</span>
                    <input type="password" name="nova_senha" placeholder="Deixe vazio para manter">
                </div>

                <button type="submit">Salvar alterações</button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>"><?= $mensagem ?></div>
            <?php endif; ?>

            <div class="rodape">
                <a href="conta.php">← Voltar para minha conta</a>
            </div>
        </div>
    </main>
</body>
</html>