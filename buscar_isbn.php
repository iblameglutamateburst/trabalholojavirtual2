<?php
header("Content-Type: application/json; charset=utf-8");

if (isset($_GET["teste"])) {
    header("Content-Type: text/plain; charset=utf-8");
    echo "PHP: " . PHP_VERSION . "\n";
    echo "curl: " . (function_exists("curl_init") ? "OK" : "AUSENTE") . "\n";
    echo "allow_url_fopen: " . ini_get("allow_url_fopen") . "\n";
    exit;
}

$isbn   = preg_replace('/\D/', '', $_GET["isbn"] ?? "");
$titulo = trim($_GET["titulo"] ?? "");

if (strlen($isbn) !== 10 && strlen($isbn) !== 13 && strlen($titulo) < 3) {
    echo json_encode(["ok" => false, "erro" => "Informe um ISBN de 10 ou 13 numeros, ou um titulo com 3+ letras."]);
    exit;
}

function para13($isbn) {
    if (strlen($isbn) !== 10) return $isbn;
    $base = "978" . substr($isbn, 0, 9);
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma += (int)$base[$i] * ($i % 2 === 0 ? 1 : 3);
    }
    return $base . ((10 - ($soma % 10)) % 10);
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
    $r   = curl_exec($ch);
    $cod = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$r || $cod >= 400) return null;
    $d = json_decode($r, true);
    return is_array($d) ? $d : null;
}

function limpar($valor) {
    if (is_array($valor)) {
        $valor = implode(", ", array_filter($valor, "is_string"));
    }
    $valor = trim((string)$valor);
    if ($valor === "") return "";

    $lixo = ["[author not identified]", "[authors not identified]", "[s.n.]", "[n.d.]",
             "[unknown]", "unknown", "unknown author", "not identified", "n/a"];
    foreach ($lixo as $l) {
        if (mb_strtolower($valor) === mb_strtolower($l)) return "";
    }
    return $valor;
}

function sinopse_obra($key) {
    if (empty($key)) return "";
    $obra = pegar("https://openlibrary.org" . $key . ".json");
    if (!$obra || empty($obra["description"])) return "";
    $d = $obra["description"];
    return limpar(is_array($d) ? ($d["value"] ?? "") : $d);
}

$livro   = null;
$fonte   = "";
$reserva = null; 

if ($isbn !== "") {
    $variantes = [$isbn];
    $v13 = para13($isbn);
    if ($v13 !== $isbn) $variantes[] = $v13;

    foreach ($variantes as $codigo) {
        $j = pegar("https://brasilapi.com.br/api/isbn/v1/" . $codigo);
        if ($j && limpar($j["title"] ?? "") !== "") {
            $livro = [
                "titulo"    => limpar($j["title"]),
                "autor"     => limpar($j["authors"] ?? []),
                "editora"   => limpar($j["publisher"] ?? ""),
                "descricao" => limpar($j["synopsis"] ?? "")
            ];
            $fonte = "BrasilAPI";
            break;
        }
    }

    if (!$livro) {
        foreach ($variantes as $codigo) {
            $j = pegar("https://openlibrary.org/search.json?isbn=" . $codigo . "&limit=1&fields=key,title,author_name,publisher");
            if ($j && !empty($j["docs"][0])) {
                $d = $j["docs"][0];
                $t = limpar($d["title"] ?? "");
                if ($t !== "") {
                    $livro = [
                        "titulo"    => $t,
                        "autor"     => limpar($d["author_name"] ?? []),
                        "editora"   => limpar($d["publisher"][0] ?? ""),
                        "descricao" => sinopse_obra($d["key"] ?? "")
                    ];
                    $fonte = "Open Library";
                    break;
                }
            }
        }
    }

    if (!$livro) {
        foreach ($variantes as $codigo) {
            $j = pegar("https://www.googleapis.com/books/v1/volumes?q=isbn:" . $codigo);
            if ($j && !empty($j["items"][0]["volumeInfo"])) {
                $v = $j["items"][0]["volumeInfo"];
                $livro = [
                    "titulo"    => limpar($v["title"] ?? ""),
                    "autor"     => limpar($v["authors"] ?? []),
                    "editora"   => limpar($v["publishers"][0] ?? ""),
                    "descricao" => limpar($v["description"] ?? "")
                ];
                $fonte = "Google Books";
                break;
            }
        }
    }
}

if (!$livro && $titulo !== "") {

    $j = pegar("https://openlibrary.org/search.json?title=" . urlencode($titulo)
             . "&limit=10&fields=key,title,author_name,publisher,language");

    if ($j && !empty($j["docs"])) {
        $melhor = null;
        $melhorPeso = 0;

        foreach ($j["docs"] as $d) {
            $t = limpar($d["title"] ?? "");
            if ($t === "") continue;

            $a = limpar($d["author_name"] ?? []);
            $e = limpar($d["publisher"][0] ?? "");

            similar_text(mb_strtolower($titulo), mb_strtolower($t), $pct);

            $peso = 1 + ($pct / 50);
            if ($a !== "") $peso += 3;
            if ($e !== "") $peso += 1;

            if ($peso > $melhorPeso) {
                $melhorPeso = $peso;
                $melhor = ["titulo" => $t, "autor" => $a, "editora" => $e, "key" => $d["key"] ?? ""];
            }
        }

        if ($melhor) {
            $melhor["descricao"] = sinopse_obra($melhor["key"]);
            $melhor["peso"] = $melhorPeso;

            if ($melhor["autor"] !== "" || $melhor["descricao"] !== "") {
                $livro = $melhor;
                $fonte = "Open Library (titulo)";
            } else {
                $reserva = $melhor;
            }
        }
    }

    if (!$livro) {
        $j = pegar("https://www.googleapis.com/books/v1/volumes?q=intitle:" . urlencode($titulo) . "&maxResults=5");
        if ($j && !empty($j["items"])) {
            $melhor = null;
            $melhorPeso = 0;

            foreach ($j["items"] as $item) {
                $v = $item["volumeInfo"] ?? [];
                $t = limpar($v["title"] ?? "");
                if ($t === "") continue;

                $a = limpar($v["authors"] ?? []);
                $e = limpar($v["publishers"][0] ?? "");
                $s = limpar($v["description"] ?? "");

                similar_text(mb_strtolower($titulo), mb_strtolower($t), $pct);

                $peso = 1 + ($pct / 50);
                if ($a !== "") $peso += 3;
                if ($s !== "") $peso += 2;
                if ($e !== "") $peso += 1;

                if ($peso > $melhorPeso) {
                    $melhorPeso = $peso;
                    $melhor = ["titulo" => $t, "autor" => $a, "editora" => $e, "descricao" => $s];
                }
            }

            if ($melhor) {
                $livro = $melhor;
                $fonte = "Google Books (titulo)";
            }
        }
    }

    if (!$livro && $reserva) {
        $livro = $reserva;
        $fonte = "Open Library (titulo, dados parciais)";
    }
}

if (!$livro) {
    echo json_encode([
        "ok"   => false,
        "erro" => "Nao encontrado nas bases publicas. Confira se o titulo esta correto ou use o ISBN."
    ]);
    exit;
}

$faltando = [];
if ($livro["autor"] === "")     $faltando[] = "autor";
if ($livro["editora"] === "")   $faltando[] = "editora";
if ($livro["descricao"] === "") $faltando[] = "sinopse";

$livro["ok"]       = true;
$livro["fonte"]    = $fonte;
$livro["faltando"] = $faltando;

echo json_encode($livro, JSON_UNESCAPED_UNICODE);