<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require "conexao.php";

$isbn = (int)($_GET["isbn"] ?? 0);
$uid = (int)$_SESSION["id"];
$origem = $_GET["origem"] ?? "index.php";

if ($origem == "" || str_starts_with($origem, "http") || str_starts_with($origem, "//")) {
    $origem = "index.php";
}

if ($isbn > 0) {
    $stmt = $conexao->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND isbn = ?");
    $stmt->bind_param("ii", $uid, $isbn);
    $stmt->execute();
    $ja_favoritado = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($ja_favoritado) {
        $stmt = $conexao->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND isbn = ?");
    } else {
        $stmt = $conexao->prepare("INSERT IGNORE INTO favoritos (usuario_id, isbn) VALUES (?, ?)");
    }

    $stmt->bind_param("ii", $uid, $isbn);
    $stmt->execute();
    $stmt->close();
}

mysqli_close($conexao);
header("Location: " . $origem);
exit;