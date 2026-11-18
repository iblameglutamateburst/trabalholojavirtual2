<?php
require_once "conexao.php";

header("Content-Type: text/html; charset=UTF-8");

function linha($rotulo, $valor) {
    echo "<tr><td style='padding:6px 12px;border-bottom:1px solid #eee;font-weight:600;'>"
       . htmlspecialchars($rotulo) . "</td><td style='padding:6px 12px;border-bottom:1px solid #eee;'>"
       . htmlspecialchars((string)$valor) . "</td></tr>";
}

echo "<html><body style='font-family:monospace;padding:20px;background:#fafafa;'>";
echo "<h2 style='font-family:sans-serif;'>Diagnóstico de capas</h2>";

/* 1. Colunas da tabela */
echo "<h3>1. Colunas da tabela livros</h3><table style='background:#fff;border-collapse:collapse;'>";

$tem_foto = false;

try {
    $res = $conexao->query("SHOW COLUMNS FROM livros");

    if ($res instanceof mysqli_result) {
        while ($c = $res->fetch_assoc()) {
            if (strtolower($c["Field"]) === "foto") {
                $tem_foto = true;
            }

            linha($c["Field"], $c["Type"]);
        }
    }
} catch (Throwable $e) {
    linha("ERRO", $e->getMessage());
}

echo "</table>";

/* 2. Coluna foto existe? */
echo "<h3>2. Coluna foto</h3>";

if ($tem_foto) {
    echo "<p style='color:green;'>✓ A coluna <b>foto</b> existe.</p>";
} else {
    echo "<p style='color:#b93c2e;'>✗ A coluna <b>foto</b> NÃO existe. Rode:<br>"
       . "<code>ALTER TABLE livros ADD COLUMN foto VARCHAR(255) NULL;</code></p>";
}

/* 3. Conteúdo da coluna */
echo "<h3>3. O que está gravado</h3>";

if ($tem_foto) {
    echo "<table style='background:#fff;border-collapse:collapse;'>";

    try {
        $res = $conexao->query("SELECT isbn, titulo, foto FROM livros ORDER BY titulo");

        if ($res instanceof mysqli_result) {
            while ($l = $res->fetch_assoc()) {
                $v = $l["foto"];
                $txt = ($v === null || trim((string)$v) === "") ? "VAZIO (NULL)" : $v;
                linha($l["titulo"] . " [" . $l["isbn"] . "]", $txt);
            }
        }
    } catch (Throwable $e) {
        linha("ERRO", $e->getMessage());
    }

    echo "</table>";
}

/* 4. Pasta fotos */
echo "<h3>4. Arquivos na pasta fotos/</h3>";

$dir = __DIR__ . "/fotos";

if (!is_dir($dir)) {
    echo "<p style='color:#b93c2e;'>✗ A pasta <b>fotos</b> NÃO existe em " . htmlspecialchars(__DIR__) . "</p>";
} else {
    $arquivos = @scandir($dir);
    $lista = [];

    foreach ((array)$arquivos as $a) {
        if ($a === "." || $a === "..") {
            continue;
        }

        if (is_file($dir . "/" . $a)) {
            $lista[] = $a . " (" . round(filesize($dir . "/" . $a) / 1024) . " KB)";
        }
    }

    if (empty($lista)) {
        echo "<p style='color:#b93c2e;'>✗ A pasta existe mas está VAZIA.</p>";
    } else {
        echo "<ul>";

        foreach ($lista as $f) {
            echo "<li>" . htmlspecialchars($f) . "</li>";
        }

        echo "</ul>";
    }
}

/* 5. Teste da função */
echo "<h3>5. Teste do foto_url()</h3>";

if (is_file(__DIR__ . "/_fotos.php")) {
    require_once __DIR__ . "/_fotos.php";
    echo "<p style='color:green;'>✓ _fotos.php encontrado.</p>";
} else {
    echo "<p style='color:#b93c2e;'>✗ _fotos.php NÃO está na raiz do projeto.</p>";
}

echo "<table style='background:#fff;border-collapse:collapse;'>";

if ($tem_foto) {
    try {
        $res = $conexao->query("SELECT titulo, foto FROM livros ORDER BY titulo");

        if ($res instanceof mysqli_result) {
            while ($l = $res->fetch_assoc()) {
                if (function_exists("foto_url")) {
                    $r = foto_url($l["foto"] ?? "");
                    $out = ($r === "") ? "RETORNOU VAZIO → vai mostrar 📕" : $r;
                } else {
                    $out = "função foto_url() não existe";
                }

                linha($l["titulo"], $out);
            }
        }
    } catch (Throwable $e) {
        linha("ERRO", $e->getMessage());
    }
}

echo "</table>";

/* 6. Extensão GD */
echo "<h3>6. Extensão GD (redimensionamento)</h3>";
echo function_exists("imagecreatefromstring")
    ? "<p style='color:green;'>✓ GD ativa.</p>"
    : "<p style='color:#b93c2e;'>✗ GD desativada. O upload funciona, mas não redimensiona.</p>";

echo "</body></html>";

$conexao->close();