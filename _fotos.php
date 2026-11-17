<?php
define("PASTA_FOTOS", __DIR__ . "/fotos");
define("URL_FOTOS", "fotos");

function garantir_pasta_fotos() {
    if (!is_dir(PASTA_FOTOS)) {
        @mkdir(PASTA_FOTOS, 0777, true);
    }
}

function salvar_foto($arquivo, $isbn, &$erro) {
    if (!is_array($arquivo) || $arquivo["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo["error"] !== UPLOAD_ERR_OK) {
        $erro = "Falha no envio da imagem.";
        return null;
    }

    if ($arquivo["size"] > 5 * 1024 * 1024) {
        $erro = "A imagem deve ter no máximo 5 MB.";
        return null;
    }

    $ext = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
    $permitidas = ["jpg", "jpeg", "png", "gif", "webp"];

    if (!in_array($ext, $permitidas, true)) {
        $erro = "Formato inválido. Use JPG, PNG, GIF ou WEBP.";
        return null;
    }

    garantir_pasta_fotos();

    $base = preg_replace('/\D/', '', (string)$isbn);
    if ($base === "") {
        $base = "livro";
    }

    $nome = $base . "_" . time() . "_" . bin2hex(random_bytes(3)) . "." . $ext;

    if (!move_uploaded_file($arquivo["tmp_name"], PASTA_FOTOS . "/" . $nome)) {
        $erro = "Não foi possível salvar a imagem.";
        return null;
    }

    reduzir_foto(PASTA_FOTOS . "/" . $nome);

    return URL_FOTOS . "/" . $nome;
}

function reduzir_foto($caminho, $largura_max = 600) {
    if (!function_exists("imagecreatetruecolor")) {
        return;
    }

    $info = @getimagesize($caminho);
    if (!$info) {
        return;
    }

    $largura = $info[0];
    $altura  = $info[1];

    if ($largura <= $largura_max) {
        return;
    }

    $nova_altura = (int)round($altura * ($largura_max / $largura));
    $tipo = $info[2];

    switch ($tipo) {
        case IMAGETYPE_JPEG: $img = @imagecreatefromjpeg($caminho); break;
        case IMAGETYPE_PNG:  $img = @imagecreatefrompng($caminho);  break;
        case IMAGETYPE_GIF:  $img = @imagecreatefromgif($caminho);  break;
        case IMAGETYPE_WEBP: $img = function_exists("imagecreatefromwebp") ? @imagecreatefromwebp($caminho) : null; break;
        default: return;
    }

    if (!$img) {
        return;
    }

    $nova = imagecreatetruecolor($largura_max, $nova_altura);

    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_WEBP) {
        imagealphablending($nova, false);
        imagesavealpha($nova, true);
    }

    imagecopyresampled($nova, $img, 0, 0, 0, 0, $largura_max, $nova_altura, $largura, $altura);

    switch ($tipo) {
        case IMAGETYPE_JPEG: imagejpeg($nova, $caminho, 82); break;
        case IMAGETYPE_PNG:  imagepng($nova, $caminho, 8);   break;
        case IMAGETYPE_GIF:  imagegif($nova, $caminho);      break;
        case IMAGETYPE_WEBP: if (function_exists("imagewebp")) imagewebp($nova, $caminho, 82); break;
    }

    imagedestroy($img);
    imagedestroy($nova);
}

function apagar_foto($foto) {
    if (empty($foto)) {
        return;
    }

    $foto = str_replace("\\", "/", $foto);
    $caminho = realpath(__DIR__ . "/" . ltrim($foto, "/"));
    $pasta = realpath(PASTA_FOTOS);

    if ($caminho && $pasta && strpos($caminho, $pasta) === 0 && is_file($caminho)) {
        @unlink($caminho);
    }
}

function foto_url($foto) {
    if (empty($foto)) {
        return null;
    }

    $foto = str_replace("\\", "/", $foto);

    if (is_file(__DIR__ . "/" . ltrim($foto, "/"))) {
        return $foto;
    }

    $alt = URL_FOTOS . "/" . basename($foto);

    if (is_file(__DIR__ . "/" . $alt)) {
        return $alt;
    }

    return null;
}