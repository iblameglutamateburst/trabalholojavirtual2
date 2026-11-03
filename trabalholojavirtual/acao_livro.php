<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require "conexao.php";

$isbn = (int)($_POST["isbn"] ?? 0);
$acao = $_POST["acao"] ?? "";
$uid = (int)$_SESSION["id"];

if ($isbn == 0 || $acao == "") {
    header("Location: catalogo.php");
    exit;
}

if ($acao == "salvar") {
    $conexao->query("INSERT IGNORE INTO favoritos (usuario_id, isbn) VALUES ($uid, $isbn)");
} else if ($acao == "remover") {
    $conexao->query("DELETE FROM favoritos WHERE usuario_id = $uid AND isbn = $isbn");
} else if ($acao == "avaliar") {
    $nota = (int)($_POST["nota"] ?? 0);
    $comentario = mysqli_real_escape_string($conexao, trim($_POST["comentario"] ?? ""));

    if ($nota >= 1 && $nota <= 5) {
        $conexao->query("INSERT INTO avaliacoes (usuario_id, isbn, nota, comentario)
                         VALUES ($uid, $isbn, $nota, '$comentario')
                         ON DUPLICATE KEY UPDATE nota = $nota, comentario = '$comentario'");
    }
}

if (($_GET["voltar"] ?? $_POST["voltar"] ?? "") === "conta") {
    header("Location: conta.php?aba=favoritos");
} else {
    header("Location: livro.php?isbn=" . $isbn);
}
exit;