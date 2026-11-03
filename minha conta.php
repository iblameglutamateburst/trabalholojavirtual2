<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$sql = "SELECT nome, email, admin FROM usuario WHERE id = ?";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dreams Books - Minha conta</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <h1>Dreams Books</h1>
        <nav>
            <a href="index.php">Início</a>
            <a href="catalogo.php">Catálogo</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <h2>Minha conta</h2>
        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario["nome"]) ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario["email"]) ?></p>
        <p><strong>Tipo:</strong> <?= $usuario["admin"] == 1 ? "Administrador" : "Usuário comum" ?></p>
    </main>
</body>
</html>