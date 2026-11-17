<?php
header("Content-Type: application/json; charset=utf-8");

if (isset($_GET["teste"])) {
    header("Content-Type: text/plain; charset=utf-8");
    echo "PHP: " . PHP_VERSION . "\n";
    echo "curl: " . (function_exists("curl_init") ? "OK" : "AUSENTE") . "\n";
    echo "allow_url_fopen: " . ini_get("allow_url_fopen") . "\n";
    exit;
}

$LOJA_CEP    = "93410324";
$LOJA_RUA    = "Rua Guia Lopes";
$LOJA_NUMERO = "4300";
$LOJA_BAIRRO = "Boa Vista";
$LOJA_CIDADE = "Novo Hamburgo";
$LOJA_UF     = "RS";
$LOJA_LAT    = -29.7118;
$LOJA_LON    = -51.1102;

$TARIFA_BASE        = 5.00;
$TARIFA_POR_KM      = 0.80;
$FRETE_MINIMO       = 15.00;
$FRETE_MAXIMO       = 45.00;
$FRETE_GRATIS_ACIMA = 200.00;

$CONTATO = "contato@dreamsbooks.com";

$CACHE_DIR = __DIR__ . "/cache_frete";
if (!is_dir($CACHE_DIR)) @mkdir($CACHE_DIR, 0777, true);

function http_json($url, $contato) {
    if (!function_exists("curl_init")) return null;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => "DreamsBooks/1.0 (" . $contato . ")"
    ]);
    $r   = curl_exec($ch);
    $cod = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$r || $cod >= 400) return null;
    $d = json_decode($r, true);
    return is_array($d) ? $d : null;
}

function cache_get($dir, $chave) {
    $f = $dir . "/" . md5($chave) . ".json";
    if (is_file($f) && (time() - filemtime($f)) < 604800) {
        $c = file_get_contents($f);
        if ($c !== false) return json_decode($c, true);
    }
    return null;
}

function cache_set($dir, $chave, $valor) {
    @file_put_contents($dir . "/" . md5($chave) . ".json", json_encode($valor));
}

function geocodificar_endereco($rua, $numero, $bairro, $cidade, $uf, $cep, $dir, $contato) {
    $chave = "geo_end_" . $rua . "|" . $numero . "|" . $bairro . "|" . $cidade . "|" . $uf . "|" . $cep;
    $cache = cache_get($dir, $chave);
    if ($cache) return $cache;

    $logradouro = trim($numero . " " . $rua);

    $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=br"
         . "&street=" . urlencode($logradouro)
         . "&city=" . urlencode($cidade)
         . "&state=" . urlencode($uf)
         . "&postalcode=" . urlencode($cep);

    $j = http_json($url, $contato);

    if (!$j || empty($j[0]["lat"])) {
        $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=br&q="
             . urlencode($logradouro . ", " . $bairro . ", " . $cidade . ", " . $uf . ", Brasil");
        $j = http_json($url, $contato);
    }

    if (!$j || empty($j[0]["lat"])) return null;

    $c = ["lat" => (float)$j[0]["lat"], "lon" => (float)$j[0]["lon"]];
    cache_set($dir, $chave, $c);
    return $c;
}

function geocodificar_cidade($cidade, $uf, $dir, $contato) {
    $chave = "geo_cid_" . $cidade . "_" . $uf;
    $cache = cache_get($dir, $chave);
    if ($cache) return $cache;

    $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=br&q="
         . urlencode($cidade . ", " . $uf . ", Brasil");

    $j = http_json($url, $contato);
    if (!$j || empty($j[0]["lat"])) return null;

    $c = ["lat" => (float)$j[0]["lat"], "lon" => (float)$j[0]["lon"]];
    cache_set($dir, $chave, $c);
    return $c;
}

function distancia_km($a, $b, $dir, $contato) {
    $chave = "dist_" . round($a["lat"], 5) . "," . round($a["lon"], 5)
           . "_" . round($b["lat"], 5) . "," . round($b["lon"], 5);
    $cache = cache_get($dir, $chave);
    if ($cache !== null) return $cache;

    $url = "https://router.project-osrm.org/route/v1/driving/"
         . $a["lon"] . "," . $a["lat"] . ";" . $b["lon"] . "," . $b["lat"]
         . "?overview=false";

    $j = http_json($url, $contato);
    if (!$j || !isset($j["routes"][0]["distance"])) return null;

    $km = round($j["routes"][0]["distance"] / 1000, 1);
    cache_set($dir, $chave, $km);
    return $km;
}

$cep      = preg_replace('/\D/', '', $_GET["cep"] ?? "");
$subtotal = (float)($_GET["subtotal"] ?? 0);

if (strlen($cep) !== 8) {
    echo json_encode(["ok" => false, "erro" => "Informe um CEP com 8 numeros."]);
    exit;
}

$end = http_json("https://viacep.com.br/ws/" . $cep . "/json/", $CONTATO);

if (!$end || !empty($end["erro"]) || empty($end["localidade"])) {
    echo json_encode(["ok" => false, "erro" => "CEP nao encontrado."]);
    exit;
}

$rua_dest    = trim($end["logradouro"] ?? "");
$bairro_dest = trim($end["bairro"] ?? "");
$cidade      = $end["localidade"];
$uf          = $end["uf"];

$mesma_cidade = (mb_strtolower($cidade) === mb_strtolower($LOJA_CIDADE))
             && (strtoupper($uf) === strtoupper($LOJA_UF));

$origem = geocodificar_endereco($LOJA_RUA, $LOJA_NUMERO, $LOJA_BAIRRO, $LOJA_CIDADE, $LOJA_UF, $LOJA_CEP, $CACHE_DIR, $CONTATO);
if (!$origem) $origem = ["lat" => $LOJA_LAT, "lon" => $LOJA_LON];

$destino = null;

if ($rua_dest !== "") {
    $destino = geocodificar_endereco($rua_dest, "", $bairro_dest, $cidade, $uf, $cep, $CACHE_DIR, $CONTATO);
}

if (!$destino) {
    $destino = geocodificar_cidade($cidade, $uf, $CACHE_DIR, $CONTATO);
}

$km = null;

if ($cep === $LOJA_CEP) {
    $km = 0.0;
} else if ($origem && $destino) {
    $km = distancia_km($origem, $destino, $CACHE_DIR, $CONTATO);
    if ($km !== null && $km < 0.2) $km = 0.0;
}

if ($km !== null) {
    $valor  = $TARIFA_BASE + ($km * $TARIFA_POR_KM);
    $prazo  = max(1, (int)ceil($km / 500) + 1);
    $metodo = "distancia";
} else {
    $tabela = [0=>45, 1=>45, 2=>45, 3=>45, 4=>45, 5=>45, 6=>45, 7=>45, 8=>40, 9=>25];
    $valor  = $tabela[(int)$cep[0]] ?? 45;
    $prazo  = 5;
    $km     = null;
    $metodo = "faixa-cep";
}

if ($valor < $FRETE_MINIMO) $valor = $FRETE_MINIMO;
if ($valor > $FRETE_MAXIMO) $valor = $FRETE_MAXIMO;

$gratis      = ($FRETE_GRATIS_ACIMA > 0 && $subtotal >= $FRETE_GRATIS_ACIMA);
$valor_final = $gratis ? 0 : round($valor, 2);

echo json_encode([
    "ok"       => true,
    "cep"      => $cep,
    "rua"      => $rua_dest,
    "bairro"   => $bairro_dest,
    "origem"   => $LOJA_CIDADE . " / " . $LOJA_UF,
    "destino"  => $cidade . " / " . $uf,
    "local"    => $mesma_cidade,
    "km"       => $km,
    "valor"    => $valor_final,
    "gratis"   => $gratis,
    "prazo"    => $prazo,
    "metodo"   => $metodo
], JSON_UNESCAPED_UNICODE);