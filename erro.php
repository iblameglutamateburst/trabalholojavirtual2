<?php
$servidor = "sqlXXX.infinityfree.com";
$usuario = "if0_12345678";
$senha = "a_senha_do_painel";
$banco = "if0_12345678_biblioteca";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");
?>
