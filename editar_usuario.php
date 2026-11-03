<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["admin"] != 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$mensagem = "";
$tipo = "";
$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

$stmt = $conexao->prepare("SELECT id, nome, email, admin FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header("Location: listar_usuarios.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $admin = isset($_POST["admin"]) ? 1 : 0;
    $nova_senha = $_POST["nova_senha"] ?? "";

    if ($nome == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Preencha o nome e um e-mail válido.";
        $tipo = "erro";
    } else {
        if ($nova_senha != "") {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("UPDATE usuario SET nome = ?, email = ?, admin = ?, senha = ? WHERE id = ?");
            $stmt->bind_param("ssisi", $nome, $email, $admin, $senha_hash, $id);
        } else {
            $stmt = $conexao->prepare("UPDATE usuario SET nome = ?, email = ?, admin = ? WHERE id = ?");
            $stmt->bind_param("ssii", $nome, $email, $admin, $id);
        }

        if ($stmt->execute()) {
            $mensagem = "Usuário atualizado com sucesso!";
            $tipo = "ok";
            $usuario = ["id" => $id, "nome" => $nome, "email" => $email, "admin" => $admin];
        } else {
            $mensagem = "Erro ao atualizar usuário.";
            $tipo = "erro";
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
    <title>Dreams Books - Editar Usuário</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include "header.php"; ?>

    <main>
        <div class="formulario">
            <h1>Editar Usuário</h1>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $usuario["id"] ?>">

                <label>Nome</label>
                <div class="campo">
                    <span class="icone">👤</span>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario["nome"]) ?>" required>
                </div>

                <label>E-mail</label>
                <div class="campo">
                    <span class="icone">✉️</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario["email"]) ?>" required>
                </div>

                <label>Tipo de conta</label>
                <div class="opcoes" style="justify-content:flex-start;">
                    <input type="checkbox" name="admin" <?= $usuario["admin"] == 1 ? "checked" : "" ?>>
                    <span>Administrador</span>
                </div>

                <label>Nova senha (deixe vazio para manter)</label>
                <div class="campo">
                    <span class="icone">🔒</span>
                    <input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres">
                </div>

                <button type="submit">Salvar alterações</button>
            </form>

            <?php if ($mensagem != ""): ?>
                <div class="mensagem <?= $tipo ?>"><?= $mensagem ?></div>
            <?php endif; ?>

            <div class="rodape">
                <a href="listar_usuarios.php">← Voltar para usuários</a>
            </div>
        </div>
    </main>
</body>
</html>