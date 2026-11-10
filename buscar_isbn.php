<?php
header("Content-Type: application/json; charset=utf-8");

if (isset($_GET["teste"])) {
    header("Content-Type: text/plain; charset=utf-8");
    echo "PHP: " . PHP_VERSION . "\n";
    echo "curl: " . (function_exists("curl_init") ? "OK" : "AUSENTE") . "\n";
    echo "allow_url_fopen: " . ini_get("allow_url_fopen") . "\n\n";

    $url = "https://brasilapi.com.br/api/isbn/v1/9786586029802";
    echo "Testando: " . $url . "\n";

    if (function_exists("curl_init")) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        $r = curl_exec($ch);
        echo "HTTP: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
        echo "ERRO: " . curl_error($ch) . "\n";
        curl_close($ch);
        echo "RETORNO:\n" . substr((string)$r, 0, 600) . "\n";
    } else {
        echo "curl nao instalado neste PHP\n";
    }
    exit;
}

$isbn = preg_replace('/\D/', '', $_GET["isbn"] ?? "");

if (strlen($isbn) < 10) {
    echo json_encode(["ok" => false, "erro" => "ISBN precisa ter 10 ou 13 numeros."]);
    exit;
}

function pegar($url) {
    if (!function_exists("curl_init")) return null;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => "DreamsBooks/1.0"
    ]);
    $r = curl_exec($ch);
    curl_close($ch);
    if (!$r) return null;
    $d = json_decode($r, true);
    return is_array($d) ? $d : null;
}

$livro = null;
$fonte = "";

$j = pegar("https://brasilapi.com.br/api/isbn/v1/" . $isbn);
if ($j && !empty($j["title"])) {
    $livro = [
        "titulo"    => $j["title"],
        "autor"     => implode(", ", array_slice($j["authors"] ?? [], 0, 2)),
        "editora"   => $j["publisher"] ?? "",
        "descricao" => $j["synopsis"] ?? "",
        "categoria" => implode(", ", array_slice($j["subjects"] ?? [], 0, 2))
    ];
    $fonte = "BrasilAPI";
}

if (!$livro) {
    $j = pegar("https://openlibrary.org/search.json?q=" . $isbn . "&limit=1");
    if ($j && !empty($j["docs"][0])) {
        $d = $j["docs"][0];
        $livro = [
            "titulo"    => $d["title"] ?? "",
            "autor"     => implode(", ", array_slice($d["author_name"] ?? [], 0, 2)),
            "editora"   => $d["publisher"][0] ?? "",
            "descricao" => "",
            "categoria" => implode(", ", array_slice($d["subject"] ?? [], 0, 2))
        ];
        $fonte = "Open Library";
    }
}

if (!$livro) {
    $j = pegar("https://www.googleapis.com/books/v1/volumes?q=isbn:" . $isbn);
    if ($j && !empty($j["items"][0]["volumeInfo"])) {
        $v = $j["items"][0]["volumeInfo"];
        $livro = [
            "titulo"    => $v["title"] ?? "",
            "autor"     => implode(", ", array_slice($v["authors"] ?? [], 0, 2)),
            "editora"   => $v["publishers"][0] ?? "",
            "descricao" => $v["description"] ?? "",
            "categoria" => implode(", ", array_slice($v["categories"] ?? [], 0, 2))
        ];
        $fonte = "Google Books";
    }
}

if (!$livro) {
    echo json_encode(["ok" => false, "erro" => "ISBN nao encontrado nas bases publicas."]);
    exit;
}

$livro["ok"] = true;
$livro["fonte"] = $fonte;
echo json_encode($livro, JSON_UNESCAPED_UNICODE);