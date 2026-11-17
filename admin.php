<?php
session_start();

if (!isset($_SESSION["id"]) || (int)($_SESSION["admin"] ?? 0) !== 1) {
    header("Location: login.php");
    exit;
}

require_once "conexao.php";

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

function contar_tabelas($conexao, $tabelas, $sql) {
    foreach ($tabelas as $tabela) {
        $valor = contar($conexao, str_replace("{t}", $tabela, $sql));

        if ($valor > 0) {
            return $valor;
        }
    }

    return 0;
}

$total_livros = contar($conexao, "SELECT COUNT(*) AS t FROM livros");

$total_exemplares = contar_tabelas(
    $conexao,
    ["exemplares", "exemplar", "copias", "copia", "estoque"],
    "SELECT COUNT(*) AS t FROM {t}"
);

$total_usuarios = contar_tabelas(
    $conexao,
    ["usuarios", "usuario", "clientes", "cliente", "users", "user"],
    "SELECT COUNT(*) AS t FROM {t}"
);

$total_categorias = contar($conexao, "SELECT COUNT(DISTINCT categoria) AS t FROM livros WHERE categoria IS NOT NULL AND categoria <> ''");

if ($total_categorias === 0) {
    $total_categorias = contar($conexao, "SELECT COUNT(*) AS t FROM categorias");
}

$total_vendas = contar($conexao, "SELECT COUNT(*) AS t FROM pedidos");

$conexao->close();

$stats = [
    ["valor" => $total_livros,     "label" => "Livros",     "icone" => "📚"],
    ["valor" => $total_exemplares, "label" => "Exemplares", "icone" => "📦"],
    ["valor" => $total_usuarios,   "label" => "Usuários",   "icone" => "👤"],
    ["valor" => $total_categorias, "label" => "Categorias", "icone" => "🏷️"],
    ["valor" => $total_vendas,     "label" => "Vendas",     "icone" => "💰"]
];

$acoes = [
    [
        "href"  => "cadastrar_livro.php",
        "icone" => "📕",
        "titulo" => "Cadastrar livro",
        "texto" => "Adiciona um novo livro ao acervo"
    ],
    [
        "href"  => "cadastrar_exemplar.php",
        "icone" => "📦",
        "titulo" => "Cadastrar exemplar",
        "texto" => "Registra uma cópia física"
    ],
    [
        "href"  => "cadastrar_editora.php",
        "icone" => "🏢",
        "titulo" => "Cadastrar editora",
        "texto" => "Vincula editoras aos livros"
    ],
    [
        "href"  => "cadastrar_autor.php",
        "icone" => "✍️",
        "titulo" => "Cadastrar autor",
        "texto" => "Vincula autores aos livros"
    ],
    [
        "href"  => "categorias.php",
        "icone" => "🏷️",
        "titulo" => "Categorias",
        "texto" => "Cria e remove categorias do filtro"
    ],
    [
        "href"  => "listar_livros.php",
        "icone" => "🗂️",
        "titulo" => "Livros cadastrados",
        "texto" => "Ver, editar e gerenciar o acervo"
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dreams Books - Área do administrador</title>
    <link rel="stylesheet" href="style.css">

    <style>
    .ad-wrap {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 16px 56px;
        box-sizing: border-box;
    }

    .ad-head {
        margin-bottom: 22px;
    }

    .ad-head h2 {
        color: #4d1933;
        font-size: 25px;
        margin: 0 0 4px;
    }

    .ad-head p {
        color: #8a7a90;
        font-size: 14px;
        margin: 0;
    }

    .ad-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 32px;
    }

    .ad-stat {
        background: #fff;
        border: 1px solid #f1e7f5;
        border-radius: 14px;
        padding: 16px 14px;
        box-shadow: 0 3px 14px rgba(77, 25, 51, .07);
        box-sizing: border-box;
    }

    .ad-stat .ad-stat-ico {
        font-size: 16px;
        line-height: 1;
        display: block;
        margin-bottom: 8px;
        opacity: .85;
    }

    .ad-stat strong {
        display: block;
        font-size: 26px;
        line-height: 1;
        color: #7b2d5e;
        margin-bottom: 4px;
    }

    .ad-stat span {
        font-size: 12px;
        color: #8a7a90;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .ad-section {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .ad-section h3 {
        color: #4d1933;
        font-size: 18px;
        margin: 0;
    }

    .ad-section small {
        color: #a595ab;
        font-size: 12px;
    }

    .ad-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .ad-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 20px 18px;
        background: #fff;
        border: 1px solid #ece1f2;
        border-radius: 14px;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(77, 25, 51, .05);
        transition: border-color .16s, box-shadow .16s, transform .16s;
    }

    .ad-card:hover {
        border-color: #c9a3d8;
        box-shadow: 0 8px 22px rgba(123, 45, 94, .15);
        transform: translateY(-2px);
    }

    .ad-card .ad-ico {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #f5eef8;
        font-size: 19px;
        line-height: 1;
        margin-bottom: 2px;
    }

    .ad-card strong {
        font-size: 15px;
        color: #4d1933;
        line-height: 1.25;
    }

    .ad-card p {
        font-size: 13px;
        color: #7a6b80;
        line-height: 1.45;
        margin: 0;
    }

    @media (max-width: 900px) {
        .ad-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ad-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 560px) {
        .ad-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ad-grid { grid-template-columns: 1fr; }
        .ad-head h2 { font-size: 21px; }
    }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <main class="ad-wrap">
        <div class="ad-head">
            <h2>Área do administrador</h2>
        </div>

        <div class="ad-stats">
            <?php foreach ($stats as $s): ?>
                <div class="ad-stat">
                    <span class="ad-stat-ico"><?= $s["icone"] ?></span>
                    <strong><?= (int)$s["valor"] ?></strong>
                    <span><?= htmlspecialchars($s["label"]) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="ad-section">
            <h3>Gerenciar</h3>
            <small><?= count($acoes) ?> atalhos</small>
        </div>

        <div class="ad-grid">
            <?php foreach ($acoes as $a): ?>
                <a href="<?= htmlspecialchars($a["href"]) ?>" class="ad-card">
                    <span class="ad-ico"><?= $a["icone"] ?></span>
                    <strong><?= htmlspecialchars($a["titulo"]) ?></strong>
                    <p><?= htmlspecialchars($a["texto"]) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include "footer.php"; ?>
</body>
</html>