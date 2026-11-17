<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

$uid = (int)$_SESSION["id"];

function buscar_um($conexao, $sql) {
    try {
        $res = $conexao->query($sql);

        if (!$res || !($res instanceof mysqli_result)) {
            return null;
        }

        $row = $res->fetch_assoc();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function contar($conexao, $sql) {
    try {
        $res = $conexao->query($sql);

        if (!$res || !($res instanceof mysqli_result)) {
            return 0;
        }

        $row = $res->fetch_assoc();
        return $row ? (int)reset($row) : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

$nome  = $_SESSION["nome"] ?? "";
$email = $_SESSION["email"] ?? "";
$admin = (int)($_SESSION["admin"] ?? 0);

$tabelas = ["usuarios", "usuario", "clientes", "cliente", "users", "user"];

foreach ($tabelas as $tabela) {
    $row = buscar_um($conexao, "SELECT * FROM $tabela WHERE id = $uid LIMIT 1");

    if ($row) {
        foreach (["nome", "name", "nome_completo"] as $k) {
            if (!empty($row[$k])) { $nome = $row[$k]; break; }
        }

        if (!empty($row["email"])) {
            $email = $row["email"];
        }

        foreach (["admin", "is_admin", "administrador"] as $k) {
            if (isset($row[$k])) { $admin = (int)$row[$k]; break; }
        }

        break;
    }
}

if (trim($nome) === "") {
    $nome = $email !== "" ? strstr($email, "@", true) : "Usuário";
}

$total_compras = contar($conexao, "SELECT COUNT(*) AS t FROM pedidos WHERE usuario_id = $uid");

if ($total_compras === 0) {
    $total_compras = contar($conexao, "SELECT COUNT(*) AS t FROM pedidos WHERE id_usuario = $uid");
}

$total_favoritos = contar($conexao, "SELECT COUNT(*) AS t FROM favoritos WHERE usuario_id = $uid");

if ($total_favoritos === 0) {
    $total_favoritos = contar($conexao, "SELECT COUNT(*) AS t FROM favoritos WHERE id_usuario = $uid");
}

$conexao->close();

$inicial = function_exists("mb_substr")
    ? mb_strtoupper(mb_substr($nome, 0, 1, "UTF-8"), "UTF-8")
    : strtoupper(substr($nome, 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Minha conta</title>
    <link rel="stylesheet" href="style.css">

    <style>
    .mc-wrap {
        max-width: 760px;
        margin: 0 auto;
        padding-bottom: 50px;
        box-sizing: border-box;
    }

    .mc-titulo {
        color: #4d1933;
        font-size: 24px;
        margin: 0 0 18px;
    }

    .mc-card {
        background: #fff;
        border-radius: 16px;
        padding: 26px 24px;
        border: 0;
        box-shadow: 0 5px 22px rgba(77, 25, 51, .10);
        box-sizing: border-box;
    }

    .mc-topo {
        display: flex;
        align-items: center;
        gap: 16px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f4eef7;
    }

    .mc-avatar {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #7b2d5e;
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        text-transform: uppercase;
    }

    .mc-dados {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .mc-nome {
        font-size: 19px;
        font-weight: 700;
        color: #4d1933;
        line-height: 1.2;
    }

    .mc-email {
        font-size: 13px;
        color: #8a7a90;
        word-break: break-all;
    }

    .mc-selo {
        align-self: flex-start;
        margin-top: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        background: #f5eef8;
        color: #7b2d5e;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
    }

    .mc-stats {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .mc-stat {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 15px 10px;
        background: #faf6fc;
        border: 1px solid #f1e7f5;
        border-radius: 12px;
    }

    .mc-stat strong {
        font-size: 23px;
        color: #7b2d5e;
        line-height: 1.1;
    }

    .mc-stat span {
        font-size: 12px;
        color: #8a7a90;
    }

    .mc-subtitulo {
        color: #4d1933;
        font-size: 17px;
        margin: 28px 0 14px;
    }

    .mc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .mc-acao {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        background: #fff;
        border: 1px solid #ece1f2;
        border-radius: 12px;
        color: #4d1933;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: border-color .16s, background .16s, box-shadow .16s, transform .16s;
    }

    .mc-acao:hover {
        border-color: #c9a3d8;
        background: #fdfaff;
        box-shadow: 0 4px 14px rgba(123, 45, 94, .13);
        transform: translateY(-1px);
    }

    .mc-icone {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #f5eef8;
        font-size: 16px;
        line-height: 1;
    }

    .mc-acao.sair {
        color: #b93c2e;
        border-color: #f3dcd8;
    }

    .mc-acao.sair .mc-icone {
        background: #fdeeec;
    }

    .mc-acao.sair:hover {
        background: #fff8f7;
        border-color: #e8bcb5;
        box-shadow: 0 4px 14px rgba(185, 60, 46, .12);
    }

    @media (max-width: 560px) {
        .mc-grid { grid-template-columns: 1fr; }

        .mc-topo {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <main class="mc-wrap">
        <h2 class="mc-titulo">Minha conta</h2>

        <div class="mc-card">
            <div class="mc-topo">
                <div class="mc-avatar"><?= htmlspecialchars($inicial) ?></div>

                <div class="mc-dados">
                    <span class="mc-nome"><?= htmlspecialchars($nome) ?></span>

                    <?php if ($email !== ""): ?>
                        <span class="mc-email"><?= htmlspecialchars($email) ?></span>
                    <?php endif; ?>

                    <?php if ($admin === 1): ?>
                        <span class="mc-selo">Administrador</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mc-stats">
                <div class="mc-stat">
                    <strong><?= $total_compras ?></strong>
                    <span>compras</span>
                </div>
                <div class="mc-stat">
                    <strong><?= $total_favoritos ?></strong>
                    <span>favoritos</span>
                </div>
            </div>
        </div>

        <h3 class="mc-subtitulo">Ações rápidas</h3>

        <div class="mc-grid">
            <a href="editar_dados.php" class="mc-acao">
                <span class="mc-icone">✏️</span> Editar dados e senha
            </a>
            <a href="meus_pedidos.php" class="mc-acao">
                <span class="mc-icone">📦</span> Meus pedidos
            </a>
            <a href="favoritos.php" class="mc-acao">
                <span class="mc-icone">💖</span> Meus favoritos
            </a>
            <a href="carrinho.php" class="mc-acao">
                <span class="mc-icone">🛒</span> Meu carrinho
            </a>
            <a href="livros.php" class="mc-acao">
                <span class="mc-icone">📕</span> Explorar catálogo
            </a>

            <?php if ($admin === 1): ?>
                <a href="admin.php" class="mc-acao">
                    <span class="mc-icone">⚙️</span> Área admin
                </a>
            <?php endif; ?>

            <a href="logout.php" class="mc-acao sair">
                <span class="mc-icone">🚪</span> Sair da conta
            </a>
        </div>
    </main>
        <?php include "footer.php"; ?>
</body>
</html>